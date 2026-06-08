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
        
        // Redirect buyers and customers to the face-registration page
        if (in_array($user->role, ['customer', 'buyer'])) {
            return redirect()->route('buyer.register-face');
        }

        // Redirect admin to admin dashboard
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
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
