<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Album;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    /**
     * Display revenue analytics and statistics.
     */
    public function index(Request $request): View
    {
        $periodFilter = $request->period ?? 'this_month';
        $startDate = $this->getStartDate($periodFilter, $request);
        $endDate = Carbon::now();

        // Base query for transactions in the period
        $query = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Total platform revenue
        $totalRevenue = (clone $query)->sum('total_amount');

        // Revenue by photographer
        $revenueByPhotographer = (clone $query)
            ->with('photographer')
            ->select('photographer_id')
            ->selectRaw('SUM(total_amount) as total_revenue')
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('photographer_id')
            ->orderBy('total_revenue', 'desc')
            ->take(10)
            ->get();

        // Revenue by album
        $revenueByAlbum = Album::with('photographer')
            ->withCount('photos')
            ->orderBy('photos_count', 'desc')
            ->take(10)
            ->get();

        // Sales statistics
        $totalPhotosSold = \App\Models\TransactionItem::whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'completed')
              ->whereBetween('created_at', [$startDate, $endDate]);
        })->sum('quantity');
        
        $totalTransactions = (clone $query)->count();
        $averagePhotoPrice = $totalPhotosSold > 0 ? $totalRevenue / $totalPhotosSold : 0;

        // Revenue trend (daily)
        $revenueTrend = (clone $query)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total_amount) as daily_revenue')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.revenue.index', [
            'periodFilter' => $periodFilter,
            'totalRevenue' => $totalRevenue,
            'revenueByPhotographer' => $revenueByPhotographer,
            'revenueByAlbum' => $revenueByAlbum,
            'totalPhotosSold' => $totalPhotosSold ?? 0,
            'totalTransactions' => $totalTransactions,
            'averagePhotoPrice' => $averagePhotoPrice,
            'revenueTrend' => $revenueTrend,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Get the start date based on the selected period filter.
     */
    private function getStartDate($period, Request $request): Carbon
    {
        return match ($period) {
            'today' => Carbon::now()->startOfDay(),
            'this_week' => Carbon::now()->startOfWeek(),
            'this_month' => Carbon::now()->startOfMonth(),
            'this_year' => Carbon::now()->startOfYear(),
            'custom' => Carbon::parse($request->start_date),
            default => Carbon::now()->startOfMonth(),
        };
    }
}
