<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            💳 Pembayaran
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Payment Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-xl p-8">
                        <h3 class="text-2xl font-bold text-white mb-2">Pilih Metode Pembayaran</h3>
                        <p class="text-gray-400 mb-6">Total yang harus dibayar: <span class="text-2xl font-bold text-green-400">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span></p>

                        <!-- Payment Methods -->
                        <div class="space-y-4 mb-8">
                            <!-- Transfer Bank -->
                            <div class="p-4 border-2 border-white/20 rounded-lg cursor-pointer hover:border-purple-500 transition payment-method" data-method="bank">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="payment_method" value="bank" class="w-5 h-5" checked>
                                    <div>
                                        <h4 class="text-white font-bold">Transfer Bank</h4>
                                        <p class="text-gray-400 text-sm">Transfer ke rekening kami</p>
                                    </div>
                                </div>
                            </div>

                            <!-- E-Wallet -->
                            <div class="p-4 border-2 border-white/20 rounded-lg cursor-pointer hover:border-purple-500 transition payment-method" data-method="wallet">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="payment_method" value="wallet" class="w-5 h-5">
                                    <div>
                                        <h4 class="text-white font-bold">E-Wallet (GCash, PayMaya)</h4>
                                        <p class="text-gray-400 text-sm">Pembayaran instan via e-wallet</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Debit/Credit Card -->
                            <div class="p-4 border-2 border-white/20 rounded-lg cursor-pointer hover:border-purple-500 transition payment-method" data-method="card">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="payment_method" value="card" class="w-5 h-5">
                                    <div>
                                        <h4 class="text-white font-bold">Kartu Kredit/Debit</h4>
                                        <p class="text-gray-400 text-sm">Visa, Mastercard, JCB</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Gateway Form -->
                        <form action="{{ route('payment.process') }}" method="POST" id="paymentForm">
                            @csrf
                            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
                            <input type="hidden" name="payment_method" value="bank">

                            <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105">
                                💳 Proses Pembayaran
                            </button>
                        </form>

                        <p class="text-gray-400 text-sm text-center mt-6">
                            🔒 Pembayaran Aman & Terenkripsi
                        </p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-xl p-6 sticky top-20">
                        <h3 class="text-xl font-bold text-white mb-6">Detail Pesanan</h3>

                        <div class="space-y-3 mb-6 pb-6 border-b border-white/10">
                            <p class="text-gray-400">
                                <span class="block text-sm">No. Pesanan</span>
                                <span class="text-white font-bold">#{{ $transaction->id }}</span>
                            </p>
                            <p class="text-gray-400">
                                <span class="block text-sm">Status</span>
                                <span class="inline-block px-3 py-1 bg-yellow-500/20 text-yellow-300 rounded-full text-sm font-bold">{{ ucfirst($transaction->status) }}</span>
                            </p>
                            <p class="text-gray-400">
                                <span class="block text-sm">Waktu Pesanan</span>
                                <span class="text-white text-sm">{{ $transaction->created_at->format('d M Y H:i') }}</span>
                            </p>
                        </div>

                        <div class="mb-6">
                            <p class="text-gray-400 text-sm mb-2">Total Pembayaran</p>
                            <p class="text-3xl font-bold text-green-400">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                        </div>

                        <a href="{{ route('catalog.index') }}" class="block px-6 py-2 text-center text-gray-400 hover:text-white transition">
                            ← Kembali ke Katalog
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                document.getElementById('paymentForm').elements['payment_method'].value = radio.value;
            });
        });
    </script>
</x-app-layout>
