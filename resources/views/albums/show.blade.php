<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            📸 Foto dalam Album: {{ $album->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Album Header -->
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-xl sm:rounded-3xl mb-8">
                <div class="p-8">
                    <div class="flex items-start gap-6 flex-col md:flex-row">
                        <!-- Thumbnail -->
                        <div class="w-full md:w-48 flex-shrink-0">
                            @if($album->thumbnail_path && file_exists(public_path('storage/' . $album->thumbnail_path)))
                                <img src="{{ asset('storage/' . $album->thumbnail_path) }}" alt="{{ $album->title }}" class="w-full h-48 object-cover rounded-lg shadow-lg">
                            @else
                                <div class="w-full h-48 bg-gray-900 rounded-lg flex items-center justify-center">
                                    <span class="text-5xl">📁</span>
                                </div>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-1">
                            <h1 class="text-4xl font-bold gradient-text mb-3">{{ $album->title }}</h1>
                            <p class="text-gray-400 text-lg mb-4">oleh <span class="font-bold text-white">{{ $album->photographer->name }}</span></p>
                            
                            <div class="flex flex-wrap gap-6 text-base text-gray-400 mb-6">
                                @if($album->location)
                                    <span class="flex items-center gap-2">
                                        <span class="text-2xl">📍</span> 
                                        <span class="font-medium">{{ $album->location }}</span>
                                    </span>
                                @endif
                                
                                @if($album->event_date)
                                    <span class="flex items-center gap-2">
                                        <span class="text-2xl">📅</span> 
                                        <span class="font-medium">{{ $album->event_date->format('d M Y') }}</span>
                                    </span>
                                @endif
                                
                                <span class="flex items-center gap-2">
                                    <span class="text-2xl">📸</span> 
                                    <span class="font-bold text-white">{{ $album->photos->count() }} foto tersedia</span>
                                </span>
                            </div>

                            <a href="{{ route('albums.index') }}" class="inline-block px-6 py-3 bg-white/5 hover:bg-white/10 border border-white/10 text-gray-300 hover:text-white rounded-lg transition font-medium">
                                ← Kembali ke Daftar Album
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 px-6 py-4 rounded-lg text-center font-medium">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-400 px-6 py-4 rounded-lg text-center font-medium">
                    ✗ {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 bg-blue-500/10 border border-blue-500/30 text-blue-400 px-6 py-4 rounded-lg text-center font-medium">
                    ℹ {{ session('info') }}
                </div>
            @endif

            <!-- Photos Grid -->
            @if ($album->photos->count() > 0)
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-white">Semua Foto dalam Album Ini ({{ $album->photos->count() }})</h3>
                    <p class="text-gray-400 mt-1">Klik tombol "Tambah ke Keranjang" untuk membeli foto</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($album->photos as $photo)
                        @php
                            $isPurchased = auth()->check() ? $photo->isPurchasedBy(auth()->id()) : false;
                            $displayPath = $photo->getDisplayPath(auth()->id());
                        @endphp
                        
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg hover:bg-white/10 hover:scale-105 transition-all duration-300 group {{ $isPurchased ? 'ring-2 ring-green-500' : '' }}">
                            <!-- Photo Preview -->
                            <div class="relative h-56 bg-gray-900 overflow-hidden flex items-center justify-center">
                                @if($displayPath && file_exists(public_path('storage/' . $displayPath)))
                                    <img src="{{ asset('storage/' . $displayPath) }}" alt="Photo" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                    
                                    @if(!$isPurchased && !$photo->watermark_path)
                                        <!-- Watermark overlay jika watermark belum di-generate -->
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <div class="text-white/30 text-4xl font-bold transform -rotate-45">
                                                FOTLIST
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-gray-500 text-center">
                                        <div class="text-4xl mb-2">🖼️</div>
                                        <p class="text-sm">Foto tidak tersedia</p>
                                    </div>
                                @endif

                                @if($isPurchased)
                                    <div class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                        ✓ Sudah Dibeli
                                    </div>
                                @endif
                            </div>

                            <!-- Photo Info -->
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl font-bold text-green-400">Rp {{ number_format($photo->price, 0, ',', '.') }}</span>
                                </div>
                                
                                @if($isPurchased)
                                    <div class="w-full px-4 py-3 bg-green-500/20 border border-green-500/30 text-green-400 text-sm font-bold rounded-lg text-center">
                                        ✓ Anda sudah membeli foto ini
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('cart.add') }}" class="w-full">
                                        @csrf
                                        <input type="hidden" name="photo_id" value="{{ $photo->id }}">
                                        <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white text-sm font-bold rounded-lg transition shadow-lg">
                                            🛒 Tambah ke Keranjang
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 border-dashed rounded-2xl p-16 text-center">
                    <div class="text-7xl mb-6">📸</div>
                    <h3 class="text-2xl font-bold text-gray-300 mb-3">Belum Ada Foto di Album Ini</h3>
                    <p class="text-gray-400 mb-6 text-lg">Album ini belum memiliki foto yang tersedia</p>
                    <a href="{{ route('albums.index') }}" class="inline-block px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg transition">
                        ← Kembali ke Daftar Album
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
