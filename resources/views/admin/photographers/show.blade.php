@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.photographers.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Kembali</a>
        <h1 class="text-3xl font-bold text-gray-900">{{ $photographer->name }}</h1>
        <p class="text-gray-600 mt-1">{{ $photographer->email }}</p>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Status</p>
            <p class="text-2xl font-bold mt-2 {{ $photographer->status === 'active' ? 'text-green-600' : 'text-red-600' }}">
                {{ ucfirst($photographer->status) }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Total Album</p>
            <p class="text-2xl font-bold mt-2 text-blue-600">{{ $photographer->albums->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Saldo Dompet</p>
            <p class="text-2xl font-bold mt-2 text-purple-600">Rp {{ number_format($photographer->wallet_balance, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Total Pendapatan</p>
            <p class="text-2xl font-bold mt-2 text-yellow-600">Rp {{ number_format($earnings, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-8 flex gap-3">
        <a href="{{ route('admin.photographers.edit', $photographer) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
            Edit Fotografer
        </a>
        <form method="POST" action="{{ route('admin.photographers.toggle-status', $photographer) }}" style="display: inline;">
            @csrf
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-medium transition" onclick="return confirm('Ubah status fotografer?')">
                {{ $photographer->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </form>
    </div>

    <!-- Albums Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">Album Fotografer</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Judul Album</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Lokasi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal Event</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Jumlah Foto</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($albums as $album)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $album->title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $album->location }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $album->event_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $album->photos->count() }} foto</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('admin.albums.show', $album) }}" class="text-blue-600 hover:text-blue-800">Lihat</a>
                                <a href="{{ route('admin.albums.edit', $album) }}" class="text-purple-600 hover:text-purple-800">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada album</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t">
            {{ $albums->links() }}
        </div>
    </div>
</div>
@endsection
