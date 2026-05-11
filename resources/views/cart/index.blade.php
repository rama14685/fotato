<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            🛒 Keranjang Belanja
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 bg-blue-500/10 border border-blue-500/30 text-blue-400 px-4 py-3 rounded-lg">
                    {{ session('info') }}
                </div>
            @endif

            @if(count($cartItems) > 0)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        @foreach($cartItems as $item)
                            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 {{ $item['is_purchased'] ? 'opacity-50' : '' }}">
                                <div class="flex gap-6">
                                    <!-- Photo Preview -->
                                    <div class="w-32 h-32 flex-shrink-0 bg-gray-900 rounded-lg overflow-hidden">
                                        @if($item['photo']->watermark_path)
                                            <img src="{{ asset('storage/' . $item['photo']->watermark_path) }}" alt="Photo" class="w-full h-full object-cover">
                                        @elseif($item['photo']->original_path)
                                            <img src="{{ asset('storage/' . $item['photo']->original_path) }}" alt="Photo" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-500">
                                                <span class="text-3xl">🖼️</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Photo Info -->
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-white mb-1">{{ $item['photo']->album->title }}</h3>
                                        <p class="text-gray-400 text-sm mb-2">oleh {{ $item['photo']->album->photographer->name }}</p>
                                        
                                        @if($item['photo']->album->location)
                                            <p class="text-gray-400 text-xs mb-3 flex items-center gap-1">
                                                <span>📍</span> {{ $item['photo']->album->location }}
                                            </p>
                                        @endif

                                        @if($item['is_purchased'])
                                            <div class="inline-block px-3 py-1 bg-green-500/20 border border-green-500/30 text-green-400 text-sm rounded-full">
                                                ✓ Sudah Dibeli
                                            </div>
                                        @else
                                            <div class="flex items-center justify-between mt-4">
                                                <span class="text-2xl font-bold text-green-400">
                                                    Rp {{ number_format($item['photo']->price, 0, ',', '.') }}
                                                </span>
                                                
                                                <form method="POST" action="{{ route('cart.remove') }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="photo_id" value="{{ $item['photo']->id }}">
                                                    <button type="submit" class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 text-red-400 rounded-lg transition">
                                                        🗑️ Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 sticky top-6">
                            <h3 class="text-xl font-bold text-white mb-6">Ringkasan Pesanan</h3>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-gray-300">
                                    <span>Jumlah Item</span>
                                    <span class="font-semibold">{{ count(array_filter($cartItems, fn($item) => !$item['is_purchased'])) }}</span>
                                </div>
                                
                                <div class="border-t border-white/10 pt-3">
                                    <div class="flex justify-between text-white text-lg font-bold">
                                        <span>Total</span>
                                        <span class="text-green-400">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($totalPrice > 0)
                                <a href="{{ route('checkout.index') }}" class="block w-full py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white text-center font-bold rounded-lg transition">
                                    💳 Checkout
                                </a>
                            @else
                                <button disabled class="block w-full py-3 bg-gray-700 text-gray-400 text-center font-bold rounded-lg cursor-not-allowed">
                                    Keranjang Kosong
                                </button>
                            @endif

                            <form method="POST" action="{{ route('cart.clear') }}" class="mt-3">
                                @csrf
                                <button type="submit" class="block w-full py-2 bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 text-red-400 text-center font-medium rounded-lg transition">
                                    🗑️ Kosongkan Keranjang
                                </button>
                            </form>

                            <a href="{{ route('catalog.index') }}" class="block w-full py-2 mt-3 bg-white/5 hover:bg-white/10 border border-white/10 text-gray-300 text-center font-medium rounded-lg transition">
                                ← Lanjut Belanja
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 border-dashed rounded-2xl p-12 text-center">
                    <div class="text-6xl mb-4">🛒</div>
                    <h3 class="text-xl font-semibold text-gray-300 mb-2">Keranjang Belanja Kosong</h3>
                    <p class="text-gray-400 mb-6">Belum ada foto yang ditambahkan ke keranjang</p>
                    <a href="{{ route('catalog.index') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-bold rounded-lg transition">
                        🛍️ Mulai Belanja
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
