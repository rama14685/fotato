<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition">Dashboard</a>
            <span class="text-gray-500">/</span>
            Upload Foto
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-xl sm:rounded-3xl">
                <div class="p-8 md:p-12">
                    
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-white mb-2">Upload Foto Baru</h3>
                        <p class="text-gray-400 text-sm">Tambahkan foto ke album <span class="text-purple-400 font-semibold">{{ $album->title }}</span>. Sistem akan otomatis menempelkan watermark di tengah foto.</p>
                    </div>

                    <form action="{{ route('photos.store', $album->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="photo" class="block text-sm font-medium text-gray-300 mb-2">Pilih Foto (Max 10MB) <span class="text-red-500">*</span></label>
                            <input type="file" id="photo" name="photo" accept="image/jpeg, image/png, image/jpg" required 
                                class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 text-white transition 
                                file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer">
                            @error('photo')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-300 mb-2">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" id="price" name="price" required min="0" placeholder="Contoh: 15000" 
                                class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 text-white placeholder-gray-500 transition">
                            <p class="text-gray-500 text-xs mt-2">*Harga ini yang akan dibayar oleh pelanggan untuk menebus foto tanpa watermark.</p>
                            @error('price')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-white/10">
                            <a href="{{ route('dashboard') }}" class="px-6 py-3 text-sm font-medium text-gray-300 hover:text-white transition">Batal</a>
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition transform hover:scale-105">
                                Upload & Proses Watermark
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>