@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Album</h1>
            <p class="text-gray-600 mt-1">Kelola koleksi foto fotografer</p>
        </div>
        <a href="{{ route('admin.albums.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
            + Buat Album
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.albums.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <input type="text" name="search" placeholder="Cari judul atau lokasi..." 
                   value="{{ $searchQuery }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">

            <!-- Photographer Filter -->
            <select name="photographer_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                <option value="">Semua Fotografer</option>
                @foreach($photographers as $photographer)
                    <option value="{{ $photographer->id }}" {{ $selectedPhotographer == $photographer->id ? 'selected' : '' }}>
                        {{ $photographer->name }}
                    </option>
                @endforeach
            </select>

            <!-- Sort -->
            <select name="sort" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                <option value="event_date" {{ $sortBy == 'event_date' ? 'selected' : '' }}>Tanggal Event</option>
                <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                <option value="photo_count" {{ $sortBy == 'photo_count' ? 'selected' : '' }}>Jumlah Foto</option>
            </select>

            <!-- Submit -->
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Judul Album</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Fotografer</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Lokasi</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal Event</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Foto</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($albums as $album)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $album->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $album->photographer->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $album->location }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $album->event_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $album->photos->count() }} foto</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.albums.show', $album) }}" class="text-blue-600 hover:text-blue-800">Lihat</a>
                            <a href="{{ route('admin.albums.edit', $album) }}" class="text-purple-600 hover:text-purple-800">Edit</a>
                            <form method="POST" action="{{ route('admin.albums.destroy', $album) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Hapus album ini? Semua foto akan dihapus.')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Tidak ada album ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $albums->links() }}
    </div>
</div>
@endsection
