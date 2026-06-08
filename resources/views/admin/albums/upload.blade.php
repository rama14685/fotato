<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto – {{ $album->title }} | FOTATO Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Core Layout ────────────────────────────────── */
        body { background: #0d061a; min-height: 100vh; font-family: 'Inter', sans-serif; color: #ffffff; position: relative; }
        .glow { position: fixed; width: 450px; height: 450px; background: radial-gradient(circle, rgba(168, 85, 247, 0.1) 0%, rgba(168, 85, 247, 0) 70%); border-radius: 50%; pointer-events: none; z-index: 0; }

        /* ── Glassmorphism Card ─────────────────────────── */
        .glass { background: rgba(168, 85, 247, 0.03); backdrop-filter: blur(20px); border: 1px solid rgba(168, 85, 247, 0.1); border-radius: 16px; position: relative; z-index: 10; }

        /* ── Drop Zone ──────────────────────────────────── */
        #dropZone { border: 2px dashed rgba(168, 85, 247, 0.3); border-radius: 12px; padding: 60px 20px; text-align: center; cursor: pointer; transition: all 0.3s ease; background: rgba(168, 85, 247, 0.02); }
        #dropZone:hover, #dropZone.drag-over { border-color: #a855f7; background: rgba(168, 85, 247, 0.08); transform: scale(1.01); }

        /* ── Progress Items ─────────────────────────────── */
        .progress-item { background: rgba(168, 85, 247, 0.02); border: 1px solid rgba(168, 85, 247, 0.08); border-radius: 10px; padding: 14px 18px; margin-bottom: 10px; border-left: 4px solid #8B4FFF; transition: all 0.3s; }
        .progress-item.scanning { border-left-color: #d97706; animation: pulse 1.5s infinite; }
        .progress-item.uploading { border-left-color: #3b82f6; }
        .progress-item.success  { border-left-color: #10b981; }
        .progress-item.error    { border-left-color: #ef4444; }
        @keyframes pulse { 0%,100% { opacity:1 } 50% { opacity:0.6 } }

        /* ── Photo Grid ─────────────────────────────────── */
        .photo-thumb { position: relative; aspect-ratio:1; border-radius:10px; overflow:hidden; background:#1e1b4b; cursor:pointer; }
        .photo-thumb img { width:100%; height:100%; object-fit:cover; }
        .photo-thumb .face-badge { position:absolute; top:6px; right:6px; background:rgba(16,185,129,0.9); color:#fff; font-size:.65rem; font-weight:700; padding:2px 7px; border-radius:20px; }
        .photo-thumb .del-btn { position:absolute; top:6px; left:6px; background:rgba(239,68,68,0.85); color:#fff; border:none; width:26px; height:26px; border-radius:50%; cursor:pointer; font-size:1rem; display:none; align-items:center; justify-content:center; transition:.2s; }
        .photo-thumb:hover .del-btn { display:flex; }

        /* ── Buttons ────────────────────────────────────── */
        .btn-primary { background: linear-gradient(135deg, #5A2A8F 0%, #8A4FFF 100%); color:#fff; border:none; border-radius:10px; padding:12px 28px; font-weight:700; cursor:pointer; transition:all .2s; box-shadow:0 4px 15px rgba(139,92,246,.2); }
        .btn-primary:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 6px 20px rgba(139,92,246,.4); }
        .btn-primary:disabled { opacity:.5; cursor:not-allowed; }
        .btn-secondary { background:rgba(168, 85, 247, 0.05); color:#c084fc; border:1px solid rgba(168, 85, 247, 0.25); border-radius:10px; padding:12px 24px; font-weight:600; cursor:pointer; transition:all .2s; text-decoration:none; display:inline-block; }
        .btn-secondary:hover { background:rgba(168, 85, 247, 0.15); }

        /* ── Loading bar ────────────────────────────────── */
        .progress-bar-wrap { background: rgba(255,255,255,.05); border-radius:4px; height:6px; overflow:hidden; margin-top:8px; }
        .progress-bar-fill { height:100%; border-radius:4px; transition: width .3s ease; background: linear-gradient(90deg, #5A2A8F, #8A4FFF); }

        /* ── Status badge ────────────────────────────────── */
        .status-badge { display:inline-flex; align-items:center; gap:6px; font-size:.8rem; font-weight:600; padding:4px 12px; border-radius:20px; }
        .badge-scanning { background:rgba(217,119,6,.2); color:#f59e0b; }
        .badge-upload   { background:rgba(59,130,246,.2); color:#60a5fa; }
        .badge-done     { background:rgba(16,185,129,.2); color:#34d399; }
        .badge-error    { background:rgba(239,68,68,.2); color:#f87171; }
        .badge-skip     { background:rgba(100,116,139,.2); color:#94a3b8; }
    </style>
</head>
<body>
    <!-- Background Glows -->
    <div class="glow top-[-10%] left-[-10%]"></div>
    <div class="glow bottom-[-10%] right-[-10%]"></div>

<div class="max-w-4xl mx-auto px-4 py-10 relative z-10">

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-3 text-sm text-purple-300/40 font-sans">
        <a href="{{ route('admin.albums.index') }}" class="hover:text-white transition">Albums</a>
        <span>/</span>
        <a href="{{ route('admin.albums.show', $album) }}" class="hover:text-white transition">{{ $album->title }}</a>
        <span>/</span>
        <span class="text-purple-300/80">Upload Foto</span>
    </div>

    {{-- Header --}}
    <div class="glass p-6 mb-8 bg-gradient-to-r from-[#5A2A8F]/20 to-[#8A4FFF]/10 border border-purple-500/20">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-[#5A2A8F]/20 text-[#a855f7] border border-purple-500/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316A2.192 2.192 0 0 0 14.502 4h-5c-.7 0-1.363.336-1.78.918l-.895 1.257ZM12 10.5a3.75 3.75 0 1 1 0 7.5 3.75 3.75 0 0 1 0-7.5ZM12 12a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold font-display text-white">{{ $album->title }}</h1>
                <p class="text-purple-300/60 text-sm mt-1">
                    {{ $album->location ?? 'No location' }}
                    @if($album->event_date)
                        &bull; {{ $album->event_date->format('d M Y') }}
                    @endif
                    &bull; <strong>{{ $album->photos->count() }}</strong> foto saat ini
                </p>
            </div>
        </div>
    </div>

    {{-- Alert info --}}
    <div class="glass p-5 mb-6 border-l-4 border-l-purple-500 bg-[#0f0720]/60">
        <div class="flex items-start gap-4">
            <div class="text-[#a855f7] mt-0.5 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 21l8.982-8.983m-8.982 8.983a9.01 9.01 0 0 1-3.078-10.457m3.078 10.457A9.01 9.01 0 0 0 17.92 11.23M18 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>
            <div>
                <p class="font-bold text-purple-300 mb-1 text-sm">Face Detection Aktif</p>
                <p class="text-purple-300/60 text-xs leading-relaxed">Setiap foto akan dianalisis secara otomatis menggunakan AI (face-api.js) langsung di browser Anda sebelum diunggah. Wajah yang terdeteksi disimpan untuk mempermudah pencarian foto pembeli.</p>
            </div>
        </div>
    </div>

    {{-- Upload Card --}}
    <div class="glass p-8 mb-8 bg-[#0f0720]/40">
        <div id="messageBox"></div>

        {{-- Drop Zone --}}
        <div id="dropZone">
            <div class="text-purple-400 flex justify-center mb-4">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2 font-display">Drag & drop foto di sini</h3>
            <p class="text-purple-300/40 text-sm mb-4">atau klik untuk memilih file</p>
            <div class="flex gap-3 justify-center flex-wrap">
                <label class="btn-secondary text-xs cursor-pointer">
                    Pilih File
                    <input type="file" id="fileInput" accept="image/*" multiple class="hidden">
                </label>
                <label class="btn-secondary text-xs cursor-pointer">
                    Pilih Folder
                    <input type="file" id="folderInput" accept="image/*" webkitdirectory directory class="hidden">
                </label>
            </div>
        </div>

        {{-- Price Input --}}
        <div class="mt-6 p-5 rounded-xl border border-purple-500/10 bg-[#1f0e3d]/20">
            <label for="priceInput" class="block text-xs font-bold text-purple-300 mb-2 uppercase tracking-wider">Harga per Foto (Rp)</label>
            <input type="number" id="priceInput" value="50000" min="0" step="1000"
                   class="w-full px-4 py-3 rounded-lg text-white font-bold text-lg bg-black/30 border border-purple-500/20 focus:border-purple-500 outline-none transition">
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-4 mt-6 flex-wrap">
            <button id="startBtn" class="btn-primary text-sm" disabled>
                Scan & Upload Foto
            </button>
            <a href="{{ route('admin.albums.show', $album) }}" class="btn-secondary text-sm">
                Kembali
            </a>
        </div>

        {{-- Overall Progress Bar --}}
        <div id="overallProgress" class="mt-6 hidden">
            <div class="flex justify-between text-xs text-purple-300/40 mb-2">
                <span id="progressLabel" class="font-medium">Memproses...</span>
                <span id="progressPercent" class="font-bold">0%</span>
            </div>
            <div class="progress-bar-wrap">
                <div id="progressBarFill" class="progress-bar-fill" style="width:0%"></div>
            </div>
        </div>
    </div>

    {{-- Per-file Progress List --}}
    <div id="progressList" class="mb-8 hidden">
        <h3 class="text-lg font-bold font-display text-white mb-4">Status Upload</h3>
        <div id="progressItems"></div>
    </div>

    {{-- Uploaded Photos Grid --}}
    <div id="photosSection" class="hidden">
        <h3 class="text-lg font-bold font-display text-white mb-4">Foto Berhasil Diupload</h3>
        <div id="photoGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
    </div>

</div>

{{-- face-api.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
// ════════════════════════════════════════════════════════════════════════════
// CONFIG
// ════════════════════════════════════════════════════════════════════════════
const UPLOAD_URL      = @json(route('admin.albums.store-photos', $album));
const CSRF_TOKEN      = document.querySelector('meta[name="csrf-token"]').content;
const MODEL_URL       = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

// ════════════════════════════════════════════════════════════════════════════
// DOM REFS
// ════════════════════════════════════════════════════════════════════════════
const dropZone        = document.getElementById('dropZone');
const fileInput       = document.getElementById('fileInput');
const folderInput     = document.getElementById('folderInput');
const priceInput      = document.getElementById('priceInput');
const startBtn        = document.getElementById('startBtn');
const overallProgress = document.getElementById('overallProgress');
const progressLabel   = document.getElementById('progressLabel');
const progressPercent = document.getElementById('progressPercent');
const progressBarFill = document.getElementById('progressBarFill');
const progressList    = document.getElementById('progressList');
const progressItems   = document.getElementById('progressItems');
const photosSection   = document.getElementById('photosSection');
const photoGrid       = document.getElementById('photoGrid');
const messageBox      = document.getElementById('messageBox');

let selectedFiles   = [];
let modelsLoaded    = false;
let isProcessing    = false;

// ════════════════════════════════════════════════════════════════════════════
// MODEL LOADING (background, lazy)
// ════════════════════════════════════════════════════════════════════════════
async function loadModels() {
    if (modelsLoaded) return;
    showMessage('Memuat model face detection…', 'info');
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        modelsLoaded = true;
        clearMessages();
        showMessage('Model face detection siap digunakan.', 'success');
        setTimeout(clearMessages, 3000);
    } catch (err) {
        showMessage('Gagal memuat model face detection. Foto tetap akan diupload tanpa deteksi wajah.', 'warning');
    }
}

// Start loading models immediately on page load
loadModels();

// ════════════════════════════════════════════════════════════════════════════
// FILE SELECTION
// ════════════════════════════════════════════════════════════════════════════
function handleFiles(files) {
    selectedFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
    updateDropZoneText();
    updateStartBtn();
}

fileInput.addEventListener('change',   e => handleFiles(e.target.files));
folderInput.addEventListener('change', e => handleFiles(e.target.files));
dropZone.addEventListener('click',     () => fileInput.click());

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    handleFiles(e.dataTransfer.files);
});

function updateDropZoneText() {
    const totalMB = (selectedFiles.reduce((s, f) => s + f.size, 0) / 1048576).toFixed(1);
    dropZone.innerHTML = `
        <div class="text-[#34d399] flex justify-center mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-white mb-1 font-display">${selectedFiles.length} foto dipilih</h3>
        <p class="text-purple-300/40 text-sm">${totalMB} MB total &bull;
            <button type="button" onclick="resetSelection()" class="text-purple-400 hover:underline ml-1 font-semibold">Ganti pilihan</button>
        </p>
    `;
}

function resetSelection() {
    selectedFiles = [];
    fileInput.value = '';
    folderInput.value = '';
    dropZone.innerHTML = `
        <div class="text-purple-400 flex justify-center mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-white mb-2 font-display">Drag & drop foto di sini</h3>
        <p class="text-purple-300/40 text-sm mb-4">atau klik untuk memilih file</p>
        <div class="flex gap-3 justify-center flex-wrap">
            <label class="btn-secondary text-xs cursor-pointer">
                Pilih File
                <input type="file" id="fileInput" accept="image/*" multiple class="hidden">
            </label>
            <label class="btn-secondary text-xs cursor-pointer">
                Pilih Folder
                <input type="file" id="folderInput" accept="image/*" webkitdirectory directory class="hidden">
            </label>
        </div>
    `;
    // Re-attach listeners on newly created inputs
    document.getElementById('fileInput').addEventListener('change', e => handleFiles(e.target.files));
    document.getElementById('folderInput').addEventListener('change', e => handleFiles(e.target.files));
    updateStartBtn();
}

function updateStartBtn() {
    startBtn.disabled = selectedFiles.length === 0 || isProcessing;
    startBtn.textContent = selectedFiles.length > 0
        ? `Scan & Upload ${selectedFiles.length} Foto`
        : 'Scan & Upload Foto';
}

// ════════════════════════════════════════════════════════════════════════════
// MAIN PIPELINE: SCAN FACES → UPLOAD
// ════════════════════════════════════════════════════════════════════════════
startBtn.addEventListener('click', async () => {
    if (selectedFiles.length === 0 || isProcessing) return;

    isProcessing = true;
    startBtn.disabled = true;
    clearMessages();

    // Show UI sections
    overallProgress.classList.remove('hidden');
    progressList.classList.remove('hidden');
    progressItems.innerHTML = '';

    // Build progress item elements
    const itemEls = selectedFiles.map((file, idx) => {
        const el = document.createElement('div');
        el.className = 'progress-item';
        el.id = `item-${idx}`;
        el.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="font-bold text-sm text-purple-200 truncate max-w-xs">${file.name}</span>
                <span class="status-badge badge-scanning" id="badge-${idx}">Antri…</span>
            </div>
            <div class="progress-bar-wrap mt-2">
                <div class="progress-bar-fill" id="bar-${idx}" style="width:0%"></div>
            </div>
            <p class="text-xs text-purple-300/40 mt-1.5" id="msg-${idx}"></p>
        `;
        progressItems.appendChild(el);
        return el;
    });

    let done = 0;
    const total = selectedFiles.length;

    function setOverall(label) {
        const pct = Math.round((done / total) * 100);
        progressLabel.textContent   = label;
        progressPercent.textContent = pct + '%';
        progressBarFill.style.width = pct + '%';
    }

    // Process each file sequentially
    for (let i = 0; i < selectedFiles.length; i++) {
        const file = selectedFiles[i];
        const itemEl = itemEls[i];
        const badgeEl = document.getElementById(`badge-${i}`);
        const barEl   = document.getElementById(`bar-${i}`);
        const msgEl   = document.getElementById(`msg-${i}`);

        setOverall(`Memproses ${i + 1} / ${total}: ${file.name}`);

        // ── Step 1: Scan faces ───────────────────────────────────────────────
        itemEl.class = 'progress-item scanning';
        badgeEl.className = 'status-badge badge-scanning';
        badgeEl.textContent = 'Scanning wajah…';
        barEl.style.width = '20%';
        msgEl.textContent = 'Mendeteksi wajah dalam foto…';

        let descriptors = [];
        try {
            if (modelsLoaded) {
                const img = await createImageBitmap(file);
                const canvas = document.createElement('canvas');
                canvas.width  = img.width;
                canvas.height = img.height;
                canvas.getContext('2d').drawImage(img, 0, 0);

                const detections = await faceapi
                    .detectAllFaces(canvas, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                descriptors = detections.map(d => Array.from(d.descriptor));
                msgEl.textContent = `${descriptors.length} wajah terdeteksi.`;
            } else {
                msgEl.textContent = 'Model belum siap, foto diupload tanpa data wajah.';
            }
        } catch (scanErr) {
            msgEl.textContent = `Scan gagal (${scanErr.message}), upload tanpa data wajah.`;
        }

        barEl.style.width = '50%';

        // ── Step 2: Upload to backend ────────────────────────────────────────
        itemEl.classList.remove('scanning');
        itemEl.classList.add('uploading');
        badgeEl.className = 'status-badge badge-upload';
        badgeEl.textContent = 'Uploading…';

        const formData = new FormData();
        formData.append('photo', file);
        formData.append('price', priceInput.value || 50000);
        formData.append('face_descriptors', JSON.stringify(descriptors));

        try {
            const response = await fetch(UPLOAD_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData,
            });

            const data = await response.json();

            barEl.style.width = '100%';

            if (data.success) {
                itemEl.classList.remove('uploading');
                itemEl.classList.add('success');
                badgeEl.className = 'status-badge badge-done';
                badgeEl.textContent = `Selesai (${data.photo.face_count} wajah)`;
                msgEl.textContent = data.message;

                // Add to photo grid
                addPhotoToGrid(data.photo);
            } else {
                throw new Error(data.message || 'Upload gagal');
            }
        } catch (uploadErr) {
            itemEl.classList.remove('uploading');
            itemEl.classList.add('error');
            badgeEl.className = 'status-badge badge-error';
            badgeEl.textContent = 'Gagal';
            msgEl.textContent = uploadErr.message;
        }

        done++;
        setOverall(done < total ? `Memproses ${done + 1} / ${total}…` : 'Selesai!');
    }

    // All done
    progressLabel.textContent   = `${done} foto diproses`;
    progressPercent.textContent = '100%';
    progressBarFill.style.width = '100%';

    if (photoGrid.children.length > 0) {
        photosSection.classList.remove('hidden');
    }

    isProcessing = false;
    startBtn.disabled = false;
    startBtn.textContent = 'Upload Lebih Banyak';
});

// ════════════════════════════════════════════════════════════════════════════
// PHOTO GRID
// ════════════════════════════════════════════════════════════════════════════
function addPhotoToGrid(photoData) {
    const thumb = document.createElement('div');
    thumb.className = 'photo-thumb';
    thumb.innerHTML = `
        <img src="${photoData.path}" alt="${photoData.original_name}" loading="lazy">
        <div class="face-badge">Wajah: ${photoData.face_count}</div>
        <button class="del-btn" data-id="${photoData.id}" title="Hapus foto ini">×</button>
    `;
    photoGrid.appendChild(thumb);
}

photoGrid.addEventListener('click', async e => {
    const btn = e.target.closest('.del-btn');
    if (!btn) return;
    if (!confirm('Hapus foto ini dari album?')) return;

    const photoId = btn.dataset.id;
    try {
        const res = await fetch(`/admin/photos/${photoId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
        });
        const data = await res.json();
        if (data.success) {
            btn.closest('.photo-thumb').remove();
            showMessage('Foto berhasil dihapus.', 'success');
        }
    } catch {
        showMessage('Gagal menghapus foto.', 'error');
    }
});

// ════════════════════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════════════════════
function showMessage(msg, type = 'info') {
    const colors = {
        success: 'rgba(16,185,129,.15);border-color:rgba(16,185,129,.4);color:#34d399',
        error:   'rgba(239,68,68,.15);border-color:rgba(239,68,68,.4);color:#f87171',
        warning: 'rgba(245,158,11,.15);border-color:rgba(245,158,11,.4);color:#fbbf24',
        info:    'rgba(168,85,247,.15);border-color:rgba(168,85,247,.4);color:#c084fc',
    };
    const style = colors[type] || colors.info;
    messageBox.innerHTML += `<div style="padding:12px 16px;border-radius:10px;border:1px solid;background:${style};margin-bottom:10px;font-size:.875rem;">${msg}</div>`;
}

// Clear Messages function
function clearMessages() {
    messageBox.innerHTML = '';
}
</script>
</body>
</html>
