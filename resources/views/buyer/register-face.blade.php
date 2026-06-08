<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Wajah | FOTATO</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ showModal: false, activePhoto: null }" class="bg-[#0d061a] text-white font-sans selection:bg-purple-500/20 selection:text-white min-h-screen flex flex-col relative">

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
                <a href="{{ route('albums.index') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Gallery</a>
                <a href="{{ route('events.index') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Upcoming Concert</a>
                <a href="{{ route('landing') }}#usage" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">FAQ</a>
            </div>

            <div class="flex gap-4 items-center">
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
                            <a href="{{ route('buyer.register-face') }}" class="block px-4 py-2 text-xs text-white hover:bg-purple-500/20 transition-colors">
                                Temukan Wajah
                            </a>
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
                <span class="text-purple-300/80">Daftar Wajah</span>
            </div>

            <!-- Heading -->
            <div class="text-center mb-12">
                <p class="text-[10px] tracking-[0.2em] uppercase text-purple-400 font-bold mb-3">Langkah Wajib</p>
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-white mb-4">
                    Daftarkan Wajah Anda
                </h1>
                <p class="text-purple-200/50 text-sm max-w-md mx-auto leading-relaxed">
                    @if($hasFace)
                        Perbarui data wajah Anda dengan memindai ulang untuk optimasi hasil pencarian foto.
                    @else
                        Scan wajah sekali untuk mengaktifkan fitur pencocokan wajah otomatis pada semua galeri foto.
                    @endif
                </p>
            </div>

            @if(session('info'))
            <div class="border border-purple-500/20 bg-[#0f0720]/60 p-4 rounded-2xl mb-8 text-center max-w-2xl mx-auto">
                <p class="text-purple-200/70 text-xs">ℹ️ {{ session('info') }}</p>
            </div>
            @endif

            @if($hasFace)
            <!-- Stats Summary Bar -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-12 max-w-4xl mx-auto">
                <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-2xl text-center shadow-lg">
                    <div class="text-3xl font-extrabold text-white font-display">{{ $totalMatched }}</div>
                    <p class="text-purple-300/40 text-[10px] font-bold tracking-wider uppercase mt-2">Foto Cocok</p>
                </div>
                <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-2xl text-center shadow-lg">
                    <div class="text-3xl font-extrabold text-white font-display">{{ $groupedByAlbum->count() }}</div>
                    <p class="text-purple-300/40 text-[10px] font-bold tracking-wider uppercase mt-2">Album Terkait</p>
                </div>
                <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-2xl text-center col-span-2 md:col-span-1 shadow-lg">
                    <div class="text-3xl font-extrabold text-[#FFE600] font-display">Aktif</div>
                    <p class="text-purple-300/40 text-[10px] font-bold tracking-wider uppercase mt-2">Status AI Scan</p>
                </div>
            </div>
            @endif

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- LEFT COLUMN: Live Camera Stream & Tips -->
                <div class="lg:col-span-8 flex flex-col gap-6">
                    
                    <!-- Live Camera Feed -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-[28px] shadow-lg">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-base font-bold font-display text-white">Kamera Langsung</h2>
                            <div class="flex items-center gap-2 bg-[#160d28] px-3 py-1 rounded-full border border-purple-500/10">
                                <span class="inline-block w-2 h-2 rounded-full bg-slate-600 transition-all duration-300" id="liveDot"></span>
                                <span class="text-[9px] font-bold text-purple-300/40 tracking-wider uppercase" id="liveLabel">Tidak aktif</span>
                            </div>
                        </div>

                        <!-- Webcam Viewport -->
                        <div id="camWrap" class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden bg-black/50 border border-purple-500/10 shadow-inner flex items-center justify-center">
                            <div id="camPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center bg-[#160d28]/20 z-10">
                                <div class="text-5xl opacity-20 mb-3">🎥</div>
                                <p class="text-purple-200/40 text-xs text-center max-w-[220px] leading-relaxed">Klik "Aktifkan Kamera" untuk memulai scan wajah biometrik</p>
                            </div>
                            <video id="video" autoplay playsinline class="w-full h-full object-cover hidden z-0"></video>
                            <canvas id="detectionCanvas" class="absolute inset-0 w-full h-full hidden z-20 pointer-events-none"></canvas>
                            <div class="face-ring no-face absolute inset-0 rounded-2xl border-2 border-purple-500/20 pointer-events-none hidden z-30 transition-all duration-300" id="faceRing"></div>
                            <div class="guide-circle absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-60 rounded-[50%] border-2 border-dashed border-purple-500/20 pointer-events-none hidden z-30" id="guideCircle"></div>
                            <div id="statusMsg" class="absolute bottom-4 left-1/2 -translate-x-1/2 font-sans font-bold text-xs px-4 py-1.5 rounded-full backdrop-blur-md shadow-lg hidden z-40 bg-[#1f0e3d]/90 text-purple-300 border border-purple-500/20"></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4 mt-6">
                            <button id="startCamBtn" class="flex-1 py-3 border border-[#8c66ff]/30 bg-purple-950/20 text-[#8c66ff] hover:text-white hover:bg-[#8c66ff]/20 font-bold font-display rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                                🎥 Aktifkan Kamera
                            </button>
                            <button id="captureBtn" class="flex-1 py-3 bg-[#FFE600] hover:bg-[#fff04d] disabled:opacity-35 disabled:hover:scale-100 disabled:hover:bg-[#FFE600] disabled:cursor-not-allowed text-black font-bold font-display rounded-xl text-xs transition-all shadow-md shadow-[#FFE600]/10 flex items-center justify-center gap-2" disabled>
                                📸 Simpan Wajah
                            </button>
                        </div>
                    </div>

                    <!-- Accuracy Tips -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-[28px] shadow-lg">
                        <p class="text-[10px] font-bold tracking-wider text-[#a855f7] uppercase mb-4">Tips agar Akurasi Tinggi</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(['Posisikan wajah di dalam lingkaran panduan','Pastikan pencahayaan cukup dan merata','Lepas kacamata gelap atau masker','Hadap kamera secara langsung (frontal)'] as $tip)
                            <div class="flex items-start gap-2.5">
                                <span class="text-[#a855f7] text-xs">◎</span>
                                <p class="text-purple-200/60 text-xs leading-relaxed font-sans">{{ $tip }}</p>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-purple-300/40 text-[10px] leading-relaxed mt-6 pt-4 border-t border-purple-500/10 font-sans">
                            Tunggu bingkai kamera berubah <strong class="text-white">terang</strong> sebelum klik Simpan Wajah.
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Steps & Detection Status -->
                <div class="lg:col-span-4 flex flex-col gap-6">
                    
                    <!-- Progress Checklist -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-[28px] shadow-lg">
                        <p class="text-[10px] font-bold tracking-wider text-[#a855f7] uppercase mb-6">Langkah-langkah</p>

                        <div id="step1row" class="step-row flex items-start gap-4 p-3 rounded-2xl transition-all duration-300 bg-purple-500/5 border border-purple-500/10">
                            <div class="step-num active w-8 h-8 rounded-full border border-purple-400 flex items-center justify-center text-xs font-bold shrink-0 bg-purple-500/20 text-white" id="stepNum1">1</div>
                            <div>
                                <p class="font-bold text-white text-xs">Aktifkan Kamera</p>
                                <p class="text-purple-300/40 text-[10px] mt-1 font-sans">Izinkan akses kamera di browser</p>
                            </div>
                        </div>
                        <div class="w-0.5 h-6 bg-purple-500/10 ml-7 my-1"></div>
                        <div id="step2row" class="step-row flex items-start gap-4 p-3 rounded-2xl transition-all duration-300">
                            <div class="step-num pending w-8 h-8 rounded-full border border-purple-500/10 flex items-center justify-center text-xs font-bold shrink-0 bg-purple-500/5 text-purple-300/20" id="stepNum2">2</div>
                            <div>
                                <p class="font-bold text-purple-300/20 text-xs" id="s2title">Posisikan Wajah</p>
                                <p class="text-purple-300/10 text-[10px] mt-1 font-sans">Tunggu bingkai berubah terang</p>
                            </div>
                        </div>
                        <div class="w-0.5 h-6 bg-purple-500/10 ml-7 my-1"></div>
                        <div id="step3row" class="step-row flex items-start gap-4 p-3 rounded-2xl transition-all duration-300">
                            <div class="step-num pending w-8 h-8 rounded-full border border-purple-500/10 flex items-center justify-center text-xs font-bold shrink-0 bg-purple-500/5 text-purple-300/20" id="stepNum3">3</div>
                            <div>
                                <p class="font-bold text-purple-300/20 text-xs" id="s3title">Simpan Data Wajah</p>
                                <p class="text-purple-300/10 text-[10px] mt-1 font-sans">Klik tombol "Simpan Wajah"</p>
                            </div>
                        </div>
                    </div>

                    <!-- Detection Status Feedback -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-[28px] shadow-lg" id="statusCard">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-base">👁️</div>
                            <div>
                                <p class="font-bold text-white text-xs">Status Deteksi</p>
                                <p class="text-purple-300/40 text-[9px] font-sans">Real-time AI</p>
                            </div>
                        </div>
                        <p id="detectionStatusText" class="text-xs text-purple-200/50 leading-relaxed font-sans">Kamera belum aktif</p>
                    </div>

                    <!-- Saving Progress overlay -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-[28px] shadow-lg hidden text-center py-8" id="savingCard">
                        <svg class="spinner w-8 h-8 text-[#a855f7] mx-auto mb-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-white font-bold text-sm">Menyimpan data wajah…</p>
                        <p class="text-purple-300/40 text-xs mt-1 font-sans">Harap tunggu sebentar</p>
                    </div>

                    <!-- Success Redirect State -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-[28px] shadow-lg hidden text-center py-8" id="successCard">
                        <div class="text-4xl text-[#FFE600] mb-3">✓</div>
                        <p class="text-white font-bold text-sm mb-1">Berhasil Disimpan!</p>
                        <p class="text-purple-300/60 text-xs mb-4 font-sans">Memuat halaman…</p>
                        <div class="bg-purple-950/50 rounded-full h-1.5 overflow-hidden w-2/3 mx-auto">
                            <div id="successBar" class="h-full bg-gradient-to-r from-[#8c66ff] to-[#a855f7] w-0 transition-all duration-1000"></div>
                        </div>
                    </div>

                    <!-- Registered State Info -->
                    @if($hasFace)
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 p-6 rounded-[28px] shadow-lg text-center">
                        <div class="w-12 h-12 rounded-full bg-purple-500/10 border border-purple-500/20 flex items-center justify-center mx-auto mb-4 text-[#a855f7] text-lg font-bold">✓</div>
                        <p class="text-white font-bold text-xs mb-2">Wajah Anda Sudah Terdaftar</p>
                        <p class="text-purple-300/50 text-[10px] leading-relaxed mb-6 font-sans">Anda dapat mencari foto-foto konser Anda di semua album galeri.</p>
                        <a href="{{ route('albums.index') }}" class="w-full py-2.5 bg-gradient-to-r from-[#5A2A8F] to-[#8A4FFF] hover:from-[#6d30b0] hover:to-[#9b5cff] text-white font-bold font-display rounded-xl text-center text-xs transition-all flex items-center justify-center gap-1.5 shadow-md shadow-purple-500/10">
                            Lihat Galeri Album
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Matched Photos Section -->
            @if($hasFace)
            <div class="mt-16 relative z-10">
                <div class="flex items-center justify-between mb-8 border-b border-purple-500/10 pb-4">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold font-display text-white">Foto Wajah Anda</h2>
                        <p class="text-xs text-purple-200/50 mt-1 font-sans">Ditemukan {{ $totalMatched }} foto yang mirip dengan wajah Anda di seluruh album galeri.</p>
                    </div>
                    <div class="flex gap-4 text-xs font-semibold">
                        <span class="bg-purple-500/10 text-[#a855f7] px-3 py-1.5 rounded-full border border-purple-500/20">
                            📁 {{ $groupedByAlbum->count() }} Album
                        </span>
                    </div>
                </div>

                @if($groupedByAlbum->isEmpty())
                    <!-- Empty Matched State -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 p-12 rounded-[28px] text-center max-w-xl mx-auto">
                        <div class="text-5xl mb-4">🔍</div>
                        <h3 class="text-lg font-bold text-white mb-2">Belum Ada Foto Cocok</h3>
                        <p class="text-purple-200/50 text-xs leading-relaxed max-w-md mx-auto mb-6 font-sans">
                            Kami belum menemukan foto yang mengandung wajah Anda. Kemungkinan fotografer belum mengunggah foto dari acara Anda, atau wajah Anda perlu dipindai ulang dengan pencahayaan yang lebih baik.
                        </p>
                    </div>
                @else
                    <div class="space-y-12">
                        @foreach($groupedByAlbum as $group)
                            @php 
                                $album = $group['album']; 
                                $photos = $group['photos']; 
                            @endphp
                            <div class="border border-purple-500/10 bg-[#0f0720]/40 p-6 rounded-[28px] space-y-6">
                                <!-- Album Info Header -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-purple-500/5 pb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/25 flex items-center justify-center text-lg">📁</div>
                                        <div>
                                            <h3 class="font-bold text-white text-sm truncate max-w-xs sm:max-w-md">{{ $album->title ?? 'Album' }}</h3>
                                            <div class="flex items-center gap-3 text-[10px] text-purple-200/50 mt-1 flex-wrap font-sans">
                                                @if($album->location)
                                                    <span>📍 {{ $album->location }}</span>
                                                @endif
                                                @if($album->event_date)
                                                    <span>📅 {{ \Carbon\Carbon::parse($album->event_date)->translatedFormat('d M Y') }}</span>
                                                @endif
                                                <span class="px-2 py-0.5 rounded-full bg-purple-500/20 text-[#a855f7]">
                                                    {{ $photos->count() }} foto cocok
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('albums.show', $album->id) }}" class="px-4 py-2 border border-[#8c66ff]/30 hover:bg-[#8c66ff]/10 text-white font-bold font-display rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto">
                                        Lihat Album
                                        <span>&rarr;</span>
                                    </a>
                                </div>

                                <!-- Photos Grid -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                    @foreach($photos as $photo)
                                        @php
                                            $score = $photo->match_score ?? 0;
                                            $displayPath = $photo->watermark_path ?? $photo->original_path;
                                            $imgUrl = $displayPath ? asset('storage/' . $displayPath) : 'https://via.placeholder.com/300x400?text=No+Image';
                                            $isPurchased = in_array($photo->id, $purchasedPhotoIds);
                                            // Get transaction item download route if purchased
                                            $downloadRoute = '';
                                            if ($isPurchased) {
                                                $txItem = \App\Models\TransactionItem::where('photo_id', $photo->id)
                                                    ->whereHas('transaction', function ($q) {
                                                        $q->where('buyer_id', Auth::id())
                                                          ->whereIn('status', ['paid', 'completed']);
                                                    })->first();
                                                if ($txItem) {
                                                    $downloadRoute = route('purchase.download', $txItem->id);
                                                }
                                            }
                                        @endphp
                                        <div @click="activePhoto = {
                                            id: {{ $photo->id }},
                                            url: '{{ $imgUrl }}',
                                            price: 'Rp {{ number_format($photo->price, 0, ',', '.') }}',
                                            album: '{{ addslashes($album->title) }}',
                                            photographer: '{{ addslashes($album->photographer->name ?? 'Anonim') }}',
                                            location: '{{ addslashes($album->location ?? '-') }}',
                                            date: '{{ $album->event_date ? \Carbon\Carbon::parse($album->event_date)->translatedFormat('d M Y') : '-' }}',
                                            owned: {{ $isPurchased ? 'true' : 'false' }},
                                            download_url: '{{ $downloadRoute }}',
                                            cart_action: '{{ route('cart.add') }}',
                                            checkout_action: '{{ route('cart.add', ['buy_now' => 1]) }}'
                                        }; showModal = true;"
                                        class="relative bg-[#0f0720]/60 border border-purple-500/20 rounded-2xl overflow-hidden hover:scale-[1.02] hover:shadow-[0_0_20px_rgba(168,85,247,0.15)] transition-all duration-300 group cursor-pointer aspect-[3/4]">
                                            <img src="{{ $imgUrl }}" alt="Foto {{ $photo->id }}" class="w-full h-full object-cover" loading="lazy">
                                            
                                            <!-- Match Score Badge -->
                                            <div class="absolute top-3 left-3 px-2 py-1 rounded-full text-[9px] font-bold shadow-md bg-purple-600/90 text-white backdrop-blur-sm">
                                                {{ $score }}% cocok
                                            </div>

                                            @if($isPurchased)
                                                <div class="absolute top-3 right-3 bg-emerald-500 text-white text-[9px] font-bold px-2 py-1 rounded-full shadow-md backdrop-blur-sm">
                                                    Milik Anda
                                                </div>
                                            @endif

                                            <!-- Card Hover Overlay -->
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                                                <p class="text-white text-xs font-bold font-display">📸 Foto #{{ $photo->id }}</p>
                                                <p class="text-purple-300 text-[9px] mt-0.5">Klik untuk detail & beli</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

        </div>
    </main>

    <!-- Preview Modal -->
    <div x-show="showModal" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        
        <div @click.away="showModal = false; activePhoto = null" 
             class="relative w-full max-w-3xl bg-[#9b8da9] rounded-3xl overflow-hidden shadow-2xl p-6 md:p-8 flex flex-col md:flex-row gap-6 md:gap-8 border border-white/10">
            
            <!-- Close Button -->
            <button @click="showModal = false; activePhoto = null" 
                    class="absolute top-4 right-4 text-white/80 hover:text-white text-2xl font-bold z-10 transition">
                ✕
            </button>

            <!-- Left: Image Preview -->
            <div class="w-full md:w-1/2 flex items-center justify-center bg-black/20 rounded-2xl overflow-hidden aspect-square md:aspect-auto md:h-[400px]">
                <img :src="activePhoto ? activePhoto.url : ''" 
                     alt="Preview" 
                     class="w-full h-full object-cover">
            </div>

            <!-- Right: Photo Details & Actions -->
            <div class="w-full md:w-1/2 flex flex-col justify-between bg-[#5c4a78] rounded-2xl p-6 text-white">
                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-semibold text-purple-200 uppercase tracking-wider">Informasi Foto</span>
                        <h3 class="text-xl font-bold font-display text-white mt-1 break-all" x-text="activePhoto ? '📸 Foto #' + activePhoto.id : ''"></h3>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-white/10 font-sans">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-purple-200/70">Harga</span>
                            <span class="text-lg font-black text-green-400" x-text="activePhoto ? activePhoto.price : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-purple-200/70">Album</span>
                            <span class="text-sm font-medium text-white text-right" x-text="activePhoto ? activePhoto.album : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-purple-200/70">Fotografer</span>
                            <span class="text-sm font-medium text-white text-right" x-text="activePhoto ? activePhoto.photographer : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-purple-200/70">Lokasi</span>
                            <span class="text-sm font-medium text-white text-right" x-text="activePhoto ? activePhoto.location : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-purple-200/70">Tanggal Event</span>
                            <span class="text-sm font-medium text-white text-right" x-text="activePhoto ? activePhoto.date : ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-6 mt-6 border-t border-white/10">
                    <!-- If Owned -->
                    <template x-if="activePhoto && activePhoto.owned">
                        <a :href="activePhoto.download_url" 
                           class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-center font-bold rounded-xl transition block shadow-lg">
                            📥 Download Foto
                        </a>
                    </template>

                    <!-- If Available (Not Owned Yet) -->
                    <template x-if="activePhoto && !activePhoto.owned">
                        <div class="flex gap-4">
                            <!-- Beli Sekarang -->
                            <form :action="activePhoto.cart_action" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="photo_id" :value="activePhoto.id">
                                <input type="hidden" name="buy_now" value="1">
                                <button type="submit" 
                                        class="w-full py-3 bg-[#FFE600] hover:bg-[#fff04d] text-black font-bold font-display rounded-xl transition shadow-lg transform hover:scale-[1.02] text-xs">
                                    Beli Sekarang
                                </button>
                            </form>

                            <!-- Tambah ke Keranjang -->
                            <form :action="activePhoto.cart_action" method="POST">
                                @csrf
                                <input type="hidden" name="photo_id" :value="activePhoto.id">
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

    <!-- Footer -->
    <footer class="py-12 px-6 bg-black border-t border-purple-500/5 mt-auto relative z-10">
        <div class="container mx-auto text-center text-purple-300/20 text-xs font-sans">
            <p>&copy; 2026 Fotato. Semua hak dilindungi. Dibuat dengan ❤️ untuk komunitas fotografi.</p>
        </div>
    </footer>

    <!-- Face API JS Script imports -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
    const STORE_URL = @json(route('buyer.register-face.store'));
    const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

    const video        = document.getElementById('video');
    const canvas       = document.getElementById('detectionCanvas');
    const ctx          = canvas.getContext('2d');
    const faceRing     = document.getElementById('faceRing');
    const guideCircle  = document.getElementById('guideCircle');
    const statusMsgEl  = document.getElementById('statusMsg');
    const startCamBtn  = document.getElementById('startCamBtn');
    const captureBtn   = document.getElementById('captureBtn');
    const statusTxt    = document.getElementById('detectionStatusText');
    const savingCard   = document.getElementById('savingCard');
    const successCard  = document.getElementById('successCard');
    const statusCard   = document.getElementById('statusCard');
    const liveDot      = document.getElementById('liveDot');
    const liveLabel    = document.getElementById('liveLabel');

    let modelsLoaded = false, detectionLoop = null, currentDescriptor = null;

    // ── Load models ──────────────────────────────────────────────────────────────
    async function loadModels() {
        setStatus('Memuat model AI…', '#c084fc');
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
            modelsLoaded = true;
            setStatus('Model siap. Klik "Aktifkan Kamera".', '#cbd5e1');
        } catch(e) {
            setStatus('Gagal memuat model: ' + e.message, '#f87171');
        }
    }

    // ── Start camera ──────────────────────────────────────────────────────────────
    startCamBtn.addEventListener('click', async () => {
        if (!modelsLoaded) { setStatus('Model masih dimuat, tunggu sebentar…', '#cbd5e1'); return; }
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video:{ width:640, height:480, facingMode:'user' } });
            video.srcObject = stream;
            video.style.display = 'block';
            canvas.style.display = 'block';
            document.getElementById('camPlaceholder').style.display = 'none';
            faceRing.style.display = 'block';
            guideCircle.style.display = 'block';
            statusMsgEl.style.display = 'block';

            liveDot.className = 'inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
            liveLabel.textContent = 'Live';
            liveLabel.className = 'text-[9px] font-bold text-emerald-400 tracking-wider uppercase';
            startCamBtn.textContent = '🔄 Kamera Aktif';
            startCamBtn.disabled = true;
            startCamBtn.className = 'flex-1 py-3 border border-purple-500/10 bg-purple-500/5 text-purple-300/40 font-bold font-display rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-not-allowed';
            markStep(1,'done'); activateStep(2);

            video.addEventListener('loadedmetadata', () => {
                canvas.width  = video.videoWidth  || 640;
                canvas.height = video.videoHeight || 480;
                startLoop();
            });
        } catch(err) {
            setStatus('Kamera tidak dapat diakses: ' + err.message, '#f87171');
        }
    });

    // ── Detection loop ─────────────────────────────────────────────────────────────
    function startLoop() {
        detectionLoop = setInterval(async () => {
            if (video.paused || video.ended) return;
            try {
                const det = await faceapi
                    .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence:0.6 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (det) {
                    const { box } = det.detection;
                    const sx = canvas.width / video.videoWidth;
                    const sy = canvas.height / video.videoHeight;
                    const bx=box.x*sx, by=box.y*sy, bw=box.width*sx, bh=box.height*sy;

                    // Glow box
                    ctx.shadowColor='rgba(168,85,247,0.6)'; ctx.shadowBlur=16;
                    ctx.strokeStyle='rgba(168,85,247,0.9)'; ctx.lineWidth=2;
                    ctx.strokeRect(bx,by,bw,bh);
                    ctx.shadowBlur=0;

                    // Corner accents
                    const L=16; ctx.strokeStyle='#a855f7'; ctx.lineWidth=3;
                    [[bx,by+L,bx,by,bx+L,by],[bx+bw-L,by,bx+bw,by,bx+bw,by+L],
                     [bx,by+bh-L,bx,by+bh,bx+L,by+bh],[bx+bw,by+bh-L,bx+bw,by+bh,bx+bw-L,by+bh]]
                    .forEach(p=>{ctx.beginPath();ctx.moveTo(p[0],p[1]);ctx.lineTo(p[2],p[3]);ctx.lineTo(p[4],p[5]);ctx.stroke();});

                    currentDescriptor = Array.from(det.descriptor);
                    faceRing.className = 'face-ring has-face absolute inset-0 rounded-2xl border-2 pointer-events-none transition-all duration-300 border-white shadow-[0_0_30px_rgba(255,255,255,0.2)_in]';
                    statusMsgEl.className = 'absolute bottom-4 left-1/2 -translate-x-1/2 font-sans font-bold text-xs px-4 py-1.5 rounded-full backdrop-blur-md shadow-lg bg-white text-black';
                    statusMsgEl.textContent = '✓ Wajah Terdeteksi';
                    statusMsgEl.style.display = 'block';
                    captureBtn.disabled = false;
                    setStatus('Wajah terdeteksi dengan jelas. Siap untuk disimpan!', '#a855f7');
                    activateStep(3);
                } else {
                    currentDescriptor = null;
                    faceRing.className = 'face-ring no-face absolute inset-0 rounded-2xl border-2 pointer-events-none transition-all duration-300 border-purple-500/20';
                    statusMsgEl.className = 'absolute bottom-4 left-1/2 -translate-x-1/2 font-sans font-bold text-xs px-4 py-1.5 rounded-full backdrop-blur-md shadow-lg bg-[#1f0e3d]/90 text-purple-300 border border-purple-500/20';
                    statusMsgEl.textContent = '🔍 Mencari wajah…';
                    statusMsgEl.style.display = 'block';
                    captureBtn.disabled = true;
                    setStatus('Wajah belum terdeteksi. Posisikan wajah di tengah kamera.', '#cbd5e1');
                }
            } catch(_) {}
        }, 300);
    }

    // ── Save ──────────────────────────────────────────────────────────────────────
    captureBtn.addEventListener('click', async () => {
        if (!currentDescriptor) return;
        clearInterval(detectionLoop);
        captureBtn.disabled = true; startCamBtn.disabled = true;
        statusCard.classList.add('hidden');
        savingCard.classList.remove('hidden');
        try {
            const res  = await fetch(STORE_URL, {
                method:'POST',
                headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json' },
                body: JSON.stringify({ face_descriptor: currentDescriptor }),
            });
            const data = await res.json();
            if (data.success) {
                savingCard.classList.add('hidden');
                successCard.classList.remove('hidden');
                markStep(3,'done');
                setTimeout(()=>{ document.getElementById('successBar').style.width='100%'; }, 50);
                setTimeout(()=>{ window.location.href = data.redirect || @json(route('buyer.register-face')); }, 2200);
            } else { throw new Error(data.message||'Gagal menyimpan'); }
        } catch(err) {
            savingCard.classList.add('hidden');
            statusCard.classList.remove('hidden');
            setStatus('Error: ' + err.message, '#f87171');
            captureBtn.disabled = false;
            startLoop();
        }
    });

    // ── Helpers ───────────────────────────────────────────────────────────────────
    function markStep(n,state) {
        const el = document.getElementById('stepNum'+n);
        const row = document.getElementById('step'+n+'row');
        if (state==='done'){
            el.className='step-num done w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-[#FFE600] text-black border-transparent';
            el.textContent='✓';
            row.classList.remove('active', 'bg-purple-500/5', 'border', 'border-purple-500/10');
            row.classList.add('border', 'border-emerald-500/20', 'bg-emerald-500/5');
        }
    }
    function activateStep(n) {
        const el=document.getElementById('stepNum'+n);
        const row=document.getElementById('step'+n+'row');
        const title=document.getElementById('s'+n+'title');
        if(!el) return;
        el.className='step-num active w-8 h-8 rounded-full border border-purple-400 flex items-center justify-center text-xs font-bold shrink-0 bg-purple-500/20 text-white';
        el.textContent=n;
        row.classList.add('active', 'bg-purple-500/5', 'border', 'border-purple-500/10');
        if(title) {
            title.classList.remove('text-purple-300/20');
            title.classList.add('text-white');
        }
    }
    function setStatus(msg, color='#cbd5e1') {
        statusTxt.textContent=msg; statusTxt.style.color=color;
    }

    // utility hidden class mapping
    document.querySelectorAll('.hidden').forEach(el=>el.style.display='none');

    loadModels();
    </script>
</body>
</html>
