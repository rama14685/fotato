<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight flex items-center gap-2">
            <a href="{{ route('cart.index') }}" class="text-gray-400 hover:text-white transition">Keranjang</a>
            <span class="text-gray-500">/</span>
            Checkout
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Checkout Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-8">
                        <h3 class="text-2xl font-bold text-white mb-6">Data Pembeli</h3>

                        <form action="{{ route('checkout.process') }}" method="POST" class="space-y-6">
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Nama Lengkap</label>
                                <input type="text" value="{{ $user->name }}" disabled
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg text-gray-400 cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                                <input type="email" value="{{ $user->email }}" disabled
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg text-gray-400 cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Nomor Telepon <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone" required placeholder="Cth: 081234567890"
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white placeholder-gray-500">
                                @error('phone')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                <textarea name="address" required placeholder="Jl. ..., Kota, Provinsi" rows="4"
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"></textarea>
                                @error('address')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pt-6 border-t border-white/10">
                                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg transition transform hover:scale-105">
                                    💳 Lanjut ke Pembayaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6 sticky top-20">
                        <h3 class="text-2xl font-bold text-white mb-6">Ringkasan Pesanan</h3>

                        <div class="space-y-3 mb-6 pb-6 border-b border-white/10 max-h-64 overflow-y-auto">
                            @foreach ($cartItems as $item)
                                <div class="flex justify-between text-gray-400 text-sm">
                                    <span class="truncate mr-2">{{ $item['title'] }} x{{ $item['quantity'] }}</span>
                                    <span class="flex-shrink-0">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-gray-400 text-sm">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-400 text-sm">
                                <span>Pajak</span>
                                <span>Rp 0</span>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-white/10">
                            <div class="flex justify-between text-white">
                                <span class="text-lg font-bold">Total</span>
                                <span class="text-2xl font-bold text-green-400">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <a href="{{ route('cart.index') }}" class="w-full px-6 py-2 text-center text-gray-400 hover:text-white transition mt-6 block">
                            ← Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
