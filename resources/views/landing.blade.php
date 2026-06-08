<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotato - Platform Foto Event Terbaik</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0d061a] text-white font-sans selection:bg-purple-500/20 selection:text-white">
    <!-- Navigation Bar -->
    <nav id="main-navbar" class="fixed top-0 inset-x-0 z-50 py-3 bg-[#0d061a]/50 backdrop-blur-lg transition-all duration-300 border-b border-transparent">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <a href="#" class="text-2xl font-black font-display tracking-wider text-white">
                FOTATO
            </a>
            
            <div class="hidden md:flex gap-8 items-center">
                <a href="#" class="text-white hover:text-purple-300 transition-colors text-sm font-medium">Home</a>
                <a href="{{ route('albums.index') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Gallery</a>
                <a href="{{ route('events.index') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Upcoming Concert</a>
                <a href="#usage" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">FAQ</a>
                @auth
                    @if(in_array(Auth::user()->role, ['buyer', 'customer']))
                        <a href="{{ route('buyer.register-face') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Temukan Wajah</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Dashboard</a>
                    @endif
                @endauth
            </div>

            <div class="flex gap-4 items-center">
                <div class="relative hidden sm:block">
                    <input type="text" placeholder="Search..." class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-1.5 pl-4 pr-8 text-white focus:outline-none focus:border-[#a855f7] w-36 md:w-44 font-sans placeholder:text-gray-400">
                    <span class="absolute right-3 top-2.5 text-[10px] opacity-70">🔍</span>
                </div>
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

    <!-- Hero Section -->
    <section class="relative h-screen flex items-center justify-center overflow-hidden">
        <!-- Background GIF -->
        <img src="{{ asset('images/hero.gif') }}" alt="Hero Background" class="absolute inset-0 w-full h-full object-cover">
        <!-- Dark overlay with purple hue for text readability -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/45 via-black/85 to-[#0d061a] z-0"></div>
        
        <!-- Hero Box CTA -->
        <div class="relative z-10 container mx-auto px-6 text-center max-w-4xl">
            <h1 class="text-2xl md:text-[48px] font-black mb-6 font-display text-white tracking-tight uppercase leading-tight" style="text-shadow: 0 4px 20px rgba(0,0,0,0.6);">
                Every Concert Has a Story,<br>Find Yours.
            </h1>
            <p class="text-purple-200/90 text-sm md:text-lg mb-10 leading-relaxed font-sans max-w-2xl mx-auto" style="text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
                Cari wajahmu di ribuan momen konser, temukan foto terbaikmu, dan simpan kenangan yang tidak akan terulang lagi.
            </p>
            <div class="flex flex-row gap-4 justify-center items-center">
                <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-full bg-[#FFE600] text-black font-display font-bold transition-all hover:scale-105 hover:bg-[#fff04d] text-center text-sm md:text-base shadow-lg shadow-[#FFE600]/25">
                    Cari Foto Sekarang
                </a>
                <a href="{{ route('events.index') }}" class="px-8 py-3.5 rounded-full border border-purple-500/40 bg-black/40 hover:bg-[#a855f7]/20 text-white font-display font-semibold transition-all hover:scale-105 text-center text-sm md:text-base">
                    Lihat Event
                </a>
            </div>
        </div>

        <!-- Bottom Fade -->
        <div class="absolute bottom-0 inset-x-0 h-32 bg-gradient-to-t from-[#0d061a] to-transparent pointer-events-none z-10"></div>

        <!-- Promoter Logo Strip (Infinity Carousel) -->
        <div class="absolute bottom-3 inset-x-0 py-5 bg-gradient-to-r from-[#6D7CFF] to-[#5A2A8F] overflow-hidden z-20 shadow-lg shadow-purple-500/10">
            <div class="animate-marquee flex items-center">
                <div class="flex items-center gap-16 pr-16 flex-shrink-0">
                    <img src="{{ asset('images/ic.png') }}" alt="Promoters" class="h-20 object-contain">
                </div>
                <div class="flex items-center gap-16 pr-16 flex-shrink-0">
                    <img src="{{ asset('images/ic.png') }}" alt="Promoters" class="h-20 object-contain">
                </div>
                <div class="flex items-center gap-16 pr-16 flex-shrink-0">
                    <img src="{{ asset('images/ic.png') }}" alt="Promoters" class="h-20 object-contain">
                </div>
                <div class="flex items-center gap-16 pr-16 flex-shrink-0">
                    <img src="{{ asset('images/ic.png') }}" alt="Promoters" class="h-20 object-contain">
                </div>
            </div>
        </div>
    </section>

    <!-- Riwayat Event Terbaru (Konser Terbaru) -->
    <section class="py-24 px-6 bg-[#0d061a]">
        <div class="container mx-auto max-w-6xl">
            <h2 class="text-3xl md:text-5xl font-bold font-display text-center mb-16 text-white tracking-tight">Konser Terbaru</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Event Card 1 -->
                <div class="border-2 border-[#5A2A8F] bg-[#0f0720]/60 p-5 rounded-[28px] flex flex-col justify-between h-full hover:border-[#a855f7] hover:shadow-[0_0_30px_rgba(168,85,247,0.15)] transition-all duration-300 group">
                    <div>
                        <div class="relative h-56 rounded-2xl overflow-hidden mb-5 border border-purple-500/10">
                            <img src="{{ asset('images/landing1.jpg') }}" alt="LaLaLa Festival 2025" class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105">
                            <div class="absolute bottom-4 left-0 bg-[#FFE600] text-black text-xs font-black px-5 py-1.5 rounded-r-full shadow-md">
                                2-4 November 2025
                            </div>
                        </div>
                        <h3 class="text-xl font-bold font-display text-white mb-1.5 group-hover:text-purple-300 transition-colors">LaLaLa Festival 2025</h3>
                        <p class="text-purple-300/50 text-sm font-sans mb-4">2.430 photos</p>
                    </div>
                    <div class="flex justify-end">
                        <a href="{{ route('albums.index') }}" class="px-6 py-2.5 bg-gradient-to-r from-[#5A2A8F] to-[#8A4FFF] hover:from-[#6d30b0] hover:to-[#9b5cff] text-white text-xs font-bold font-display rounded-full transition-all shadow-md hover:shadow-purple-500/20 hover:scale-[1.02]">
                            Lihat Galeri
                        </a>
                    </div>
                </div>
                
                <!-- Event Card 2 -->
                <div class="border-2 border-[#5A2A8F] bg-[#0f0720]/60 p-5 rounded-[28px] flex flex-col justify-between h-full hover:border-[#a855f7] hover:shadow-[0_0_30px_rgba(168,85,247,0.15)] transition-all duration-300 group">
                    <div>
                        <div class="relative h-56 rounded-2xl overflow-hidden mb-5 border border-purple-500/10">
                            <img src="{{ asset('images/landing2.jpg') }}" alt="Jakarta Head in the Clouds 2025" class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105">
                            <div class="absolute bottom-4 left-0 bg-[#FFE600] text-black text-xs font-black px-5 py-1.5 rounded-r-full shadow-md">
                                3-4 Desember 2025
                            </div>
                        </div>
                        <h3 class="text-xl font-bold font-display text-white mb-1.5 group-hover:text-purple-300 transition-colors">Jakarta Head in the Clouds 2025</h3>
                        <p class="text-purple-300/50 text-sm font-sans mb-4">3.901 photos</p>
                    </div>
                    <div class="flex justify-end">
                        <a href="{{ route('albums.index') }}" class="px-6 py-2.5 bg-gradient-to-r from-[#5A2A8F] to-[#8A4FFF] hover:from-[#6d30b0] hover:to-[#9b5cff] text-white text-xs font-bold font-display rounded-full transition-all shadow-md hover:shadow-purple-500/20 hover:scale-[1.02]">
                            Lihat Galeri
                        </a>
                    </div>
                </div>
                
                <!-- Event Card 3 -->
                <div class="border-2 border-[#5A2A8F] bg-[#0f0720]/60 p-5 rounded-[28px] flex flex-col justify-between h-full hover:border-[#a855f7] hover:shadow-[0_0_30px_rgba(168,85,247,0.15)] transition-all duration-300 group">
                    <div>
                        <div class="relative h-56 rounded-2xl overflow-hidden mb-5 border border-purple-500/10">
                            <img src="{{ asset('images/landing3.jpg') }}" alt="Weverse Con Festival 2024" class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105">
                            <div class="absolute bottom-4 left-0 bg-[#FFE600] text-black text-xs font-black px-5 py-1.5 rounded-r-full shadow-md">
                                16-18 Agustus 2024
                            </div>
                        </div>
                        <h3 class="text-xl font-bold font-display text-white mb-1.5 group-hover:text-purple-300 transition-colors">Weverse Con Festival 2024</h3>
                        <p class="text-purple-300/50 text-sm font-sans mb-4">2.714 photos</p>
                    </div>
                    <div class="flex justify-end">
                        <a href="{{ route('albums.index') }}" class="px-6 py-2.5 bg-gradient-to-r from-[#5A2A8F] to-[#8A4FFF] hover:from-[#6d30b0] hover:to-[#9b5cff] text-white text-xs font-bold font-display rounded-full transition-all shadow-md hover:shadow-purple-500/20 hover:scale-[1.02]">
                            Lihat Galeri
                        </a>
                    </div>
                </div>
            </div>

            <!-- See All Events Link -->
            <div class="flex justify-end mt-10">
                <a href="{{ route('albums.index') }}" class="text-white hover:text-purple-300 font-display font-medium flex items-center gap-1.5 text-sm transition-all duration-300">
                    Lihat Semua Event <span class="font-sans font-normal ml-0.5">--&rarr;</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Cara Kerja (Split View) -->
    <section class="py-24 px-6 bg-[#0d061a]" id="usage">
        <div class="container mx-auto max-w-6xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                <!-- Left Column: Mockup Phone Image -->
                <div class="lg:col-span-4 lg:col-start-2 relative flex justify-center lg:justify-end items-center">
                    <!-- Glow spotlight background (purple glow) -->
                    <div class="glow-spot bg-purple-500/10 blur-3xl"></div>
                    
                    <div class="relative z-10 max-w-[260px] md:max-w-[300px] lg:mr-4 animate-float">
                        <img src="{{ asset('images/hp.png') }}" alt="Smartphone Mockup" class="w-full h-auto drop-shadow-[0_20px_40px_rgba(168,85,247,0.15)]">
                    </div>
                </div>
                
                <!-- Right Column: Steps -->
                <div class="lg:col-span-6 flex flex-col gap-4">
                    <h2 class="text-3xl md:text-[44px] font-bold font-display text-white mb-6 leading-tight">Cara Kerja Fotato</h2>
                    
                    <!-- Step 1 -->
                    <div class="border border-[#5A2A8F] bg-[#0f0720]/60 p-5 rounded-2xl flex items-center gap-5 transition-all duration-300 hover:border-[#a855f7]/50">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center border border-[#8A4FFF] bg-purple-500/5 shrink-0 text-[#8A4FFF]">
                            <!-- Camera Icon -->
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316A2.192 2.192 0 0 0 14.502 4h-5c-.7 0-1.363.336-1.78.918l-.895 1.257ZM12 10.5a3.75 3.75 0 1 1 0 7.5 3.75 3.75 0 0 1 0-7.5ZM12 12a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                            </svg>
                        </div>
                        <p class="text-white text-sm md:text-base font-medium font-sans leading-relaxed">
                            Fotografer profesional mengabadikan momen terbaik Anda selama acara.
                        </p>
                    </div>
                    
                    <!-- Arrow 1 -->
                    <div class="flex justify-center my-0.5">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <polyline points="19 12 12 19 5 12"></polyline>
                        </svg>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="border border-[#5A2A8F] bg-[#0f0720]/60 p-5 rounded-2xl flex items-center gap-5 transition-all duration-300 hover:border-[#a855f7]/50">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center border border-[#8A4FFF] bg-purple-500/5 shrink-0 text-[#8A4FFF]">
                            <!-- Upload Icon -->
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>
                        <p class="text-white text-sm md:text-base font-medium font-sans leading-relaxed">
                            Foto diunggah ke FOTATO selama acara masih berlangsung oleh tim.
                        </p>
                    </div>
                    
                    <!-- Arrow 2 -->
                    <div class="flex justify-center my-0.5">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <polyline points="19 12 12 19 5 12"></polyline>
                        </svg>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="border border-[#5A2A8F] bg-[#0f0720]/60 p-5 rounded-2xl flex items-center gap-5 transition-all duration-300 hover:border-[#a855f7]/50">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center border border-[#8A4FFF] bg-purple-500/5 shrink-0 text-[#8A4FFF]">
                            <!-- Search Icon -->
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                            </svg>
                        </div>
                        <p class="text-white text-sm md:text-base font-medium font-sans leading-relaxed">
                            Temukan foto Anda dengan mudah dengan scan wajah atau lihat event.
                        </p>
                    </div>
                    
                    <!-- Arrow 3 -->
                    <div class="flex justify-center my-0.5">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <polyline points="19 12 12 19 5 12"></polyline>
                        </svg>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="border border-[#5A2A8F] bg-[#0f0720]/60 p-5 rounded-2xl flex items-center gap-5 transition-all duration-300 hover:border-[#a855f7]/50">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center border border-[#8A4FFF] bg-purple-500/5 shrink-0 text-[#8A4FFF]">
                            <!-- Download Icon -->
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </div>
                        <p class="text-white text-sm md:text-base font-medium font-sans leading-relaxed">
                            Beli dan unduh foto terbaik Anda dengan kualitas tinggi tanpa watermark.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kenapa Harus Fotato -->
    <section class="py-24 bg-[#0d061a]" id="why-us">
        <!-- Top split layout (Title + 2x2 Grid) -->
        <div class="container mx-auto max-w-6xl mb-20 px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- Left Column: Title -->
                <div class="lg:col-span-5">
                    <h2 class="text-4xl md:text-[48px] font-black font-display leading-tight mb-6 text-white tracking-tight">
                        Kenapa Harus <br>
                        <span class="text-[#a855f7]">FOTATO?</span>
                    </h2>
                    <p class="text-purple-200/80 text-sm md:text-base font-sans leading-relaxed max-w-sm">
                        Kami menghadirkan pengalaman terbaik untuk mendapatkan foto konser terbaikmu.
                    </p>
                </div>
                
                <!-- Right Column: 2x2 Grid -->
                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Card 1 -->
                    <div class="border border-[#5A2A8F]/40 bg-[#0f0720]/60 p-6 rounded-2xl flex flex-col justify-center min-h-[140px] hover:border-[#a855f7]/40 transition-all duration-300">
                        <h4 class="text-white font-bold font-display text-base mb-1.5">Teknologi AI Presisi</h4>
                        <p class="text-purple-300/50 text-xs md:text-sm leading-relaxed font-sans">
                            Cari wajahmu secara instan dari ribuan foto konser menggunakan kecerdasan buatan.
                        </p>
                    </div>
                    
                    <!-- Card 2 -->
                    <div class="border border-[#5A2A8F]/40 bg-[#0f0720]/60 p-6 rounded-2xl flex flex-col justify-center min-h-[140px] hover:border-[#a855f7]/40 transition-all duration-300">
                        <h4 class="text-white font-bold font-display text-base mb-1.5">Keamanan Data Terjamin</h4>
                        <p class="text-purple-300/50 text-xs md:text-sm leading-relaxed font-sans">
                            Keamanan data wajah Anda terlindungi secara penuh dengan enkripsi tingkat tinggi.
                        </p>
                    </div>
                    
                    <!-- Card 3 -->
                    <div class="border border-[#5A2A8F]/40 bg-[#0f0720]/60 p-6 rounded-2xl flex flex-col justify-center min-h-[140px] hover:border-[#a855f7]/40 transition-all duration-300">
                        <h4 class="text-white font-bold font-display text-base mb-1.5">Kualitas Foto Asli</h4>
                        <p class="text-purple-300/50 text-xs md:text-sm leading-relaxed font-sans">
                            Unduh foto kualitas tinggi resolusi penuh tanpa kompresi langsung dari fotografer.
                        </p>
                    </div>
                    
                    <!-- Card 4 -->
                    <div class="border border-[#5A2A8F]/40 bg-[#0f0720]/60 p-6 rounded-2xl flex flex-col justify-center min-h-[140px] hover:border-[#a855f7]/40 transition-all duration-300">
                        <h4 class="text-white font-bold font-display text-base mb-1.5">Pembayaran Cepat & Aman</h4>
                        <p class="text-purple-300/50 text-xs md:text-sm leading-relaxed font-sans">
                            Lakukan transaksi dengan konfirmasi pembayaran digital yang instan dan aman.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom full-width collage section -->
        <div class="w-full overflow-hidden py-10 relative">
            <!-- Background spotlight glow behind collage -->
            <div class="absolute inset-0 flex justify-center items-center pointer-events-none">
                <div class="w-[800px] h-[300px] bg-purple-900/10 blur-[120px] rounded-full"></div>
            </div>
            
            <div id="scroll-collage" class="flex justify-center items-center gap-6 px-4 md:px-10 max-w-[1600px] mx-auto select-none">
                <!-- Column 1 (Far Left) -->
                <div class="collage-col-left-far w-48 h-80 rounded-2xl overflow-hidden flex-shrink-0 opacity-0 translate-y-20 transition-all duration-1000 ease-out">
                    <img src="https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=600&q=80" alt="Concert Crowd" class="w-full h-full object-cover">
                </div>
                
                <!-- Column 2 (Left) -->
                <div class="collage-col-left w-72 flex flex-col gap-4 flex-shrink-0 opacity-0 translate-y-20 transition-all duration-1000 ease-out">
                    <div class="h-64 rounded-2xl overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=600&q=80" alt="Concert Light" class="w-full h-full object-cover">
                    </div>
                    <div class="h-72 rounded-2xl overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=600&q=80" alt="Concert Girl" class="w-full h-full object-cover">
                    </div>
                </div>
                
                <!-- Column 3 (Center) -->
                <div class="collage-col-center w-[420px] h-[520px] rounded-3xl overflow-hidden flex-shrink-0 shadow-2xl shadow-black/50 border border-purple-500/20 opacity-0 translate-y-20 transition-all duration-1000 ease-out">
                    <img src="https://images.unsplash.com/photo-1459749411175-04bf5292ceea?auto=format&fit=crop&w=800&q=80" alt="Main Concert Poster" class="w-full h-full object-cover">
                </div>
                
                <!-- Column 4 (Right) -->
                <div class="collage-col-right w-72 flex flex-col gap-4 flex-shrink-0 opacity-0 translate-y-20 transition-all duration-1000 ease-out">
                    <div class="h-48 rounded-2xl overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=600&q=80" alt="DJ playing" class="w-full h-full object-cover">
                    </div>
                    <div class="h-[320px] rounded-2xl overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=600&q=80" alt="Happy Crowd" class="w-full h-full object-cover">
                    </div>
                </div>
                
                <!-- Column 5 (Far Right) -->
                <div class="collage-col-right-far w-48 h-80 rounded-2xl overflow-hidden flex-shrink-0 opacity-0 translate-y-20 transition-all duration-1000 ease-out">
                    <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80" alt="Photographer camera" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section (Apa Kata Mereka?) -->
    <section class="py-24 px-6 bg-[#0d061a]" id="testimonials">
        <div class="container mx-auto max-w-6xl relative">
            <h2 class="text-3xl md:text-5xl font-bold font-display text-center mb-4 bg-gradient-to-b from-white to-purple-300 bg-clip-text text-transparent">Apa Kata Mereka?</h2>
            <p class="text-purple-200/60 text-center font-sans max-w-2xl mx-auto mb-16 leading-relaxed text-sm">
                Ulasan jujur dari fotografer profesional dan para pecinta konser yang telah merasakan kemudahan menggunakan Fotato.
            </p>
            
            <!-- Slider Controls -->
            <div class="absolute right-4 top-0 flex gap-3 z-20">
                <button id="prev-testimonial" class="w-10 h-10 rounded-full border border-purple-500/20 bg-purple-500/5 flex items-center justify-center text-white hover:bg-[#a855f7] hover:border-[#a855f7] transition-all cursor-pointer">
                    ←
                </button>
                <button id="next-testimonial" class="w-10 h-10 rounded-full border border-purple-500/20 bg-purple-500/5 flex items-center justify-center text-white hover:bg-[#a855f7] hover:border-[#a855f7] transition-all cursor-pointer">
                    →
                </button>
            </div>
            
            <!-- Slider container -->
            <div id="testimonial-slider" class="flex gap-6 overflow-x-auto no-scrollbar scroll-smooth snap-x snap-mandatory py-4">
                
                <!-- Testimonial 1 -->
                <div class="glass-effect p-8 rounded-2xl w-[300px] md:w-[360px] shrink-0 snap-start flex flex-col justify-between">
                    <p class="text-purple-100 font-sans italic leading-relaxed mb-8 text-xs">
                        "Sangat terbantu! Saya cuma butuh upload satu foto selfie saat mendaftar, lalu semua foto konser saya di VibeFest langsung muncul semua secara otomatis. Sistem pembayarannya juga cepat banget."
                    </p>
                    <div class="flex items-center gap-4 border-t border-purple-500/5 pt-4">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80" alt="Sarah Diana" class="w-10 h-10 rounded-full object-cover border border-purple-500/20">
                        <div>
                            <h4 class="font-bold font-display text-white text-xs">Sarah Diana</h4>
                            <p class="text-purple-300/40 text-[10px]">Konser Goer & Festival Enthusiast</p>
                            <div class="text-[#a855f7] text-[10px] mt-0.5">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="glass-effect p-8 rounded-2xl w-[300px] md:w-[360px] shrink-0 snap-start flex flex-col justify-between">
                    <p class="text-purple-100 font-sans italic leading-relaxed mb-8 text-xs">
                        "Sebagai fotografer event, Fotlist adalah penyelamat. Dulu saya harus kirim link drive dan biarkan klien cari manual. Sekarang, sistem AI Fotlist yang mencocokkannya. Penjualan foto saya naik drastis!"
                    </p>
                    <div class="flex items-center gap-4 border-t border-purple-500/5 pt-4">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" alt="Rian Pratama" class="w-10 h-10 rounded-full object-cover border border-purple-500/20">
                        <div>
                            <h4 class="font-bold font-display text-white text-xs">Rian Pratama</h4>
                            <p class="text-purple-300/40 text-[10px]">Fotografer Dokumentasi Event</p>
                            <div class="text-[#a855f7] text-[10px] mt-0.5">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="glass-effect p-8 rounded-2xl w-[300px] md:w-[360px] shrink-0 snap-start flex flex-col justify-between">
                    <p class="text-purple-100 font-sans italic leading-relaxed mb-8 text-xs">
                        "Kami mengintegrasikan Fotlist di festival musik tahunan kami. Para audiens sangat senang karena mereka bisa langsung mendapatkan foto aksi terbaik mereka tanpa repot. Platform yang luar biasa!"
                    </p>
                    <div class="flex items-center gap-4 border-t border-purple-500/5 pt-4">
                        <img src="https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=150&q=80" alt="Budi Santoso" class="w-10 h-10 rounded-full object-cover border border-purple-500/20">
                        <div>
                            <h4 class="font-bold font-display text-white text-xs">Budi Santoso</h4>
                            <p class="text-purple-300/40 text-[10px]">Event Organizer - VibeFest</p>
                            <div class="text-[#a855f7] text-[10px] mt-0.5">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                </div>
 
                <!-- Testimonial 4 -->
                <div class="glass-effect p-8 rounded-2xl w-[300px] md:w-[360px] shrink-0 snap-start flex flex-col justify-between">
                    <p class="text-purple-100 font-sans italic leading-relaxed mb-8 text-xs">
                        "Teknologi pencarian wajah AI-nya benar-benar akurat. Bahkan ketika saya memakai topi atau kacamata hitam di tengah konser, sistem tetap bisa mendeteksi wajah saya. Rekomendasi banget!"
                    </p>
                    <div class="flex items-center gap-4 border-t border-purple-500/5 pt-4">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80" alt="Clara Amanda" class="w-10 h-10 rounded-full object-cover border border-purple-500/20">
                        <div>
                            <h4 class="font-bold font-display text-white text-xs">Clara Amanda</h4>
                            <p class="text-purple-300/40 text-[10px]">Pengunjung Java Jazz 2025</p>
                            <div class="text-[#a855f7] text-[10px] mt-0.5">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                </div>
            </div>
 
            <!-- Indicator Dots -->
            <div id="testimonial-dots" class="flex justify-center gap-2 mt-8">
                <button class="w-2.5 h-2.5 rounded-full bg-[#a855f7] transition-all duration-300" data-index="0"></button>
                <button class="w-2.5 h-2.5 rounded-full bg-white/20 transition-all duration-300" data-index="1"></button>
                <button class="w-2.5 h-2.5 rounded-full bg-white/20 transition-all duration-300" data-index="2"></button>
                <button class="w-2.5 h-2.5 rounded-full bg-white/20 transition-all duration-300" data-index="3"></button>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 px-6 bg-gradient-to-b from-[#0d061a] to-[#05020a]">
        <div class="container mx-auto max-w-3xl text-center">
            <h2 class="text-4xl md:text-6xl font-bold mb-6 font-display bg-gradient-to-r from-white to-purple-300 bg-clip-text text-transparent">Siap Memulai?</h2>
            <p class="text-purple-200/60 mb-10 text-base md:text-lg font-sans font-light">Bergabunglah dengan ribuan pengguna yang telah menemukan dan menjual foto mereka di Fotato</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl bg-gradient-to-r from-[#a855f7] to-[#7e22ce] hover:from-[#b066ff] hover:to-[#8b2ff2] text-white font-display font-semibold transition-all hover:scale-[1.02] shadow-lg shadow-purple-500/20 text-base text-center">
                    🚀 Daftar Gratis Sekarang
                </a>
                <a href="#usage" class="px-8 py-4 rounded-xl border border-purple-500/30 bg-purple-500/5 hover:bg-purple-500/10 text-purple-200 hover:text-white font-display font-semibold transition-all hover:scale-[1.02] text-base text-center">
                    Pelajari Lebih Lanjut
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-6 bg-black">
        <div class="container mx-auto text-center text-purple-300/20 text-xs font-sans">
            <p>&copy; 2026 Fotato. Semua hak dilindungi. Dibuat dengan ❤️ untuk komunitas fotografi.</p>
        </div>
    </footer>

    <!-- Login Success Pop-up Modal -->
    @if(session('login_success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" class="fixed bottom-6 right-6 z-50 max-w-sm bg-gradient-to-r from-[#5a2a8f] to-[#8a4fff] border border-purple-400/30 rounded-2xl shadow-2xl p-5 backdrop-blur-xl transition-all duration-500" style="display: none;">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-xl shrink-0">
                    🎉
                </div>
                <div class="flex-grow">
                    <h4 class="font-bold text-white text-sm mb-1">Berhasil Masuk!</h4>
                    <p class="text-purple-100 text-xs leading-relaxed">
                        {{ session('login_success') }}
                    </p>
                </div>
                <button @click="show = false" class="text-white/60 hover:text-white text-lg font-bold transition-colors">
                    &times;
                </button>
            </div>
        </div>
    @endif
</body>
</html>
