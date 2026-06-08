@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-black font-display gradient-text">Revenue Analytics</h1>
        <p class="text-purple-300/60 mt-1 text-sm">Analisis performa penjualan platform FOTATO</p>
    </div>

    <!-- Period Filter -->
    <div class="glass-card rounded-3xl p-6 mb-6">
        <form method="GET" action="{{ route('admin.revenue.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Period Selection -->
            <div class="w-full md:w-auto">
                <label class="block text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-2">Periode</label>
                <select name="period" class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                    <option value="today" class="bg-[#0d061a]" {{ $periodFilter == 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="this_week" class="bg-[#0d061a]" {{ $periodFilter == 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="this_month" class="bg-[#0d061a]" {{ $periodFilter == 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="this_year" class="bg-[#0d061a]" {{ $periodFilter == 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="custom" class="bg-[#0d061a]" {{ $periodFilter == 'custom' ? 'selected' : '' }}>Kustom</option>
                </select>
            </div>

            <!-- Date Range (for custom) -->
            @if($periodFilter == 'custom')
                <div class="w-full md:w-auto">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-2">Dari Tanggal</label>
                    <input type="date" name="start_date" class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                </div>
            @endif

            <!-- Submit Button -->
            <button type="submit" class="w-full md:w-auto btn-primary px-8 py-2.5 rounded-xl text-sm font-semibold transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Revenue Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Revenue -->
        <div class="stat-card rounded-3xl p-6 border-l-4 border-yellow-500/40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Total Revenue</p>
                    <p class="text-2xl font-black font-display text-white mt-1.5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="text-yellow-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="text-[10px] text-purple-300/40 mt-4">{{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}</p>
        </div>

        <!-- Total Transactions -->
        <div class="stat-card rounded-3xl p-6 border-l-4 border-blue-500/40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Total Transaksi</p>
                    <p class="text-3xl font-black font-display text-white mt-1.5">{{ $totalTransactions }}</p>
                </div>
                <div class="text-blue-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
            </div>
            <p class="text-[10px] text-purple-300/40 mt-4">Transaksi selesai</p>
        </div>

        <!-- Total Photos Sold -->
        <div class="stat-card rounded-3xl p-6 border-l-4 border-purple-500/40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Foto Terjual</p>
                    <p class="text-3xl font-black font-display text-white mt-1.5">{{ $totalPhotosSold }}</p>
                </div>
                <div class="text-purple-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375 0 1 1-.75 0 .375 0 0 1 .75 0Z" />
                    </svg>
                </div>
            </div>
            <p class="text-[10px] text-purple-300/40 mt-4">Foto yang dibeli</p>
        </div>

        <!-- Average Photo Price -->
        <div class="stat-card rounded-3xl p-6 border-l-4 border-green-500/40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Rata-rata Harga</p>
                    <p class="text-2xl font-black font-display text-white mt-1.5">Rp {{ number_format($averagePhotoPrice, 0, ',', '.') }}</p>
                </div>
                <div class="text-green-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="text-[10px] text-purple-300/40 mt-4">Per foto</p>
        </div>
    </div>

    <!-- Top Photographers & Albums -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Photographers -->
        <div class="glass-card rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-purple-500/10">
                <h2 class="text-xl font-bold font-display text-white">Top Fotografer</h2>
            </div>
            <div class="divide-y divide-purple-500/5">
                @forelse($revenueByPhotographer as $item)
                    <div class="p-6 hover:bg-purple-500/5 transition duration-150">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-white">{{ $item->photographer?->name ?? 'Admin / Sistem' }}</p>
                                <p class="text-xs text-purple-300/50 mt-1">{{ $item->transaction_count }} transaksi</p>
                            </div>
                            <p class="font-bold text-yellow-400">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-purple-300/40 italic">Tidak ada data</div>
                @endforelse
            </div>
        </div>

        <!-- Top Albums -->
        <div class="glass-card rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-purple-500/10">
                <h2 class="text-xl font-bold font-display text-white">Top Album</h2>
            </div>
            <div class="divide-y divide-purple-500/5">
                @forelse($revenueByAlbum->take(5) as $album)
                    <div class="p-6 hover:bg-purple-500/5 transition duration-150">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-white">{{ $album->title }}</p>
                                <p class="text-xs text-purple-300/50 mt-1">{{ $album->photographer?->name ?? 'Admin / Sistem' }}</p>
                            </div>
                            <p class="font-bold text-yellow-400">Rp {{ number_format($album->revenue ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-purple-300/40 italic">Tidak ada data</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart Info -->
    <div class="glass-card rounded-3xl p-6">
        <h2 class="text-xl font-bold font-display text-white mb-4">Tren Revenue</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-purple-950/30 border-b border-purple-500/10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Tanggal</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-purple-300/60">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-500/5">
                    @forelse($revenueTrend as $trend)
                        <tr class="hover:bg-purple-500/5 transition duration-150">
                            <td class="px-6 py-4 text-sm text-purple-200/80">{{ \Carbon\Carbon::parse($trend->date)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-white">
                                Rp {{ number_format($trend->daily_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-8 text-center text-sm text-purple-300/40 italic">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
