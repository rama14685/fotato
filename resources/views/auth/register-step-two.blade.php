<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi - Step 2 | Fotlist</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- face-api.js library -->
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Step Indicator -->
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">✓</div>
                    <span class="ml-2 text-green-400 font-semibold">Informasi Dasar</span>
                </div>
                <div class="w-16 h-1 bg-blue-500"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">2</div>
                    <span class="ml-2 text-white font-semibold">Scan Wajah</span>
                </div>
            </div>
            <p class="text-gray-300 text-sm">Step 2 dari 2</p>
        </div>

        <!-- Face Scan Form -->
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-white mb-2">📸 Scan Wajah</h1>
                <p class="text-gray-300">Scan wajah Anda untuk menyelesaikan registrasi</p>
                <div class="mt-4 bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-3">
                    <p class="text-yellow-200 text-sm">⚠️ <strong>Wajib:</strong> Scan wajah diperlukan untuk fitur pencarian foto otomatis</p>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mb-6 bg-blue-500/20 border border-blue-500/50 rounded-lg p-4">
                <h3 class="text-white font-semibold mb-2">💡 Tips untuk hasil terbaik:</h3>
                <ul class="text-gray-300 text-sm space-y-1">
                    <li>• Pastikan wajah Anda terlihat jelas</li>
                    <li>• Gunakan pencahayaan yang cukup</li>
                    <li>• Hadapkan wajah langsung ke kamera</li>
                    <li>• Hindari menggunakan kacamata atau masker</li>
                </ul>
            </div>

            <!-- Loading Models Indicator -->
            <div id="loadingModels" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-white mb-4"></div>
                <p class="text-white">Memuat model AI...</p>
            </div>

            <!-- Face Scan Options -->
            <div id="faceScanOptions" class="hidden space-y-6">
                <!-- Camera Capture Option -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                    <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                        <span class="text-2xl">📷</span>
                        Opsi 1: Ambil Foto dengan Kamera
                    </h3>
                    <button id="startCamera" type="button"
                        class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105">
                        📸 Buka Kamera
                    </button>
                    <video id="video" autoplay muted playsinline class="hidden w-full mt-4 rounded-lg"></video>
                    <canvas id="canvas" width="320" height="240" class="hidden"></canvas>
                </div>

                <!-- File Upload Option -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                    <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                        <span class="text-2xl">📁</span>
                        Opsi 2: Upload Foto dari Perangkat
                    </h3>
                    <label for="uploadFace" class="block w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105 text-center cursor-pointer">
                        📤 Pilih Foto
                    </label>
                    <input type="file" id="uploadFace" accept="image/jpeg,image/png,image/webp" class="hidden">
                    <p class="text-gray-400 text-xs mt-2">Format: JPEG, PNG, WebP (Maks. 5MB)</p>
                    <img id="preview" class="hidden w-full mt-4 rounded-lg">
                </div>

                <!-- Status Messages -->
                <div id="statusMessage" class="hidden rounded-lg p-4"></div>

                <!-- Complete Registration Button -->
                <button id="completeRegistration" type="button" disabled
                    class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    <span id="completeText">✓ Selesaikan Registrasi</span>
                    <span id="completeLoading" class="hidden">Memproses...</span>
                </button>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-sm">Sesi akan berakhir dalam 15 menit</p>
        </div>
    </div>

    <script src="{{ asset('js/registration-face-scan.js') }}" type="module"></script>
</body>
</html>
