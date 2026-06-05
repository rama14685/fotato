<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Concert - FOTATO</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0d061a] text-white font-sans selection:bg-purple-500/20 selection:text-white min-h-screen flex flex-col justify-between">
    
    <!-- Navigation Bar -->
    <nav id="main-navbar" class="fixed top-0 inset-x-0 z-50 py-3 bg-[#0d061a]/70 backdrop-blur-lg border-b border-purple-500/10 transition-all duration-300">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <a href="{{ route('landing') }}" class="text-2xl font-black font-display tracking-wider text-white">
                FOTATO
            </a>
            
            <div class="hidden md:flex gap-8 items-center">
                <a href="{{ route('landing') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Home</a>
                <a href="{{ route('albums.index') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">Gallery</a>
                <a href="{{ route('events.index') }}" class="text-white transition-colors text-sm font-semibold border-b-2 border-[#a855f7] pb-1">Upcoming Concert</a>
                <a href="{{ route('landing') }}#usage" class="text-gray-300 hover:text-white transition-colors text-sm font-medium">FAQ</a>
            </div>

            <div class="flex gap-4 items-center">
                <div class="relative hidden sm:block">
                    <input type="text" id="navbar-search" placeholder="Search..." class="bg-[#1f0e3d]/50 border border-purple-500/30 text-xs rounded-full py-1.5 pl-4 pr-8 text-white focus:outline-none focus:border-[#a855f7] w-36 md:w-44 font-sans placeholder:text-gray-400">
                    <span class="absolute right-3 top-2.5 text-[10px] opacity-70">🔍</span>
                </div>
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-[#8c66ff] hover:bg-[#a855f7] text-black text-xs font-semibold px-5 py-1.5 rounded-full transition-all">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-[#8c66ff] hover:bg-[#a855f7] text-black text-xs font-semibold px-5 py-1.5 rounded-full transition-all">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow pt-32 pb-24 relative overflow-hidden">
        <!-- Background radial glows matching landing page -->
        <div class="absolute top-[10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/10 rounded-full mix-blend-screen filter blur-[120px] opacity-75 pointer-events-none z-0"></div>
        <div class="absolute bottom-[20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 rounded-full mix-blend-screen filter blur-[120px] opacity-75 pointer-events-none z-0"></div>

        <div class="container mx-auto px-6 relative z-10 max-w-6xl">
            <!-- Header Section (Grid matching mockup layout) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center mb-16">
                <!-- Left: Title Content -->
                <div class="md:col-span-7 text-left">
                    <h1 class="text-5xl md:text-[64px] font-black leading-none font-display mb-4 tracking-tight uppercase">
                        Upcoming<br>
                        <span class="text-[#a855f7] drop-shadow-[0_0_20px_rgba(168,85,247,0.3)]">Concert</span>
                    </h1>
                    <p class="text-purple-200/80 text-sm md:text-lg leading-relaxed font-sans max-w-xl">
                        Nantikan konser seru yang akan datang, FOTATO siap hadir untuk mengabadikan momen terbaikmu!
                    </p>
                </div>
                
                <!-- Right: Glowing Banner Illustration -->
                <div class="md:col-span-5 relative flex justify-center items-center">
                    <div class="absolute w-[300px] h-[300px] bg-[#a855f7]/10 blur-[80px] rounded-full pointer-events-none"></div>
                    <img src="{{ asset('images/events_banner.png') }}" alt="Calendar Banner" class="w-full max-w-[340px] h-auto object-contain z-10 animate-float" style="mask-image: radial-gradient(circle at center, black 40%, transparent 80%); -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 80%); mix-blend-mode: screen;">
                </div>
            </div>

            <!-- Controls & Category Filters -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <!-- Pill Group -->
                <div class="flex gap-2.5 overflow-x-auto no-scrollbar py-1">
                    <button onclick="filterCategory('all')" id="btn-cat-all" class="px-6 py-2.5 bg-[#8c66ff] text-black font-bold font-display rounded-xl text-xs transition-all shadow-md shadow-[#8c66ff]/20">
                        All Concert
                    </button>
                    <button onclick="filterCategory('indonesia')" id="btn-cat-indonesia" class="px-6 py-2.5 border border-[#8c66ff]/30 bg-transparent text-purple-200 hover:text-white hover:bg-[#8c66ff]/10 font-bold font-display rounded-xl text-xs transition-all">
                        Indonesia
                    </button>
                    <button onclick="filterCategory('international')" id="btn-cat-international" class="px-6 py-2.5 border border-[#8c66ff]/30 bg-transparent text-purple-200 hover:text-white hover:bg-[#8c66ff]/10 font-bold font-display rounded-xl text-xs transition-all">
                        International
                    </button>
                </div>
                
                <!-- Sort Dropdown -->
                <div class="relative">
                    <button id="sort-dropdown-btn" class="px-4 py-2 border border-[#8c66ff]/30 bg-purple-950/20 text-white font-semibold font-sans rounded-xl text-xs flex items-center gap-2 hover:bg-purple-950/40 transition-all cursor-pointer">
                        Urutkan: Terdekat <span class="text-[10px] opacity-75">▼</span>
                    </button>
                </div>
            </div>

            <!-- Events Horizontal List Wrapper -->
            <div id="events-list" class="space-y-6">
                @forelse($events as $index => $ev)
                    @php
                        // Cycle through high-quality concert unsplash images
                        $unsplashImages = [
                            'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=600&q=80',
                            'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=600&q=80',
                            'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=600&q=80',
                            'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=600&q=80',
                            'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=600&q=80',
                            'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80'
                        ];
                        $cardImg = $unsplashImages[$index % count($unsplashImages)];
                        if ($ev->image_path) {
                            $cardImg = asset('storage/' . $ev->image_path);
                        }

                        // Determine Category
                        $locationLower = strtolower($ev->location);
                        $isInternational = false;
                        $intlKeywords = ['singapore', 'tokyo', 'seoul', 'london', 'malaysia', 'kuala lumpur', 'bangkok', 'la ', 'los angeles', 'new york', 'usa', 'america', 'korea', 'japan', 'australia', 'sydney'];
                        foreach($intlKeywords as $kw) {
                            if (strpos($locationLower, $kw) !== false) {
                                $isInternational = true;
                                break;
                            }
                        }
                        $category = $isInternational ? 'international' : 'indonesia';

                        // Parse Title and Subtitle like BTS : ARIRANG IN JAKARTA
                        $mainTitle = $ev->name;
                        $subTitle = '';
                        if (strpos($ev->name, ':') !== false) {
                            $parts = explode(':', $ev->name, 2);
                            $mainTitle = trim($parts[0]);
                            $subTitle = trim($parts[1]);
                        } elseif (strpos($ev->name, '-') !== false) {
                            $parts = explode('-', $ev->name, 2);
                            $mainTitle = trim($parts[0]);
                            $subTitle = trim($parts[1]);
                        }

                        if (empty($subTitle) && !empty($ev->location)) {
                            $locParts = explode(',', $ev->location);
                            $city = trim(end($locParts));
                            $subTitle = 'IN ' . strtoupper($city);
                        }

                        $startDate = $ev->start_date;
                    @endphp

                    <!-- Horizontal Event Card -->
                    <div class="event-card border border-[#8c66ff]/20 bg-[#0f0720]/60 p-6 rounded-[28px] hover:border-[#a855f7] hover:shadow-[0_0_35px_rgba(168,85,247,0.12)] transition-all duration-300 flex flex-col md:flex-row justify-between items-stretch gap-6"
                         data-name="{{ strtolower($ev->name) }}"
                         data-location="{{ strtolower($ev->location) }}"
                         data-category="{{ $category }}"
                         data-timestamp="{{ $startDate ? $startDate->timestamp : 0 }}">
                        
                        <!-- Left: Vertical Poster Image -->
                        <div class="w-full md:w-[200px] h-[260px] rounded-2xl overflow-hidden flex-shrink-0 border border-purple-500/10 shadow-inner relative">
                            <img src="{{ $cardImg }}" alt="{{ $ev->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        </div>

                        <!-- Middle: Title & Event Meta Info -->
                        <div class="flex-grow flex flex-col justify-between py-2 pl-0 md:pl-4">
                            <div>
                                <h3 class="text-2xl md:text-3xl font-black font-display text-white mb-1 uppercase tracking-tight">
                                    {{ $mainTitle }}
                                </h3>
                                @if(!empty($subTitle))
                                    <h4 class="text-xl md:text-2xl font-black font-display text-[#a855f7] mb-6 uppercase tracking-tight">
                                        {{ $subTitle }}
                                    </h4>
                                @endif
                            </div>

                            <!-- Meta Details -->
                            <div class="space-y-3">
                                @if($startDate)
                                    <div class="text-purple-200/90 text-sm md:text-base font-sans flex items-center gap-3">
                                        <span class="text-purple-400 text-lg">📅</span>
                                        <span>{{ $startDate->format('d - H') ? $startDate->translatedFormat('d - d F Y') : $startDate->format('d - d F Y') }}</span>
                                    </div>
                                @endif
                                <div class="text-purple-200/90 text-sm md:text-base font-sans flex items-center gap-3">
                                    <span class="text-purple-400 text-lg">📍</span>
                                    <span>{{ $ev->location }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Countdown Timer Box & Action Buttons -->
                        <div class="w-full md:w-[290px] flex-shrink-0 flex flex-col justify-between pl-0 md:pl-6 border-t md:border-t-0 md:border-l border-purple-500/10 pt-6 md:pt-0">
                            <!-- Countdown Box -->
                            <div class="countdown-timer border border-[#8c66ff]/20 bg-[#160d28]/80 p-4 py-5 rounded-2xl text-center mb-4 flex flex-col justify-center min-h-[100px]"
                                 data-start-date="{{ $startDate ? $startDate->toIso8601String() : '' }}">
                                <span class="text-[10px] font-bold font-display text-[#8c66ff] tracking-wider mb-1.5 block">AKAN DIMULAI DALAM</span>
                                <span class="countdown-numbers text-2xl font-black font-display text-white tracking-wider block">
                                    00 : 00 : 00
                                </span>
                                <div class="countdown-labels flex justify-between px-6 text-[9px] font-semibold text-purple-300/40 tracking-wider mt-1.5 uppercase">
                                    <span>HARI</span>
                                    <span>JAM</span>
                                    <span>MENIT</span>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="space-y-3">
                                <a href="https://tiket.com" target="_blank" class="w-full py-3 bg-gradient-to-r from-[#8c66ff] to-[#a855f7] text-black font-bold font-display rounded-xl text-center text-xs shadow-lg shadow-purple-500/10 transition-all hover:scale-[1.02] flex items-center justify-center gap-1.5">
                                    Beli Tiket
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                </a>
                                <a href="{{ route('albums.index') }}" class="w-full py-3 border border-[#8c66ff]/30 hover:bg-[#8c66ff]/10 text-white font-bold font-display rounded-xl text-center text-xs transition-all hover:scale-[1.02] block">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="glass-effect p-16 rounded-3xl text-center border border-purple-500/10 max-w-xl mx-auto">
                        <div class="text-5xl mb-4">📅</div>
                        <h3 class="text-xl font-bold font-display text-white mb-2">Belum Ada Event Mendatang</h3>
                        <p class="text-purple-200/50 text-sm font-sans max-w-sm mx-auto">
                            Saat ini belum ada event mendatang terdaftar. Ikuti terus media sosial kami agar tidak ketinggalan jadwal event terbaru!
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Empty Filter State Placeholder -->
            <div id="search-empty-state" class="hidden glass-effect p-16 rounded-3xl text-center border border-purple-500/10 max-w-md mx-auto mt-10">
                <div class="text-5xl mb-4">🔍</div>
                <h3 class="text-xl font-bold font-display text-white mb-2">Event Tidak Ditemukan</h3>
                <p class="text-purple-200/50 text-sm font-sans">
                    Tidak ada event mendatang yang cocok dengan filter atau kata kunci Anda.
                </p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-12 px-6 bg-black border-t border-purple-500/5">
        <div class="container mx-auto text-center text-purple-300/20 text-xs font-sans">
            <p>&copy; 2026 Fotato. Semua hak dilindungi. Dibuat dengan ❤️ untuk komunitas fotografi.</p>
        </div>
    </footer>

    <!-- Interactive Filtering & Real-time Countdown Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navbarSearch = document.getElementById('navbar-search');
            const eventCards = document.querySelectorAll('.event-card');
            const emptyState = document.getElementById('search-empty-state');
            let currentCategory = 'all';

            // 1. Live Countdown Timer Logic
            const countdowns = document.querySelectorAll('.countdown-timer');
            
            function updateCountdowns() {
                const now = new Date().getTime();
                
                countdowns.forEach(el => {
                    const startDateStr = el.dataset.startDate;
                    if (!startDateStr) {
                        const numEl = el.querySelector('.countdown-numbers');
                        if (numEl) numEl.textContent = 'TBA';
                        return;
                    }
                    
                    const targetDate = new Date(startDateStr).getTime();
                    const difference = targetDate - now;
                    
                    const numEl = el.querySelector('.countdown-numbers');
                    const labelEl = el.querySelector('.countdown-labels');
                    
                    if (difference < 0) {
                        if (numEl) numEl.textContent = '00 : 00 : 00';
                        if (labelEl) labelEl.innerHTML = '<span class="w-full text-center text-[#a855f7] font-bold tracking-wider">SEDANG BERLANGSUNG</span>';
                        return;
                    }
                    
                    const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                    
                    const dStr = String(days).padStart(2, '0');
                    const hStr = String(hours).padStart(2, '0');
                    const mStr = String(minutes).padStart(2, '0');
                    
                    if (numEl) {
                        numEl.textContent = `${dStr} : ${hStr} : ${mStr}`;
                    }
                });
            }
            
            updateCountdowns();
            setInterval(updateCountdowns, 1000);

            // 2. Client-side Search and Category Filters
            function applyFilters() {
                const query = navbarSearch.value.trim().toLowerCase();
                let visibleCount = 0;

                eventCards.forEach(card => {
                    const name = card.dataset.name;
                    const location = card.dataset.location;
                    const category = card.dataset.category;

                    const matchesQuery = name.includes(query) || location.includes(query);
                    const matchesCategory = (currentCategory === 'all' || category === currentCategory);

                    if (matchesQuery && matchesCategory) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (visibleCount === 0 && eventCards.length > 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }
            }

            // Bind Search Input
            navbarSearch.addEventListener('input', applyFilters);

            // Bind Category Filtering function to Window
            window.filterCategory = (category) => {
                currentCategory = category;
                
                // Toggle active classes on buttons
                const btnAll = document.getElementById('btn-cat-all');
                const btnIndo = document.getElementById('btn-cat-indonesia');
                const btnIntl = document.getElementById('btn-cat-international');
                
                const activeClasses = "px-6 py-2.5 bg-[#8c66ff] text-black font-bold font-display rounded-xl text-xs transition-all shadow-md shadow-[#8c66ff]/20";
                const inactiveClasses = "px-6 py-2.5 border border-[#8c66ff]/30 bg-transparent text-purple-200 hover:text-white hover:bg-[#8c66ff]/10 font-bold font-display rounded-xl text-xs transition-all";
                
                btnAll.className = (category === 'all') ? activeClasses : inactiveClasses;
                btnIndo.className = (category === 'indonesia') ? activeClasses : inactiveClasses;
                btnIntl.className = (category === 'international') ? activeClasses : inactiveClasses;

                applyFilters();
            };

            // 3. Sorting Dropdown logic
            const sortBtn = document.getElementById('sort-dropdown-btn');
            let sortAsc = true;

            sortBtn.addEventListener('click', () => {
                sortAsc = !sortAsc;
                sortBtn.innerHTML = `Urutkan: ${sortAsc ? 'Terdekat' : 'Terlama'} <span class="text-[10px] opacity-75">${sortAsc ? '▼' : '▲'}</span>`;
                
                const cardArray = Array.from(eventCards);
                const listWrapper = document.getElementById('events-list');

                cardArray.sort((a, b) => {
                    const timeA = parseInt(a.dataset.timestamp);
                    const timeB = parseInt(b.dataset.timestamp);
                    return sortAsc ? (timeA - timeB) : (timeB - timeA);
                });

                listWrapper.innerHTML = '';
                cardArray.forEach(card => listWrapper.appendChild(card));
            });
        });
    </script>
</body>
</html>
