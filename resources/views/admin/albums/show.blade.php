@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.albums.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Kembali</a>
        <h1 class="text-3xl font-bold text-gray-900">{{ $album->title }}</h1>
        <p class="text-gray-600 mt-1">{{ $album->photographer->name }} • {{ $album->location }}</p>
    </div>

    <!-- Album Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Fotografer</p>
            <p class="text-lg font-bold mt-2 text-gray-900">{{ $album->photographer->name }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Jumlah Foto</p>
            <p class="text-lg font-bold mt-2 text-blue-600">{{ $photos->total() }} foto</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Total Pendapatan</p>
            <p class="text-lg font-bold mt-2 text-yellow-600">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-8 flex gap-3">
        <a href="{{ route('admin.albums.edit', $album) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
            Edit Album
        </a>
        <form method="POST" action="{{ route('admin.albums.destroy', $album) }}" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition" onclick="return confirm('Hapus album ini? Semua foto akan dihapus.')">
                Hapus Album
            </button>
        </form>
    </div>

    <!-- Photos Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">Daftar Foto</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
            @forelse($photos as $photo)
                <div class="border rounded-lg overflow-hidden hover:shadow-lg transition">
                    @if($photo->watermark_path)
                        <img src="{{ asset('storage/' . $photo->watermark_path) }}" alt="Photo" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">No Image</span>
                        </div>
                    @endif
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="text-sm text-gray-600">Harga</p>
                                <p class="text-lg font-bold text-gray-900">Rp {{ number_format($photo->price, 0, ',', '.') }}</p>
                            </div>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded font-medium">
                                {{ ucfirst($photo->processing_status) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">{{ $photo->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">Belum ada foto dalam album ini</p>
                </div>
            @endforelse
        </div>

        <div class="p-6 border-t">
            {{ $photos->links() }}
        </div>
    </div>
</div>
@endsection
