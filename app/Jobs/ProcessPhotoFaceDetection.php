<?php

namespace App\Jobs;

use App\Models\FaceEmbedding;
use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoFaceDetection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The photo instance
     */
    protected $photo;

    /**
     * The number of times the job may be attempted
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job
     */
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance
     */
    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        try {
            Log::info("Starting face detection for photo ID: {$this->photo->id}");

            // Check if photo file exists
            if (!Storage::exists($this->photo->original_path)) {
                Log::error("Photo file not found: {$this->photo->original_path}");
                $this->fail(new \Exception("Photo file not found"));
                return;
            }

            // Get photo file path
            $photoPath = Storage::path($this->photo->original_path);

            // Call Python script for face detection
            // Note: This requires Python with face_recognition library installed
            $faceEmbeddings = $this->detectFaces($photoPath);

            if (empty($faceEmbeddings)) {
                Log::warning("No faces detected in photo ID: {$this->photo->id}");
                return;
            }

            // Store face embeddings in database
            foreach ($faceEmbeddings as $embedding) {
                // Validate embedding dimensions
                if (count($embedding) !== 128) {
                    Log::error("Invalid embedding dimensions for photo ID: {$this->photo->id}");
                    continue;
                }

                // Encrypt embedding before storing
                $encryptedEmbedding = Crypt::encryptString(json_encode($embedding));

                // Create or update face embedding record
                FaceEmbedding::updateOrCreate(
                    ['photo_id' => $this->photo->id],
                    ['embedding_vector' => $encryptedEmbedding]
                );

                Log::info("Face embedding stored for photo ID: {$this->photo->id}");
                
                // For now, we only store the first detected face
                // TODO: Support multiple faces per photo
                break;
            }

        } catch (\Exception $e) {
            Log::error("Face detection failed for photo ID: {$this->photo->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Detect faces in photo using Python face_recognition library or Node.js face-api.js
     * 
     * @param string $photoPath
     * @return array Array of face embeddings (128-dimensional arrays)
     */
    protected function detectFaces(string $photoPath): array
    {
        // Try Node.js first (easier setup)
        $result = $this->detectFacesNodeJS($photoPath);
        
        if (!empty($result)) {
            return $result;
        }
        
        // Fallback to Python
        return $this->detectFacesPython($photoPath);
    }
    
    /**
     * Detect faces using Node.js face-api.js
     */
    protected function detectFacesNodeJS(string $photoPath): array
    {
        $scriptPath = base_path('scripts/detect-faces-nodejs.js');
        
        if (!file_exists($scriptPath)) {
            Log::warning("Node.js script not found: {$scriptPath}");
            return [];
        }
        
        // Check if node is available
        $nodeCheck = shell_exec('node --version 2>&1');
        if (!$nodeCheck) {
            Log::warning("Node.js not found in system PATH");
            return [];
        }
        
        $escapedPath = escapeshellarg($photoPath);
        $command = "node " . escapeshellarg($scriptPath) . " {$escapedPath} 2>&1";
        $output = shell_exec($command);
        
        if (!$output) {
            Log::error("Node.js script returned no output for: {$photoPath}");
            return [];
        }
        
        $result = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Failed to parse Node.js script output: " . json_last_error_msg());
            return [];
        }
        
        return $result['embeddings'] ?? [];
    }
    
    /**
     * Detect faces using Python face_recognition library
     */
    protected function detectFacesPython(string $photoPath): array
    {
        $scriptPath = base_path('scripts/detect_faces.py');
        
        if (!file_exists($scriptPath)) {
            Log::error("Python script not found: {$scriptPath}");
            return [];
        }
        
        $escapedPath = escapeshellarg($photoPath);
        $command = "python " . escapeshellarg($scriptPath) . " {$escapedPath} 2>&1";
        $output = shell_exec($command);
        
        if (!$output) {
            Log::error("Python script returned no output for: {$photoPath}");
            return [];
        }
        
        $result = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Failed to parse Python script output: " . json_last_error_msg());
            return [];
        }
        
        return $result['embeddings'] ?? [];
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Face detection job failed permanently for photo ID: {$this->photo->id}", [
            'error' => $exception->getMessage()
        ]);

        // TODO: Mark photo as processing_failed in database
        // $this->photo->update(['processing_status' => 'failed']);
    }
}
