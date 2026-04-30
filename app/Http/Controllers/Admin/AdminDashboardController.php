<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Transaction;
use App\Models\AdminAuditLog;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with statistics.
     */
    public function index(): View
    {
        $totalPhotographers = User::where('role', 'photographer')->count();
        $activePhotographers = User::where('role', 'photographer')->where('status', 'active')->count();
        $totalAlbums = Album::count();
        $totalPhotos = Photo::count();
        $totalRevenue = Transaction::where('status', 'completed')->sum('total_amount');
        $totalTransactions = Transaction::where('status', 'completed')->count();
        $recentAuditLogs = AdminAuditLog::with('admin')->latest()->take(10)->get();

        return view('admin.dashboard', [
            'totalPhotographers' => $totalPhotographers,
            'activePhotographers' => $activePhotographers,
            'totalAlbums' => $totalAlbums,
            'totalPhotos' => $totalPhotos,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }
}
