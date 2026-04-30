<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller
{
    // Menampilkan halaman form tambah album (hanya admin)
    public function create()
    {
        // Hanya admin yang bisa membuat album
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat membuat album.');
        }
        
        return view('albums.create');
    }

    // Menampilkan detail album dengan foto-fotonya
    public function show(Album $album)
    {
        $user = auth()->user();
        
        // Admin bisa lihat semua album
        // Photographer hanya bisa lihat album miliknya sendiri
        if ($user->role === 'admin') {
            // Admin bisa lihat semua album
        } elseif ($user->role === 'photographer' && $album->photographer_id === $user->id) {
            // Photographer bisa lihat album miliknya
        } else {
            abort(403, 'Anda tidak memiliki akses ke album ini.');
        }

        // Load photos dengan eager loading
        $album->load('photos');

        return view('albums.show', compact('album'));
    }

    // Memproses data yang dikirim dari form dan menyimpannya ke database (hanya admin)
    public function store(Request $request)
    {
        // Hanya admin yang bisa membuat album
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat membuat album.');
        }
        
        // 1. Validasi inputan (wajib diisi dan formatnya benar)
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
            'photographer_id' => 'required|exists:users,id', // Admin harus pilih fotografer
        ]);

        // 2. Simpan ke database menggunakan Model Album
        Album::create([
            'photographer_id' => $request->photographer_id, // Admin memilih fotografer
            'title' => $request->title,
            'location' => $request->location,
            'event_date' => $request->event_date,
        ]);

        // 3. Arahkan kembali ke dashboard dengan pesan sukses
        return redirect()->route('dashboard')->with('success', 'Album baru berhasil dibuat!');
    }
}