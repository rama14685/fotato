<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;

class AlbumCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Album::where('created_at', '>=', now()->subMonth())
            ->with(['photographer'])
            ->withCount('photos');

        // Search berdasarkan location
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filter berdasarkan date range
        if ($request->filled('date_from')) {
            $query->whereDate('event_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }

        // Search berdasarkan photographer
        if ($request->filled('photographer')) {
            $query->whereHas('photographer', fn($q) => 
                $q->where('name', 'like', '%' . $request->photographer . '%')
            );
        }

        // Search berdasarkan title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $albums = $query->orderBy('event_date', 'desc')->paginate(12);

        return view('albums.index', [
            'albums' => $albums,
            'searchFilters' => $request->all(),
        ]);
    }

    public function show(Album $album)
    {
        if ($album->created_at < now()->subMonth()) {
            abort(404, 'Album ini sudah kedaluwarsa.');
        }

        $album->load(['photographer', 'photos']);
        
        return view('albums.show', compact('album'));
    }
}
