@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold font-display gradient-text">Manajemen Album</h1>
            <p class="text-purple-300/60 mt-1 text-sm">Kelola koleksi foto fotografer</p>
        </div>
        <a href="{{ route('admin.albums.create') }}" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold transition inline-flex items-center gap-1.5">
            + Buat Album
        </a>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-3xl p-6 mb-6">
        <form method="GET" action="{{ route('admin.albums.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-2">Pencarian</label>
                <input type="text" name="search" placeholder="Cari judul atau lokasi..." 
                       value="{{ $searchQuery }}" 
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30">
            </div>

            <!-- Photographer Filter -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-2">Fotografer</label>
                <select name="photographer_id" class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                    <option value="" class="bg-[#0d061a]">Semua Fotografer</option>
                    @foreach($photographers as $photographer)
                        <option value="{{ $photographer->id }}" class="bg-[#0d061a]" {{ $selectedPhotographer == $photographer->id ? 'selected' : '' }}>
                            {{ $photographer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-2">Urutkan</label>
                <select name="sort" class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                    <option value="event_date" class="bg-[#0d061a]" {{ $sortBy == 'event_date' ? 'selected' : '' }}>Tanggal Event</option>
                    <option value="created_at" class="bg-[#0d061a]" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                    <option value="photo_count" class="bg-[#0d061a]" {{ $sortBy == 'photo_count' ? 'selected' : '' }}>Jumlah Foto</option>
                </select>
            </div>

            <!-- Submit -->
            <div class="flex items-end">
                <button type="submit" class="w-full btn-secondary px-6 py-2 rounded-xl text-sm font-semibold transition">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-purple-950/30 border-b border-purple-500/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Judul Album</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Fotografer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Lokasi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Tanggal Event</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Foto</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-500/5">
                    @forelse($albums as $album)
                        <tr class="hover:bg-purple-500/5 transition duration-150">
                            <td class="px-6 py-4 text-sm font-medium text-white">{{ $album->title }}</td>
                            <td class="px-6 py-4 text-sm text-purple-200/70">{{ $album->photographer?->name ?? 'Admin / Sistem' }}</td>
                            <td class="px-6 py-4 text-sm text-purple-300/70">{{ $album->location }}</td>
                            <td class="px-6 py-4 text-sm text-purple-300/60">{{ $album->event_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-purple-300/40">{{ $album->photos->count() }} foto</td>
                            <td class="px-6 py-4 text-sm space-x-3 flex items-center">
                                <a href="{{ route('admin.albums.show', $album) }}" class="text-purple-300 hover:text-white font-semibold transition">Lihat</a>
                                <a href="{{ route('admin.albums.edit', $album) }}" class="text-blue-400 hover:text-white font-semibold transition">Edit</a>
                                <form method="POST" action="{{ route('admin.albums.destroy', $album) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 font-semibold transition" onclick="return confirm('Hapus album ini? Semua foto akan dihapus.')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-purple-300/40 italic">
                                Tidak ada album ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $albums->links() }}
    </div>
</div>
@endsection
