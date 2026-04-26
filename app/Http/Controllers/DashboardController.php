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
        
        // Jika user adalah buyer/customer, redirect ke catalog
        if ($user->role === 'customer') {
            return redirect()->route('catalog.index');
        }
        
        // Ambil semua album milik photographer
        $albums = Album::where('photographer_id', $user->id)
            ->with('photos')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Hitung total album
        $totalAlbums = $albums->count();
        
        // Hitung total foto dari semua album
        $totalPhotos = $albums->sum(fn($album) => $album->photos->count());
        
        // Hitung total pendapatan dari transactions yang sudah dibayar/completed
        $totalRevenue = Transaction::where('photographer_id', $user->id)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');
        
        return view('dashboard', [
            'albums' => $albums,
            'totalAlbums' => $totalAlbums,
            'totalPhotos' => $totalPhotos,
            'totalRevenue' => $totalRevenue,
        ]);
    }
}
