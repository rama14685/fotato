<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Wajah | Fotlist</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); min-height: 100vh; color: #e2e8f0; }

        /* Animated background particles */
        body::before { content:''; position:fixed; inset:0; background: radial-gradient(ellipse at 20% 50%, rgba(99,102,241,.15) 0%, transparent 60%), radial-gradient(ellipse at 80% 20%, rgba(139,92,246,.12) 0%, transparent 60%); pointer-events:none; }

        /* Glass card */
        .glass { background: rgba(255,255,255,0.06); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; }

        /* Webcam container */
        #camWrap { position: relative; width: 100%; max-width: 540px; margin: 0 auto; border-radius: 16px; overflow: hidden; background: #0a0a1a; aspect-ratio: 4/3; }
        #video { width:100%; height:100%; object-fit:cover; display:block; }
        #overlay { position:absolute; inset:0; pointer-events:none; }

        /* Face detection box drawn on canvas */
        #detectionCanvas { position:absolute; inset:0; width:100%; height:100%; }

        /* Status ring around video */
        .face-ring { position:absolute; inset:0; border-radius:16px; border:3px solid transparent; transition: border-color .4s, box-shadow .4s; }
        .face-ring.no-face  { border-color: rgba(239,68,68,.5); box-shadow: inset 0 0 20px rgba(239,68,68,.1); }
        .face-ring.has-face { border-color: rgba(16,185,129,.7); box-shadow: inset 0 0 20px rgba(16,185,129,.15); }

        /* Status overlay text */
        #statusMsg { position:absolute; bottom:14px; left:50%; transform:translateX(-50%); white-space:nowrap; font-size:.8rem; font-weight:700; letter-spacing:.05em; padding:6px 18px; border-radius:20px; backdrop-filter:blur(10px); transition: all .3s; }
        .status-searching { background:rgba(239,68,68,.85); color:#fff; }
        .status-found     { background:rgba(16,185,129,.85); color:#fff; }
        .status-loading   { background:rgba(99,102,241,.85); color:#fff; }

        /* Guide circle */
        .guide-circle { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:200px; height:240px; border-radius:50%; border:2px dashed rgba(255,255,255,.25); pointer-events:none; }

        /* Buttons */
        .btn-capture { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; border-radius:14px; padding:14px 36px; font-size:1rem; font-weight:700; cursor:pointer; transition:all .2s; box-shadow:0 4px 20px rgba(99,102,241,.5); }
        .btn-capture:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 8px 28px rgba(99,102,241,.6); }
        .btn-capture:disabled { opacity:.4; cursor:not-allowed; }
        .btn-ghost { background:rgba(255,255,255,.08); color:#cbd5e1; border:1px solid rgba(255,255,255,.15); border-radius:12px; padding:12px 24px; font-size:.9rem; font-weight:600; cursor:pointer; transition:all .2s; }
        .btn-ghost:hover { background:rgba(255,255,255,.14); }

        /* Animated spinner */
        @keyframes spin { to { transform:rotate(360deg) } }
        .spinner { animation: spin 1s linear infinite; }

        /* Step indicator */
        .step { display:flex; align-items:center; gap:12px; padding:14px; border-radius:12px; transition: background .3s; }
        .step.active { background:rgba(99,102,241,.2); }
        .step-num { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:800; flex-shrink:0; }
        .step-num.done { background:rgba(16,185,129,.3); color:#34d399; }
        .step-num.active-num { background:rgba(99,102,241,.5); color:#a5b4fc; }
        .step-num.pending { background:rgba(255,255,255,.08); color:#64748b; }

        /* Pulse animation on face found */
        @keyframes faceFound { 0%,100%{transform:scale(1)} 50%{transform:scale(1.02)} }
        .face-found-anim { animation: faceFound .6s ease; }
    </style>
</head>
<body>
    {{-- NAV --}}
    <nav style="background:rgba(0,0,0,.3); backdrop-filter:blur(16px); border-bottom:1px solid rgba(255,255,255,.07);">
        <div class="max-w-5xl mx-auto px-5 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📸</span>
                <span class="text-xl font-bold text-white">Fotlist</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-slate-300 text-sm">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-ghost text-sm py-2 px-4">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-5 py-10">
        {{-- Page title --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 text-purple-400 text-sm font-semibold mb-3 px-4 py-2 rounded-full" style="background:rgba(139,92,246,.15); border:1px solid rgba(139,92,246,.3);">
                🔐 Langkah Wajib
            </div>
            <h1 class="text-4xl font-bold text-white mb-3">Daftarkan Wajah Anda</h1>
            <p class="text-slate-400 text-lg max-w-lg mx-auto">
                @if($hasFace)
                    Anda sudah mendaftar sebelumnya. Perbarui data wajah Anda dengan scan ulang.
                @else
                    Scan wajah Anda sekali untuk mengaktifkan pencarian foto otomatis di semua event.
                @endif
            </p>
        </div>

        @if(session('info'))
            <div class="mb-6 p-4 rounded-xl text-center" style="background:rgba(99,102,241,.2); border:1px solid rgba(99,102,241,.4); color:#a5b4fc;">
                {{ session('info') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-5 gap-8">

            {{-- LEFT: Webcam --}}
            <div class="lg:col-span-3 space-y-5">
                <div class="glass p-6">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        📷 <span>Kamera Langsung</span>
                        <span id="liveIndicator" class="ml-auto w-2 h-2 rounded-full bg-red-500"></span>
                    </h2>

                    {{-- Webcam area --}}
                    <div id="camWrap">
                        {{-- Placeholder before camera --}}
                        <div id="camPlaceholder" class="flex flex-col items-center justify-center h-full" style="height:350px;">
                            <div class="text-6xl mb-4">🎥</div>
                            <p class="text-slate-400 text-sm">Klik tombol di bawah untuk mengaktifkan kamera</p>
                        </div>
                        <video id="video" autoplay playsinline style="display:none;"></video>
                        <canvas id="detectionCanvas" style="display:none;"></canvas>
                        <div class="face-ring no-face" id="faceRing" style="display:none;"></div>
                        <div class="guide-circle" id="guideCircle" style="display:none;"></div>
                        <div id="statusMsg" class="status-loading" style="display:none;"></div>
                    </div>

                    {{-- Camera controls --}}
                    <div class="flex gap-3 mt-4 flex-wrap">
                        <button id="startCamBtn" class="btn-capture flex-1">
                            🎥 Aktifkan Kamera
                        </button>
                        <button id="captureBtn" class="btn-capture flex-1" disabled style="background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 4px 20px rgba(16,185,129,.4);">
                            📸 Ambil & Simpan Wajah
                        </button>
                    </div>
                </div>

                {{-- Tips --}}
                <div class="glass p-5">
                    <h3 class="font-semibold text-white mb-3 flex items-center gap-2">💡 Tips agar Akurat</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">✓</span> Pastikan wajah Anda berada di dalam lingkaran panduan</li>
                        <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">✓</span> Pencahayaan cukup dan merata di wajah</li>
                        <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">✓</span> Lepas kacamata gelap / masker yang menutupi wajah</li>
                        <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">✓</span> Hadap kamera secara langsung (frontal)</li>
                        <li class="flex items-start gap-2"><span class="text-yellow-400 mt-0.5">✓</span> Tunggu border berubah <strong style="color:#34d399;">hijau</strong> sebelum klik simpan</li>
                    </ul>
                </div>
            </div>

            {{-- RIGHT: Steps / Status --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="glass p-6">
                    <h3 class="text-white font-bold mb-5">📋 Langkah-langkah</h3>
                    <div class="space-y-2">
                        <div class="step active" id="step1">
                            <div class="step-num active-num" id="stepNum1">1</div>
                            <div>
                                <p class="font-semibold text-white text-sm">Aktifkan Kamera</p>
                                <p class="text-slate-400 text-xs mt-1" id="step1Desc">Izinkan akses kamera di browser Anda</p>
                            </div>
                        </div>
                        <div class="step" id="step2">
                            <div class="step-num pending" id="stepNum2">2</div>
                            <div>
                                <p class="font-semibold text-slate-400 text-sm">Posisikan Wajah</p>
                                <p class="text-slate-500 text-xs mt-1">Tunggu border berubah hijau</p>
                            </div>
                        </div>
                        <div class="step" id="step3">
                            <div class="step-num pending" id="stepNum3">3</div>
                            <div>
                                <p class="font-semibold text-slate-400 text-sm">Simpan Data Wajah</p>
                                <p class="text-slate-500 text-xs mt-1">Klik "Ambil & Simpan Wajah"</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status card --}}
                <div class="glass p-6" id="statusCard">
                    <div id="statusContent">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:rgba(99,102,241,.3);">👁️</div>
                            <div>
                                <p class="font-bold text-white text-sm">Deteksi Wajah</p>
                                <p class="text-xs text-slate-400">Status real-time</p>
                            </div>
                        </div>
                        <div id="detectionStatus" class="text-sm text-slate-400">Kamera belum aktif</div>
                    </div>
                </div>

                {{-- Loading overlay (shown during save) --}}
                <div id="savingCard" class="glass p-6 hidden">
                    <div class="text-center">
                        <svg class="spinner w-10 h-10 text-purple-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-white font-semibold mb-1">Menyimpan Data Wajah…</p>
                        <p class="text-slate-400 text-xs">Harap tunggu sebentar</p>
                    </div>
                </div>

                {{-- Success card --}}
                <div id="successCard" class="glass p-6 hidden text-center">
                    <div class="text-5xl mb-3">🎉</div>
                    <p class="font-bold text-green-400 text-lg mb-1">Berhasil!</p>
                    <p class="text-slate-400 text-sm mb-4">Data wajah tersimpan. Mengarahkan ke dashboard…</p>
                    <div class="progress-bar-wrap" style="background:rgba(255,255,255,.08); border-radius:4px; height:4px;">
                        <div id="successBar" class="progress-bar-fill" style="width:0%; background:linear-gradient(90deg,#10b981,#34d399); height:100%; border-radius:4px; transition:width 2s linear;"></div>
                    </div>
                </div>

                @if($hasFace)
                <div class="glass p-4 text-center">
                    <p class="text-sm text-slate-400 mb-2">Sudah punya wajah terdaftar?</p>
                    <a href="{{ route('buyer.dashboard') }}" class="btn-ghost text-sm py-2 px-5">Langsung ke Dashboard →</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
    // ════════════════════════════════════════════════════════════════════════
    // CONFIG
    // ════════════════════════════════════════════════════════════════════════
    const STORE_URL  = @json(route('buyer.register-face.store'));
    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
    const MODEL_URL  = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

    // DOM
    const video          = document.getElementById('video');
    const canvas         = document.getElementById('detectionCanvas');
    const ctx            = canvas.getContext('2d');
    const faceRing       = document.getElementById('faceRing');
    const guideCircle    = document.getElementById('guideCircle');
    const statusMsgEl    = document.getElementById('statusMsg');
    const startCamBtn    = document.getElementById('startCamBtn');
    const captureBtn     = document.getElementById('captureBtn');
    const detectionStatus= document.getElementById('detectionStatus');
    const liveIndicator  = document.getElementById('liveIndicator');
    const savingCard     = document.getElementById('savingCard');
    const successCard    = document.getElementById('successCard');
    const statusCard     = document.getElementById('statusCard');
    const camPlaceholder = document.getElementById('camPlaceholder');

    let modelsLoaded  = false;
    let detectionLoop = null;
    let currentFaceDescriptor = null;
    let cameraActive  = false;

    // ════════════════════════════════════════════════════════════════════════
    // LOAD MODELS
    // ════════════════════════════════════════════════════════════════════════
    async function loadModels() {
        setDetectionStatus('⏳ Memuat model AI…', 'text-purple-400');
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
            modelsLoaded = true;
            setDetectionStatus('✅ Model siap. Aktifkan kamera.', 'text-green-400');
        } catch (e) {
            setDetectionStatus('❌ Gagal memuat model AI: ' + e.message, 'text-red-400');
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // START CAMERA
    // ════════════════════════════════════════════════════════════════════════
    startCamBtn.addEventListener('click', async () => {
        if (!modelsLoaded) {
            setDetectionStatus('Masih memuat model AI, harap tunggu…', 'text-yellow-400');
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { width:640, height:480, facingMode:'user' } });
            video.srcObject = stream;
            video.style.display = 'block';
            camPlaceholder.style.display = 'none';
            canvas.style.display = 'block';
            faceRing.style.display = 'block';
            guideCircle.style.display = 'block';
            statusMsgEl.style.display = 'block';

            liveIndicator.style.background = '#10b981';
            liveIndicator.style.animation  = 'spin 2s linear infinite';
            cameraActive = true;

            startCamBtn.textContent = '🔄 Kamera Aktif';
            startCamBtn.disabled = true;

            // Update step 1
            markStepDone(1);
            activateStep(2);

            video.addEventListener('loadedmetadata', () => {
                canvas.width  = video.videoWidth  || 640;
                canvas.height = video.videoHeight || 480;
                startDetectionLoop();
            });

        } catch (err) {
            setDetectionStatus('❌ Kamera tidak dapat diakses: ' + err.message, 'text-red-400');
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // DETECTION LOOP
    // ════════════════════════════════════════════════════════════════════════
    function startDetectionLoop() {
        let lastDescriptor = null;

        detectionLoop = setInterval(async () => {
            if (!cameraActive || video.paused || video.ended) return;

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.6 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detection) {
                    const { box } = detection.detection;
                    const scaleX  = canvas.width  / video.videoWidth;
                    const scaleY  = canvas.height / video.videoHeight;

                    // Draw bounding box
                    ctx.strokeStyle = '#10b981';
                    ctx.lineWidth   = 3;
                    ctx.shadowColor = '#10b981';
                    ctx.shadowBlur  = 10;
                    ctx.strokeRect(box.x * scaleX, box.y * scaleY, box.width * scaleX, box.height * scaleY);
                    ctx.shadowBlur  = 0;

                    // Draw corner accents
                    const cx = box.x * scaleX, cy = box.y * scaleY, cw = box.width * scaleX, ch = box.height * scaleY;
                    const accentLen = 20;
                    ctx.strokeStyle = '#34d399';
                    ctx.lineWidth = 5;
                    // TL
                    ctx.beginPath(); ctx.moveTo(cx, cy + accentLen); ctx.lineTo(cx, cy); ctx.lineTo(cx + accentLen, cy); ctx.stroke();
                    // TR
                    ctx.beginPath(); ctx.moveTo(cx + cw - accentLen, cy); ctx.lineTo(cx + cw, cy); ctx.lineTo(cx + cw, cy + accentLen); ctx.stroke();
                    // BL
                    ctx.beginPath(); ctx.moveTo(cx, cy + ch - accentLen); ctx.lineTo(cx, cy + ch); ctx.lineTo(cx + accentLen, cy + ch); ctx.stroke();
                    // BR
                    ctx.beginPath(); ctx.moveTo(cx + cw, cy + ch - accentLen); ctx.lineTo(cx + cw, cy + ch); ctx.lineTo(cx + cw - accentLen, cy + ch); ctx.stroke();

                    currentFaceDescriptor = Array.from(detection.descriptor);
                    faceRing.className    = 'face-ring has-face';
                    statusMsgEl.className = 'status-found';
                    statusMsgEl.textContent = '✅ Wajah Terdeteksi!';
                    captureBtn.disabled   = false;

                    setDetectionStatus('✅ Wajah terdeteksi dengan jelas. Siap untuk disimpan!', 'text-green-400');
                    activateStep(3);

                } else {
                    currentFaceDescriptor = null;
                    faceRing.className    = 'face-ring no-face';
                    statusMsgEl.className = 'status-searching';
                    statusMsgEl.textContent = '🔍 Mencari wajah…';
                    captureBtn.disabled   = true;

                    setDetectionStatus('🔍 Wajah belum terdeteksi. Posisikan wajah di tengah kamera.', 'text-yellow-400');
                }
            } catch (e) {
                // Silent – detection errors happen on empty frames
            }
        }, 300); // Run every 300ms
    }

    // ════════════════════════════════════════════════════════════════════════
    // CAPTURE & SAVE
    // ════════════════════════════════════════════════════════════════════════
    captureBtn.addEventListener('click', async () => {
        if (!currentFaceDescriptor) {
            setDetectionStatus('❌ Tidak ada wajah yang terdeteksi. Coba lagi.', 'text-red-400');
            return;
        }

        // Stop detection loop
        clearInterval(detectionLoop);
        cameraActive = false;
        captureBtn.disabled = true;
        startCamBtn.disabled = true;

        // Show saving state
        statusCard.classList.add('hidden');
        savingCard.classList.remove('hidden');

        try {
            const response = await fetch(STORE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ face_descriptor: currentFaceDescriptor }),
            });

            const data = await response.json();

            if (data.success) {
                savingCard.classList.add('hidden');
                successCard.classList.remove('hidden');
                markStepDone(3);

                // Animate progress bar then redirect
                setTimeout(() => {
                    document.getElementById('successBar').style.width = '100%';
                }, 50);
                setTimeout(() => {
                    window.location.href = data.redirect || @json(route('buyer.dashboard'));
                }, 2200);
            } else {
                throw new Error(data.message || 'Gagal menyimpan data wajah');
            }
        } catch (err) {
            savingCard.classList.add('hidden');
            statusCard.classList.remove('hidden');
            setDetectionStatus('❌ Error: ' + err.message, 'text-red-400');
            captureBtn.disabled = false;
            cameraActive = true;
            startDetectionLoop();
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // STEP HELPERS
    // ════════════════════════════════════════════════════════════════════════
    function markStepDone(num) {
        document.getElementById(`stepNum${num}`).className = 'step-num done';
        document.getElementById(`stepNum${num}`).textContent = '✓';
        document.getElementById(`step${num}`).classList.remove('active');
    }

    function activateStep(num) {
        const stepEl    = document.getElementById(`step${num}`);
        const stepNumEl = document.getElementById(`stepNum${num}`);
        if (!stepEl || !stepNumEl) return;
        stepEl.classList.add('active');
        stepNumEl.className = 'step-num active-num';
        stepNumEl.textContent = num;
    }

    function setDetectionStatus(msg, colorClass = 'text-slate-300') {
        detectionStatus.className = `text-sm ${colorClass}`;
        detectionStatus.textContent = msg;
    }

    // ════════════════════════════════════════════════════════════════════════
    // BOOT
    // ════════════════════════════════════════════════════════════════════════
    loadModels();
    </script>
</body>
</html>
