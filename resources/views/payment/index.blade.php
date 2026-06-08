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
                            <!-- QRIS -->
                            <div class="p-4 border-2 border-purple-500 rounded-lg cursor-pointer hover:border-purple-500 transition payment-method" data-method="qris">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="payment_method" value="qris" class="w-5 h-5" checked>
                                    <div>
                                        <h4 class="text-white font-bold">QRIS</h4>
                                        <p class="text-gray-400 text-sm">Scan barcode QRIS untuk bayar instan</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Transfer Bank -->
                            <div class="p-4 border-2 border-white/20 rounded-lg cursor-pointer hover:border-purple-500 transition payment-method" data-method="bank">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="payment_method" value="bank" class="w-5 h-5">
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
                                        <h4 class="text-white font-bold">E-Wallet</h4>
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

                        <!-- QRIS Container -->
                        <div id="qrisContainer" class="mt-6 p-6 bg-white/5 border border-white/10 rounded-2xl text-center">
                            <h4 class="text-white font-bold mb-4">Scan QRIS untuk Pembayaran</h4>
                            <img src="{{ asset('images/qris.png') }}" alt="QRIS Barcode" class="mx-auto max-w-xs rounded-xl shadow-lg mb-4">
                            <p class="text-gray-400 text-xs">Pindai kode QR di atas menggunakan aplikasi e-wallet atau mobile banking Anda.</p>
                        </div>

                        <!-- Payment Gateway Form -->
                        <form action="{{ route('payment.process', $transaction) }}" method="POST" id="paymentForm">
                            @csrf
                            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
                            <input type="hidden" name="payment_method" value="qris">

                            <button type="submit" class="w-full mt-6 px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105">
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
                // Reset active borders
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('border-purple-500');
                    m.classList.add('border-white/20');
                });
                
                // Add active border to selected
                this.classList.remove('border-white/20');
                this.classList.add('border-purple-500');

                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                document.getElementById('paymentForm').elements['payment_method'].value = radio.value;

                // Toggle QRIS container
                const qrisContainer = document.getElementById('qrisContainer');
                if (radio.value === 'qris') {
                    qrisContainer.classList.remove('hidden');
                } else {
                    qrisContainer.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>
