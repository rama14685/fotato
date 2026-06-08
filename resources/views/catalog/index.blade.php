<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            🛍️ Belanja Foto
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Search & Filter Section -->
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-xl sm:rounded-3xl mb-8">
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-white mb-6">Cari Foto</h3>
                    
                    <form method="GET" action="{{ route('catalog.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Lokasi</label>
                                <input type="text" name="location" value="{{ request('location') }}" placeholder="Cth: Kota Lama Semarang"
                                    class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white placeholder-gray-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Dari Tanggal</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                    class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white color-scheme-dark">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Sampai Tanggal</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}"
                                    class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white color-scheme-dark">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Harga Min (Rp)</label>
                                <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="0"
                                    class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white placeholder-gray-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Harga Max (Rp)</label>
                                <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="999999999"
                                    class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white placeholder-gray-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Photographer</label>
                                <input type="text" name="photographer" value="{{ request('photographer') }}" placeholder="Nama photographer"
                                    class="w-full px-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white placeholder-gray-500">
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                                🔍 Cari
                            </button>
                            <a href="{{ route('catalog.index') }}" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-lg transition">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Photos Grid -->
            @if ($photos->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach ($photos as $photo)
                        <a href="{{ route('catalog.show', $photo) }}" class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg hover:bg-white/10 transition group">
                            <!-- Photo Preview -->
                            <div class="relative h-56 bg-gray-900 overflow-hidden flex items-center justify-center">
                                @if($photo->watermark_path && file_exists(public_path('storage/' . $photo->watermark_path)))
                                    <img src="{{ asset('storage/' . $photo->watermark_path) }}" alt="{{ $photo->album->title }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                @elseif($photo->original_path && file_exists(public_path('storage/' . $photo->original_path)))
                                    <img src="{{ asset('storage/' . $photo->original_path) }}" alt="{{ $photo->album->title }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                @else
                                    <div class="text-gray-500 text-center">
                                        <div class="text-4xl mb-2">🖼️</div>
                                        <p class="text-sm">Foto tidak tersedia</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Photo Info -->
                            <div class="p-4">
                                <h3 class="text-lg font-bold text-white mb-1 truncate group-hover:text-purple-400 transition">{{ $photo->album->title }}</h3>
                                
                                <p class="text-gray-400 text-sm mb-2">oleh <span class="font-medium">{{ $photo->album->photographer?->name ?? 'Admin' }}</span></p>

                                @if ($photo->album->location)
                                    <p class="text-gray-400 text-xs mb-3 flex items-center gap-1">
                                        <span>📍</span> {{ $photo->album->location }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between pt-3 border-t border-white/10">
                                    <span class="text-2xl font-bold text-green-400">Rp {{ number_format($photo->price, 0, ',', '.') }}</span>
                                    <form method="POST" action="{{ route('cart.add') }}" class="inline" onclick="event.stopPropagation();">
                                        @csrf
                                        <input type="hidden" name="photo_id" value="{{ $photo->id }}">
                                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition">
                                            🛍️ Keranjang
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center">
                    {{ $photos->links() }}
                </div>
            @else
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 border-dashed rounded-2xl p-12 text-center">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-xl font-semibold text-gray-300 mb-2">Tidak Ada Foto Ditemukan</h3>
                    <p class="text-gray-400 mb-6">Coba ubah filter pencarian Anda</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
