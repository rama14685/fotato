<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            📸 Cari Foto Anda
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Face Capture & Album Selection -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Step 1: Capture/Upload Face -->
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">1</span>
                            Scan Wajah Anda
                        </h3>
                        
                        <div class="flex flex-wrap gap-4 mb-4">
                            <button id="startCamera" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg transition transform hover:scale-105">
                                📷 Gunakan Kamera
                            </button>
                            <label for="uploadFace" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-xl shadow-lg transition transform hover:scale-105 cursor-pointer">
                                📁 Upload Foto
                            </label>
                            <input type="file" id="uploadFace" accept="image/jpeg,image/png,image/webp" class="hidden">
                        </div>

                        <!-- Video and Canvas for Camera Capture -->
                        <div class="relative">
                            <video id="video" width="640" height="480" autoplay class="w-full rounded-lg border border-white/10 bg-gray-900" style="display:none;"></video>
                            <canvas id="canvas" width="640" height="480" class="w-full rounded-lg border border-white/10 bg-gray-900" style="display:none;"></canvas>
                            <img id="preview" class="w-full rounded-lg border border-white/10 bg-gray-900" style="display:none;">
                        </div>

                        <!-- Error/Status Messages -->
                        <div id="faceStatus" class="mt-4 text-sm"></div>
                    </div>

                    <!-- Step 2: Select Album -->
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-purple-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                            Pilih Event/Album
                        </h3>
                        
                        <select id="albumSelect" class="w-full px-4 py-3 bg-gray-900/50 border border-gray-700 rounded-lg focus:outline-none focus:border-purple-500 text-white">
                            <option value="">-- Pilih Album --</option>
                            @foreach($albums as $album)
                                <option value="{{ $album->id }}">
                                    {{ $album->title }} - {{ $album->location }} ({{ $album->event_date->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Step 3: Search Button -->
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-green-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">3</span>
                            Cari Foto
                        </h3>
                        
                        <button id="searchBtn" class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg transition transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none" disabled>
                            🔍 Cari Foto Saya
                        </button>
                        
                        <div id="loading" style="display:none;" class="mt-4 flex items-center justify-center text-blue-400">
                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Mencari foto...</span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Instructions -->
                <div class="lg:col-span-1">
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6 sticky top-20">
                        <h3 class="text-xl font-bold text-white mb-4">💡 Cara Menggunakan</h3>
                        
                        <div class="space-y-4 text-gray-300 text-sm">
                            <div class="flex gap-3">
                                <span class="text-blue-400 font-bold">1.</span>
                                <div>
                                    <p class="font-semibold text-white mb-1">Scan Wajah</p>
                                    <p>Gunakan kamera atau upload foto wajah Anda. Pastikan wajah terlihat jelas dan menghadap kamera.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-3">
                                <span class="text-purple-400 font-bold">2.</span>
                                <div>
                                    <p class="font-semibold text-white mb-1">Pilih Album</p>
                                    <p>Pilih event/album yang ingin Anda cari. Album diurutkan berdasarkan tanggal terbaru.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-3">
                                <span class="text-green-400 font-bold">3.</span>
                                <div>
                                    <p class="font-semibold text-white mb-1">Cari Foto</p>
                                    <p>Klik tombol "Cari Foto Saya" untuk menemukan foto yang mengandung wajah Anda.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-white/10">
                            <p class="text-xs text-gray-400">
                                🔒 <strong>Privasi Terjaga:</strong> Data wajah Anda hanya digunakan untuk pencarian dan tidak disimpan di server kami.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Step 4: Results Section -->
            <div class="mt-8">
                <div id="resultsContainer" style="display:none;">
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden rounded-2xl shadow-lg p-6">
                        <h3 class="text-2xl font-bold text-white mb-6">📷 Hasil Pencarian</h3>
                        <div id="results" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="{{ asset('js/face-scan.js') }}"></script>
</x-app-layout>
