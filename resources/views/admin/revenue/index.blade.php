@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Revenue Analytics</h1>
        <p class="text-gray-600 mt-1">Analisis performa penjualan platform</p>
    </div>

    <!-- Period Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.revenue.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Period Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Periode</label>
                <select name="period" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="today" {{ $periodFilter == 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="this_week" {{ $periodFilter == 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="this_month" {{ $periodFilter == 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="this_year" {{ $periodFilter == 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="custom" {{ $periodFilter == 'custom' ? 'selected' : '' }}>Kustom</option>
                </select>
            </div>

            <!-- Date Range (for custom) -->
            @if($periodFilter == 'custom')
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Dari Tanggal</label>
                    <input type="date" name="start_date" class="px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            @endif

            <!-- Submit Button -->
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Revenue Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
            <p class="text-sm opacity-90">Total Revenue</p>
            <p class="text-3xl font-bold mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="text-xs opacity-75 mt-2">{{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
            <p class="text-sm opacity-90">Total Transaksi</p>
            <p class="text-3xl font-bold mt-2">{{ $totalTransactions }}</p>
            <p class="text-xs opacity-75 mt-2">Transaksi selesai</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-6 text-white">
            <p class="text-sm opacity-90">Total Foto Terjual</p>
            <p class="text-3xl font-bold mt-2">{{ $totalPhotosSold }}</p>
            <p class="text-xs opacity-75 mt-2">Foto yang dibeli</p>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow p-6 text-white">
            <p class="text-sm opacity-90">Rata-rata Harga Foto</p>
            <p class="text-3xl font-bold mt-2">Rp {{ number_format($averagePhotoPrice, 0, ',', '.') }}</p>
            <p class="text-xs opacity-75 mt-2">Per foto</p>
        </div>
    </div>

    <!-- Top Photographers & Albums -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Photographers -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Top Fotografer</h2>
            </div>
            <div class="divide-y">
                @forelse($revenueByPhotographer as $item)
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-900">{{ $item->photographer->name }}</p>
                                <p class="text-sm text-gray-600">{{ $item->transaction_count }} transaksi</p>
                            </div>
                            <p class="font-bold text-gray-900">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">Tidak ada data</div>
                @endforelse
            </div>
        </div>

        <!-- Top Albums -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Top Album</h2>
            </div>
            <div class="divide-y">
                @forelse($revenueByAlbum->take(5) as $album)
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-900">{{ $album->title }}</p>
                                <p class="text-sm text-gray-600">{{ $album->photographer->name }}</p>
                            </div>
                            <p class="font-bold text-gray-900">Rp {{ number_format($album->revenue ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">Tidak ada data</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart Info -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Tren Revenue</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($revenueTrend as $trend)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::parse($trend->date)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">
                                Rp {{ number_format($trend->daily_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-8 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
