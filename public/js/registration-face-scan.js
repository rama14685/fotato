/**
 * Registration Face Scan Module
 * Handles face detection and embedding extraction during customer registration
 */

let faceEmbedding = null;
let videoStream = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', async () => {
    await initializeRegistrationFaceScan();
});

/**
 * Initialize face scan functionality
 */
async function initializeRegistrationFaceScan() {
    const loadingModels = document.getElementById('loadingModels');
    const faceScanOptions = document.getElementById('faceScanOptions');
    
    try {
        // Load face-api.js models from public/models directory
        await loadFaceApiModels();
        
        // Hide loading, show options
        loadingModels.classList.add('hidden');
        faceScanOptions.classList.remove('hidden');
        
        // Setup event listeners
        setupEventListeners();
        
    } catch (error) {
        console.error('Failed to load face-api models:', error);
        showStatus('error', 'Gagal memuat model AI. Silakan refresh halaman.');
    }
}

/**
 * Load face-api.js models
 */
async function loadFaceApiModels() {
    const MODEL_URL = '/models';
    
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
    ]);
    
    console.log('Face-api models loaded successfully');
}

/**
 * Setup event listeners for buttons and inputs
 */
function setupEventListeners() {
    const startCameraBtn = document.getElementById('startCamera');
    const uploadFaceInput = document.getElementById('uploadFace');
    const completeRegistrationBtn = document.getElementById('completeRegistration');
    
    startCameraBtn.addEventListener('click', handleCameraCapture);
    uploadFaceInput.addEventListener('change', handleFileUpload);
    completeRegistrationBtn.addEventListener('click', handleCompleteRegistration);
}

/**
 * Handle camera capture
 */
async function handleCameraCapture() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const startCameraBtn = document.getElementById('startCamera');
    
    try {
        // Request camera permission
        videoStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            } 
        });
        
        video.srcObject = videoStream;
        video.classList.remove('hidden');
        startCameraBtn.textContent = '⏳ Bersiap...';
        startCameraBtn.disabled = true;
        
        showStatus('info', 'Posisikan wajah Anda di tengah kamera...');
        
        // Wait for video to be ready
        await new Promise(resolve => {
            video.onloadedmetadata = () => {
                video.play();
                resolve();
            };
        });
        
        // Countdown before capture
        for (let i = 3; i > 0; i--) {
            showStatus('info', `Foto akan diambil dalam ${i}...`);
            await sleep(1000);
        }
        
        // Capture frame
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Stop camera
        stopCamera();
        
        // Extract face embedding
        showStatus('info', 'Mendeteksi wajah...');
        await extractFaceEmbedding(canvas);
        
    } catch (error) {
        console.error('Camera capture error:', error);
        
        if (error.name === 'NotAllowedError') {
            showStatus('error', 'Izin kamera ditolak. Silakan gunakan opsi upload foto.');
        } else if (error.name === 'NotFoundError') {
            showStatus('error', 'Kamera tidak ditemukan. Silakan gunakan opsi upload foto.');
        } else {
            showStatus('error', 'Gagal mengakses kamera. Silakan gunakan opsi upload foto.');
        }
        
        startCameraBtn.textContent = '📸 Buka Kamera';
        startCameraBtn.disabled = false;
    }
}

/**
 * Handle file upload
 */
