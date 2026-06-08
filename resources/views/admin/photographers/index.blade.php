@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold font-display gradient-text">Manajemen Fotografer</h1>
            <p class="text-purple-300/60 mt-1 text-sm">Kelola akun fotografer di platform</p>
        </div>
        <a href="{{ route('admin.photographers.create') }}" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold transition inline-flex items-center gap-1.5">
            + Tambah Fotografer
        </a>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-3xl p-6 mb-6">
        <form method="GET" action="{{ route('admin.photographers.index') }}" class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <input type="text" name="search" placeholder="Cari nama atau email..." 
                       value="{{ $searchQuery }}" 
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30">
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                    <option value="all" class="bg-[#0d061a]" {{ $currentStatus == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" class="bg-[#0d061a]" {{ $currentStatus == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" class="bg-[#0d061a]" {{ $currentStatus == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-secondary px-6 py-2 rounded-xl text-sm font-semibold transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-purple-950/30 border-b border-purple-500/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Nama</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Tanggal Bergabung</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-500/5">
                    @forelse($photographers as $photographer)
                        <tr class="hover:bg-purple-500/5 transition duration-150">
                            <td class="px-6 py-4 text-sm font-medium text-white">{{ $photographer->name }}</td>
                            <td class="px-6 py-4 text-sm text-purple-200/70">{{ $photographer->email }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($photographer->status === 'active')
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-400 border border-green-500/20">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-purple-300/40">{{ $photographer->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm space-x-3 flex items-center">
                                <a href="{{ route('admin.photographers.show', $photographer) }}" class="text-purple-300 hover:text-white font-semibold transition">Lihat</a>
                                <a href="{{ route('admin.photographers.edit', $photographer) }}" class="text-blue-400 hover:text-white font-semibold transition">Edit</a>
                                <form method="POST" action="{{ route('admin.photographers.toggle-status', $photographer) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="font-semibold transition {{ $photographer->status === 'active' ? 'text-yellow-500 hover:text-yellow-400' : 'text-green-400 hover:text-green-300' }}" onclick="return confirm('Ubah status fotografer?')">
                                        {{ $photographer->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-purple-300/40 italic">
                                Tidak ada fotografer ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $photographers->links() }}
    </div>
</div>
@endsection
