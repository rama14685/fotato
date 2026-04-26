<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            🛒 Keranjang Belanja
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            @if (count($cartItems) > 0)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6">
                            <h3 class="text-2xl font-bold text-white mb-6">Item dalam Keranjang ({{ count($cartItems) }})</h3>

                            <div class="space-y-4">
                                @foreach ($cartItems as $item)
                                    <div class="flex gap-4 pb-4 border-b border-white/10 last:border-b-0">
                                        <!-- Item Image -->
                                        <div class="w-24 h-24 bg-gray-900 rounded-lg overflow-hidden flex-shrink-0">
                                            @if (file_exists(public_path('storage/' . $item['image'])))
                                                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-500">🖼️</div>
                                            @endif
                                        </div>

                                        <!-- Item Details -->
                                        <div class="flex-1">
                                            <h4 class="text-lg font-bold text-white">{{ $item['title'] }}</h4>
                                            <p class="text-gray-400 text-sm">oleh {{ $item['photographer'] }}</p>
                                            <p class="text-green-400 font-bold mt-2">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                        </div>

                                        <!-- Quantity & Actions -->
                                        <div class="flex flex-col items-end justify-between">
                                            <form action="{{ route('cart.remove') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="photo_id" value="{{ $item['photo_id'] }}">
                                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Hapus</button>
                                            </form>

                                            <form action="{{ route('cart.update') }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="photo_id" value="{{ $item['photo_id'] }}">
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="w-12 px-2 py-1 bg-gray-900/50 border border-gray-700 rounded text-white text-center">
                                                <button type="submit" class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <form action="{{ route('cart.clear') }}" method="POST" class="mt-6 pt-4 border-t border-white/10">
                                @csrf
                                <button type="submit" class="text-gray-400 hover:text-red-400 text-sm transition">🗑️ Kosongkan Keranjang</button>
                            </form>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6 sticky top-20">
                            <h3 class="text-2xl font-bold text-white mb-6">Ringkasan Pesanan</h3>

                            <div class="space-y-4 mb-6 pb-6 border-b border-white/10">
                                <div class="flex justify-between text-gray-400">
                                    <span>Subtotal</span>
                                    <span>Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-gray-400">
                                    <span>Pajak (0%)</span>
                                    <span>Rp 0</span>
                                </div>
                                <div class="flex justify-between text-gray-400">
                                    <span>Biaya Admin</span>
                                    <span>Rp 0</span>
                                </div>
                            </div>

                            <div class="flex justify-between text-white mb-6">
                                <span class="text-lg font-bold">Total</span>
                                <span class="text-2xl font-bold text-green-400">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                            </div>

                            <a href="{{ route('checkout.index') }}" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg transition transform hover:scale-105 block text-center">
                                Lanjut ke Checkout
                            </a>

                            <a href="{{ route('catalog.index') }}" class="w-full px-6 py-2 text-center text-gray-400 hover:text-white transition mt-3">
                                Lanjut Belanja
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 border-dashed rounded-2xl p-12 text-center">
                    <div class="text-6xl mb-4">🛒</div>
                    <h3 class="text-xl font-semibold text-gray-300 mb-2">Keranjang Kosong</h3>
                    <p class="text-gray-400 mb-6">Mulai belanja foto favorit Anda sekarang!</p>
                    <a href="{{ route('catalog.index') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition transform hover:scale-105">
                        🛍️ Lihat Katalog
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
