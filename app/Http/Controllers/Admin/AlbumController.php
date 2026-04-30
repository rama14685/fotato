<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Models\Album;
use App\Models\User;
use App\Models\AdminAuditLog;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    /**
     * Display a listing of albums.
     */
    public function index(Request $request): View
    {
        $query = Album::with('photographer', 'photos');

        // Filter by photographer
        if ($request->has('photographer_id') && $request->photographer_id) {
            $query->where('photographer_id', $request->photographer_id);
        }

        // Search by title or location
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->sort ?? 'event_date';
        $sortOrder = $request->order ?? 'desc';
        
        switch ($sortBy) {
            case 'event_date':
            case 'created_at':
                $query->orderBy($sortBy, $sortOrder);
                break;
            case 'photo_count':
                $query->withCount('photos')
                      ->orderBy('photos_count', $sortOrder);
                break;
        }

        $albums = $query->paginate(25);
        $photographers = User::where('role', 'photographer')->get();

        return view('admin.albums.index', [
            'albums' => $albums,
            'photographers' => $photographers,
            'selectedPhotographer' => $request->photographer_id,
            'searchQuery' => $request->search ?? '',
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    /**
     * Show the form for creating a new album.
     */
    public function create(): View
    {
        $photographers = User::where('role', 'photographer')->where('status', 'active')->get();

        return view('admin.albums.create', ['photographers' => $photographers]);
    }

    /**
     * Store a newly created album in storage.
     */
    public function store(StoreAlbumRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $album = Album::create($validated);

        AdminAuditLog::logAction(
            auth()->id(),
            'album_created',
            'album',
            $album->id,
            "Album '{$album->title}' created for photographer {$album->photographer_id}"
        );

        return redirect()->route('admin.albums.show', $album)
                       ->with('success', 'Album berhasil dibuat.');
    }

    /**
     * Display the specified album.
     */
    public function show(Album $album): View
    {
        $photos = $album->photos()->paginate(20);
        $totalEarnings = $album->photos()
            ->join('transaction_items', 'photos.id', '=', 'transaction_items.photo_id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->sum('transactions.total_amount');

        return view('admin.albums.show', [
            'album' => $album,
            'photos' => $photos,
            'totalEarnings' => $totalEarnings,
        ]);
    }

    /**
     * Show the form for editing the specified album.
     */
    public function edit(Album $album): View
    {
        $photographers = User::where('role', 'photographer')->get();

        return view('admin.albums.edit', [
            'album' => $album,
            'photographers' => $photographers,
        ]);
    }

    /**
     * Update the specified album in storage.
     */
    public function update(UpdateAlbumRequest $request, Album $album): RedirectResponse
    {
        $validated = $request->validated();
        $changes = [];

        foreach (['title', 'location', 'event_date'] as $field) {
            if (isset($validated[$field]) && $validated[$field] != $album->{$field}) {
                $changes[$field] = ['from' => $album->{$field}, 'to' => $validated[$field]];
            }
        }

        $album->update($validated);

        if (!empty($changes)) {
            AdminAuditLog::logAction(
                auth()->id(),
                'album_updated',
                'album',
                $album->id,
                "Album information updated",
                $changes
            );
        }

        return redirect()->route('admin.albums.show', $album)
                       ->with('success', 'Data album berhasil diperbarui.');
    }

    /**
     * Delete the specified album.
     */
    public function destroy(Album $album): RedirectResponse
    {
        $photoCount = $album->photos()->count();
        $albumTitle = $album->title;
        
        // Delete all photos and their associated data
        foreach ($album->photos as $photo) {
            // Delete face embeddings
            if ($photo->faceEmbedding) {
                $photo->faceEmbedding()->delete();
            }
            $photo->delete();
        }
        
        $album->delete();

        AdminAuditLog::logAction(
            auth()->id(),
            'album_deleted',
            'album',
            $album->id,
            "Album '{$albumTitle}' deleted with {$photoCount} photos"
        );

        return redirect()->route('admin.albums.index')
                       ->with('success', 'Album dan semua foto berhasil dihapus.');
    }
}
