<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fotato - {{ $album->title }}</title>
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
                    <input type="text" name="search" placeholder="Search..." class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-1.5 pl-4 pr-8 text-white focus:outline-none focus:border-[#a855f7] w-36 md:w-44 font-sans placeholder:text-gray-400">
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
        <div class="container mx-auto max-w-6xl" x-data="{ 
            activeFilter: 'semua', 
            showTemukanWajah: false, 
            selectedPhoto: null, 
            showPreviewModal: false 
        }">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-purple-300/40 mb-6 font-sans">
                <a href="{{ route('albums.index') }}" class="hover:text-purple-300 transition-colors">Gallery</a>
                <span>&gt;</span>
                <span class="text-purple-300/80">{{ $album->title }}</span>
            </div>

            <!-- Statistics Calculation -->
            @php
                $allPhotos = $album->photos;
                $totalPhotos = $allPhotos->count();
                
                // Get IDs of photos in this album that have been purchased (completed transaction) by anyone (global stats)
                $soldPhotoIds = \App\Models\TransactionItem::whereIn('photo_id', $allPhotos->pluck('id'))
                    ->whereHas('transaction', function($query) {
                        $query->where('status', 'completed');
                    })
                    ->pluck('photo_id')
                    ->unique()
                    ->toArray();
                
                $soldCount = count($soldPhotoIds);
                $availableCount = $totalPhotos - $soldCount;

                // Load IDs of photos in this album purchased by the current user
                $purchasedPhotoIds = [];
                if (auth()->check()) {
                    $purchasedPhotoIds = \App\Models\TransactionItem::whereIn('photo_id', $allPhotos->pluck('id'))
                        ->whereHas('transaction', function($query) {
                            $query->where('buyer_id', auth()->id())
                                  ->where('status', 'completed');
                        })
                        ->pluck('photo_id')
                        ->unique()
                        ->toArray();
                }
                
                // Photographer count: number of photographers in the system
                $photographerCount = \App\Models\User::where('role', 'photographer')->count();
            @endphp

            @auth
                <!-- Statistics Panel -->
                <div class="border border-purple-500/20 bg-[#0f0720]/60 rounded-3xl p-6 mb-8 flex flex-col md:flex-row md:items-center justify-around gap-6 divide-y md:divide-y-0 md:divide-x divide-purple-500/10">
                    <!-- Column 1: Total Foto -->
                    <div class="flex items-center gap-4 w-full md:w-auto md:px-6 py-2 md:py-0">
                        <div class="w-12 h-12 rounded-full border border-purple-500/20 bg-purple-500/5 flex items-center justify-center text-[#8A4FFF]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375 0 1 1-.75 0 .375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-black font-display text-white leading-none mb-1">{{ number_format($totalPhotos, 0, ',', '.') }}</p>
                            <p class="text-purple-300/40 text-[10px] font-semibold uppercase tracking-wider">Total Foto</p>
                        </div>
                    </div>

                    <!-- Column 2: Tersedia -->
                    <div class="flex items-center gap-4 w-full md:w-auto md:px-6 pt-4 md:pt-0">
                        <div class="w-12 h-12 rounded-full border border-green-500/20 bg-green-500/5 flex items-center justify-center text-green-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-black font-display text-white leading-none mb-1">{{ number_format($availableCount, 0, ',', '.') }}</p>
                            <p class="text-purple-300/40 text-[10px] font-semibold uppercase tracking-wider">Tersedia</p>
                        </div>
                    </div>

                    <!-- Column 3: Terjual -->
                    <div class="flex items-center gap-4 w-full md:w-auto md:px-6 pt-4 md:pt-0">
                        <div class="w-12 h-12 rounded-full border border-purple-500/20 bg-purple-500/5 flex items-center justify-center text-[#8A4FFF]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-black font-display text-white leading-none mb-1">{{ number_format($soldCount, 0, ',', '.') }}</p>
                            <p class="text-purple-300/40 text-[10px] font-semibold uppercase tracking-wider">Terjual</p>
                        </div>
                    </div>

                    <!-- Column 4: Fotografer -->
                    <div class="flex items-center gap-4 w-full md:w-auto md:px-6 pt-4 md:pt-0">
                        <div class="w-12 h-12 rounded-full border border-purple-500/20 bg-purple-500/5 flex items-center justify-center text-[#8A4FFF]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a.75.75 0 0 0 0-1.445 6.75 6.75 0 0 0-12 0 .75.75 0 0 0 0 1.445m12 0a.75.75 0 0 1 0 1.445m0-1.445-1.445-1.445m1.445 1.445v1.445M6 18.72a.75.75 0 0 0-1.445 0m1.445 0A.75.75 0 0 1 4.555 20.16m1.445-1.445-1.445 1.445M4.555 20.16H18m-12 0v1.445M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-black font-display text-white leading-none mb-1">{{ $photographerCount }}</p>
                            <p class="text-purple-300/40 text-[10px] font-semibold uppercase tracking-wider">Fotografer</p>
                        </div>
                    </div>
                </div>
            @endauth

            <!-- Session Alerts -->
            @if(session('success'))
                <div class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 px-6 py-4 rounded-2xl text-center font-medium text-sm">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-400 px-6 py-4 rounded-2xl text-center font-medium text-sm">
                    ✗ {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 bg-blue-500/10 border border-blue-500/30 text-blue-400 px-6 py-4 rounded-2xl text-center font-medium text-sm">
                    ℹ {{ session('info') }}
                </div>
            @endif

            <!-- Filters Row -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
                <!-- Left: Status Tabs -->
                <div class="flex flex-wrap gap-2">
                    @auth
                        <button @click="activeFilter = 'semua'; filterPhotos('all')" :class="activeFilter === 'semua' ? 'bg-[#5A2A8F] text-white border-transparent shadow-lg shadow-purple-500/10' : 'bg-[#1f0e3d]/50 text-purple-200 border-purple-500/20'" class="px-5 py-2.5 rounded-full text-xs font-bold border transition-all">
                            Semua ({{ $totalPhotos }})
                        </button>
                        <button @click="activeFilter = 'tersedia'; filterPhotos('available')" :class="activeFilter === 'tersedia' ? 'bg-[#5A2A8F] text-white border-transparent shadow-lg shadow-purple-500/10' : 'bg-[#1f0e3d]/50 text-purple-200 border-purple-500/20'" class="px-5 py-2.5 rounded-full text-xs font-bold border transition-all">
                            Tersedia ({{ $totalPhotos - count($purchasedPhotoIds) }})
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-[#1f0e3d]/50 text-purple-200 border border-purple-500/20 rounded-full text-xs font-bold transition-all">
                            Semua ({{ $totalPhotos }})
                        </a>
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-[#1f0e3d]/50 text-purple-200 border border-purple-500/20 rounded-full text-xs font-bold transition-all">
                            Tersedia ({{ $totalPhotos }})
                        </a>
                    @endauth
                </div>

                <!-- Right: Dropdowns & Action Button -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Photographer Select -->
                    @auth
                        <select class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-2.5 px-4 text-white focus:outline-none focus:border-[#a855f7] font-sans">
                            <option value="semua">Semua Fotografer</option>
                            @if($album->photographer)
                                <option value="{{ $album->photographer->id }}">{{ $album->photographer->name }}</option>
                            @endif
                        </select>
                    @else
                        <div class="relative">
                            <select disabled class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-2.5 px-4 text-white/50 focus:outline-none font-sans cursor-pointer">
                                <option value="semua">Semua Fotografer</option>
                            </select>
                            <a href="{{ route('login') }}" class="absolute inset-0 z-10"></a>
                        </div>
                    @endauth

                    <!-- Sort Select -->
                    @auth
                        <select onchange="sortPhotos(this.value)" class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-2.5 px-4 text-white focus:outline-none focus:border-[#a855f7] font-sans">
                            <option value="terbaru">Urutkan: Terbaru</option>
                            <option value="harga-murah">Harga: Terendah</option>
                            <option value="harga-mahal">Harga: Tertinggi</option>
                        </select>
                    @else
                        <div class="relative">
                            <select disabled class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-2.5 px-4 text-white/50 focus:outline-none font-sans cursor-pointer">
                                <option value="terbaru">Urutkan: Terbaru</option>
                            </select>
                            <a href="{{ route('login') }}" class="absolute inset-0 z-10"></a>
                        </div>
                    @endauth

                    <!-- Face Scan Action Link (Now Temukan Wajah) -->
                    @auth
                        <a href="{{ route('buyer.register-face') }}" class="px-5 py-2.5 bg-[#FFE600] hover:bg-[#fff04d] text-black text-xs font-bold rounded-full transition duration-300 flex items-center gap-1.5 shadow-md shadow-[#FFE600]/25">
                            Temukan Wajah 📷
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-[#FFE600] hover:bg-[#fff04d] text-black text-xs font-bold rounded-full transition duration-300 flex items-center gap-1.5 shadow-md shadow-[#FFE600]/25">
                            Temukan Wajah 📷
                        </a>
                    @endauth
                </div>
            </div>

            @auth
                <!-- Temukan Wajah Drawer -->
                <div x-show="showTemukanWajah" x-init="$watch('showTemukanWajah', value => { if(!value && typeof stopDrawerCamera === 'function') stopDrawerCamera(); })" class="mb-8 p-6 border border-purple-500/20 bg-[#0f0720]/60 rounded-3xl shadow-xl transition-all duration-300 space-y-6" style="display: none;" id="temukan-wajah-drawer">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Left: Face Source Selection -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-bold text-white uppercase tracking-wider font-display">Metode Pencarian</h4>
                            
                            <!-- Option 1: Registered Face (if available) -->
                            @if(auth()->user()->userFace)
                                <div class="p-4 border border-purple-500/20 bg-purple-500/5 rounded-2xl flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-bold text-white">Wajah Terdaftar Anda</p>
                                        <p class="text-[10px] text-purple-300/50 mt-0.5">Cari menggunakan data wajah registrasi Anda.</p>
                                    </div>
                                    <button id="searchRegisteredFaceBtn" class="px-4 py-2 bg-[#FFE600] hover:bg-[#fff04d] text-black text-[10px] font-black rounded-full transition-all shadow-md">
                                        Cari Foto Saya
                                    </button>
                                </div>
                            @else
                                <div class="p-4 border border-yellow-500/20 bg-yellow-500/5 rounded-2xl flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-bold text-yellow-400">Belum Ada Wajah Terdaftar</p>
                                        <p class="text-[10px] text-yellow-200/50 mt-0.5">Daftarkan wajah Anda di profile untuk mencari otomatis tanpa scan.</p>
                                    </div>
                                    <a href="{{ route('buyer.register-face') }}" class="px-4 py-2 bg-[#FFE600] hover:bg-[#fff04d] text-black text-[10px] font-bold rounded-full transition-all shadow-md">
                                        Daftar Wajah
                                    </a>
                                </div>
                            @endif

                            <!-- Option 2: Temporary Scan/Upload -->
                            <div class="p-4 border border-purple-500/10 bg-[#0d061a]/40 rounded-2xl space-y-3">
                                <p class="text-xs font-bold text-white">Scan Wajah Baru (Opsional)</p>
                                <div class="flex flex-wrap gap-2">
                                    <button id="drawerCameraBtn" class="px-3.5 py-1.5 bg-[#1f0e3d]/50 border border-purple-500/30 text-purple-200 hover:bg-purple-500/20 hover:text-white text-[10px] font-bold rounded-full transition-all">
                                        📷 Kamera
                                    </button>
                                    <label for="drawerUploadFace" class="px-3.5 py-1.5 bg-[#1f0e3d]/50 border border-purple-500/30 text-purple-200 hover:bg-purple-500/20 hover:text-white text-[10px] font-bold rounded-full transition-all cursor-pointer">
                                        📁 Upload Foto
                                    </label>
                                    <input type="file" id="drawerUploadFace" accept="image/jpeg,image/png,image/webp" class="hidden">
                                </div>

                                <!-- Video & Canvas Preview -->
                                <div class="relative rounded-xl overflow-hidden border border-purple-500/20 bg-[#0d061a] flex items-center justify-center min-h-[140px] max-w-[240px]" style="display:none;" id="drawerMediaContainer">
                                    <video id="drawerVideo" width="320" height="240" autoplay class="w-full h-auto object-cover" style="display:none;"></video>
                                    <canvas id="drawerCanvas" width="320" height="240" class="w-full h-auto object-cover" style="display:none;"></canvas>
                                    <img id="drawerPreview" class="w-full h-auto object-cover" style="display:none;">
                                </div>
                                <div id="drawerFaceStatus" class="text-[10px] font-medium text-purple-300"></div>
                            </div>
                        </div>

                        <!-- Right: Actions & Guide -->
                        <div class="space-y-4 flex flex-col justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-2 font-display">💡 Cara Kerja</h4>
                                <ul class="text-[11px] text-purple-200/60 space-y-1.5 list-disc list-inside">
                                    <li>Gunakan wajah terdaftar Anda untuk hasil instan tanpa kamera.</li>
                                    <li>Atau scan wajah sementara untuk mencari wajah lain/baru.</li>
                                    <li>Foto yang tidak cocok akan disembunyikan secara otomatis.</li>
                                </ul>
                            </div>

                            <div class="space-y-2">
                                <button id="drawerSearchBtn" class="w-full px-5 py-2.5 bg-[#FFE600] hover:bg-[#fff04d] text-black text-xs font-black rounded-full shadow-md shadow-[#FFE600]/25 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    🔍 Cari Wajah Scan
                                </button>
                                <button id="resetFilterBtn" class="w-full px-5 py-2.5 bg-[#1f0e3d]/50 border border-purple-500/30 text-purple-200 hover:bg-purple-500/20 hover:text-white text-xs font-bold rounded-full transition duration-300" style="display:none;">
                                    ✕ Reset Pencarian Wajah
                                </button>
                                <div id="drawerLoading" style="display:none;" class="flex items-center justify-center text-purple-400 text-xs font-semibold gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Mencari foto...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endauth

            <!-- Photos Grid -->
            @if ($album->photos->count() > 0)
                <div id="photos-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($album->photos as $photo)
                        @php
                            $isPurchased = auth()->check() ? in_array($photo->id, $purchasedPhotoIds) : false;
                            $displayPath = $photo->getDisplayPath(auth()->id());
                            
                            // Get transaction item for download if purchased
                            $transactionItem = null;
                            if ($isPurchased) {
                                $transactionItem = \App\Models\TransactionItem::where('photo_id', $photo->id)
                                    ->whereHas('transaction', function($q) {
                                        $q->where('buyer_id', auth()->id())
                                          ->where('status', 'completed');
                                    })->first();
                            }
                        @endphp
                        
                        @php
                            $filename = pathinfo(basename($photo->original_path), PATHINFO_FILENAME);
                            $priceFormatted = 'Rp ' . number_format($photo->price, 0, ',', '.');
                            $isOwned = $isPurchased;
                            $downloadUrl = $transactionItem ? route('purchase.download', $transactionItem->id) : '';
                        @endphp
                        
                        <div class="photo-card relative bg-[#0f0720]/60 border border-purple-500/20 rounded-3xl overflow-hidden hover:scale-[1.02] hover:shadow-[0_0_20px_rgba(168,85,247,0.15)] transition-all duration-300 group {{ $isPurchased ? 'status-owned' : 'status-available' }} cursor-pointer"
                             data-id="{{ $photo->id }}"
                             data-price="{{ $photo->price }}" 
                             data-date="{{ $photo->created_at }}"
                             @auth
                             @click="selectedPhoto = {
                                 id: {{ $photo->id }},
                                 filename: '{{ $filename }}',
                                 price: '{{ $priceFormatted }}',
                                 isOwned: {{ $isOwned ? 'true' : 'false' }},
                                 displayUrl: '{{ asset('storage/' . $displayPath) }}',
                                 downloadUrl: '{{ $downloadUrl }}',
                                 photographerName: '{{ $album->photographer?->name ?? 'Admin' }}',
                                 albumTitle: '{{ $album->title }}',
                                 location: '{{ $album->location }}',
                                 eventDate: '{{ \Carbon\Carbon::parse($album->event_date)->format('d M Y - H:i') }}'
                             }; showPreviewModal = true;"
                             @endauth>
                            
                            @guest
                                <!-- Guest Blur & Darken Overlay Redirect -->
                                <a href="{{ route('login') }}" class="absolute inset-0 z-30 bg-[#0d061a]/65 backdrop-blur-[6px] transition-all duration-300 group-hover:bg-[#0d061a]/45 cursor-pointer"></a>
                            @endguest

                            <!-- Photo Image Area -->
                            <div class="relative h-72 bg-gray-900 overflow-hidden flex items-center justify-center">
                                @if($displayPath && file_exists(public_path('storage/' . $displayPath)))
                                    <img src="{{ asset('storage/' . $displayPath) }}" alt="Photo" class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105">
                                    
                                    @if(!$isPurchased && !$photo->watermark_path)
                                        <!-- Watermark overlay -->
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <div class="text-white/20 text-3xl font-bold transform -rotate-45 font-display tracking-widest">
                                                FOTATO
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-gray-500 text-center">
                                        <div class="text-4xl mb-2">🖼️</div>
                                        <p class="text-xs">Foto tidak tersedia</p>
                                    </div>
                                @endif

                                <!-- Heart Wishlist Button Overlay -->
                                <div class="absolute top-4 right-4" x-data="{ liked: false }">
                                    <button @click="liked = !liked; event.stopPropagation();" class="w-8 h-8 rounded-full bg-black/45 backdrop-blur-md flex items-center justify-center text-white hover:bg-purple-600 transition-colors shadow-md">
                                        <svg class="w-4 h-4 transition-colors" :class="liked ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-white'" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Bottom Image Overlay (Status & Photographer Credit) -->
                                <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between pointer-events-none">
                                    <!-- Status tag -->
                                    @if($isPurchased)
                                        <span class="bg-[#5A2A8F]/85 backdrop-blur-sm text-white text-[9px] font-black px-2.5 py-1 rounded-full shadow-md">
                                            OWNED
                                        </span>
                                    @else
                                        <span class="bg-green-500/85 backdrop-blur-sm text-white text-[9px] font-black px-2.5 py-1 rounded-full shadow-md">
                                            AVAILABLE
                                        </span>
                                    @endif

                                    <!-- Photographer credit -->
                                    <div class="flex items-center gap-1.5 bg-black/45 backdrop-blur-md px-2.5 py-1 rounded-full shadow-md">
                                        <div class="w-4 h-4 rounded-full bg-purple-500 flex items-center justify-center text-white text-[8px] font-bold">
                                            {{ substr($album->photographer?->name ?? 'Admin', 0, 1) }}
                                        </div>
                                        <span class="text-white text-[9px] font-medium">{{ $album->photographer?->name ?? 'Admin' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border border-purple-500/20 bg-[#0f0720]/40 rounded-[28px] p-16 text-center my-10 max-w-lg mx-auto">
                    <div class="text-5xl mb-6">📸</div>
                    <h3 class="text-xl font-bold text-gray-300 mb-3">Belum Ada Foto</h3>
                    <p class="text-gray-400 text-sm mb-6 leading-relaxed">Album ini belum memiliki foto yang ditambahkan oleh fotografer.</p>
                    <a href="{{ route('albums.index') }}" class="inline-block px-6 py-2.5 bg-[#5A2A8F] hover:bg-[#8a2be2] text-white text-xs font-bold rounded-full transition">
                        ← Kembali ke Galeri
                    </a>
                </div>
            @endif

            <!-- Preview Modal -->
            <div x-show="showPreviewModal" 
                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 style="display: none;">
                
                <div @click.away="showPreviewModal = false; selectedPhoto = null" 
                     class="relative w-full max-w-3xl bg-[#9b8da9] rounded-3xl overflow-hidden shadow-2xl p-6 md:p-8 flex flex-col md:flex-row gap-6 md:gap-8 border border-white/10">
                    
                    <!-- Close Button -->
                    <button @click="showPreviewModal = false; selectedPhoto = null" 
                            class="absolute top-4 right-4 text-white/80 hover:text-white text-2xl font-bold z-10 transition">
                        ✕
                    </button>

                    <!-- Left: Image Preview -->
                    <div class="w-full md:w-1/2 flex items-center justify-center bg-black/20 rounded-2xl overflow-hidden aspect-square md:aspect-auto md:h-[400px]">
                        <img :src="selectedPhoto ? selectedPhoto.displayUrl : ''" 
                             alt="Preview" 
                             class="w-full h-full object-cover">
                    </div>

                    <!-- Right: Photo Details & Actions -->
                    <div class="w-full md:w-1/2 flex flex-col justify-between bg-[#5c4a78] rounded-2xl p-6 text-white">
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-semibold text-purple-200 uppercase tracking-wider">Informasi Foto</span>
                                <h3 class="text-xl font-bold font-display text-white mt-1 break-all" x-text="selectedPhoto ? selectedPhoto.filename : ''"></h3>
                            </div>

                            <div class="space-y-3 pt-4 border-t border-white/10">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-purple-200/70">Harga</span>
                                    <span class="text-lg font-black text-green-400" x-text="selectedPhoto ? selectedPhoto.price : ''"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-purple-200/70">Album</span>
                                    <span class="text-sm font-medium text-white text-right" x-text="selectedPhoto ? selectedPhoto.albumTitle : ''"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-purple-200/70">Fotografer</span>
                                    <span class="text-sm font-medium text-white text-right" x-text="selectedPhoto ? selectedPhoto.photographerName : ''"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-purple-200/70">Lokasi</span>
                                    <span class="text-sm font-medium text-white text-right" x-text="selectedPhoto ? selectedPhoto.location : ''"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-purple-200/70">Tanggal Event</span>
                                    <span class="text-sm font-medium text-white text-right" x-text="selectedPhoto ? selectedPhoto.eventDate : ''"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-6 mt-6 border-t border-white/10">
                            <!-- If Owned -->
                            <template x-if="selectedPhoto && selectedPhoto.isOwned">
                                <a :href="selectedPhoto.downloadUrl" 
                                   class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-center font-bold rounded-xl transition block shadow-lg">
                                    📥 Download Foto
                                </a>
                            </template>

                            <!-- If Available (Not Owned Yet) -->
                            <template x-if="selectedPhoto && !selectedPhoto.isOwned">
                                <div class="flex gap-4">
                                    <!-- Beli Sekarang -->
                                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="photo_id" :value="selectedPhoto.id">
                                        <input type="hidden" name="buy_now" value="1">
                                        <button type="submit" 
                                                class="w-full py-3 bg-gradient-to-r from-[#8A4FFF] to-[#5A2A8F] hover:from-[#9b5cff] hover:to-[#6d30b0] text-white font-bold rounded-xl transition shadow-lg transform hover:scale-[1.02] text-sm">
                                            Beli Sekarang
                                        </button>
                                    </form>

                                    <!-- Tambah ke Keranjang -->
                                    <form action="{{ route('cart.add') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="photo_id" :value="selectedPhoto.id">
                                        <button type="submit" 
                                                class="p-3 bg-[#3d2f53] hover:bg-[#4b3b65] border border-white/10 text-white rounded-xl transition flex items-center justify-center font-bold">
                                            🛒
                                        </button>
                                    </form>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

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

    <!-- Client-Side Filter & Sort Scripts -->
    <script>
        function filterPhotos(status) {
            const cards = document.querySelectorAll('.photo-card');
            cards.forEach(card => {
                if (status === 'all') {
                    card.style.display = 'block';
                } else if (status === 'available') {
                    if (card.classList.contains('status-available')) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        }

        function sortPhotos(criteria) {
            const container = document.getElementById('photos-container');
            if (!container) return;
            const cards = Array.from(container.querySelectorAll('.photo-card'));
            
            cards.sort((a, b) => {
                if (criteria === 'harga-murah') {
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                } else if (criteria === 'harga-mahal') {
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                } else {
                    // Default / Terbaru
                    return new Date(b.dataset.date) - new Date(a.dataset.date);
                }
            });
            
            // Re-append elements in sorted order
            cards.forEach(card => container.appendChild(card));
        }
    </script>

    @auth
    <!-- Registered Face Data Injection -->
    @if(auth()->user()->userFace && auth()->user()->userFace->face_descriptor)
        <script>
            window.registeredFaceEmbedding = {!! json_encode(auth()->user()->userFace->face_descriptor) !!};
        </script>
    @endif

    <!-- Face API JS Library -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    
    <!-- Temukan Wajah Drawer Scripts -->
    <script>
        let drawerFaceEmbedding = null;
        let drawerVideoStream = null;
        let modelsLoaded = false;
        let modelsLoading = false;
        const albumId = {{ $album->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function loadFaceApiModels() {
            if (modelsLoaded) return true;
            if (modelsLoading) {
                while (modelsLoading) {
                    await new Promise(r => setTimeout(r, 100));
                }
                return modelsLoaded;
            }
            
            modelsLoading = true;
            const statusDiv = document.getElementById('drawerFaceStatus');
            if (statusDiv) {
                statusDiv.className = "text-[10px] font-medium text-purple-400";
                statusDiv.textContent = "⏳ Memuat model AI face-api...";
            }
            
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
                await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
                await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
                modelsLoaded = true;
                if (statusDiv) {
                    statusDiv.className = "text-[10px] font-medium text-green-400";
                    statusDiv.textContent = "✓ Model AI siap.";
                }
            } catch (err) {
                console.error("Gagal memuat model face-api:", err);
                if (statusDiv) {
                    statusDiv.className = "text-[10px] font-medium text-red-400";
                    statusDiv.textContent = "✗ Gagal memuat model AI.";
                }
            } finally {
                modelsLoading = false;
            }
            return modelsLoaded;
        }

        async function startDrawerCamera() {
            const video = document.getElementById('drawerVideo');
            const canvas = document.getElementById('drawerCanvas');
            const preview = document.getElementById('drawerPreview');
            const container = document.getElementById('drawerMediaContainer');
            const statusDiv = document.getElementById('drawerFaceStatus');
            const cameraBtn = document.getElementById('drawerCameraBtn');

            const ready = await loadFaceApiModels();
            if (!ready) return;

            try {
                drawerVideoStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 320 },
                        height: { ideal: 240 },
                        facingMode: 'user'
                    }
                });
                
                video.srcObject = drawerVideoStream;
                if (container) container.style.display = 'flex';
                if (video) video.style.display = 'block';
                if (canvas) canvas.style.display = 'none';
                if (preview) preview.style.display = 'none';

                if (cameraBtn) {
                    cameraBtn.textContent = '⏳ Mengambil...';
                    cameraBtn.disabled = true;
                }

                if (statusDiv) {
                    statusDiv.className = "text-[10px] font-medium text-purple-300";
                    statusDiv.textContent = "Posisikan wajah Anda. Foto diambil otomatis dalam 3 detik...";
                }

                await new Promise(resolve => setTimeout(resolve, 3000));

                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth || 320;
                canvas.height = video.videoHeight || 240;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                if (video) video.style.display = 'none';
                if (canvas) canvas.style.display = 'block';

                stopDrawerCamera();

                if (statusDiv) statusDiv.textContent = "Mendeteksi wajah...";
                await processDrawerFaceSource(canvas);

            } catch (err) {
                console.error('Camera error:', err);
                if (statusDiv) {
                    statusDiv.className = "text-[10px] font-medium text-red-400";
                    statusDiv.textContent = "Gagal mengakses kamera. Silakan upload foto.";
                }
                if (cameraBtn) {
                    cameraBtn.textContent = '📷 Kamera';
                    cameraBtn.disabled = false;
                }
            }
        }

        function stopDrawerCamera() {
            if (drawerVideoStream) {
                drawerVideoStream.getTracks().forEach(track => track.stop());
                drawerVideoStream = null;
            }
            const cameraBtn = document.getElementById('drawerCameraBtn');
            if (cameraBtn) {
                cameraBtn.textContent = '📷 Kamera';
                cameraBtn.disabled = false;
            }
        }

        async function handleDrawerFileUpload(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('drawerPreview');
            const video = document.getElementById('drawerVideo');
            const canvas = document.getElementById('drawerCanvas');
            const container = document.getElementById('drawerMediaContainer');
            const statusDiv = document.getElementById('drawerFaceStatus');

            if (!file) return;

            const ready = await loadFaceApiModels();
            if (!ready) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (container) container.style.display = 'flex';
                if (video) video.style.display = 'none';
                if (canvas) canvas.style.display = 'none';
                stopDrawerCamera();

                if (statusDiv) {
                    statusDiv.className = "text-[10px] font-medium text-purple-300";
                    statusDiv.textContent = "Mendapatkan wajah...";
                }

                const img = new Image();
                img.onload = async () => {
                    await processDrawerFaceSource(img);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        async function processDrawerFaceSource(source) {
            const statusDiv = document.getElementById('drawerFaceStatus');
            const searchBtn = document.getElementById('drawerSearchBtn');
            
            try {
                const detection = await faceapi
                    .detectSingleFace(source, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    if (statusDiv) {
                        statusDiv.className = "text-[10px] font-medium text-red-400";
                        statusDiv.textContent = "Wajah tidak terdeteksi. Coba foto yang lebih jelas.";
                    }
                    drawerFaceEmbedding = null;
                    if (searchBtn) searchBtn.disabled = true;
                } else {
                    drawerFaceEmbedding = Array.from(detection.descriptor);
                    if (statusDiv) {
                        statusDiv.className = "text-[10px] font-medium text-green-400";
                        statusDiv.textContent = "✓ Wajah terdeteksi!";
                    }
                    if (searchBtn) searchBtn.disabled = false;
                }
            } catch (err) {
                console.error('Face api processing error:', err);
                if (statusDiv) {
                    statusDiv.className = "text-[10px] font-medium text-red-400";
                    statusDiv.textContent = "Gagal memproses wajah.";
                }
                drawerFaceEmbedding = null;
                if (searchBtn) searchBtn.disabled = true;
            }
        }

        async function performDrawerSearch(embedding) {
            const loading = document.getElementById('drawerLoading');
            const resetBtn = document.getElementById('resetFilterBtn');
            
            if (!embedding) return;

            if (loading) loading.style.display = 'flex';
            if (resetBtn) resetBtn.style.display = 'none';

            try {
                const response = await fetch('/face-scan/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        embedding_vector: embedding,
                        album_id: albumId
                    })
                });

                const data = await response.json();
                if (loading) loading.style.display = 'none';

                if (data.success) {
                    applyPhotosFilter(data.photos);
                    if (resetBtn) resetBtn.style.display = 'block';
                    
                    const container = document.getElementById('photos-container');
                    if (container) {
                        container.scrollIntoView({ behavior: 'smooth' });
                    }
                } else {
                    alert(data.message || 'Pencarian gagal.');
                }
            } catch (err) {
                console.error('Search request failed:', err);
                if (loading) loading.style.display = 'none';
                alert('Terjadi kesalahan jaringan.');
            }
        }

        function applyPhotosFilter(matchedPhotos) {
            const matchedIds = matchedPhotos.map(p => p.id.toString());
            const cards = document.querySelectorAll('.photo-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const cardId = card.getAttribute('data-id');
                if (matchedIds.includes(cardId)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            let noResultsPlaceholder = document.getElementById('no-photos-match-placeholder');
            if (visibleCount === 0) {
                if (!noResultsPlaceholder) {
                    noResultsPlaceholder = document.createElement('div');
                    noResultsPlaceholder.id = 'no-photos-match-placeholder';
                    noResultsPlaceholder.className = 'col-span-full text-center py-16 border border-purple-500/20 bg-[#0f0720]/40 rounded-[28px] max-w-lg mx-auto w-full';
                    noResultsPlaceholder.innerHTML = `
                        <div class="text-5xl mb-6">🔍</div>
                        <h3 class="text-xl font-bold text-gray-300 mb-3 font-display">Tidak Ada Foto yang Cocok</h3>
                        <p class="text-gray-400 text-sm mb-6 leading-relaxed font-sans">Wajah tidak terdeteksi pada foto-foto di album ini.</p>
                    `;
                    const container = document.getElementById('photos-container');
                    if (container) {
                        container.appendChild(noResultsPlaceholder);
                    }
                } else {
                    noResultsPlaceholder.style.display = 'block';
                }
            } else {
                if (noResultsPlaceholder) {
                    noResultsPlaceholder.style.display = 'none';
                }
            }
        }

        function resetPhotosFilter() {
            const cards = document.querySelectorAll('.photo-card');
            cards.forEach(card => {
                card.style.display = 'block';
            });

            const noResultsPlaceholder = document.getElementById('no-photos-match-placeholder');
            if (noResultsPlaceholder) {
                noResultsPlaceholder.style.display = 'none';
            }

            const resetBtn = document.getElementById('resetFilterBtn');
            if (resetBtn) {
                resetBtn.style.display = 'none';
            }

            drawerFaceEmbedding = null;
            const searchBtn = document.getElementById('drawerSearchBtn');
            if (searchBtn) searchBtn.disabled = true;

            const statusDiv = document.getElementById('drawerFaceStatus');
            if (statusDiv) statusDiv.textContent = '';

            const container = document.getElementById('drawerMediaContainer');
            if (container) container.style.display = 'none';

            const video = document.getElementById('drawerVideo');
            if (video) video.style.display = 'none';

            const canvas = document.getElementById('drawerCanvas');
            if (canvas) canvas.style.display = 'none';

            const preview = document.getElementById('drawerPreview');
            if (preview) {
                preview.src = '';
                preview.style.display = 'none';
            }

            const uploadFace = document.getElementById('drawerUploadFace');
            if (uploadFace) uploadFace.value = '';

            stopDrawerCamera();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchRegisteredFaceBtn = document.getElementById('searchRegisteredFaceBtn');
            const drawerCameraBtn = document.getElementById('drawerCameraBtn');
            const drawerUploadFace = document.getElementById('drawerUploadFace');
            const drawerSearchBtn = document.getElementById('drawerSearchBtn');
            const resetFilterBtn = document.getElementById('resetFilterBtn');

            if (searchRegisteredFaceBtn) {
                searchRegisteredFaceBtn.addEventListener('click', () => {
                    if (window.registeredFaceEmbedding) {
                        performDrawerSearch(window.registeredFaceEmbedding);
                    } else {
                        alert('Wajah terdaftar tidak ditemukan.');
                    }
                });
            }

            if (drawerCameraBtn) {
                drawerCameraBtn.addEventListener('click', startDrawerCamera);
            }

            if (drawerUploadFace) {
                drawerUploadFace.addEventListener('change', handleDrawerFileUpload);
            }

            if (drawerSearchBtn) {
                drawerSearchBtn.addEventListener('click', () => {
                    if (drawerFaceEmbedding) {
                        performDrawerSearch(drawerFaceEmbedding);
                    }
                });
            }

            if (resetFilterBtn) {
                resetFilterBtn.addEventListener('click', resetPhotosFilter);
            }
        });
    </script>
    @endauth
</body>
</html>
