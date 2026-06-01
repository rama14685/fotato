<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Wajah | Fotlist</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        *{box-sizing:border-box;margin:0;padding:0;}

        body {
            font-family:'Inter',sans-serif;
            background: linear-gradient(135deg,#0a0a0a 0%,#1a1a1a 25%,#2d2d2d 50%,#1a1a1a 75%,#0a0a0a 100%);
            background-attachment:fixed;
            color:#fff;
            min-height:100vh;
        }

        /* NAV */
        .nav-bar {
            background:rgba(255,255,255,0.03);
            backdrop-filter:blur(10px);
            -webkit-backdrop-filter:blur(10px);
            border-bottom:1px solid rgba(255,255,255,0.1);
            padding:16px 0;
            position:sticky;top:0;z-index:100;
        }
        .nav-inner {
            max-width:1100px;margin:0 auto;padding:0 24px;
            display:flex;align-items:center;justify-content:space-between;
        }
        .logo { font-size:1.3rem;font-weight:800;color:#fff;text-decoration:none; }

        /* GLASS */
        .card {
            background:rgba(255,255,255,0.05);
            backdrop-filter:blur(10px);
            -webkit-backdrop-filter:blur(10px);
            border:1px solid rgba(255,255,255,0.1);
            border-radius:14px;
            padding:28px;
        }

        /* GRADIENT TEXT */
        .gt {
            background:linear-gradient(135deg,#ffffff 0%,#b0b0b0 50%,#e0e0e0 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text;
        }

        /* BUTTONS */
        .btn-w {
            background:linear-gradient(135deg,#ffffff,#d0d0d0);
            color:#000;font-weight:700;font-size:.9rem;
            padding:12px 24px;border-radius:8px;border:none;
            cursor:pointer;transition:all .3s;
            display:inline-flex;align-items:center;justify-content:center;gap:8px;
            text-decoration:none;
        }
        .btn-w:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 10px 30px rgba(255,255,255,0.2);}
        .btn-w:disabled{opacity:.3;cursor:not-allowed;transform:none;}

        .btn-g {
            background:rgba(255,255,255,0.08);
            color:#fff;border:1px solid rgba(255,255,255,0.2);
            font-weight:600;font-size:.9rem;
            padding:12px 24px;border-radius:8px;
            cursor:pointer;transition:all .3s;
            display:inline-flex;align-items:center;justify-content:center;gap:8px;
            text-decoration:none;
        }
        .btn-g:hover{background:rgba(255,255,255,0.14);border-color:rgba(255,255,255,0.5);transform:translateY(-2px);}
        .btn-g:disabled{opacity:.3;cursor:not-allowed;}

        /* TWO COLUMN */
        .two-col {
            display:grid;
            grid-template-columns:1fr 380px;
            gap:28px;
            align-items:start;
        }
        @media(max-width:900px){
            .two-col{grid-template-columns:1fr;}
        }

        /* WEBCAM */
        #camWrap {
            position:relative;
            width:100%;aspect-ratio:4/3;
            border-radius:12px;overflow:hidden;
            background:#080808;
            border:1px solid rgba(255,255,255,0.08);
        }
        #video{width:100%;height:100%;object-fit:cover;display:block;}
        #detectionCanvas{position:absolute;inset:0;width:100%;height:100%;}
        .face-ring{position:absolute;inset:0;border-radius:12px;border:2px solid transparent;transition:border-color .4s,box-shadow .4s;pointer-events:none;}
        .face-ring.no-face {border-color:rgba(180,180,180,0.2);}
        .face-ring.has-face{border-color:rgba(255,255,255,0.85);box-shadow:0 0 30px rgba(255,255,255,0.08) inset;}
        #camPlaceholder{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:260px;background:rgba(255,255,255,0.02);}
        .guide-circle{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:180px;height:220px;border-radius:50%;border:2px dashed rgba(255,255,255,0.15);pointer-events:none;display:none;}
        #statusMsg{position:absolute;bottom:14px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:.75rem;font-weight:700;letter-spacing:.06em;padding:5px 18px;border-radius:20px;backdrop-filter:blur(8px);display:none;}
        .s-search{background:rgba(60,60,60,0.9);color:#aaa;}
        .s-found {background:rgba(240,240,240,0.9);color:#000;}

        /* STEP */
        .step-num{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0;border:1.5px solid transparent;}
        .step-num.done   {background:linear-gradient(135deg,#fff,#ccc);color:#000;border-color:transparent;}
        .step-num.active {background:rgba(255,255,255,0.1);color:#fff;border-color:rgba(255,255,255,0.4);}
        .step-num.pending{background:rgba(255,255,255,0.04);color:#444;border-color:rgba(255,255,255,0.08);}
        .step-row{display:flex;align-items:flex-start;gap:14px;padding:12px 10px;border-radius:10px;}
        .step-row.active{background:rgba(255,255,255,0.04);}
        .divider-line{width:1px;height:20px;background:rgba(255,255,255,0.08);margin-left:27px;}

        /* SPINNER */
        @keyframes spin{to{transform:rotate(360deg)}}
        .spinner{animation:spin 1s linear infinite;}

        /* LIVE DOT */
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
        .live{display:inline-block;width:7px;height:7px;border-radius:50%;background:#555;}
        .live.on{background:#fff;animation:blink 1.4s infinite;}

        /* SECTION LABEL */
        .sec-label{font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#555;margin-bottom:18px;}
    </style>
</head>
<body>

<!-- ════ NAV ════ -->
<nav class="nav-bar">
    <div class="nav-inner">
        <span class="logo">📸 Fotlist</span>
        <div style="display:flex;align-items:center;gap:16px;">
            <span style="color:#555;font-size:.85rem;">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button class="btn-g" style="padding:8px 18px;font-size:.8rem;">Keluar</button>
            </form>
        </div>
    </div>
</nav>

<!-- ════ PAGE ════ -->
<div style="max-width:1100px;margin:0 auto;padding:48px 24px 80px;">

    <!-- Heading -->
    <div style="text-align:center;margin-bottom:48px;">
        <p style="font-size:.7rem;letter-spacing:.18em;text-transform:uppercase;color:#444;font-weight:700;margin-bottom:12px;">Langkah Wajib</p>
        <h1 style="font-size:clamp(2rem,5vw,3.2rem);font-weight:800;line-height:1.15;margin-bottom:14px;" class="gt">Daftarkan Wajah Anda</h1>
        <p style="color:#666;font-size:1rem;max-width:420px;margin:0 auto;line-height:1.6;">
            @if($hasFace)
                Perbarui data wajah Anda dengan scan ulang.
            @else
                Scan wajah sekali untuk mengaktifkan pencarian foto otomatis di semua album.
            @endif
        </p>
    </div>

    @if(session('info'))
    <div class="card" style="margin-bottom:24px;text-align:center;padding:14px 20px;border-color:rgba(255,255,255,0.15);">
        <p style="color:#aaa;font-size:.875rem;">ℹ️ {{ session('info') }}</p>
    </div>
    @endif

    <!-- Two column -->
    <div class="two-col">

        <!-- ── LEFT: Webcam ── -->
        <div style="display:flex;flex-direction:column;gap:20px;">

            <!-- Camera card -->
            <div class="card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <h2 style="font-size:1rem;font-weight:700;color:#fff;">Kamera Langsung</h2>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="live" id="liveDot"></span>
                        <span style="font-size:.75rem;color:#444;" id="liveLabel">Tidak aktif</span>
                    </div>
                </div>

                <!-- Webcam area -->
                <div id="camWrap">
                    <div id="camPlaceholder">
                        <div style="font-size:3rem;opacity:.2;margin-bottom:14px;">🎥</div>
                        <p style="color:#3a3a3a;font-size:.85rem;text-align:center;max-width:220px;line-height:1.5;">Klik "Aktifkan Kamera" untuk memulai</p>
                    </div>
                    <video id="video" autoplay playsinline style="display:none;"></video>
                    <canvas id="detectionCanvas" style="display:none;"></canvas>
                    <div class="face-ring no-face" id="faceRing" style="display:none;"></div>
                    <div class="guide-circle" id="guideCircle"></div>
                    <div id="statusMsg" class="s-search"></div>
                </div>

                <!-- Controls -->
                <div style="display:flex;gap:12px;margin-top:18px;flex-wrap:wrap;">
                    <button id="startCamBtn" class="btn-g" style="flex:1;">🎥 Aktifkan Kamera</button>
                    <button id="captureBtn" class="btn-w" style="flex:1;" disabled>📸 Simpan Wajah</button>
                </div>
            </div>

            <!-- Tips card -->
            <div class="card" style="padding:22px 28px;">
                <p class="sec-label">Tips agar Akurasi Tinggi</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    @foreach(['Posisikan wajah di dalam lingkaran panduan','Pastikan pencahayaan cukup dan merata','Lepas kacamata gelap atau masker','Hadap kamera secara langsung (frontal)'] as $tip)
                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <span style="color:#555;margin-top:1px;font-size:.9rem;">◎</span>
                        <p style="color:#666;font-size:.8rem;line-height:1.5;">{{ $tip }}</p>
                    </div>
                    @endforeach
                </div>
                <p style="color:#333;font-size:.75rem;margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.05);line-height:1.5;">
                    Tunggu bingkai kamera berubah <strong style="color:#aaa;">terang</strong> sebelum klik Simpan Wajah.
                </p>
            </div>
        </div>

        <!-- ── RIGHT: Steps + Status ── -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Steps -->
            <div class="card">
                <p class="sec-label">Langkah-langkah</p>

                <div id="step1row" class="step-row active">
                    <div class="step-num active" id="stepNum1">1</div>
                    <div>
                        <p style="font-weight:600;color:#fff;font-size:.875rem;">Aktifkan Kamera</p>
                        <p style="color:#555;font-size:.75rem;margin-top:2px;">Izinkan akses kamera di browser</p>
                    </div>
                </div>
                <div class="divider-line"></div>
                <div id="step2row" class="step-row">
                    <div class="step-num pending" id="stepNum2">2</div>
                    <div>
                        <p style="font-weight:600;color:#3a3a3a;font-size:.875rem;" id="s2title">Posisikan Wajah</p>
                        <p style="color:#333;font-size:.75rem;margin-top:2px;">Tunggu bingkai berubah terang</p>
                    </div>
                </div>
                <div class="divider-line"></div>
                <div id="step3row" class="step-row">
                    <div class="step-num pending" id="stepNum3">3</div>
                    <div>
                        <p style="font-weight:600;color:#3a3a3a;font-size:.875rem;" id="s3title">Simpan Data Wajah</p>
                        <p style="color:#333;font-size:.75rem;margin-top:2px;">Klik tombol "Simpan Wajah"</p>
                    </div>
                </div>
            </div>

            <!-- Status card -->
            <div class="card" id="statusCard">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">👁️</div>
                    <div>
                        <p style="font-weight:600;color:#fff;font-size:.85rem;">Status Deteksi</p>
                        <p style="color:#333;font-size:.7rem;margin-top:1px;">Real-time AI</p>
                    </div>
                </div>
                <p id="detectionStatusText" style="font-size:.8rem;color:#444;line-height:1.5;">Kamera belum aktif</p>
            </div>

            <!-- Saving -->
            <div class="card hidden" id="savingCard" style="text-align:center;padding:32px 20px;">
                <svg class="spinner" style="width:36px;height:36px;color:#fff;margin:0 auto 12px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p style="color:#fff;font-weight:600;font-size:.9rem;">Menyimpan data wajah…</p>
                <p style="color:#444;font-size:.75rem;margin-top:4px;">Harap tunggu sebentar</p>
            </div>

            <!-- Success -->
            <div class="card hidden" id="successCard" style="text-align:center;padding:32px 20px;">
                <div style="font-size:2.2rem;margin-bottom:10px;">✓</div>
                <p style="color:#fff;font-weight:700;font-size:.95rem;margin-bottom:4px;">Berhasil Disimpan!</p>
                <p style="color:#555;font-size:.8rem;margin-bottom:16px;">Mengarahkan ke dashboard…</p>
                <div style="background:rgba(255,255,255,0.08);border-radius:4px;height:3px;overflow:hidden;">
                    <div id="successBar" style="height:100%;background:linear-gradient(90deg,#fff,#b0b0b0);width:0%;transition:width 2s linear;border-radius:4px;"></div>
                </div>
            </div>

            <!-- Skip link -->
            @if($hasFace)
            <div class="card" style="text-align:center;padding:18px;">
                <p style="color:#444;font-size:.75rem;margin-bottom:12px;">Sudah punya wajah terdaftar sebelumnya</p>
                <a href="{{ route('buyer.dashboard') }}" class="btn-g" style="font-size:.8rem;padding:9px 20px;width:100%;justify-content:center;">
                    Langsung ke Dashboard →
                </a>
            </div>
            @endif

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const STORE_URL = @json(route('buyer.register-face.store'));
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

const video        = document.getElementById('video');
const canvas       = document.getElementById('detectionCanvas');
const ctx          = canvas.getContext('2d');
const faceRing     = document.getElementById('faceRing');
const guideCircle  = document.getElementById('guideCircle');
const statusMsgEl  = document.getElementById('statusMsg');
const startCamBtn  = document.getElementById('startCamBtn');
const captureBtn   = document.getElementById('captureBtn');
const statusTxt    = document.getElementById('detectionStatusText');
const savingCard   = document.getElementById('savingCard');
const successCard  = document.getElementById('successCard');
const statusCard   = document.getElementById('statusCard');
const liveDot      = document.getElementById('liveDot');
const liveLabel    = document.getElementById('liveLabel');

let modelsLoaded = false, detectionLoop = null, currentDescriptor = null;

// ── Load models ──────────────────────────────────────────────────────────────
async function loadModels() {
    setStatus('Memuat model AI…', '#555');
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        modelsLoaded = true;
        setStatus('Model siap. Klik "Aktifkan Kamera".', '#666');
    } catch(e) {
        setStatus('Gagal memuat model: ' + e.message, '#f87171');
    }
}

// ── Start camera ──────────────────────────────────────────────────────────────
startCamBtn.addEventListener('click', async () => {
    if (!modelsLoaded) { setStatus('Model masih dimuat, tunggu sebentar…', '#aaa'); return; }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video:{ width:640, height:480, facingMode:'user' } });
        video.srcObject = stream;
        video.style.display = 'block';
        canvas.style.display = 'block';
        document.getElementById('camPlaceholder').style.display = 'none';
        faceRing.style.display = 'block';
        guideCircle.style.display = 'block';
        statusMsgEl.style.display = 'block';

        liveDot.classList.add('on');
        liveLabel.textContent = 'Live';
        liveLabel.style.color = '#888';
        startCamBtn.textContent = '🔄 Kamera Aktif';
        startCamBtn.disabled = true;
        markStep(1,'done'); activateStep(2);

        video.addEventListener('loadedmetadata', () => {
            canvas.width  = video.videoWidth  || 640;
            canvas.height = video.videoHeight || 480;
            startLoop();
        });
    } catch(err) {
        setStatus('Kamera tidak dapat diakses: ' + err.message, '#f87171');
    }
});

// ── Detection loop ─────────────────────────────────────────────────────────────
function startLoop() {
    detectionLoop = setInterval(async () => {
        if (video.paused || video.ended) return;
        try {
            const det = await faceapi
                .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence:0.6 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (det) {
                const { box } = det.detection;
                const sx = canvas.width / video.videoWidth;
                const sy = canvas.height / video.videoHeight;
                const bx=box.x*sx, by=box.y*sy, bw=box.width*sx, bh=box.height*sy;

                // Glow box
                ctx.shadowColor='rgba(255,255,255,0.5)'; ctx.shadowBlur=16;
                ctx.strokeStyle='rgba(255,255,255,0.9)'; ctx.lineWidth=2;
                ctx.strokeRect(bx,by,bw,bh);
                ctx.shadowBlur=0;

                // Corner accents
                const L=16; ctx.strokeStyle='#fff'; ctx.lineWidth=3;
                [[bx,by+L,bx,by,bx+L,by],[bx+bw-L,by,bx+bw,by,bx+bw,by+L],
                 [bx,by+bh-L,bx,by+bh,bx+L,by+bh],[bx+bw,by+bh-L,bx+bw,by+bh,bx+bw-L,by+bh]]
                .forEach(p=>{ctx.beginPath();ctx.moveTo(p[0],p[1]);ctx.lineTo(p[2],p[3]);ctx.lineTo(p[4],p[5]);ctx.stroke();});

                currentDescriptor = Array.from(det.descriptor);
                faceRing.className = 'face-ring has-face';
                statusMsgEl.className = 's-found'; statusMsgEl.textContent = '✓ Wajah Terdeteksi';
                statusMsgEl.style.display = 'block';
                captureBtn.disabled = false;
                setStatus('Wajah terdeteksi dengan jelas. Siap untuk disimpan!', '#888');
                activateStep(3);
            } else {
                currentDescriptor = null;
                faceRing.className = 'face-ring no-face';
                statusMsgEl.className = 's-search'; statusMsgEl.textContent = '🔍 Mencari wajah…';
                statusMsgEl.style.display = 'block';
                captureBtn.disabled = true;
                setStatus('Wajah belum terdeteksi. Posisikan wajah di tengah kamera.', '#444');
            }
        } catch(_) {}
    }, 300);
}

// ── Save ──────────────────────────────────────────────────────────────────────
captureBtn.addEventListener('click', async () => {
    if (!currentDescriptor) return;
    clearInterval(detectionLoop);
    captureBtn.disabled = true; startCamBtn.disabled = true;
    statusCard.classList.add('hidden');
    savingCard.classList.remove('hidden');
    try {
        const res  = await fetch(STORE_URL, {
            method:'POST',
            headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json' },
            body: JSON.stringify({ face_descriptor: currentDescriptor }),
        });
        const data = await res.json();
        if (data.success) {
            savingCard.classList.add('hidden');
            successCard.classList.remove('hidden');
            markStep(3,'done');
            setTimeout(()=>{ document.getElementById('successBar').style.width='100%'; }, 50);
            setTimeout(()=>{ window.location.href = data.redirect || @json(route('buyer.dashboard')); }, 2200);
        } else { throw new Error(data.message||'Gagal menyimpan'); }
    } catch(err) {
        savingCard.classList.add('hidden');
        statusCard.classList.remove('hidden');
        setStatus('Error: ' + err.message, '#f87171');
        captureBtn.disabled = false;
        startLoop();
    }
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function markStep(n,state) {
    const el = document.getElementById('stepNum'+n);
    const row = document.getElementById('step'+n+'row');
    if (state==='done'){
        el.className='step-num done'; el.textContent='✓';
        row.classList.remove('active');
    }
}
function activateStep(n) {
    const el=document.getElementById('stepNum'+n);
    const row=document.getElementById('step'+n+'row');
    const title=document.getElementById('s'+n+'title');
    if(!el) return;
    el.className='step-num active'; el.textContent=n;
    row.classList.add('active');
    if(title) title.style.color='#fff';
}
function setStatus(msg, color='#444') {
    statusTxt.textContent=msg; statusTxt.style.color=color;
}

// utility hidden class
document.querySelectorAll('.hidden').forEach(el=>el.style.display='none');

loadModels();
</script>
</body>
</html>
