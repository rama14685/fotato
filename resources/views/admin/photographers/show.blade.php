@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.photographers.index') }}" class="text-purple-300 hover:text-white mb-4 inline-block font-semibold transition">← Kembali</a>
        <h1 class="text-3xl font-black font-display gradient-text">{{ $photographer->name }}</h1>
        <p class="text-purple-300/60 text-sm mt-1">{{ $photographer->email }}</p>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Status Card -->
        <div class="stat-card rounded-3xl p-6">
            <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Status Akun</p>
            <p class="text-2xl font-black mt-2 font-display {{ $photographer->status === 'active' ? 'text-green-400' : 'text-red-400' }}">
                {{ $photographer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
            </p>
        </div>

        <!-- Total Albums Card -->
        <div class="stat-card rounded-3xl p-6">
            <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Total Album</p>
            <p class="text-3xl font-black mt-2 font-display text-white">{{ $photographer->albums->count() }}</p>
        </div>

        <!-- Wallet Balance Card -->
        <div class="stat-card rounded-3xl p-6">
            <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Saldo Dompet</p>
            <p class="text-2xl font-black mt-2 font-display text-purple-300">Rp {{ number_format($photographer->wallet_balance, 0, ',', '.') }}</p>
        </div>

        <!-- Total Earnings Card -->
        <div class="stat-card rounded-3xl p-6">
            <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Total Pendapatan</p>
            <p class="text-2xl font-black mt-2 font-display text-yellow-400">Rp {{ number_format($earnings, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-8 flex gap-3">
        <a href="{{ route('admin.photographers.edit', $photographer) }}" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold transition">
            Edit Fotografer
        </a>
        <form method="POST" action="{{ route('admin.photographers.toggle-status', $photographer) }}" class="inline">
            @csrf
            <button type="submit" class="btn-secondary px-6 py-2.5 rounded-xl text-sm font-semibold transition" onclick="return confirm('Ubah status fotografer?')">
                {{ $photographer->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </form>
    </div>

    <!-- Albums Section -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-purple-500/10">
            <h2 class="text-xl font-bold font-display text-white">Album Fotografer</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-purple-950/30 border-b border-purple-500/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Judul Album</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Lokasi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Tanggal Event</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Jumlah Foto</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-500/5">
                    @forelse($albums as $album)
                        <tr class="hover:bg-purple-500/5 transition duration-150">
                            <td class="px-6 py-4 text-sm font-medium text-white">{{ $album->title }}</td>
                            <td class="px-6 py-4 text-sm text-purple-200/70">{{ $album->location }}</td>
                            <td class="px-6 py-4 text-sm text-purple-300/60">{{ $album->event_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-purple-300/40">{{ $album->photos->count() }} foto</td>
                            <td class="px-6 py-4 text-sm space-x-3">
                                <a href="{{ route('admin.albums.show', $album) }}" class="text-purple-300 hover:text-white font-semibold transition">Lihat</a>
                                <a href="{{ route('admin.albums.edit', $album) }}" class="text-blue-400 hover:text-white font-semibold transition">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-purple-300/40 italic">Belum ada album</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-purple-500/10">
            {{ $albums->links() }}
        </div>
    </div>
</div>
@endsection
