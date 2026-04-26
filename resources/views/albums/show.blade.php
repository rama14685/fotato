<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition">Dashboard</a>
            <span class="text-gray-500">/</span>
            {{ $album->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Album Header -->
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-xl sm:rounded-3xl mb-8">
                <div class="bg-gradient-to-r from-blue-500/30 to-purple-600/30 h-40 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-6xl mb-4">📸</div>
                        <p class="text-gray-300 text-lg">{{ $album->photos->count() }} Foto</p>
                    </div>
                </div>

                <div class="p-8">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $album->title }}</h1>
                    
                    <div class="flex flex-wrap gap-4 text-gray-400 mb-6">
                        @if ($album->location)
                            <div class="flex items-center gap-2">
                                <span>📍</span>
                                <span>{{ $album->location }}</span>
                            </div>
                        @endif

                        @if ($album->event_date)
                            <div class="flex items-center gap-2">
                                <span>📅</span>
                                <span>{{ $album->event_date->format('d M Y - H:i') }}</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-2">
                            <span>⏱️</span>
                            <span>Dibuat {{ $album->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-white/10">
                        <a href="{{ route('photos.create', $album) }}" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition transform hover:scale-105">
                            + Tambah Foto
                        </a>
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-medium rounded-xl transition">
                            ← Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Photos List -->
            <div>
                <h2 class="text-2xl font-bold text-white mb-6">Foto dalam Album</h2>

                @if ($album->photos->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($album->photos as $photo)
                            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg hover:bg-white/10 transition group">
                                <!-- Photo Preview -->
                                <div class="relative h-48 bg-gray-900 overflow-hidden flex items-center justify-center">
                                    @if (file_exists(public_path('storage/' . $photo->watermark_path)))
                                        <img src="{{ asset('storage/' . $photo->watermark_path) }}" alt="{{ $photo->album->title }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                    @else
                                        <div class="text-gray-500 text-center">
                                            <div class="text-4xl mb-2">🖼️</div>
                                            <p class="text-sm">Foto tidak tersedia</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Photo Info -->
                                <div class="p-4">
                                    <h3 class="text-lg font-bold text-white mb-2">Harga Tinggi</h3>
                                    
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-2xl font-bold text-green-400">Rp {{ number_format($photo->price, 0, ',', '.') }}</span>
                                    </div>

                                    <div class="flex gap-2 pt-3 border-t border-white/10">
                                        <a href="{{ asset('storage/' . $photo->watermark_path) }}" target="_blank" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition text-center">
                                            👁️ Lihat
                                        </a>
                                        <button class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition">
                                            🛒 Beli
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 border-dashed rounded-2xl p-12 text-center">
                        <div class="text-6xl mb-4">📷</div>
                        <h3 class="text-xl font-semibold text-gray-300 mb-2">Album Kosong</h3>
                        <p class="text-gray-400 mb-6">Belum ada foto di album ini. Tambahkan foto pertama Anda sekarang!</p>
                        <a href="{{ route('photos.create', $album) }}" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition transform hover:scale-105">
                            + Tambah Foto Pertama
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
