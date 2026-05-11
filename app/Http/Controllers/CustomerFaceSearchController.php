<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Services\FaceMatching\FaceMatchingService;
use App\Services\FaceMatching\DTOs\PhotoEmbeddingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CustomerFaceSearchController extends Controller
{
    /**
     * Display customer dashboard with search form
     */
    public function index()
    {
        $user = Auth::user();

        // Verify user is a customer
        if ($user->role !== 'customer') {
            return redirect()->route('dashboard')
                ->with('error', 'Halaman ini hanya untuk customer.');
        }

        // Check if customer has face embedding
        if (!$user->face_embedding_id || !$user->faceEmbedding) {
            return redirect()->route('register.step-two')
                ->with('error', 'Silakan lengkapi registrasi wajah Anda terlebih dahulu.');
        }

        return view('customer.dashboard');
    }

    /**
     * Search albums by event name and/or date
     */
    public function searchAlbums(Request $request)
    {
        $request->validate([
            'event_name' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
        ], [
            'event_date.date' => 'Format tanggal tidak valid.',
        ]);

        // At least one field must be filled
        if (!$request->event_name && !$request->event_date) {
            return back()->withErrors(['search' => 'Mohon isi minimal satu field pencarian.']);
        }

        // Build query
        $query = Album::with('photographer');

        if ($request->event_name) {
            $query->where('title', 'like', '%' . $request->event_name . '%');
        }

        if ($request->event_date) {
            $query->whereDate('event_date', $request->event_date);
        }

        // Order by most recent first
        $albums = $query->orderBy('event_date', 'desc')->get();

        // Add photo count to each album
        $albums->each(function ($album) {
            $album->photo_count = $album->photos()->count();
        });

        return view('customer.albums', [
            'albums' => $albums,
            'searchQuery' => [
                'event_name' => $request->event_name,
                'event_date' => $request->event_date,
            ]
        ]);
    }

    /**
     * View photos in album with automatic face filtering
     */
    public function viewAlbum(Request $request, $albumId, FaceMatchingService $faceMatchingService)
    {
        $user = Auth::user();

        // Get album
        $album = Album::with('photographer')->findOrFail($albumId);

        // Check if customer has face embedding
        if (!$user->faceEmbedding) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Face embedding tidak ditemukan. Silakan lengkapi registrasi wajah Anda.');
        }

        try {
            // Get customer's face embedding
            $customerEmbedding = $this->getCustomerEmbedding($user);

            // Get all photos with face embeddings in this album
            $photos = Photo::where('album_id', $albumId)
                ->whereHas('faceEmbedding')
                ->with('faceEmbedding')
                ->get();

            if ($photos->isEmpty()) {
                return view('customer.photos', [
                    'album' => $album,
                    'photos' => collect(),
                    'noFaceData' => true,
                    'message' => 'Album ini belum memiliki data wajah. Silakan hubungi admin.'
                ]);
            }

            // Prepare photo embeddings for face matching
            $photoEmbeddings = $photos->map(function ($photo) {
                $embedding = json_decode(Crypt::decryptString($photo->faceEmbedding->embedding_vector), true);
                return new PhotoEmbeddingData(
                    photoId: $photo->id,
                    embeddings: [$embedding] // Wrap in array as service expects multiple faces per photo
                );
            })->toArray();

            // Perform face matching
            $matchResults = $faceMatchingService->matchFaces(
                $customerEmbedding,
                $photoEmbeddings,
                $request->query('threshold', 0.6) // Allow threshold override via query param
            );

            // Filter photos that match threshold
            $matchedPhotoIds = collect($matchResults)
                ->filter(fn($result) => $result->matchesThreshold)
                ->pluck('photoId')
                ->toArray();

            // Get matched photos with similarity scores
            $matchedPhotos = $photos->whereIn('id', $matchedPhotoIds)
                ->map(function ($photo) use ($matchResults) {
                    $matchResult = collect($matchResults)->firstWhere('photoId', $photo->id);
                    $photo->similarity_score = $matchResult ? $matchResult->similarityScore : 0;
                    $photo->similarity_percentage = round($matchResult->similarityScore * 100, 1);
                    return $photo;
                })
                ->sortByDesc('similarity_score')
                ->values();

            // Pagination
            $perPage = 25;
            $currentPage = $request->query('page', 1);
            $paginatedPhotos = $matchedPhotos->forPage($currentPage, $perPage);

            return view('customer.photos', [
                'album' => $album,
                'photos' => $paginatedPhotos,
                'totalPhotos' => $matchedPhotos->count(),
                'totalPages' => ceil($matchedPhotos->count() / $perPage),
                'currentPage' => $currentPage,
                'threshold' => $request->query('threshold', 0.6),
                'noMatches' => $matchedPhotos->isEmpty(),
            ]);

        } catch (\Exception $e) {
            Log::error('Face matching error', [
                'user_id' => $user->id,
                'album_id' => $albumId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mencocokkan foto. Silakan coba lagi.');
        }
    }

    /**
     * View all photos in album without face filtering
     */
    public function viewAllPhotos($albumId)
    {
        $album = Album::with('photographer')->findOrFail($albumId);
        $photos = Photo::where('album_id', $albumId)->paginate(25);

        return view('customer.photos-all', [
            'album' => $album,
            'photos' => $photos,
        ]);
    }

    /**
     * Get and decrypt customer's face embedding
     */
    protected function getCustomerEmbedding($user): array
    {
        // Get encrypted embedding
        $encryptedEmbedding = $user->faceEmbedding->embedding_vector;

        // Decrypt embedding
        $embeddingJson = Crypt::decryptString($encryptedEmbedding);
        $embedding = json_decode($embeddingJson, true);

        // Validate embedding
        if (!is_array($embedding) || count($embedding) !== 128) {
            throw new \Exception('Invalid face embedding dimensions');
        }

        // Validate all values are numeric
        foreach ($embedding as $value) {
            if (!is_numeric($value)) {
                throw new \Exception('Invalid face embedding values');
            }
        }

        return $embedding;
    }
}
