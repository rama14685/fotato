<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FaceScanController extends Controller
{
    /**
     * Show face scan page
     * 
     * Albums are cached for 1 hour (3600 seconds) to improve performance.
     * Cache should be cleared when albums are created/updated/deleted
     * (actual cache clearing would be in AlbumController which is out of scope).
     */
    public function index()
    {
        $albums = Cache::remember('face_scan_albums', 3600, function () {
            return Album::where('created_at', '>=', now()->subMonth())
                ->with('photographer')
                ->orderBy('event_date', 'desc')
                ->get();
        });
        
        return view('face-scan.index', compact('albums'));
    }

    /**
     * Search photos by face embedding
     * 
     * PRIVACY DESIGN: Client face embeddings are NEVER persisted to the database.
     * 
     * This method receives the client's face embedding vector from the frontend,
     * uses it IN MEMORY ONLY to calculate similarity with photo embeddings,
     * and discards it after returning the search results.
     * 
     * The client embedding exists only for the duration of this request and is
     * never saved to any persistent storage (database, cache, logs, or files).
     * This ensures client biometric data remains private and is not stored.
     * 
     * Requirements: 8.2 (no storage of client embeddings), 8.3 (immediate use only)
     */
    public function search(Request $request)
    {
        $request->validate([
            'embedding_vector'   => 'required|array|size:128',
            'embedding_vector.*' => 'numeric',
            'album_id'           => 'required|exists:albums,id',
            'page'               => 'sometimes|integer|min:1',
        ]);

        // PRIVACY: Client embedding is stored in memory only for this request
        // It will be discarded when this method returns
        $clientEmbedding = $request->embedding_vector;
        $albumId = $request->album_id;
        $page = $request->input('page', 1);
        $perPage = 50;

        // Check if the album is expired
        $album = Album::findOrFail($albumId);
        if ($album->created_at < now()->subMonth()) {
            return response()->json([
                'success' => false,
                'message' => 'Album ini sudah kedaluwarsa.',
            ], 404);
        }

        try {
            // Get all photos with face embeddings in the selected album
            // NOTE: We only READ photo embeddings from database, never WRITE client embeddings
            $photos = \App\Models\Photo::where('album_id', $albumId)
                ->whereHas('faceEmbedding')
                ->with('faceEmbedding')
                ->get();

            $matchedPhotos = [];

            foreach ($photos as $photo) {
                // PRIVACY: Photo embeddings are pre-computed and stored in database
                // Client embedding is only in memory and used for comparison
                $photoEmbedding = json_decode($photo->faceEmbedding->embedding_vector, true);

                // Calculate cosine similarity (in-memory operation only)
                $similarity = $this->cosineSimilarity($clientEmbedding, $photoEmbedding);

                // Threshold: only include if similarity > 0.6
                if ($similarity > 0.6) {
                    $matchedPhotos[] = [
                        'id'             => $photo->id,
                        'watermark_path' => asset('storage/' . $photo->watermark_path),
                        'price'          => $photo->price,
                        'similarity'     => $similarity,
                    ];
                }
            }

            // Sort by similarity (descending)
            usort($matchedPhotos, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });

            // Implement pagination if results exceed 50 photos
            $totalPhotos = count($matchedPhotos);
            $totalPages = ceil($totalPhotos / $perPage);
            
            // Calculate offset and slice the array
            $offset = ($page - 1) * $perPage;
            $paginatedPhotos = array_slice($matchedPhotos, $offset, $perPage);

            // PRIVACY: Client embedding is discarded here when method returns
            // It is never logged, cached, or persisted anywhere
            return response()->json([
                'success'      => true,
                'photos'       => $paginatedPhotos,
                'pagination'   => [
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'per_page'     => $perPage,
                    'total_items'  => $totalPhotos,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Face scan search failed', [
                'user_id'  => auth()->id(),
                'album_id' => $albumId,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed. Please try again',
            ], 500);
        }
    }

    /**
     * Calculate cosine similarity between two vectors.
     *
     * @throws \InvalidArgumentException if vectors have different dimensions
     */
    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        if (count($vec1) !== count($vec2)) {
            throw new \InvalidArgumentException('Vectors must have the same dimension');
        }

        $dotProduct = 0.0;
        $magnitude1 = 0.0;
        $magnitude2 = 0.0;

        for ($i = 0; $i < count($vec1); $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $magnitude1 += $vec1[$i] ** 2;
            $magnitude2 += $vec2[$i] ** 2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}
