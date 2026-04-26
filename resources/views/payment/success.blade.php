<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            ✅ Pembayaran Berhasil
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            
            <div class="text-center">
                <div class="inline-block p-4 bg-green-500/20 border border-green-500/50 rounded-full mb-6">
                    <svg class="w-16 h-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-4xl font-bold text-white mb-2">Pembayaran Berhasil!</h1>
                <p class="text-gray-400 mb-8">Terima kasih telah melakukan pembelian. Silakan download foto Anda sekarang.</p>

                <!-- Transaction Details -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-xl p-8 mb-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-gray-400 text-sm mb-2">No. Pesanan</p>
                            <p class="text-white font-bold text-lg">#{{ $transaction->id }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-2">Total Pembayaran</p>
                            <p class="text-green-400 font-bold text-lg">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-2">Jumlah Foto</p>
                            <p class="text-white font-bold text-lg">{{ count($transactionItems) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-2">Waktu Transaksi</p>
                            <p class="text-white font-bold text-sm">{{ $transaction->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Downloaded Photos -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-xl p-8 mb-8">
                    <h3 class="text-xl font-bold text-white mb-6 text-left">📥 Download Foto Anda</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($transactionItems as $item)
                            <div class="bg-gray-900/50 rounded-lg p-4 flex gap-4 items-start">
                                <div class="w-16 h-16 bg-gray-800 rounded overflow-hidden flex-shrink-0">
                                    @if($item->photo->watermark_path && file_exists(public_path('storage/' . $item->photo->watermark_path)))
                                        <img src="{{ asset('storage/' . $item->photo->watermark_path) }}" alt="Foto" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-500">🖼️</div>
                                    @endif
                                </div>

                                <div class="flex-1 text-left">
                                    <h4 class="text-white font-bold text-sm mb-2">{{ $item->photo->album->title }}</h4>
                                    <p class="text-gray-400 text-xs mb-3">👤 {{ $item->photo->album->photographer->name }}</p>
                                    <a href="{{ route('purchase.download', $item) }}" class="inline-block px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white text-sm font-semibold rounded transition">
                                        📥 Download
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    <a href="{{ route('purchase.history') }}" class="inline-block px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg transition transform hover:scale-105">
                        📜 Lihat Riwayat Pembelian
                    </a>

                    <a href="{{ route('catalog.index') }}" class="inline-block px-8 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-medium rounded-lg transition ml-3">
                        🛍️ Lanjut Belanja
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
