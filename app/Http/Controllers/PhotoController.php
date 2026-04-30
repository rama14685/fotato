<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Alignment;

class PhotoController extends Controller
{
    // Menampilkan form upload foto untuk album tertentu (hanya admin)
    public function create(Album $album)
    {
        // Hanya admin yang bisa upload foto
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengunggah foto.');
        }
        
        return view('photos.create', compact('album'));
    }

    // Memproses foto, memberi watermark, dan menyimpan ke database (hanya admin)
    public function store(Request $request, Album $album)
    {
        // Hanya admin yang bisa upload foto
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengunggah foto.');
        }

        // 1. Validasi Input
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240', // Maksimal 10MB
            'price' => 'required|numeric|min:0',
        ]);

        $file = $request->file('photo');
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();

        // Buat folder jika belum ada (demi keamanan eksekusi)
        Storage::disk('public')->makeDirectory('photos/originals');
        Storage::disk('public')->makeDirectory('photos/watermarked');

        // 2. Simpan Foto Asli (Resolusi Tinggi)
        $originalPath = $file->storeAs('photos/originals', $filename, 'public');

        // 3. Proses Watermarking dengan Tiled Watermark (Intervention Image v4)
        // Inisialisasi ImageManager dengan GD Driver
        $manager = new ImageManager(new Driver());
        
        // Baca foto yang baru diunggah
        $image = $manager->decodePath($file->getRealPath());

        // Perkecil ukuran (Resize) agar web ringan. Lebar maksimal 800px, tinggi menyesuaikan
        $image->scaleDown(width: 800);

        // Baca dan resize watermark menjadi sangat kecil
        $watermark = $manager->decodePath(public_path('images/watermark.png'));
        $watermark->scaleDown(width: 65); // Watermark ukuran kecil (65px lebar)

        // Tiling watermark di berbagai posisi di seluruh image dengan transparency
        $imageWidth = $image->width();
        $imageHeight = $image->height();

        // Tempel watermark dengan pattern/tiling yang rapat
        $spacing = 80; // Jarak antar watermark (lebih kecil = lebih rapat)
        for ($y = 0; $y < $imageHeight; $y += $spacing) {
            for ($x = 0; $x < $imageWidth; $x += $spacing) {
                $image->insert($watermark, $x, $y, alignment: Alignment::TOP_LEFT, transparency: 0.25);
            }
        }

        // Simpan versi ber-watermark ke storage
        $watermarkPath = 'photos/watermarked/' . $filename;
        $image->save(storage_path('app/public/' . $watermarkPath));

        // 4. Simpan Data ke Database
        Photo::create([
            'album_id' => $album->id,
            'original_path' => $originalPath,
            'watermark_path' => $watermarkPath,
            'price' => $request->price,
        ]);

        return redirect()->route('dashboard')->with('success', 'Foto berhasil diunggah dan diberi watermark!');
    }
}