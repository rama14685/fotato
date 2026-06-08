<?php

namespace App\Http\Controllers;

use App\Models\UserFace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BuyerFaceRegistrationController extends Controller
{
    /**
     * Show the face registration page (webcam capture).
     * If the user already has a face registered, allow re-registration.
     */
    public function index()
    {
        $user = Auth::user();

        // Only buyers/customers need face registration
        if (!in_array($user->role, ['buyer', 'customer'])) {
            return redirect()->route('dashboard');
        }

        $userFace = $user->userFace;
        $hasFace = $userFace !== null;
        $groupedByAlbum = collect();
        $totalMatched = 0;
        $purchasedPhotoIds = [];

        if ($hasFace && !empty($userFace->face_descriptor)) {
            $userDescriptor = $userFace->face_descriptor;
            if (count($userDescriptor) === 128) {
                try {
                    // Load ALL photo_faces rows with their photo + album relationships
                    $allPhotoFaces = \App\Models\PhotoFace::whereHas('photo.album', function ($q) {
                        $q->where('created_at', '>=', now()->subMonth());
                    })->with(['photo.album'])->get();

                    $matchedPhotos = collect();

                    foreach ($allPhotoFaces as $photoFace) {
                        if (empty($photoFace->face_descriptor)) {
                            continue;
                        }

                        $distance = $this->euclideanDistance($userDescriptor, $photoFace->face_descriptor);

                        if ($distance < 0.5) {
                            $photo = $photoFace->photo;
                            if (!$photo || $matchedPhotos->contains('id', $photo->id)) {
                                continue;
                            }

                            $photo->match_distance = round($distance, 4);
                            $photo->match_score    = round((1 - $distance) * 100, 1);
                            $matchedPhotos->push($photo);
                        }
                    }

                    $matchedPhotos = $matchedPhotos->sortBy('match_distance')->values();
                    $totalMatched = $matchedPhotos->count();

                    $purchasedPhotoIds = \App\Models\TransactionItem::whereIn('photo_id', $matchedPhotos->pluck('id'))
                        ->whereHas('transaction', function ($q) use ($user) {
                            $q->where('buyer_id', $user->id)
                              ->whereIn('status', ['paid', 'completed']);
                        })->pluck('photo_id')->toArray();

                    $groupedByAlbum = $matchedPhotos->groupBy('album_id')->map(function ($photos) {
                        return [
                            'album'  => $photos->first()->album,
                            'photos' => $photos->values(),
                        ];
                    })->values();

                } catch (\Exception $e) {
                    Log::error('Face registration match error', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage()
                    ]);
                }
            }
        }

        return view('buyer.register-face', compact('hasFace', 'groupedByAlbum', 'totalMatched', 'purchasedPhotoIds'));
    }

    /**
     * Calculate Euclidean distance between two 128-dimensional vectors.
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

    /**
     * Save the face descriptor captured from the webcam via AJAX.
     *
     * Expects JSON body: { face_descriptor: [128 floats] }
     */
    public function store(Request $request)
    {
        $request->validate([
            'face_descriptor'   => 'required|array|size:128',
            'face_descriptor.*' => 'numeric',
        ], [
            'face_descriptor.required' => 'Data wajah tidak ditemukan.',
            'face_descriptor.size'     => 'Data wajah tidak valid (harus 128 dimensi).',
            'face_descriptor.*.numeric'=> 'Data wajah mengandung nilai tidak valid.',
        ]);

        try {
            $user = Auth::user();

            // Upsert: update if exists, insert if not
            UserFace::updateOrCreate(
                ['user_id' => $user->id],
                ['face_descriptor' => $request->face_descriptor]
            );

            Log::info('Buyer face registered', ['user_id' => $user->id]);

            return response()->json([
                'success'  => true,
                'message'  => 'Wajah berhasil didaftarkan!',
                'redirect' => route('buyer.register-face'),
            ]);

        } catch (\Exception $e) {
            Log::error('Face registration failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data wajah. Silakan coba lagi.',
            ], 500);
        }
    }
}
