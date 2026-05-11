<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            📁 Pilih Album Event
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Welcome Message -->
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-xl sm:rounded-3xl mb-8">
                <div class="p-8 text-center">
                    <h3 class="text-3xl font-bold gradient-text mb-3">Selamat Datang di Fotlist! 📸</h3>
                    <p class="text-gray-400 text-lg">Pilih album event yang Anda ikuti untuk melihat foto-foto Anda</p>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-xl sm:rounded-3xl mb-8">
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-white mb-6">🔍 Cari Album Event</h3>
                    
                    <form method="GET" action="{{ route('albums.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Search by Album Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Nama Album / Event</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: CFD Simpang Lima, Pernikahan..."
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white placeholder-gray-500">
                            </div>

                            <!-- Search by Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Lokasi Event</label>
                                <input type="text" name="location" value="{{ request('location') }}" placeholder="Contoh: Semarang, Jakarta..."
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white placeholder-gray-500">
                            </div>

                            <!-- Date From -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Dari Tanggal</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white">
                            </div>

                            <!-- Date To -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Sampai Tanggal</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}"
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white">
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-bold rounded-lg transition">
                                🔍 Cari Album
                            </button>
                            <a href="{{ route('albums.index') }}" class="px-8 py-3 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-lg transition">
                                Reset Filter
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Albums Grid -->
            @if ($albums->count() > 0)
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-white">Ditemukan {{ $albums->total() }} Album</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach ($albums as $album)
                        <a href="{{ route('albums.show', $album) }}" class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg hover:bg-white/10 hover:scale-105 transition-all duration-300 group">
                            <!-- Album Thumbnail -->
                            <div class="relative h-56 bg-gray-900 overflow-hidden flex items-center justify-center">
                                @if($album->thumbnail_path && file_exists(public_path('storage/' . $album->thumbnail_path)))
                                    <img src="{{ asset('storage/' . $album->thumbnail_path) }}" alt="{{ $album->title }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                @else
                                    <div class="text-gray-500 text-center p-4">
                                        <div class="text-5xl mb-3">📁</div>
                                        <p class="text-sm font-medium text-gray-400">{{ $album->title }}</p>
                                    </div>
                                @endif
                                
                                <!-- Photo Count Badge -->
                                <div class="absolute top-3 right-3 bg-black/80 backdrop-blur-sm px-3 py-1.5 rounded-full text-white text-sm font-bold shadow-lg">
                                    📸 {{ $album->photos_count }} foto
                                </div>
                            </div>

                            <!-- Album Info -->
                            <div class="p-5">
                                <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-400 transition line-clamp-1">{{ $album->title }}</h3>
                                
                                <p class="text-gray-400 text-sm mb-3">oleh <span class="font-medium text-gray-300">{{ $album->photographer->name }}</span></p>

                                <div class="space-y-2 mb-4">
                                    @if ($album->location)
                                        <p class="text-gray-400 text-sm flex items-center gap-2">
                                            <span class="text-lg">📍</span> 
                                            <span class="line-clamp-1">{{ $album->location }}</span>
                                        </p>
                                    @endif

                                    @if ($album->event_date)
                                        <p class="text-gray-400 text-sm flex items-center gap-2">
                                            <span class="text-lg">📅</span> 
                                            <span>{{ $album->event_date->format('d M Y') }}</span>
                                        </p>
                                    @endif
                                </div>

                                <div class="pt-3 border-t border-white/10">
                                    <span class="text-purple-400 text-sm font-bold group-hover:text-purple-300 transition flex items-center gap-2">
                                        Lihat Foto 
                                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center">
                    {{ $albums->appends(request()->query())->links() }}
                </div>
            @else
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 border-dashed rounded-2xl p-16 text-center">
                    <div class="text-7xl mb-6">🔍</div>
                    <h3 class="text-2xl font-bold text-gray-300 mb-3">Tidak Ada Album Ditemukan</h3>
                    <p class="text-gray-400 mb-6 text-lg">Coba ubah kata kunci atau filter pencarian Anda</p>
                    <a href="{{ route('albums.index') }}" class="inline-block px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg transition">
                        Lihat Semua Album
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
