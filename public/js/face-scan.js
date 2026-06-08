/**
 * Standalone Face Scan Module
 * Handles face detection, model loading, and POST search request
 */

let faceEmbedding = null;
let videoStream = null;

document.addEventListener('DOMContentLoaded', async () => {
    const loadingEl = document.getElementById('loading');
    const statusDiv = document.getElementById('faceStatus');
    
    // Set loading models message
    if (statusDiv) {
        statusDiv.className = "mt-4 text-xs font-medium text-center text-blue-400";
        statusDiv.textContent = "⏳ Memuat model AI face-api...";
    }

    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
        
        if (statusDiv) {
            statusDiv.className = "mt-4 text-xs font-medium text-center text-green-400";
            statusDiv.textContent = "✓ Model AI siap digunakan.";
        }
        
        setupEventListeners();
    } catch (error) {
        console.error('Failed to load face-api models:', error);
        if (statusDiv) {
            statusDiv.className = "mt-4 text-xs font-medium text-center text-red-400";
            statusDiv.textContent = "✗ Gagal memuat model AI. Silakan refresh halaman.";
        }
    }
});

function setupEventListeners() {
    const startCameraBtn = document.getElementById('startCamera');
    const uploadInput = document.getElementById('uploadFace');
    const searchBtn = document.getElementById('searchBtn');
    const albumSelect = document.getElementById('albumSelect');

    if (startCameraBtn) {
        startCameraBtn.addEventListener('click', startCamera);
    }
    if (uploadInput) {
        uploadInput.addEventListener('change', handleFileUpload);
    }
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
    if (albumSelect) {
        albumSelect.addEventListener('change', validateForm);
    }
}

async function startCamera() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const preview = document.getElementById('preview');
    const statusDiv = document.getElementById('faceStatus');
    const startCameraBtn = document.getElementById('startCamera');

    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            }
        });
        
        video.srcObject = videoStream;
        video.style.display = 'block';
        if (canvas) canvas.style.display = 'none';
        if (preview) preview.style.display = 'none';

        startCameraBtn.textContent = '⏳ Mengambil...';
        startCameraBtn.disabled = true;

        statusDiv.className = "mt-4 text-xs font-medium text-center text-blue-400";
        statusDiv.textContent = "Posisikan wajah Anda di depan kamera. Foto akan diambil dalam 3 detik...";

        // Wait 3 seconds
        await new Promise(resolve => setTimeout(resolve, 3000));

        // Capture frame
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Show canvas, hide video
        video.style.display = 'none';
        canvas.style.display = 'block';

        // Stop camera stream
        stopCamera();

        // Process face
        statusDiv.textContent = "Mendeteksi wajah...";
        await processFaceSource(canvas);

    } catch (err) {
        console.error('Camera error:', err);
        statusDiv.className = "mt-4 text-xs font-medium text-center text-red-400";
        statusDiv.textContent = "Gagal mengakses kamera. Silakan upload foto sebagai alternatif.";
        startCameraBtn.textContent = '📷 Gunakan Kamera';
        startCameraBtn.disabled = false;
    }
}

function stopCamera() {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    const startCameraBtn = document.getElementById('startCamera');
    if (startCameraBtn) {
        startCameraBtn.textContent = '📷 Gunakan Kamera';
        startCameraBtn.disabled = false;
    }
}

async function handleFileUpload(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const statusDiv = document.getElementById('faceStatus');

    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (video) video.style.display = 'none';
        if (canvas) canvas.style.display = 'none';
        stopCamera();

        statusDiv.className = "mt-4 text-xs font-medium text-center text-blue-400";
        statusDiv.textContent = "Mendapatkan wajah...";

        const img = new Image();
        img.onload = async () => {
            await processFaceSource(img);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

async function processFaceSource(source) {
    const statusDiv = document.getElementById('faceStatus');
    try {
        const detection = await faceapi
            .detectSingleFace(source, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            statusDiv.className = "mt-4 text-xs font-medium text-center text-red-400";
            statusDiv.textContent = "Wajah tidak terdeteksi. Silakan coba lagi dengan foto yang lebih jelas.";
            faceEmbedding = null;
        } else {
            faceEmbedding = Array.from(detection.descriptor);
            statusDiv.className = "mt-4 text-xs font-medium text-center text-green-400";
            statusDiv.textContent = "✓ Wajah berhasil terdeteksi!";
        }
    } catch (err) {
        console.error('Face api processing error:', err);
        statusDiv.className = "mt-4 text-xs font-medium text-center text-red-400";
        statusDiv.textContent = "Terjadi kesalahan saat memproses wajah. Coba lagi.";
        faceEmbedding = null;
    }
    validateForm();
}

function validateForm() {
    const searchBtn = document.getElementById('searchBtn');
    const albumSelect = document.getElementById('albumSelect');
    if (searchBtn && albumSelect) {
        searchBtn.disabled = !(faceEmbedding && albumSelect.value);
    }
}

async function performSearch() {
    const albumSelect = document.getElementById('albumSelect');
    const loading = document.getElementById('loading');
    const resultsContainer = document.getElementById('resultsContainer');
    const results = document.getElementById('results');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (!faceEmbedding || !albumSelect.value) return;

    loading.style.display = 'flex';
    resultsContainer.style.display = 'none';
    results.innerHTML = '';

    try {
        const response = await fetch('/face-scan/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                embedding_vector: faceEmbedding,
                album_id: albumSelect.value
            })
        });

        const data = await response.json();
        loading.style.display = 'none';

        if (data.success) {
            resultsContainer.style.display = 'block';
            if (data.photos.length === 0) {
                results.innerHTML = `
                    <div class="col-span-full text-center py-8 text-purple-300/40">
                        <div class="text-4xl mb-2">📸</div>
                        <p class="text-sm">Tidak ada foto yang cocok ditemukan di album ini.</p>
                    </div>
                `;
            } else {
                data.photos.forEach(photo => {
                    results.innerHTML += `
                        <div class="border border-purple-500/20 bg-[#0f0720]/60 p-4 rounded-3xl flex flex-col justify-between hover:scale-[1.02] hover:shadow-[0_0_20px_rgba(168,85,247,0.15)] transition-all duration-300">
                            <div class="relative h-48 rounded-xl overflow-hidden mb-3 border border-purple-500/10">
                                <img src="${photo.watermark_path}" alt="Matched Photo" class="w-full h-full object-cover">
                                <div class="absolute bottom-2 left-2 bg-[#5A2A8F]/90 backdrop-blur-sm text-white text-[9px] font-black px-2.5 py-1 rounded-full shadow-md">
                                    MATCH: ${Math.round(photo.similarity * 100)}%
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-purple-300/40 text-[8px] font-semibold uppercase">Harga</p>
                                    <p class="text-xs font-black text-green-400">Rp ${Number(photo.price).toLocaleString('id-ID')}</p>
                                </div>
                                <form method="POST" action="/cart/add">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="photo_id" value="${photo.id}">
                                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#5A2A8F] to-[#8A4FFF] hover:from-[#6d30b0] hover:to-[#9b5cff] text-white text-[9px] font-bold rounded-full transition-all shadow-md">
                                        🛒 Beli
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;
                });
            }
        } else {
            alert(data.message || 'Pencarian gagal.');
        }

    } catch (err) {
        console.error('Search request failed:', err);
        loading.style.display = 'none';
        alert('Terjadi kesalahan jaringan saat mencari foto.');
    }
}
