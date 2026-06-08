@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Admin Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold font-display text-white tracking-tight mb-2">Admin Dashboard</h1>
        <p class="text-purple-300/60 text-sm">Kelola fotografer, album, dan analytics platform FOTATO</p>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Photographers -->
        <div class="stat-card rounded-3xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Total Fotografer</p>
                    <p class="text-3xl font-black font-display text-white mt-1.5">{{ $totalPhotographers }}</p>
                </div>
                <div class="text-purple-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316A2.192 2.192 0 0 0 14.502 4h-5c-.7 0-1.363.336-1.78.918l-.895 1.257ZM12 10.5a3.75 3.75 0 1 1 0 7.5 3.75 3.75 0 0 1 0-7.5ZM12 12a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-purple-300/40 mt-4">{{ $activePhotographers }} aktif</p>
        </div>

        <!-- Total Albums -->
        <div class="stat-card rounded-3xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Total Album</p>
                    <p class="text-3xl font-black font-display text-white mt-1.5">{{ $totalAlbums }}</p>
                </div>
                <div class="text-purple-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0A2.25 2.25 0 0 0 4.5 15h15a2.25 2.25 0 0 0 2.25-2.25m-19.5 0v.158c0 .871.189 1.73.551 2.513l1.832 3.964a2.25 2.25 0 0 0 2.036 1.308h10.162a2.25 2.25 0 0 0 2.036-1.308l1.832-3.964a9.23 9.23 0 0 0 .551-2.513V12.75" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-purple-300/40 mt-4">Koleksi fotografi</p>
        </div>

        <!-- Total Photos -->
        <div class="stat-card rounded-3xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Total Foto</p>
                    <p class="text-3xl font-black font-display text-white mt-1.5">{{ $totalPhotos }}</p>
                </div>
                <div class="text-purple-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375 0 1 1-.75 0 .375 0 0 1 .75 0Z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-purple-300/40 mt-4">Siap dijual</p>
        </div>

        <!-- Total Revenue -->
        <div class="stat-card rounded-3xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Total Revenue</p>
                    <p class="text-3xl font-black font-display text-white mt-1.5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="text-[#FFE600]">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-purple-300/40 mt-4">{{ $totalTransactions }} transaksi</p>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Quick Access -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold font-display text-white mb-6">Akses Cepat</h2>
            <div class="space-y-4">
                <a href="{{ route('admin.photographers.create') }}" class="flex items-center p-4 bg-[#1f0e3d]/20 border border-purple-500/10 rounded-2xl hover:bg-purple-500/10 hover:border-purple-500/30 transition duration-300">
                    <svg class="w-6 h-6 text-purple-400 mr-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.111c0-1.68 1.113-3.153 2.72-3.411a14.933 14.933 0 0 1 7.153 0c1.608.258 2.72 1.732 2.72 3.412v.11a1.24 1.24 0 0 1-1.24 1.24H4.24A1.24 1.24 0 0 1 3 19.235Z" />
                    </svg>
                    <div>
                        <p class="font-bold text-white text-sm">Tambah Fotografer</p>
                        <p class="text-xs text-purple-300/40 mt-0.5">Buat akun fotografer baru</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.albums.create') }}" class="flex items-center p-4 bg-[#1f0e3d]/20 border border-purple-500/10 rounded-2xl hover:bg-purple-500/10 hover:border-purple-500/30 transition duration-300">
                    <svg class="w-6 h-6 text-purple-400 mr-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3H9m4.06-7.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                    <div>
                        <p class="font-bold text-white text-sm">Buat Album</p>
                        <p class="text-xs text-purple-300/40 mt-0.5">Buat koleksi foto baru</p>
                    </div>
                </a>

                <a href="{{ route('admin.photographers.index') }}" class="flex items-center p-4 bg-[#1f0e3d]/20 border border-purple-500/10 rounded-2xl hover:bg-purple-500/10 hover:border-purple-500/30 transition duration-300">
                    <svg class="w-6 h-6 text-purple-400 mr-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A1.24 1.24 0 0 1 13.76 20.5H6.24a1.24 1.24 0 0 1-1.24-1.24H3v-.108a9.38 9.38 0 0 1 2.625-.372 9.337 9.337 0 0 1 4.121.952 4.125 4.125 0 0 1-7.533-2.493M13.5 7.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM19.25 10.125a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <div>
                        <p class="font-bold text-white text-sm">Kelola Fotografer</p>
                        <p class="text-xs text-purple-300/40 mt-0.5">Lihat semua fotografer</p>
                    </div>
                </a>

                <a href="{{ route('admin.albums.index') }}" class="flex items-center p-4 bg-[#1f0e3d]/20 border border-purple-500/10 rounded-2xl hover:bg-purple-500/10 hover:border-purple-500/30 transition duration-300">
                    <svg class="w-6 h-6 text-purple-400 mr-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Zm0-13.5h12M6 10.5h12M6 13.5h12M6 16.5h12" />
                    </svg>
                    <div>
                        <p class="font-bold text-white text-sm">Kelola Album</p>
                        <p class="text-xs text-purple-300/40 mt-0.5">Lihat semua album</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Audit Logs -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-xl font-bold font-display text-white mb-6">Aktivitas Terbaru</h2>
            <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
                @forelse($recentAuditLogs as $log)
                    <div class="pb-3 border-b border-purple-500/10 last:border-b-0">
                        <p class="text-sm font-bold text-white">{{ ucfirst(str_replace('_', ' ', $log->action_type)) }}</p>
                        <p class="text-xs text-purple-300/60 mt-0.5 leading-relaxed">{{ $log->description ?? 'Action logged' }}</p>
                        <p class="text-[10px] text-purple-300/40 mt-1.5 font-medium">
                            {{ $log->admin->name }} • {{ $log->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                @empty
                    <p class="text-purple-300/40 text-sm italic">Tidak ada aktivitas terbaru.</p>
                @endforelse
            </div>
            <div class="mt-4 pt-4 border-t border-purple-500/10">
                <a href="{{ route('admin.audit-logs.index') }}" class="text-purple-300 hover:text-white text-xs font-bold transition flex items-center gap-1">
                    Lihat semua aktivitas <span>→</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.revenue.index') }}" class="glass-card rounded-3xl p-6 hover:translate-y-[-4px] transition-all duration-300">
            <div class="text-purple-400">
                <svg class="w-8 h-8 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                </svg>
            </div>
            <p class="font-bold font-display text-lg text-white">Analytics Revenue</p>
            <p class="text-xs text-purple-300/40 mt-1 leading-relaxed">Pantau performa penjualan dan total pendapatan platform.</p>
        </a>

        <a href="{{ route('admin.audit-logs.index') }}" class="glass-card rounded-3xl p-6 hover:translate-y-[-4px] transition-all duration-300">
            <div class="text-purple-400">
                <svg class="w-8 h-8 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <p class="font-bold font-display text-lg text-white">Audit Logs</p>
            <p class="text-xs text-purple-300/40 mt-1 leading-relaxed">Catatan lengkap aktivitas administrator demi keamanan platform.</p>
        </a>

        <a href="{{ route('admin.photographers.index') }}" class="glass-card rounded-3xl p-6 hover:translate-y-[-4px] transition-all duration-300">
            <div class="text-purple-400">
                <svg class="w-8 h-8 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm-1.214 6.372a3.905 3.905 0 0 0-2.9 0M16.5 7.5h.008v.008h-.008V7.5Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
            </div>
            <p class="font-bold font-display text-lg text-white">Manajemen Fotografer</p>
            <p class="text-xs text-purple-300/40 mt-1 leading-relaxed">Kelola pendaftaran fotografer, status akun, dan persetujuan.</p>
        </a>
    </div>
</div>
@endsection
