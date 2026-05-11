<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto – {{ $album->title }} | Fotlist Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Core Layout ────────────────────────────────── */
        body { background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); min-height: 100vh; font-family: 'Inter', sans-serif; color: #e2e8f0; }

        /* ── Glassmorphism Card ─────────────────────────── */
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; }

        /* ── Drop Zone ──────────────────────────────────── */
        #dropZone { border: 2px dashed rgba(139,92,246,0.5); border-radius: 12px; padding: 60px 20px; text-align: center; cursor: pointer; transition: all 0.3s ease; background: rgba(139,92,246,0.05); }
        #dropZone:hover, #dropZone.drag-over { border-color: #8b5cf6; background: rgba(139,92,246,0.15); transform: scale(1.01); }

        /* ── Progress Items ─────────────────────────────── */
        .progress-item { background: rgba(255,255,255,0.05); border-radius: 10px; padding: 14px 18px; margin-bottom: 10px; border-left: 4px solid #6366f1; transition: all 0.3s; }
        .progress-item.scanning { border-left-color: #f59e0b; animation: pulse 1.5s infinite; }
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
        .btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); color:#fff; border:none; border-radius:10px; padding:12px 28px; font-weight:700; cursor:pointer; transition:all .2s; box-shadow:0 4px 15px rgba(99,102,241,.4); }
        .btn-primary:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 6px 20px rgba(99,102,241,.5); }
        .btn-primary:disabled { opacity:.5; cursor:not-allowed; }
        .btn-secondary { background:rgba(255,255,255,0.08); color:#cbd5e1; border:1px solid rgba(255,255,255,.15); border-radius:10px; padding:12px 24px; font-weight:600; cursor:pointer; transition:all .2s; text-decoration:none; display:inline-block; }
        .btn-secondary:hover { background:rgba(255,255,255,0.15); }

        /* ── Loading bar ────────────────────────────────── */
        .progress-bar-wrap { background: rgba(255,255,255,.1); border-radius:4px; height:6px; overflow:hidden; margin-top:8px; }
        .progress-bar-fill { height:100%; border-radius:4px; transition: width .3s ease; background: linear-gradient(90deg, #6366f1, #8b5cf6); }

        /* ── Status badge ────────────────────────────────── */
        .status-badge { display:inline-flex; align-items:center; gap:6px; font-size:.8rem; font-weight:600; padding:4px 12px; border-radius:20px; }
        .badge-scanning { background:rgba(245,158,11,.2); color:#f59e0b; }
        .badge-upload   { background:rgba(59,130,246,.2); color:#60a5fa; }
        .badge-done     { background:rgba(16,185,129,.2); color:#34d399; }
        .badge-error    { background:rgba(239,68,68,.2); color:#f87171; }
        .badge-skip     { background:rgba(100,116,139,.2); color:#94a3b8; }
    </style>
</head>
<body>
<div class="max-w-4xl mx-auto px-4 py-10">

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-3 text-sm text-slate-400">
        <a href="{{ route('admin.albums.index') }}" class="hover:text-white transition">← Albums</a>
        <span>/</span>
        <a href="{{ route('admin.albums.show', $album) }}" class="hover:text-white transition">{{ $album->title }}</a>
        <span>/</span>
        <span class="text-white">Upload Foto</span>
    </div>

    {{-- Header --}}
    <div class="glass p-6 mb-8" style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.2));">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl" style="background:rgba(99,102,241,.3);">📸</div>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $album->title }}</h1>
                <p class="text-slate-300 text-sm mt-1">
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
    <div class="glass p-4 mb-6 border-l-4" style="border-left-color:#f59e0b;">
        <div class="flex items-start gap-3">
            <span class="text-2xl">🤖</span>
            <div>
                <p class="font-semibold text-amber-400 mb-1">Face Detection Aktif</p>
                <p class="text-slate-300 text-sm">Setiap foto akan <strong>dianalisis otomatis</strong> menggunakan AI (face-api.js) di browser Anda sebelum diupload. Wajah yang terdeteksi disimpan ke database untuk fitur pencocokan wajah pembeli.</p>
            </div>
        </div>
    </div>

    {{-- Upload Card --}}
    <div class="glass p-8 mb-8">
        <div id="messageBox"></div>

        {{-- Drop Zone --}}
        <div id="dropZone">
            <div class="text-5xl mb-4">☁️</div>
            <h3 class="text-xl font-bold text-white mb-2">Drag & drop foto di sini</h3>
            <p class="text-slate-400 text-sm mb-4">atau klik untuk memilih file</p>
            <div class="flex gap-3 justify-center flex-wrap">
                <label class="btn-secondary text-sm cursor-pointer">
                    📷 Pilih File
                    <input type="file" id="fileInput" accept="image/*" multiple class="hidden">
                </label>
                <label class="btn-secondary text-sm cursor-pointer">
                    📂 Pilih Folder
                    <input type="file" id="folderInput" accept="image/*" webkitdirectory directory class="hidden">
                </label>
            </div>
        </div>

        {{-- Price Input --}}
        <div class="mt-6 p-5 rounded-xl" style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);">
            <label for="priceInput" class="block text-sm font-semibold text-slate-300 mb-2">💰 Harga per Foto (Rp)</label>
            <input type="number" id="priceInput" value="50000" min="0" step="1000"
                   class="w-full px-4 py-3 rounded-lg text-white font-semibold text-lg"
                   style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); outline:none;">
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-4 mt-6 flex-wrap">
            <button id="startBtn" class="btn-primary" disabled>
                🚀 Scan &amp; Upload Foto
            </button>
            <a href="{{ route('admin.albums.show', $album) }}" class="btn-secondary">
                ← Kembali
            </a>
        </div>

        {{-- Overall Progress Bar --}}
        <div id="overallProgress" class="mt-6 hidden">
            <div class="flex justify-between text-sm text-slate-400 mb-2">
                <span id="progressLabel">Memproses...</span>
                <span id="progressPercent">0%</span>
            </div>
            <div class="progress-bar-wrap">
                <div id="progressBarFill" class="progress-bar-fill" style="width:0%"></div>
            </div>
        </div>
    </div>

    {{-- Per-file Progress List --}}
    <div id="progressList" class="mb-8 hidden">
        <h3 class="text-lg font-bold text-white mb-4">📋 Status Upload</h3>
        <div id="progressItems"></div>
    </div>

    {{-- Uploaded Photos Grid --}}
    <div id="photosSection" class="hidden">
        <h3 class="text-lg font-bold text-white mb-4">✅ Foto Berhasil Diupload</h3>
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
    showMessage('⏳ Memuat model face detection…', 'info');
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        modelsLoaded = true;
        clearMessages();
        showMessage('✅ Model face detection siap digunakan.', 'success');
        setTimeout(clearMessages, 3000);
    } catch (err) {
        showMessage('⚠️ Gagal memuat model face detection. Foto tetap akan diupload tanpa deteksi wajah.', 'warning');
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
        <div class="text-5xl mb-4">✅</div>
        <h3 class="text-xl font-bold text-white mb-1">${selectedFiles.length} foto dipilih</h3>
        <p class="text-slate-400 text-sm">${totalMB} MB total &bull;
            <button type="button" onclick="resetSelection()" class="text-purple-400 hover:underline ml-1">Ganti pilihan</button>
        </p>
    `;
}

function resetSelection() {
    selectedFiles = [];
    fileInput.value = '';
    folderInput.value = '';
    dropZone.innerHTML = `
        <div class="text-5xl mb-4">☁️</div>
        <h3 class="text-xl font-bold text-white mb-2">Drag & drop foto di sini</h3>
        <p class="text-slate-400 text-sm mb-4">atau klik untuk memilih file</p>
        <div class="flex gap-3 justify-center flex-wrap">
            <label class="btn-secondary text-sm cursor-pointer">
                📷 Pilih File
                <input type="file" id="fileInput" accept="image/*" multiple class="hidden">
            </label>
            <label class="btn-secondary text-sm cursor-pointer">
                📂 Pilih Folder
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
        ? `🚀 Scan & Upload ${selectedFiles.length} Foto`
        : '🚀 Scan & Upload Foto';
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
                <span class="font-medium text-sm text-slate-200 truncate max-w-xs">${file.name}</span>
                <span class="status-badge badge-scanning" id="badge-${idx}">⏳ Antri…</span>
            </div>
            <div class="progress-bar-wrap mt-2">
                <div class="progress-bar-fill" id="bar-${idx}" style="width:0%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-1" id="msg-${idx}"></p>
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
        itemEl.classList.add('scanning');
        badgeEl.className = 'status-badge badge-scanning';
        badgeEl.textContent = '🔍 Scanning wajah…';
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
        badgeEl.textContent = '📤 Uploading…';

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
                badgeEl.textContent = `✅ ${data.photo.face_count} wajah`;
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
            badgeEl.textContent = '❌ Gagal';
            msgEl.textContent = uploadErr.message;
        }

        done++;
        setOverall(done < total ? `Memproses ${done + 1} / ${total}…` : '🎉 Selesai!');
    }

    // All done
    progressLabel.textContent   = `✅ ${done} foto diproses`;
    progressPercent.textContent = '100%';
    progressBarFill.style.width = '100%';

    if (photoGrid.children.length > 0) {
        photosSection.classList.remove('hidden');
    }

    isProcessing = false;
    startBtn.disabled = false;
    startBtn.textContent = '🚀 Upload Lebih Banyak';
});

// ════════════════════════════════════════════════════════════════════════════
// PHOTO GRID
// ════════════════════════════════════════════════════════════════════════════
function addPhotoToGrid(photoData) {
    const thumb = document.createElement('div');
    thumb.className = 'photo-thumb';
    thumb.innerHTML = `
        <img src="${photoData.path}" alt="${photoData.original_name}" loading="lazy">
        <div class="face-badge">👤 ${photoData.face_count}</div>
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
        info:    'rgba(59,130,246,.15);border-color:rgba(59,130,246,.4);color:#60a5fa',
    };
    const style = colors[type] || colors.info;
    messageBox.innerHTML += `<div style="padding:12px 16px;border-radius:10px;border:1px solid;background:${style};margin-bottom:10px;font-size:.875rem;">${msg}</div>`;
}

function clearMessages() {
    messageBox.innerHTML = '';
}
</script>
</body>
</html>
