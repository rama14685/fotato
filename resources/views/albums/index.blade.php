<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fotato - Galeri Konser</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0d061a] text-white font-sans selection:bg-purple-500/20 selection:text-white min-h-screen flex flex-col relative">

    <!-- Spotlight Background Glows -->
    <div class="fixed top-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/10 rounded-full mix-blend-screen filter blur-[120px] opacity-80 pointer-events-none z-0"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-600/10 rounded-full mix-blend-screen filter blur-[120px] opacity-80 pointer-events-none z-0"></div>

    <!-- Navigation Bar -->
    <nav id="main-navbar" class="fixed top-0 inset-x-0 z-50 py-3 bg-[#0d061a]/80 backdrop-blur-lg border-b border-purple-500/10 shadow-lg transition-all duration-300">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <a href="{{ route('landing') }}" class="text-2xl font-black font-display tracking-wider text-white hover:text-purple-300 transition-colors">
                FOTATO
            </a>
            
            <div class="hidden md:flex gap-8 items-center">
                <a href="{{ route('landing') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Home</a>
                <a href="{{ route('albums.index') }}" class="text-white hover:text-purple-300 transition-colors text-sm font-semibold">Gallery</a>
                <a href="{{ route('events.index') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Upcoming Concert</a>
                <a href="{{ route('landing') }}#usage" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">FAQ</a>
            </div>

            <div class="flex gap-4 items-center">
                <form method="GET" action="{{ route('albums.index') }}" class="relative hidden sm:block">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-1.5 pl-4 pr-8 text-white focus:outline-none focus:border-[#a855f7] w-36 md:w-44 font-sans placeholder:text-gray-400">
                    <span class="absolute right-3 top-2.5 text-[10px] opacity-70">🔍</span>
                </form>
                
                @auth
                    <!-- Cart Link -->
                    <a href="{{ route('cart.index') }}" class="relative text-gray-300 hover:text-white transition-colors text-xs font-medium flex items-center gap-1">
                        🛒 <span class="hidden md:inline">Keranjang</span>
                        @php
                            $cartCount = count(session()->get('cart', []));
                        @endphp
                        @if($cartCount > 0)
                            <span class="bg-red-500 text-white text-[9px] rounded-full w-4 h-4 flex items-center justify-center font-bold">{{ $cartCount }}</span>
                        @endif
                    </a>

                    <!-- User Menu Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1 bg-[#5A2A8F] hover:bg-[#8a2be2] text-white text-xs font-semibold px-4 py-1.5 rounded-full transition-all">
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-[#1f0e3d] border border-purple-500/30 rounded-xl shadow-xl z-50 py-1" style="display: none;">
                            @if(in_array(Auth::user()->role, ['buyer', 'customer']))
                                <a href="{{ route('buyer.register-face') }}" class="block px-4 py-2 text-xs text-gray-200 hover:bg-purple-500/20 hover:text-white transition-colors">
                                    Temukan Wajah
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-xs text-gray-200 hover:bg-purple-500/20 hover:text-white transition-colors">
                                    Dashboard
                                </a>
                            @endif
                            <a href="{{ route('face-scan.index') }}" class="block px-4 py-2 text-xs text-gray-200 hover:bg-purple-500/20 hover:text-white transition-colors">
                                Cari Wajah
                            </a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs text-gray-200 hover:bg-purple-500/20 hover:text-white transition-colors">
                                Profile
                            </a>
                            <hr class="border-purple-500/10 my-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-red-400 hover:bg-purple-500/20 hover:text-white transition-colors">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="bg-[#5A2A8F] hover:bg-[#8a2be2] text-white text-xs font-semibold px-5 py-1.5 rounded-full transition-all">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="flex-grow pt-28 pb-16 px-6 relative z-10">
        <div class="container mx-auto max-w-6xl">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-purple-300/40 mb-6 font-sans">
                <a href="{{ route('landing') }}" class="hover:text-purple-300 transition-colors">Home</a>
                <span>&gt;</span>
                <span class="text-purple-300/80">Gallery</span>
            </div>

            <!-- Header and Filter section -->
            <div x-data="{ showFilters: false }">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <h1 class="text-3xl md:text-5xl font-bold font-display text-white tracking-tight">Galeri Konser</h1>
                    
                    <div class="flex items-center gap-3">
                        <button @click="showFilters = !showFilters" class="px-4 py-2 rounded-full bg-[#1f0e3d]/50 border border-purple-500/30 text-xs text-purple-200 hover:bg-purple-500/20 hover:text-white transition-all">
                            ⚙ Filter Lanjutan
                        </button>
                        
                        <form method="GET" action="{{ route('albums.index') }}" class="flex items-center gap-2">
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari konser..." class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-2.5 pl-4 pr-10 text-white focus:outline-none focus:border-[#a855f7] w-48 sm:w-64 font-sans placeholder:text-gray-400">
                                <button type="submit" class="absolute right-3 top-3 text-xs opacity-75">🔍</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Advanced Filters Drawer -->
                <div x-show="showFilters" class="mb-8 p-6 bg-[#0f0720]/60 border border-[#5A2A8F] rounded-2xl" style="display: none;">
                    <form method="GET" action="{{ route('albums.index') }}" class="space-y-4">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-purple-200 mb-2">Lokasi Event</label>
                                <input type="text" name="location" value="{{ request('location') }}" placeholder="Contoh: Jakarta" class="w-full px-4 py-2.5 bg-gray-900/50 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500 text-white placeholder-gray-500 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-purple-200 mb-2">Dari Tanggal</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2.5 bg-gray-900/50 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500 text-white text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-purple-200 mb-2">Sampai Tanggal</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2.5 bg-gray-900/50 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500 text-white text-xs">
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <a href="{{ route('albums.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-xs font-semibold rounded-full transition">
                                Reset Filter
                            </a>
                            <button type="submit" class="px-5 py-2 bg-[#5A2A8F] hover:bg-[#8a2be2] text-white text-xs font-semibold rounded-full transition">
                                Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Albums Grid -->
            @if ($albums->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
                    @foreach ($albums as $album)
                        <div class="border-2 border-[#5A2A8F] bg-[#0f0720]/60 p-5 rounded-[28px] flex flex-col justify-between h-full hover:border-[#a855f7] hover:shadow-[0_0_30px_rgba(168,85,247,0.15)] transition-all duration-300 group">
                            <div>
                                <div class="relative h-56 rounded-2xl overflow-hidden mb-5 border border-purple-500/10">
                                    @if($album->thumbnail_path && file_exists(public_path('storage/' . $album->thumbnail_path)))
                                        <img src="{{ asset('storage/' . $album->thumbnail_path) }}" alt="{{ $album->title }}" class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105">
                                    @else
                                        <img src="{{ asset('images/landing' . (($album->id % 3) + 1) . '.jpg') }}" alt="{{ $album->title }}" class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105">
                                    @endif
                                    
                                    <div class="absolute bottom-4 left-0 bg-[#FFE600] text-black text-[10px] font-black px-4 py-1.5 rounded-r-full shadow-md font-sans">
                                        {{ $album->event_date ? $album->event_date->format('d M Y') : 'Date TBD' }}
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold font-display text-white mb-1.5 group-hover:text-purple-300 transition-colors line-clamp-1">
                                    {{ $album->title }}
                                </h3>
                                <p class="text-purple-300/50 text-sm font-sans mb-4">
                                    {{ number_format($album->photos_count, 0, ',', '.') }} photos
                                </p>
                            </div>
                            <div class="flex justify-end">
                                <a href="{{ route('albums.show', $album) }}" class="px-6 py-2.5 bg-gradient-to-r from-[#5A2A8F] to-[#8A4FFF] hover:from-[#6d30b0] hover:to-[#9b5cff] text-white text-xs font-bold font-display rounded-full transition-all shadow-md hover:shadow-purple-500/20 hover:scale-[1.02]">
                                    Lihat Galeri
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-12 flex justify-center">
                    {{ $albums->appends(request()->query())->links() }}
                </div>
            @else
                <div class="border border-purple-500/20 bg-[#0f0720]/40 rounded-[28px] p-16 text-center my-10 max-w-lg mx-auto">
                    <div class="text-5xl mb-6">📁</div>
                    <h3 class="text-xl font-bold text-gray-300 mb-3">Tidak Ada Album Ditemukan</h3>
                    <p class="text-gray-400 text-sm mb-6 leading-relaxed">Coba ubah kata kunci pencarian Anda atau periksa filter lainnya.</p>
                    <a href="{{ route('albums.index') }}" class="inline-block px-6 py-2.5 bg-[#5A2A8F] hover:bg-[#8a2be2] text-white text-xs font-bold rounded-full transition">
                        Lihat Semua Album
                    </a>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-12 px-6 bg-black border-t border-purple-900/10 mt-auto">
        <div class="container mx-auto text-center">
            <div class="text-3xl font-black font-display tracking-wider text-white mb-4">
                FOTATO
            </div>
            <div class="flex justify-center gap-8 mb-6 text-sm text-gray-400">
                <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Home</a>
                <a href="{{ route('albums.index') }}" class="text-white font-semibold">Gallery</a>
                <a href="{{ route('events.index') }}" class="hover:text-white transition-colors">Upcoming Concert</a>
                <a href="{{ route('landing') }}#usage" class="hover:text-white transition-colors">FAQ</a>
            </div>
            <p class="text-gray-600 text-xs font-sans">2026 © FOTATO.Comp</p>
        </div>
    </footer>
</body>
</html>
