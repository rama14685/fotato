<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition">Dashboard</a>
            <span class="text-gray-500">/</span>
            Buat Album Baru
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-xl sm:rounded-3xl">
                <div class="p-8 md:p-12">
                    
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-white mb-2">Informasi Album</h3>
                        <p class="text-gray-400 text-sm">Kelompokkan foto-foto jepretan Anda berdasarkan waktu dan lokasi agar mudah ditemukan oleh pelanggan.</p>
                    </div>

                    <form action="{{ route('albums.store') }}" method="POST" class="space-y-6">
                        @csrf <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Nama Album / Acara <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" required placeholder="Contoh: CFD Simpang Lima Minggu Pagi" 
                                class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 text-white placeholder-gray-500 transition">
                            @error('title')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-300 mb-2">Lokasi (Opsional)</label>
                                <input type="text" id="location" name="location" placeholder="Contoh: Jl. Pahlawan, Semarang" 
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 text-white placeholder-gray-500 transition">
                            </div>

                            <div>
                                <label for="event_date" class="block text-sm font-medium text-gray-300 mb-2">Tanggal & Waktu (Opsional)</label>
                                <input type="datetime-local" id="event_date" name="event_date" 
                                    class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-xl focus:outline-none focus:border-purple-500 text-white transition color-scheme-dark">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-white/10">
                            <a href="{{ route('dashboard') }}" class="px-6 py-3 text-sm font-medium text-gray-300 hover:text-white transition">Batal</a>
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition transform hover:scale-105">
                                Simpan Album
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>