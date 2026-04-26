<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            📜 Riwayat Pembelian
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            @if($transactions->count() > 0)
                <div class="space-y-6">
                    @foreach($transactions as $transaction)
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6">
                            <!-- Header -->
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 pb-6 border-b border-white/10">
                                <div>
                                    <h3 class="text-xl font-bold text-white mb-2">Pesanan #{{ $transaction->id }}</h3>
                                    <p class="text-gray-400 text-sm">📅 {{ $transaction->created_at->format('d M Y - H:i') }}</p>
                                </div>

                                <div class="mt-4 md:mt-0 flex items-center gap-4">
                                    <div class="text-right">
                                        <p class="text-gray-400 text-sm mb-1">Total</p>
                                        <p class="text-2xl font-bold text-green-400">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                    </div>

                                    <div>
                                        @if($transaction->status === 'completed')
                                            <span class="inline-block px-4 py-2 bg-green-500/20 text-green-300 rounded-full text-sm font-bold">✅ Selesai</span>
                                        @elseif($transaction->status === 'paid')
                                            <span class="inline-block px-4 py-2 bg-blue-500/20 text-blue-300 rounded-full text-sm font-bold">💳 Dibayar</span>
                                        @elseif($transaction->status === 'pending')
                                            <span class="inline-block px-4 py-2 bg-yellow-500/20 text-yellow-300 rounded-full text-sm font-bold">⏳ Menunggu</span>
                                        @else
                                            <span class="inline-block px-4 py-2 bg-red-500/20 text-red-300 rounded-full text-sm font-bold">❌ Dibatalkan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Items -->
                            <div class="mb-6">
                                <h4 class="text-white font-bold mb-4">📸 Foto yang Dibeli</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($transaction->items as $item)
                                        <div class="bg-gray-900/50 rounded-lg overflow-hidden">
                                            <div class="h-32 bg-gray-800 flex items-center justify-center overflow-hidden">
                                                @if($item->photo->watermark_path && file_exists(public_path('storage/' . $item->photo->watermark_path)))
                                                    <img src="{{ asset('storage/' . $item->photo->watermark_path) }}" alt="Foto" class="w-full h-full object-cover">
                                                @else
                                                    <div class="text-gray-500">🖼️</div>
                                                @endif
                                            </div>
                                            <div class="p-3">
                                                <h5 class="text-white font-bold text-sm mb-1">{{ $item->photo->album->title }}</h5>
                                                <p class="text-gray-400 text-xs mb-2">👤 {{ $item->photo->album->photographer->name }}</p>
                                                <p class="text-green-400 font-bold text-sm mb-3">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                                
                                                @if($transaction->status === 'completed' || $transaction->status === 'paid')
                                                    <a href="{{ route('purchase.download', $item) }}" class="w-full block px-3 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-semibold rounded text-center hover:from-blue-600 hover:to-purple-700 transition">
                                                        📥 Download
                                                    </a>
                                                @else
                                                    <button disabled class="w-full px-3 py-2 bg-gray-700 text-gray-400 text-sm font-semibold rounded cursor-not-allowed">
                                                        ⏳ Tunggu Pembayaran
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="pt-6 border-t border-white/10 flex gap-3">
                                @if($transaction->status === 'pending')
                                    <a href="{{ route('payment.show', $transaction) }}" class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-purple-700 transition">
                                        💳 Lanjutkan Pembayaran
                                    </a>
                                @endif

                                <a href="{{ route('catalog.index') }}" class="px-6 py-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-medium rounded-lg transition">
                                    🛍️ Belanja Lagi
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 border-dashed rounded-2xl p-12 text-center">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-xl font-semibold text-gray-300 mb-2">Belum Ada Pembelian</h3>
                    <p class="text-gray-400 mb-6">Mulai jelajahi koleksi foto kami sekarang</p>
                    <a href="{{ route('catalog.index') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-xl transition transform hover:scale-105">
                        🛍️ Mulai Belanja
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
