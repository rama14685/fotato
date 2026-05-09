@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Admin Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Admin Dashboard</h1>
        <p class="text-gray-600">Kelola fotografer, album, dan analytics platform Fotlist</p>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Photographers -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Fotografer</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPhotographers }}</p>
                </div>
                <div class="text-4xl text-blue-500">📷</div>
            </div>
            <p class="text-xs text-gray-500 mt-4">{{ $activePhotographers }} aktif</p>
        </div>

        <!-- Total Albums -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Album</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalAlbums }}</p>
                </div>
                <div class="text-4xl text-green-500">📁</div>
            </div>
            <p class="text-xs text-gray-500 mt-4">Koleksi fotografi</p>
        </div>

        <!-- Total Photos -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Foto</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPhotos }}</p>
                </div>
                <div class="text-4xl text-purple-500">🖼️</div>
            </div>
            <p class="text-xs text-gray-500 mt-4">Siap dijual</p>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="text-4xl text-yellow-500">💰</div>
            </div>
            <p class="text-xs text-gray-500 mt-4">{{ $totalTransactions }} transaksi</p>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Akses Cepat</h2>
            <div class="space-y-3">
                <a href="{{ route('admin.photographers.create') }}" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                    <span class="text-2xl mr-3">➕</span>
                    <div>
                        <p class="font-medium text-gray-900">Tambah Fotografer</p>
                        <p class="text-xs text-gray-600">Buat akun fotografer baru</p>
                    </div>
                </a>
                <a href="{{ route('admin.albums.create') }}" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition">
                    <span class="text-2xl mr-3">📂</span>
                    <div>
                        <p class="font-medium text-gray-900">Buat Album</p>
                        <p class="text-xs text-gray-600">Buat koleksi foto baru</p>
                    </div>
                </a>
                <a href="{{ route('admin.photographers.index') }}" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                    <span class="text-2xl mr-3">👥</span>
                    <div>
                        <p class="font-medium text-gray-900">Kelola Fotografer</p>
                        <p class="text-xs text-gray-600">Lihat semua fotografer</p>
                    </div>
                </a>
                <a href="{{ route('admin.albums.index') }}" class="flex items-center p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition">
                    <span class="text-2xl mr-3">🎞️</span>
                    <div>
                        <p class="font-medium text-gray-900">Kelola Album</p>
                        <p class="text-xs text-gray-600">Lihat semua album</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Audit Logs -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Aktivitas Terbaru</h2>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($recentAuditLogs as $log)
                    <div class="pb-3 border-b last:border-b-0">
                        <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $log->action_type)) }}</p>
                        <p class="text-xs text-gray-600">{{ $log->description ?? 'Action logged' }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $log->admin->name }} • {{ $log->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">Tidak ada aktivitas</p>
                @endforelse
            </div>
            <a href="{{ route('admin.audit-logs.index') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                Lihat semua →
            </a>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.revenue.index') }}" class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow p-6 text-white hover:shadow-lg transition">
            <p class="text-2xl mb-2">📊</p>
            <p class="font-bold text-lg">Analytics Revenue</p>
            <p class="text-sm opacity-90">Pantau performa penjualan</p>
        </a>

        <a href="{{ route('admin.audit-logs.index') }}" class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow p-6 text-white hover:shadow-lg transition">
            <p class="text-2xl mb-2">📋</p>
            <p class="font-bold text-lg">Audit Logs</p>
            <p class="text-sm opacity-90">Catatan aktivitas admin</p>
        </a>

        <a href="{{ route('admin.photographers.index') }}" class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow p-6 text-white hover:shadow-lg transition">
            <p class="text-2xl mb-2">👨‍💼</p>
            <p class="font-bold text-lg">Manajemen Fotografer</p>
            <p class="text-sm opacity-90">Kelola akun & status</p>
        </a>
    </div>
</div>
@endsection
