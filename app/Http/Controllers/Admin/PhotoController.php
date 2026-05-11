<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Photo;
use App\Models\PhotoFace;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class PhotoController extends Controller
{
    /**
     * Show upload form for a specific album.
     */
    public function create(Album $album)
    {
        return view('admin.albums.upload', ['album' => $album]);
    }

    /**
     * Handle photo upload with face descriptors extracted client-side via face-api.js.
     *
     * Expects multipart form data:
     *   - photo:             the image file
     *   - face_descriptors:  JSON string of an array of 128-d arrays (one per detected face)
     *   - price:             numeric price
     */
    public function store(Request $request, Album $album): JsonResponse
    {
        $request->validate([
            'photo'            => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'face_descriptors' => 'nullable|string', // JSON encoded array of descriptors
            'price'            => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $album, &$result) {
                $file     = $request->file('photo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Store original image
                $originalPath = Storage::disk('public')->putFileAs(
                    "albums/{$album->id}/originals",
                    $file,
                    $filename
                );

                // Create Photo record
                $photo = Photo::create([
                    'album_id'       => $album->id,
                    'original_path'  => $originalPath,
                    'watermark_path' => null,
                    'price'          => $request->price,
                ]);

                // Decode and save face descriptors (may be empty if no face detected)
                $descriptors = [];
                if ($request->face_descriptors) {
                    $decoded = json_decode($request->face_descriptors, true);
                    if (is_array($decoded)) {
                        $descriptors = $decoded;
                    }
                }

                $faceCount = 0;
                foreach ($descriptors as $descriptor) {
                    if (!is_array($descriptor) || count($descriptor) !== 128) {
                        continue; // Skip invalid descriptors
                    }

                    PhotoFace::create([
                        'photo_id'        => $photo->id,
                        'face_descriptor' => $descriptor,
                    ]);

                    $faceCount++;
                }

                AdminAuditLog::logAction(
                    auth()->id(),
                    'photo_uploaded',
                    'photo',
                    $photo->id,
                    "Photo uploaded to album '{$album->title}' with {$faceCount} face(s) detected"
                );

                $result = [
                    'id'            => $photo->id,
                    'filename'      => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => $this->formatBytes($file->getSize()),
                    'face_count'    => $faceCount,
                    'path'          => Storage::url($originalPath),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Foto berhasil diupload ({$result['face_count']} wajah terdeteksi)",
                'photo'   => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Photo upload failed', [
                'album_id' => $album->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uploading: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a photo and its associated face data.
     */
    public function destroy(Photo $photo): JsonResponse
    {
        try {
            // Delete face descriptors first
            $photo->photoFaces()->delete();

            // Delete from storage
            if ($photo->original_path) {
                Storage::disk('public')->delete($photo->original_path);
            }
            if ($photo->watermark_path) {
                Storage::disk('public')->delete($photo->watermark_path);
            }

            // Log audit
            AdminAuditLog::logAction(
                auth()->id(),
                'photo_deleted',
                'photo',
                $photo->id,
                "Photo deleted from album: {$photo->album->title}"
            );

            // Delete record
            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error menghapus foto: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get album photos (for listing in admin).
     */
    public function getAlbumPhotos(Album $album): JsonResponse
    {
        $photos = $album->photos()->withCount('photoFaces')->paginate(12);

        return response()->json([
            'success'      => true,
            'photos'       => $photos->items(),
            'total'        => $photos->total(),
            'per_page'     => $photos->perPage(),
            'current_page' => $photos->currentPage(),
            'last_page'    => $photos->lastPage(),
        ]);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
