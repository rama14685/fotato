<?php

namespace App\Http\Controllers;

use App\Models\PhotoFace;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BuyerDashboardController extends Controller
{
    /**
     * Match the buyer's registered face against all photo_faces rows
     * using Euclidean distance (threshold < 0.5 = match).
     * Results are grouped by album.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Load the buyer's registered face descriptor
        $userFace = $user->userFace;

        if (!$userFace || empty($userFace->face_descriptor)) {
            return redirect()->route('buyer.register-face')
                ->with('error', 'Face descriptor tidak ditemukan. Silakan daftar wajah terlebih dahulu.');
        }

        $userDescriptor = $userFace->face_descriptor;

        // Validate descriptor dimensions
        if (count($userDescriptor) !== 128) {
            return redirect()->route('buyer.register-face')
                ->with('error', 'Data wajah tidak valid. Silakan daftar ulang.');
        }

        try {
            // Load ALL photo_faces rows with their photo + album relationships
            // This is done server-side to avoid N+1 queries
            $allPhotoFaces = PhotoFace::with(['photo.album'])->get();

            $matchedPhotos = collect();

            foreach ($allPhotoFaces as $photoFace) {
                if (empty($photoFace->face_descriptor)) {
                    continue;
                }

                // Compute Euclidean distance
                $distance = $this->euclideanDistance($userDescriptor, $photoFace->face_descriptor);

                // Threshold: distance < 0.5 = match
                if ($distance < 0.5) {
                    $photo = $photoFace->photo;
                    if (!$photo) {
                        continue;
                    }

                    // Avoid duplicate photos if a photo matched multiple faces
                    if ($matchedPhotos->contains('id', $photo->id)) {
                        continue;
                    }

                    $photo->match_distance = round($distance, 4);
                    $photo->match_score    = round((1 - $distance) * 100, 1); // 0–100%
                    $matchedPhotos->push($photo);
                }
            }

            // Sort: best matches first (lowest distance)
            $matchedPhotos = $matchedPhotos->sortBy('match_distance')->values();

            // Group by album
            $groupedByAlbum = $matchedPhotos->groupBy(function ($photo) {
                return $photo->album_id;
            })->map(function ($photos, $albumId) {
                $album = $photos->first()->album;
                return [
                    'album'  => $album,
                    'photos' => $photos->values(),
                ];
            })->values();

            Log::info('Buyer face matching complete', [
                'user_id'       => $user->id,
                'total_checked' => $allPhotoFaces->count(),
                'total_matched' => $matchedPhotos->count(),
            ]);

            return view('buyer.dashboard', [
                'groupedByAlbum' => $groupedByAlbum,
                'totalMatched'   => $matchedPhotos->count(),
                'totalChecked'   => $allPhotoFaces->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Buyer dashboard matching error', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return view('buyer.dashboard', [
                'groupedByAlbum' => collect(),
                'totalMatched'   => 0,
                'totalChecked'   => 0,
                'error'          => 'Terjadi kesalahan saat mencocokkan foto. Silakan coba lagi.',
            ]);
        }
    }

    /**
     * Calculate Euclidean distance between two 128-dimensional vectors.
     * Lower value = more similar faces.
     * Distance < 0.5 is generally considered a match by face-api.js standards.
     */
    private function euclideanDistance(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new \InvalidArgumentException('Vectors must have the same dimension.');
        }

        $sum = 0.0;
        for ($i = 0; $i < count($a); $i++) {
            $diff  = $a[$i] - $b[$i];
            $sum  += $diff * $diff;
        }

        return sqrt($sum);
    }
}
