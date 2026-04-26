<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller
{
    // Menampilkan halaman form tambah album
    public function create()
    {
        return view('albums.create');
    }

    // Menampilkan detail album dengan foto-fotonya
    public function show(Album $album)
    {
        // Pastikan hanya pemilik album yang bisa lihat
        if ($album->photographer_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke album ini.');
        }

        // Load photos dengan eager loading
        $album->load('photos');

        return view('albums.show', compact('album'));
    }

    // Memproses data yang dikirim dari form dan menyimpannya ke database
    public function store(Request $request)
    {
        // 1. Validasi inputan (wajib diisi dan formatnya benar)
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
        ]);

        // 2. Simpan ke database menggunakan Model Album
        Album::create([
            'photographer_id' => Auth::id(), // Mengambil ID user yang sedang login
            'title' => $request->title,
            'location' => $request->location,
            'event_date' => $request->event_date,
        ]);

        // 3. Arahkan kembali ke dashboard dengan pesan sukses
        return redirect()->route('dashboard')->with('success', 'Album baru berhasil dibuat!');
    }
}