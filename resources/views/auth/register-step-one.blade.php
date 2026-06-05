<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi - Step 1 | FOTATO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- face-api.js library -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        .grain-overlay {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.08;
            mix-blend-mode: overlay;
            pointer-events: none;
        }

        /* Prevent browser autofill styling from breaking the dark glassmorphic design */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff !important;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #13072c !important;
        }

        /* 3D Card Flip styles */
        .flip-card-inner {
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .flip-card-flipped {
            transform: rotateY(180deg);
        }

        /* Ensure correct pointer events when flipped */
        .flip-card-inner .flip-card-front {
            pointer-events: auto;
        }
        .flip-card-inner .flip-card-back {
            pointer-events: none;
        }
        
        .flip-card-flipped .flip-card-front {
            pointer-events: none;
        }
        .flip-card-flipped .flip-card-back {
            pointer-events: auto;
        }

        /* Gray lock transition for inputs */
        .form-locked input {
            background-color: rgba(31, 41, 55, 0.25) !important;
            border-color: rgba(75, 85, 99, 0.15) !important;
            color: #6b7280 !important;
            cursor: not-allowed;
            pointer-events: none;
        }
        .form-locked label {
            color: rgba(156, 163, 175, 0.3) !important;
        }
        .form-locked .absolute {
            color: rgba(156, 163, 175, 0.2) !important;
        }
    </style>
</head>
<body class="bg-[#0c0517] text-white font-sans min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden selection:bg-purple-500/20 selection:text-white">
    
    <!-- Background Glows -->
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-purple-900/10 blur-[130px] rounded-full pointer-events-none z-0"></div>
    <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-indigo-950/15 blur-[130px] rounded-full pointer-events-none z-0"></div>

    <div class="w-full max-w-6xl z-10 my-8">
        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 text-xs md:text-sm text-gray-400 font-medium mb-6 px-1">
            <a href="/" class="hover:text-white transition-colors">Home</a>
            <span class="text-xs text-purple-300/40">
                <svg class="w-2.5 h-2.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </span>
            <span class="text-white">Sign up</span>
        </div>

        <!-- Main Card Container -->
        <div class="border border-purple-500/10 bg-[#0d061a]/75 backdrop-blur-xl rounded-[28px] p-4 md:p-6 lg:p-8 shadow-2xl shadow-black/40">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16">
                
                <!-- Left Sign Up Form -->
                <div class="lg:col-span-6 flex flex-col justify-center py-8 px-6 md:px-12 lg:pl-16 lg:pr-8">
                    <div class="w-full max-w-[360px] mx-auto">
                        
                        <!-- Step Indicator -->
                        <div class="mb-8 text-center flex flex-col items-center">
                            <div class="flex items-center justify-center gap-3 mb-2">
                                <div class="flex items-center">
                                    <div id="step-1-circle" class="w-7 h-7 bg-gradient-to-tr from-[#9d7ef2] to-[#7a4be7] text-black rounded-full flex items-center justify-center font-bold text-xs shadow-md shadow-purple-500/10 transition-all duration-500">1</div>
                                    <span id="step-1-text" class="ml-2 text-white text-xs font-semibold transition-all duration-500">Informasi Dasar</span>
                                </div>
                                <div class="w-8 h-[1px] bg-purple-500/20"></div>
                                <div class="flex items-center">
                                    <div id="step-2-circle" class="w-7 h-7 bg-[#13072c]/40 border border-purple-500/20 text-purple-300/40 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-500">2</div>
                                    <span id="step-2-text" class="ml-2 text-purple-300/40 text-xs font-semibold font-sans transition-all duration-500">Scan Wajah</span>
                                </div>
                            </div>
                        </div>

                        <h2 class="text-3xl md:text-[38px] font-bold font-display text-white mb-2.5 tracking-tight text-center">Registrasi</h2>
                        <p class="text-purple-300/40 text-sm mb-10 font-sans text-center">Silakan isi informasi dasar Anda</p>

                        <form id="registerForm" class="space-y-5 transition-all duration-500">
                            @csrf

                            <!-- Hidden Role input (signup is for customers only) -->
                            <input type="hidden" id="role" name="role" value="customer">

                            <!-- Name Input -->
                            <div>
                                <label for="name" class="block text-gray-300 text-xs font-semibold uppercase tracking-wider mb-2 font-sans transition-all duration-500">Nama Lengkap</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-purple-300/50 transition-all duration-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                    </span>
                                    <input 
                                        id="name" 
                                        type="text" 
                                        name="name" 
                                        class="w-full bg-[#13072c]/40 border border-purple-500/20 text-white rounded-xl py-3.5 pl-12 pr-4 text-sm font-sans placeholder:text-gray-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all duration-500" 
                                        placeholder="Masukkan nama lengkap Anda"
                                        required 
                                        autofocus
                                    >
                                </div>
                                <p id="name-error" class="text-red-400 text-xs mt-1.5 font-sans hidden"></p>
                            </div>

                            <!-- Email Input -->
                            <div>
                                <label for="email" class="block text-gray-300 text-xs font-semibold uppercase tracking-wider mb-2 font-sans transition-all duration-500">Email</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-purple-300/50 transition-all duration-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                        </svg>
                                    </span>
                                    <input 
                                        id="email" 
                                        type="email" 
                                        name="email" 
                                        class="w-full bg-[#13072c]/40 border border-purple-500/20 text-white rounded-xl py-3.5 pl-12 pr-4 text-sm font-sans placeholder:text-gray-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all duration-500" 
                                        placeholder="email@example.com"
                                        required
                                    >
                                </div>
                                <p id="email-error" class="text-red-400 text-xs mt-1.5 font-sans hidden"></p>
                            </div>

                            <!-- Password Input -->
                            <div>
                                <label for="password" class="block text-gray-300 text-xs font-semibold uppercase tracking-wider mb-2 font-sans transition-all duration-500">Password</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-purple-300/50 transition-all duration-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                    </span>
                                    <input 
                                        id="password" 
                                        type="password" 
                                        name="password" 
                                        class="w-full bg-[#13072c]/40 border border-purple-500/20 text-white rounded-xl py-3.5 pl-12 pr-4 text-sm font-sans placeholder:text-gray-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all duration-500" 
                                        placeholder="Minimal 8 karakter"
                                        required
                                    >
                                </div>
                                <p id="password-error" class="text-red-400 text-xs mt-1.5 font-sans hidden"></p>
                            </div>

                            <!-- Password Confirmation Input -->
                            <div>
                                <label for="password_confirmation" class="block text-gray-300 text-xs font-semibold uppercase tracking-wider mb-2 font-sans transition-all duration-500">Konfirmasi Password</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-4 text-purple-300/50 transition-all duration-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                    </span>
                                    <input 
                                        id="password_confirmation" 
                                        type="password" 
                                        name="password_confirmation" 
                                        class="w-full bg-[#13072c]/40 border border-purple-500/20 text-white rounded-xl py-3.5 pl-12 pr-4 text-sm font-sans placeholder:text-gray-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all duration-500" 
                                        placeholder="Ulangi password Anda"
                                        required
                                    >
                                </div>
                                <p id="password_confirmation-error" class="text-red-400 text-xs mt-1.5 font-sans hidden"></p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-center pt-4">
                                <button 
                                    type="submit" 
                                    id="submitBtn"
                                    class="w-48 bg-gradient-to-r from-[#9d7ef2] to-[#7a4be7] hover:from-[#aa8df5] hover:to-[#885bec] text-black font-display font-bold py-3.5 rounded-full transition-all hover:scale-[1.01] hover:shadow-lg hover:shadow-purple-500/15 text-center flex items-center justify-center cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span id="submitText">Lanjutkan</span>
                                    <span id="loadingText" class="hidden">Memproses...</span>
                                </button>
                            </div>
                        </form>

                        <!-- Footer -->
                        <div class="text-center mt-8 text-xs text-purple-300/40 font-sans">
                            Sudah punya akun? <a href="{{ route('login') }}" class="text-[#FFE600] font-semibold hover:underline decoration-[#FFE600]/30 underline-offset-4 ml-1">Masuk</a>
                        </div>
                    </div>
                </div>

                <!-- Right Banner & Scanner Container (With 3D Flip) -->
                <div class="lg:col-span-6 relative [perspective:1000px] min-h-[350px] lg:min-h-[480px] w-full" id="flip-container">
                    <div class="relative w-full h-full flip-card-inner" id="flip-card">
                        
                        <!-- Front Side: Banner Poster -->
                        <div class="absolute inset-0 w-full h-full [backface-visibility:hidden] rounded-[20px] overflow-hidden bg-gradient-to-tr from-[#5b21b6] via-[#7c3aed] to-[#3b82f6] flex flex-col justify-between p-8 md:p-10 lg:p-12 shadow-lg shadow-purple-500/5 bg-cover bg-center flip-card-front" style="background-image: url('{{ asset('images/signin.png') }}');">
                            <!-- Grain texture overlay -->
                            <div class="absolute inset-0 grain-overlay"></div>
                            
                            <!-- Content (Logo) -->
                            <div class="relative z-10">
                                <span class="text-white text-2xl font-black tracking-wider font-display">FOTATO</span>
                            </div>

                            <!-- Content (Slogans) -->
                            <div class="relative z-10 mt-auto">
                                <h1 class="text-white text-3xl md:text-[42px] font-bold font-display leading-[1.15] mb-4 tracking-tight">
                                    Where <span class="text-[#FFE600]">Concert</span> Memories <span class="text-[#FFE600]">Become</span> Yours.
                                </h1>
                                <p class="text-purple-100/80 text-sm font-sans font-light leading-relaxed">
                                    Miliki Momen Sebelum Orang Lain Memilikinya.
                                </p>
                            </div>
                        </div>

                        <!-- Back Side: Face Scanner (Reversely flipped) -->
                        <div class="absolute inset-0 w-full h-full [backface-visibility:hidden] [transform:rotateY(180deg)] rounded-[20px] overflow-hidden bg-[#0c0617] border border-purple-500/10 flex flex-col justify-between p-6 md:p-8 shadow-lg shadow-purple-500/5 flip-card-back">
                            <div class="w-full flex flex-col h-full justify-between">
                                
                                <div class="text-center mb-2">
                                    <h3 class="text-white font-bold font-display text-xl md:text-2xl mb-1">Scan Wajah</h3>
                                    <p class="text-purple-300/40 text-xs font-sans">Scan wajah Anda untuk menyelesaikan registrasi</p>
                                </div>

                                <!-- Central Area: Placeholder / Video Feed / Image Preview -->
                                <div class="flex-1 flex flex-col justify-center items-center relative min-h-[220px] rounded-2xl bg-[#13072c]/20 border border-purple-500/10 overflow-hidden p-2 my-2">
                                    
                                    <!-- Loading Models Indicator -->
                                    <div id="loadingModels" class="absolute z-20 flex flex-col items-center">
                                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#7c3aed] mb-2"></div>
                                        <p class="text-purple-300/40 text-xs">Memuat model AI...</p>
                                    </div>

                                    <!-- Default Silhouette Placeholder (shown initially when models loaded) -->
                                    <div id="scannerPlaceholder" class="hidden flex flex-col items-center justify-center p-6 text-center z-10">
                                        <div class="w-16 h-16 rounded-full bg-purple-500/5 flex items-center justify-center border border-purple-500/20 text-purple-300/50 mb-3">
                                            <!-- Silhouette SVG -->
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                        </div>
                                        <p class="text-purple-300/50 text-xs font-sans">Kamera belum diaktifkan</p>
                                    </div>

                                    <!-- Video Feed -->
                                    <video id="video" autoplay muted playsinline class="hidden w-full h-full object-cover rounded-xl z-10"></video>
                                    <canvas id="canvas" width="320" height="240" class="hidden"></canvas>

                                    <!-- Image Preview -->
                                    <img id="preview" class="hidden w-full h-full object-cover rounded-xl z-10 max-h-[220px]">
                                </div>

                                <!-- Status Messages -->
                                <div id="statusMessage" class="hidden rounded-xl p-3 text-[10px] border my-2"></div>

                                <!-- Action Buttons Area (Placed lower as requested) -->
                                <div id="faceScanOptions" class="hidden mt-4 mb-4">
                                    <!-- Initial Buttons Layout -->
                                    <div id="initialControls" class="flex flex-col gap-3 pt-2">
                                        <button id="startCamera" type="button" class="w-full py-3 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-400 hover:to-indigo-500 text-black font-semibold text-xs rounded-full transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                            Buka Kamera
                                        </button>
                                        
                                        <label for="uploadFace" class="w-full py-3 border border-purple-500/30 bg-purple-500/5 hover:bg-purple-500/10 text-purple-200 hover:text-white font-semibold text-xs rounded-full transition-all text-center cursor-pointer block">
                                            Upload Foto dari Galeri
                                        </label>
                                        <input type="file" id="uploadFace" accept="image/jpeg,image/png,image/webp" class="hidden">
                                    </div>

                                    <!-- Compact Controls (Shown when camera or upload is active) -->
                                    <div id="activeControls" class="hidden flex gap-3 pt-2">
                                        <button id="reopenCamera" type="button" class="flex-1 py-2 bg-gradient-to-r from-purple-500/90 to-indigo-600/90 hover:from-purple-400 hover:to-indigo-500 text-black font-semibold text-xs rounded-full transition-all text-center cursor-pointer">
                                            Ulangi Kamera
                                        </button>
                                        
                                        <label for="uploadFaceActive" class="flex-1 py-2 border border-purple-500/30 bg-purple-500/5 hover:bg-purple-500/10 text-purple-200 hover:text-white font-semibold text-xs rounded-full transition-all text-center cursor-pointer block">
                                            Ganti Foto
                                        </label>
                                        <input type="file" id="uploadFaceActive" accept="image/jpeg,image/png,image/webp" class="hidden">
                                    </div>
                                </div>

                                <!-- Complete Registration Button -->
                                <button id="completeRegistration" type="button" disabled class="w-full py-3 bg-gradient-to-r from-green-400 to-teal-500 hover:from-green-300 hover:to-teal-400 text-black font-display font-bold rounded-full transition-all hover:scale-[1.01] hover:shadow-lg disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center">
                                    <span id="completeText">✓ Selesaikan Registrasi</span>
                                    <span id="completeLoading" class="hidden">Memproses...</span>
                                </button>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            // State variables
            let sessionToken = null;
            let faceEmbedding = null;
            let videoStream = null;
            
            // Elements - Step 1 Form
            const form = document.getElementById('registerForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingText = document.getElementById('loadingText');
            
            // Elements - Step Indicator
            const step1Circle = document.getElementById('step-1-circle');
            const step2Circle = document.getElementById('step-2-circle');
            
            // Elements - Step 2 Face Scan
            const startCameraBtn = document.getElementById('startCamera');
            const uploadFaceInput = document.getElementById('uploadFace');
            const uploadFaceActiveInput = document.getElementById('uploadFaceActive');
            const completeRegistrationBtn = document.getElementById('completeRegistration');
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const preview = document.getElementById('preview');
            const loadingModels = document.getElementById('loadingModels');
            const faceScanOptions = document.getElementById('faceScanOptions');
            const scannerPlaceholder = document.getElementById('scannerPlaceholder');
            const initialControls = document.getElementById('initialControls');
            const activeControls = document.getElementById('activeControls');
            const statusDiv = document.getElementById('statusMessage');
            const completeText = document.getElementById('completeText');
            const completeLoading = document.getElementById('completeLoading');
            const flipCard = document.getElementById('flip-card');
            
            // ─── Initialize Step 2 In Background ───────────────────────
            let modelsLoaded = false;
            async function loadFaceApiModels() {
                const MODEL_URL = '/models';
                try {
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                    ]);
                    modelsLoaded = true;
                    console.log('Face-api models loaded successfully in background');
                    
                    // If card is already flipped, show options immediately
                    if (loadingModels.classList.contains('active-loading')) {
                        showFaceScanOptions();
                    }
                } catch (error) {
                    console.error('Failed to load face-api models:', error);
                    showStatus('error', 'Gagal memuat model AI. Silakan refresh halaman.');
                }
            }
            
            loadFaceApiModels(); // Trigger background load
            
            function showFaceScanOptions() {
                loadingModels.classList.add('hidden');
                loadingModels.classList.remove('active-loading');
                scannerPlaceholder.classList.remove('hidden');
                faceScanOptions.classList.remove('hidden');
            }
            
            // Client-side validation helpers
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            function validatePassword(password) {
                return password.length >= 8;
            }

            function showError(fieldName, message) {
                const errorElement = document.getElementById(`${fieldName}-error`);
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                }
            }

            function hideError(fieldName) {
                const errorElement = document.getElementById(`${fieldName}-error`);
                if (errorElement) {
                    errorElement.classList.add('hidden');
                }
            }

            function hideAllErrors() {
                ['name', 'email', 'password', 'password_confirmation'].forEach(hideError);
            }

            // Real-time validation
            document.getElementById('email').addEventListener('blur', function() {
                if (this.value && !validateEmail(this.value)) {
                    showError('email', 'Format email tidak valid.');
                } else {
                    hideError('email');
                }
            });

            document.getElementById('password').addEventListener('blur', function() {
                if (this.value && !validatePassword(this.value)) {
                    showError('password', 'Password minimal 8 karakter.');
                } else {
                    hideError('password');
                }
            });

            document.getElementById('password_confirmation').addEventListener('blur', function() {
                const password = document.getElementById('password').value;
                if (this.value && this.value !== password) {
                    showError('password_confirmation', 'Konfirmasi password tidak cocok.');
                } else {
                    hideError('password_confirmation');
                }
            });

            // ─── Step 1 Form Submission ────────────────────────────────
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                hideAllErrors();

                const formData = {
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    password_confirmation: document.getElementById('password_confirmation').value,
                    role: document.getElementById('role').value,
                };

                let hasError = false;

                if (!formData.name) {
                    showError('name', 'Nama wajib diisi.');
                    hasError = true;
                }

                if (!formData.email) {
                    showError('email', 'Email wajib diisi.');
                    hasError = true;
                } else if (!validateEmail(formData.email)) {
                    showError('email', 'Format email tidak valid.');
                    hasError = true;
                }

                if (!formData.password) {
                    showError('password', 'Password wajib diisi.');
                    hasError = true;
                } else if (!validatePassword(formData.password)) {
                    showError('password', 'Password minimal 8 karakter.');
                    hasError = true;
                }

                if (formData.password !== formData.password_confirmation) {
                    showError('password_confirmation', 'Konfirmasi password tidak cocok.');
                    hasError = true;
                }

                if (hasError) return;

                // Disable button and show loading
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                loadingText.classList.remove('hidden');

                try {
                    const response = await fetch('{{ route('register.step-one') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        sessionToken = data.session_token;
                        
                        // Disable Step 1 form inputs & lock them with transition
                        form.classList.add('form-locked');
                        Array.from(form.elements).forEach(el => el.disabled = true);
                        
                        // Change submit button state to 'Tersimpan'
                        submitBtn.innerHTML = '✓ Tersimpan';
                        submitBtn.disabled = true;
                        submitBtn.classList.remove('bg-gradient-to-r', 'from-[#9d7ef2]', 'to-[#7a4be7]', 'text-black', 'hover:scale-[1.01]', 'hover:shadow-lg');
                        submitBtn.classList.add('bg-gray-800/30', 'border', 'border-gray-700/20', 'text-gray-500', 'cursor-not-allowed');

                        // Update Step Indicator circles
                        step1Circle.innerHTML = `
                            <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        `;
                        step2Circle.classList.remove('bg-[#13072c]/40', 'text-purple-300/40', 'border-purple-500/20');
                        step2Circle.classList.add('bg-gradient-to-tr', 'from-[#9d7ef2]', 'to-[#7a4be7]', 'text-black', 'shadow-md', 'shadow-purple-500/10');
                        document.getElementById('step-2-text').classList.remove('text-purple-300/40');
                        document.getElementById('step-2-text').classList.add('text-white');
                        
                        // Magic Flip Transition
                        flipCard.classList.add('flip-card-flipped');
                        
                        // Handle face scan options display
                        if (modelsLoaded) {
                            showFaceScanOptions();
                        } else {
                            loadingModels.classList.add('active-loading');
                        }
                    } else {
                        // Display validation errors
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                showError(field, data.errors[field][0]);
                            });
                        } else if (data.message) {
                            alert(data.message);
                        }

                        // Re-enable button
                        submitBtn.disabled = false;
                        submitText.classList.remove('hidden');
                        loadingText.classList.add('hidden');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    loadingText.classList.add('hidden');
                }
            });

            // ─── Step 2 Camera & Upload Face Detection ──────────────────
            startCameraBtn.addEventListener('click', handleCameraCapture);
            uploadFaceInput.addEventListener('change', handleFileUpload);
            uploadFaceActiveInput.addEventListener('change', handleFileUpload);
            completeRegistrationBtn.addEventListener('click', handleCompleteRegistration);

            // Re-open camera button
            document.getElementById('reopenCamera').addEventListener('click', async () => {
                faceEmbedding = null;
                completeRegistrationBtn.disabled = true;
                
                // Clear file preview
                preview.classList.add('hidden');
                preview.src = '';
                
                stopCamera();
                await handleCameraCapture();
            });

            async function handleCameraCapture() {
                // Clear image preview
                preview.classList.add('hidden');
                preview.src = '';
                
                try {
                    videoStream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            width: { ideal: 640 },
                            height: { ideal: 480 },
                            facingMode: 'user'
                        } 
                    });
                    
                    video.srcObject = videoStream;
                    video.classList.remove('hidden');
                    scannerPlaceholder.classList.add('hidden');
                    
                    // Toggle Controls (shrink and put side-by-side)
                    initialControls.classList.add('hidden');
                    activeControls.classList.remove('hidden');
                    
                    startCameraBtn.textContent = '⏳ Bersiap...';
                    startCameraBtn.disabled = true;
                    
                    showStatus('info', 'Posisikan wajah Anda di tengah kamera...');
                    
                    await new Promise(resolve => {
                        video.onloadedmetadata = () => {
                            video.play();
                            resolve();
                        };
                    });
                    
                    for (let i = 3; i > 0; i--) {
                        showStatus('info', `Foto akan diambil dalam ${i}...`);
                        await sleep(1000);
                    }
                    
                    const context = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    stopCamera();
                    
                    // Show capture canvas image in preview box
                    preview.src = canvas.toDataURL('image/jpeg');
                    preview.classList.remove('hidden');
                    
                    showStatus('info', 'Mendeteksi wajah...');
                    await extractFaceEmbedding(canvas);
                    
                } catch (error) {
                    console.error('Camera capture error:', error);
                    if (error.name === 'NotAllowedError') {
                        showStatus('error', 'Izin kamera ditolak. Gunakan opsi upload foto.');
                    } else if (error.name === 'NotFoundError') {
                        showStatus('error', 'Kamera tidak ditemukan. Gunakan opsi upload foto.');
                    } else {
                        showStatus('error', 'Gagal mengakses kamera. Gunakan opsi upload foto.');
                    }
                    
                    // Revert controls
                    initialControls.classList.remove('hidden');
                    activeControls.classList.add('hidden');
                    
                    startCameraBtn.textContent = 'Buka Kamera';
                    startCameraBtn.disabled = false;
                }
            }

            async function handleFileUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showStatus('error', 'Format file tidak valid. Gunakan JPEG, PNG, atau WebP.');
                    return;
                }
                
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    showStatus('error', 'Ukuran file terlalu besar. Maksimal 5MB.');
                    return;
                }
                
                // Stop camera stream if active
                stopCamera();
                scannerPlaceholder.classList.add('hidden');
                
                // Toggle Controls (shrink and put side-by-side)
                initialControls.classList.add('hidden');
                activeControls.classList.remove('hidden');
                
                try {
                    const reader = new FileReader();
                    reader.onload = async (e) => {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        showStatus('info', 'Mendeteksi wajah...');
                        
                        const img = new Image();
                        img.onload = async () => {
                            await extractFaceEmbedding(img);
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } catch (error) {
                    console.error('File upload error:', error);
                    showStatus('error', 'Gagal memproses foto. Silakan coba lagi.');
                }
            }

            async function extractFaceEmbedding(source) {
                try {
                    const detection = await faceapi
                        .detectSingleFace(source, new faceapi.TinyFaceDetectorOptions())
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    
                    if (!detection) {
                        showStatus('error', 'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.');
                        return;
                    }
                    
                    faceEmbedding = Array.from(detection.descriptor);
                    
                    if (faceEmbedding.length !== 128 || !faceEmbedding.every(val => typeof val === 'number' && !isNaN(val))) {
                        showStatus('error', 'Data wajah tidak valid. Silakan coba lagi.');
                        faceEmbedding = null;
                        return;
                    }
                    
                    showStatus('success', '✓ Wajah terdeteksi! Silakan klik tombol di bawah untuk menyelesaikan registrasi.');
                    completeRegistrationBtn.disabled = false;
                } catch (error) {
                    console.error('Face detection error:', error);
                    showStatus('error', 'Gagal mendeteksi wajah. Silakan coba lagi.');
                    faceEmbedding = null;
                }
            }

            async function handleCompleteRegistration() {
                if (!faceEmbedding || !sessionToken) {
                    showStatus('error', 'Sesi registrasi tidak valid. Silakan ulangi.');
                    return;
                }
                
                completeRegistrationBtn.disabled = true;
                completeText.classList.add('hidden');
                completeLoading.classList.remove('hidden');
                
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const response = await fetch('/register/step-two', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            session_token: sessionToken,
                            face_embedding: faceEmbedding
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        showStatus('success', data.message || 'Registrasi berhasil!');
                        faceEmbedding = null;
                        setTimeout(() => {
                            window.location.href = data.redirect || '/dashboard';
                        }, 1500);
                    } else if (response.status === 419) {
                        showStatus('error', data.message || 'Sesi telah berakhir. Mulai dari awal.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showStatus('error', data.message || 'Terjadi kesalahan. Silakan coba lagi.');
                        completeRegistrationBtn.disabled = false;
                        completeText.classList.remove('hidden');
                        completeLoading.classList.add('hidden');
                    }
                } catch (error) {
                    console.error('Registration completion error:', error);
                    showStatus('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                    completeRegistrationBtn.disabled = false;
                    completeText.classList.remove('hidden');
                    completeLoading.classList.add('hidden');
                }
            }

            function stopCamera() {
                if (videoStream) {
                    videoStream.getTracks().forEach(track => track.stop());
                    videoStream = null;
                }
                video.classList.add('hidden');
                video.srcObject = null;
                startCameraBtn.textContent = 'Buka Kamera';
                startCameraBtn.disabled = false;
            }

            function showStatus(type, message) {
                statusDiv.classList.remove('hidden', 'bg-blue-500/20', 'border-purple-500/20', 'text-blue-200',
                                            'bg-green-500/10', 'border-green-500/20', 'text-green-300',
                                            'bg-red-500/10', 'border-red-500/20', 'text-red-400');
                
                if (type === 'info') {
                    statusDiv.classList.add('bg-blue-500/10', 'border-purple-500/20', 'text-blue-300', 'border');
                } else if (type === 'success') {
                    statusDiv.classList.add('bg-green-500/10', 'border-green-500/20', 'text-green-300', 'border');
                } else if (type === 'error') {
                    statusDiv.classList.add('bg-red-500/10', 'border-red-500/20', 'text-red-400', 'border');
                }
                
                statusDiv.textContent = message;
                statusDiv.classList.remove('hidden');
            }

            function sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            window.addEventListener('beforeunload', () => {
                stopCamera();
                faceEmbedding = null;
            });
        });
    </script>
</body>
</html>
