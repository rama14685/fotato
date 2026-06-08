<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fotato - Cari Foto Wajah</title>
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
                @auth
                    @if(in_array(Auth::user()->role, ['buyer', 'customer']))
                        <a href="{{ route('buyer.register-face') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Temukan Wajah</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Dashboard</a>
                    @endif
                @endauth
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
                <a href="{{ route('albums.index') }}" class="hover:text-purple-300 transition-colors">Gallery</a>
                <span>&gt;</span>
                <span class="text-purple-300/80">Pencarian Wajah</span>
            </div>

            <!-- Page Title -->
            <div class="mb-8">
                <h1 class="text-3xl md:text-5xl font-bold font-display text-white tracking-tight">Cari Foto dengan Wajah 📸</h1>
                <p class="text-purple-200/50 text-sm mt-2">Pindai wajah Anda atau unggah foto untuk menemukan foto Anda di album konser.</p>
            </div>

            <!-- Main Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Face Capture & Album Selection -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Step 1: Capture/Upload Face -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 rounded-3xl p-6 shadow-xl">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-[#5A2A8F] text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-black font-display">1</span>
                            Scan Wajah Anda
                        </h3>
                        
                        <div class="flex flex-wrap gap-3 mb-5">
                            <button id="startCamera" class="px-5 py-2.5 bg-gradient-to-r from-[#5A2A8F] to-[#8A4FFF] hover:from-[#6d30b0] hover:to-[#9b5cff] text-white text-xs font-bold rounded-full transition-all shadow-md">
                                📷 Gunakan Kamera
                            </button>
                            <label for="uploadFace" class="px-5 py-2.5 bg-[#1f0e3d]/50 border border-purple-500/30 text-xs text-purple-200 hover:bg-purple-500/20 hover:text-white font-bold rounded-full transition-all cursor-pointer">
                                📁 Upload Foto
                            </label>
                            <input type="file" id="uploadFace" accept="image/jpeg,image/png,image/webp" class="hidden">
                        </div>

                        <!-- Video and Canvas for Camera Capture -->
                        <div class="relative rounded-2xl overflow-hidden border border-purple-500/20 bg-[#0d061a] flex items-center justify-center min-h-[240px]">
                            <video id="video" width="640" height="480" autoplay class="w-full h-auto max-h-[360px] object-cover" style="display:none;"></video>
                            <canvas id="canvas" width="640" height="480" class="w-full h-auto max-h-[360px] object-cover" style="display:none;"></canvas>
                            <img id="preview" class="w-full h-auto max-h-[360px] object-cover" style="display:none;">
                            <div id="cameraPlaceholder" class="text-center p-8">
                                <div class="text-5xl mb-4 opacity-40">📸</div>
                                <p class="text-purple-300/40 text-xs font-semibold uppercase tracking-wider">Kamera belum aktif</p>
                            </div>
                        </div>

                        <!-- Error/Status Messages -->
                        <div id="faceStatus" class="mt-4 text-xs font-medium text-center"></div>
                    </div>

                    <!-- Step 2: Select Album -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 rounded-3xl p-6 shadow-xl">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-[#5A2A8F] text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-black font-display">2</span>
                            Pilih Event / Album
                        </h3>
                        
                        <select id="albumSelect" class="w-full px-4 py-3 bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-xl text-white focus:outline-none focus:border-[#a855f7] font-sans">
                            <option value="">-- Pilih Album --</option>
                            @foreach($albums as $album)
                                <option value="{{ $album->id }}" {{ request('album_id') == $album->id ? 'selected' : '' }}>
                                    {{ $album->title }} - {{ $album->location }} ({{ $album->event_date->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Step 3: Search Button -->
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 rounded-3xl p-6 shadow-xl">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-[#5A2A8F] text-white w-7 h-7 rounded-full flex items-center justify-center text-xs font-black font-display">3</span>
                            Mulai Pencarian
                        </h3>
                        
                        <button id="searchBtn" class="w-full px-6 py-3 bg-[#FFE600] hover:bg-[#fff04d] text-black text-xs font-black font-display rounded-full shadow-md shadow-[#FFE600]/25 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none" disabled>
                            🔍 Cari Foto Saya
                        </button>
                        
                        <div id="loading" style="display:none;" class="mt-4 flex items-center justify-center text-purple-400 text-xs font-semibold tracking-wide gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Mencari foto...</span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Instructions -->
                <div class="lg:col-span-1">
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 rounded-3xl p-6 shadow-xl sticky top-24">
                        <h3 class="text-lg font-bold text-white mb-4 font-display">💡 Cara Menggunakan</h3>
                        
                        <div class="space-y-4 text-purple-200/70 text-xs leading-relaxed">
                            <div class="flex gap-3">
                                <span class="text-purple-400 font-black font-display">1.</span>
                                <div>
                                    <p class="font-bold text-white mb-0.5">Scan Wajah</p>
                                    <p>Gunakan kamera Anda langsung atau unggah foto selfie wajah Anda yang jelas.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-3">
                                <span class="text-purple-400 font-black font-display">2.</span>
                                <div>
                                    <p class="font-bold text-white mb-0.5">Pilih Album</p>
                                    <p>Pilih konser/event tempat Anda difoto dari menu pilihan album.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-3">
                                <span class="text-purple-400 font-black font-display">3.</span>
                                <div>
                                    <p class="font-bold text-white mb-0.5">Cari Foto</p>
                                    <p>Tekan tombol pencarian untuk memindai seluruh album dan menyaring foto-foto Anda secara instan.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-purple-500/10">
                            <p class="text-[10px] text-purple-300/40 font-semibold leading-relaxed">
                                🔒 <strong>Privasi Biometrik Aman:</strong> Foto pencarian Anda diproses secara lokal di browser dan tidak pernah disimpan atau disimpan permanen ke server kami.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Results Section -->
            <div class="mt-8">
                <div id="resultsContainer" style="display:none;">
                    <div class="border border-purple-500/20 bg-[#0f0720]/60 rounded-3xl p-6 shadow-xl">
                        <h3 class="text-xl font-bold text-white mb-6 font-display">📷 Hasil Pencarian</h3>
                        <div id="results" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"></div>
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

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="{{ asset('js/face-scan.js') }}"></script>
    
    <!-- Camera placeholder switcher scripting -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const startCameraBtn = document.getElementById('startCamera');
            const uploadInput = document.getElementById('uploadFace');
            const placeholder = document.getElementById('cameraPlaceholder');
            const video = document.getElementById('video');
            const preview = document.getElementById('preview');

            startCameraBtn.addEventListener('click', () => {
                if (placeholder) placeholder.style.display = 'none';
                if (preview) preview.style.display = 'none';
                if (video) video.style.display = 'block';
            });

            uploadInput.addEventListener('change', () => {
                if (placeholder) placeholder.style.display = 'none';
                if (video) video.style.display = 'none';
                if (preview) preview.style.display = 'block';
            });
        });
    </script>
</body>
</html>