async function handleFileUpload(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    
    if (!file) return;
    
    // Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        showStatus('error', 'Format file tidak valid. Gunakan JPEG, PNG, atau WebP.');
        return;
    }
    
    // Validate file size (5MB max)
    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if (file.size > maxSize) {
        showStatus('error', 'Ukuran file terlalu besar. Maksimal 5MB.');
        return;
    }
    
    try {
        // Display preview
        const reader = new FileReader();
        reader.onload = async (e) => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            
            // Extract face embedding
            showStatus('info', 'Mendeteksi wajah...');
            
            // Create image element for face detection
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

/**
 * Extract face embedding from image or canvas
 */
async function extractFaceEmbedding(source) {
    try {
        // Detect face with landmarks and descriptor
        const detection = await faceapi
            .detectSingleFace(source, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();
        
        if (!detection) {
            showStatus('error', 'Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.');
            return;
        }
        
        // Get 128-dimensional face descriptor (embedding)
        faceEmbedding = Array.from(detection.descriptor);
        
        // Validate embedding
        if (faceEmbedding.length !== 128) {
            showStatus('error', 'Data wajah tidak valid. Silakan coba lagi.');
            faceEmbedding = null;
            return;
        }
        
        // Validate all values are numeric
        if (!faceEmbedding.every(val => typeof val === 'number' && !isNaN(val))) {
            showStatus('error', 'Data wajah tidak valid. Silakan coba lagi.');
            faceEmbedding = null;
            return;
        }
        
        // Success!
        showStatus('success', '✓ Wajah berhasil terdeteksi! Silakan klik tombol di bawah untuk menyelesaikan registrasi.');
        
        // Enable complete registration button
        const completeBtn = document.getElementById('completeRegistration');
        completeBtn.disabled = false;
        
    } catch (error) {
        console.error('Face detection error:', error);
        showStatus('error', 'Gagal mendeteksi wajah. Silakan coba lagi.');
        faceEmbedding = null;
    }
}

/**
 * Handle complete registration submission
 */
async function handleCompleteRegistration() {
    if (!faceEmbedding) {
        showStatus('error', 'Silakan scan wajah terlebih dahulu.');
        return;
    }
    
    const completeBtn = document.getElementById('completeRegistration');
    const completeText = document.getElementById('completeText');
    const completeLoading = document.getElementById('completeLoading');
    
    // Get session token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const sessionToken = urlParams.get('token');
    
    if (!sessionToken) {
        showStatus('error', 'Token sesi tidak ditemukan. Silakan mulai registrasi dari awal.');
        return;
    }
    
    // Disable button and show loading
    completeBtn.disabled = true;
    completeText.classList.add('hidden');
    completeLoading.classList.remove('hidden');
    
    try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Send POST request to complete registration
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
            
            // Clear face embedding from memory
            faceEmbedding = null;
            
            // Redirect to dashboard
            setTimeout(() => {
                window.location.href = data.redirect || '/dashboard';
            }, 1500);
            
        } else if (response.status === 419) {
            // Session expired
            showStatus('error', data.message || 'Sesi telah berakhir. Silakan mulai registrasi dari awal.');
            setTimeout(() => {
                window.location.href = '/register';
            }, 2000);
            
        } else {
            // Other errors
            showStatus('error', data.message || 'Terjadi kesalahan. Silakan coba lagi.');
            completeBtn.disabled = false;
            completeText.classList.remove('hidden');
            completeLoading.classList.add('hidden');
        }
        
    } catch (error) {
        console.error('Registration completion error:', error);
        showStatus('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        completeBtn.disabled = false;
        completeText.classList.remove('hidden');
        completeLoading.classList.add('hidden');
    }
}

/**
 * Stop camera stream
 */
function stopCamera() {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    
    const video = document.getElementById('video');
    video.classList.add('hidden');
    video.srcObject = null;
    
    const startCameraBtn = document.getElementById('startCamera');
    startCameraBtn.textContent = '📸 Buka Kamera';
    startCameraBtn.disabled = false;
}

/**
 * Show status message
 */
function showStatus(type, message) {
    const statusDiv = document.getElementById('statusMessage');
    statusDiv.classList.remove('hidden', 'bg-blue-500/20', 'border-blue-500/50', 'text-blue-200',
                                'bg-green-500/20', 'border-green-500/50', 'text-green-200',
                                'bg-red-500/20', 'border-red-500/50', 'text-red-200');
    
    if (type === 'info') {
        statusDiv.classList.add('bg-blue-500/20', 'border-blue-500/50', 'text-blue-200', 'border');
    } else if (type === 'success') {
        statusDiv.classList.add('bg-green-500/20', 'border-green-500/50', 'text-green-200', 'border');
    } else if (type === 'error') {
        statusDiv.classList.add('bg-red-500/20', 'border-red-500/50', 'text-red-200', 'border');
    }
    
    statusDiv.textContent = message;
}

/**
 * Sleep utility function
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    stopCamera();
    faceEmbedding = null;
});
