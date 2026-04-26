<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Dashboard Fotografer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-sm sm:rounded-3xl mb-8">
                <div class="p-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-white">Halo, {{ Auth::user()->name }}! 👋</h3>
                        <p class="text-gray-400 mt-1">Selamat datang di ruang kerja Anda. Siap membagikan karya jepretan hari ini?</p>
                    </div>
                    <a href="{{ route('albums.create') }}" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition transform hover:scale-105 whitespace-nowrap">
    + Buat Album Baru
</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg flex flex-col items-center justify-center text-center hover:bg-white/10 transition">
                    <div class="text-gray-400 text-sm font-medium mb-2">Total Album</div>
                    <div class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">{{ $totalAlbums }}</div>
                </div>
                
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg flex flex-col items-center justify-center text-center hover:bg-white/10 transition">
                    <div class="text-gray-400 text-sm font-medium mb-2">Total Foto</div>
                    <div class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">{{ $totalPhotos }}</div>
                </div>
                
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg flex flex-col items-center justify-center text-center hover:bg-white/10 transition">
                    <div class="text-gray-400 text-sm font-medium mb-2">Saldo Pendapatan</div>
                    <div class="text-4xl font-extrabold text-green-400">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </div>
            </div>

            <!-- Album List Section -->
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-white mb-6">Album Anda</h2>
                
                @if ($albums->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($albums as $album)
                            <a href="{{ route('albums.show', $album) }}" class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg hover:bg-white/10 transition group block">
                                <!-- Album Header -->
                                <div class="bg-gradient-to-r from-blue-500/30 to-purple-600/30 h-32 flex items-center justify-center group-hover:scale-105 transition duration-300">
                                    <div class="text-center">
                                        <div class="text-4xl mb-2">📸</div>
                                        <p class="text-gray-300 text-sm">{{ $album->photos->count() }} Foto</p>
                                    </div>
                                </div>

                                <!-- Album Content -->
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-white mb-2 truncate group-hover:text-purple-400 transition">{{ $album->title }}</h3>
                                    
                                    @if ($album->location)
                                        <p class="text-gray-400 text-sm mb-3 flex items-center gap-2">
                                            <span>📍</span> {{ $album->location }}
                                        </p>
                                    @endif

                                    @if ($album->event_date)
                                        <p class="text-gray-400 text-sm mb-4 flex items-center gap-2">
                                            <span>📅</span> {{ $album->event_date->format('d M Y - H:i') }}
                                        </p>
                                    @endif

                                    <div class="flex items-center justify-between pt-4 border-t border-white/10">
                                        <span class="text-xs text-gray-400">👁️ Lihat Album</span>
                                        <span class="text-gray-400 text-xs">{{ $album->created_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 border-dashed rounded-2xl p-12 text-center">
                        <div class="text-6xl mb-4">📷</div>
                        <h3 class="text-xl font-semibold text-gray-300 mb-2">Belum Ada Album</h3>
                        <p class="text-gray-400 mb-6">Mulai dengan membuat album baru untuk mengorganisir foto jepretan Anda</p>
                        <a href="{{ route('albums.create') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition transform hover:scale-105">
                            + Buat Album Pertama
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>