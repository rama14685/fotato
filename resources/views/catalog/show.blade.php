<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight flex items-center gap-2">
            <a href="{{ route('catalog.index') }}" class="text-gray-400 hover:text-white transition">Belanja</a>
            <span class="text-gray-500">/</span>
            Detail Foto
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Photo Preview -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg">
                    <div class="h-96 bg-gray-900 flex items-center justify-center">
                        @if (file_exists(public_path('storage/' . $photo->watermark_path)))
                            <img src="{{ asset('storage/' . $photo->watermark_path) }}" alt="{{ $photo->album->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-gray-500 text-center">
                                <div class="text-6xl mb-2">🖼️</div>
                                <p>Foto tidak tersedia</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Photo Details -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-8">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $photo->album->title }}</h1>
                    
                    <p class="text-gray-400 mb-6">
                        Oleh <span class="font-bold text-purple-400">{{ $photo->album->photographer?->name ?? 'Admin' }}</span>
                    </p>

                    @if ($photo->album->location)
                        <div class="flex items-center gap-2 text-gray-400 mb-2">
                            <span>📍</span>
                            <span>{{ $photo->album->location }}</span>
                        </div>
                    @endif

                    @if ($photo->album->event_date)
                        <div class="flex items-center gap-2 text-gray-400 mb-6">
                            <span>📅</span>
                            <span>{{ $photo->album->event_date->format('d M Y - H:i') }}</span>
                        </div>
                    @endif

                    <div class="border-t border-white/10 pt-6 mb-6">
                        <div class="text-sm text-gray-400 mb-2">Harga</div>
                        <div class="text-4xl font-bold text-green-400 mb-6">Rp {{ number_format($photo->price, 0, ',', '.') }}</div>

                        <p class="text-gray-400 text-sm mb-6">
                            ✓ Foto Resolusi Tinggi<br>
                            ✓ Bebas Watermark setelah pembayaran<br>
                            ✓ Bisa di-download unlimited
                        </p>
                    </div>

                    <form action="{{ route('cart.add') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="photo_id" value="{{ $photo->id }}">
                        <input type="hidden" name="title" value="{{ $photo->album->title }}">
                        <input type="hidden" name="price" value="{{ $photo->price }}">
                        <input type="hidden" name="photographer" value="{{ $photo->album->photographer?->name ?? 'Admin' }}">
                        <input type="hidden" name="image" value="{{ $photo->watermark_path }}">

                        <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg transition transform hover:scale-105">
                            🛒 Tambah ke Keranjang
                        </button>
                    </form>

                    <a href="{{ route('catalog.index') }}" class="block text-center px-6 py-2 text-gray-400 hover:text-white transition mt-4">
                        ← Kembali
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
