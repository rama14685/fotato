<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Jika user adalah buyer/customer, redirect ke albums
        if ($user->role === 'customer') {
            return redirect()->route('albums.index');
        }
        
        // Untuk admin: tampilkan semua album dari semua fotografer
        if ($user->role === 'admin') {
            $albums = Album::with('photos', 'photographer')
                ->orderBy('created_at', 'desc')
                ->get();
            
            $totalAlbums = $albums->count();
            $totalPhotos = $albums->sum(fn($album) => $album->photos->count());
            
            // Total pendapatan dari semua fotografer
            $totalRevenue = Transaction::whereIn('status', ['paid', 'completed'])
                ->sum('total_amount');
            
            return view('dashboard', [
                'albums' => $albums,
                'totalAlbums' => $totalAlbums,
                'totalPhotos' => $totalPhotos,
                'totalRevenue' => $totalRevenue,
                'isAdmin' => true,
            ]);
        }
        
        // Untuk photographer: tampilkan statistik (read-only)
        
        // Hitung total album miliknya
        $totalAlbums = Album::where('photographer_id', $user->id)->count();
        
        // Hitung total foto dari semua album
        $totalPhotos = Photo::whereIn('album_id', function($query) use ($user) {
            $query->select('id')->from('albums')->where('photographer_id', $user->id);
        })->count();
        
        // Hitung total pendapatan dari transactions yang sudah dibayar/completed
        $totalRevenue = Transaction::where('photographer_id', $user->id)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');
        
        return view('dashboard', [
            'albums' => collect(), // Empty collection since we don't show albums anymore
            'totalAlbums' => $totalAlbums,
            'totalPhotos' => $totalPhotos,
            'totalRevenue' => $totalRevenue,
            'isAdmin' => false,
        ]);
    }
}
