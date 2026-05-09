<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Photo;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class PhotoController extends Controller
{
    /**
     * Show upload form for a specific album
     */
    public function create(Album $album)
    {
        return view('admin.albums.upload', ['album' => $album]);
    }

    /**
     * Handle photo upload (single or batch)
     */
    public function store(Request $request, Album $album): JsonResponse
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'price' => 'required|numeric|min:0',
        ]);

        $uploadedPhotos = [];
        $errors = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                try {
                    if ($file->getSize() > 10485760) {
                        $errors[] = "File {$file->getClientOriginalName()} terlalu besar (max 10MB)";
                        continue;
                    }

                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = Storage::disk('public')->putFileAs(
                        "albums/{$album->id}/originals",
                        $file,
                        $filename
                    );

                    $photo = Photo::create([
                        'album_id' => $album->id,
                        'original_path' => $path,
                        'watermark_path' => null,
                        'price' => $request->price,
                    ]);

                    AdminAuditLog::logAction(
                        auth()->id(),
                        'photo_uploaded',
                        'photo',
                        $photo->id,
                        "Photo uploaded to album: {$album->title}"
                    );

                    $uploadedPhotos[] = [
                        'id' => $photo->id,
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $this->formatBytes($file->getSize()),
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Error uploading {$file->getClientOriginalName()}: " . $e->getMessage();
                }
            }
        }

        if (count($uploadedPhotos) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file yang berhasil diupload',
                'errors' => $errors,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedPhotos) . ' foto berhasil diupload',
            'photos' => $uploadedPhotos,
            'errors' => $errors,
            'album_id' => $album->id,
        ]);
    }

    /**
     * Delete a photo
     */
    public function destroy(Photo $photo): JsonResponse
    {
        try {
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
     * Get album photos
     */
    public function getAlbumPhotos(Album $album): JsonResponse
    {
        $photos = $album->photos()->paginate(12);

        return response()->json([
            'success' => true,
            'photos' => $photos->items(),
            'total' => $photos->total(),
            'per_page' => $photos->perPage(),
            'current_page' => $photos->currentPage(),
            'last_page' => $photos->lastPage(),
        ]);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
