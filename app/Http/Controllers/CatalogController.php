<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    // Menampilkan semua foto dari semua photographer
    public function index(Request $request)
    {
        $query = Photo::whereHas('album', function($q) {
            $q->where('created_at', '>=', now()->subMonth());
        })->with(['album', 'album.photographer']);

        // Search berdasarkan location (album location)
        if ($request->filled('location')) {
            $query->whereHas('album', fn($q) => 
                $q->where('location', 'like', '%' . $request->location . '%')
            );
        }

        // Filter berdasarkan date range
        if ($request->filled('date_from')) {
            $query->whereHas('album', fn($q) => 
                $q->whereDate('event_date', '>=', $request->date_from)
            );
        }

        if ($request->filled('date_to')) {
            $query->whereHas('album', fn($q) => 
                $q->whereDate('event_date', '<=', $request->date_to)
            );
        }

        // Filter berdasarkan price range
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Search berdasarkan photographer
        if ($request->filled('photographer')) {
            $query->whereHas('album.photographer', fn($q) => 
                $q->where('name', 'like', '%' . $request->photographer . '%')
            );
        }

        // Paginate hasil
        $photos = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('catalog.index', [
            'photos' => $photos,
            'searchFilters' => $request->all(),
        ]);
    }

    // Menampilkan detail foto
    public function show(Photo $photo)
    {
        if ($photo->album->created_at < now()->subMonth()) {
            abort(404, 'Foto ini sudah kedaluwarsa.');
        }

        $photo->load(['album', 'album.photographer']);
        
        return view('catalog.show', compact('photo'));
    }
}
