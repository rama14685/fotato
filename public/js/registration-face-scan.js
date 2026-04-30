/**
 * Registration Face Scan JavaScript Module
 * Handles face detection and embedding extraction for registration step 2
 * 
 * PRIVACY DESIGN: Client face embeddings are stored IN MEMORY ONLY.
 * The embedding is never saved to localStorage, sessionStorage, or cookies.
 * It is only used for the immediate registration submission.
 */

let faceEmbedding = null;  // PRIVACY: Temporary in-memory storage only
let modelsLoaded = false;
let sessionToken = null;

/**
 * Load face-api.js models from /models directory
 */
async function loadModels() {
    try {
        console.log('Loading face-api.js models...');
        
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models')
        ]);
        
        modelsLoaded = true;
        console.log('All face-api.js models loaded successfully');
        
        // Hide loading indicator and show face scan options
        document.getElementById('loadingModels').classList.add('hidden');
        document.getElementById('faceScanOptions').classList.remove('hidden');
        
    } catch (error) {
        console.error('Error loading face-api.js models:', error);
        showStatus('Gagal memuat model AI. Silakan refresh halaman.', 'error');
    }
}

/**
 * Extract face embedding from an image element
 */
async function extractFaceEmbedding(imageElement) {
    if (!modelsLoaded) {
        console.error('Models not loaded yet');
        return null;
    }
    
    try {
        showStatus('Mendeteksi wajah...', 'info');
        
        const detection = await faceapi
            .detectSingleFace(imageElement)
            .withFaceLandmarks()
            .withFaceDescriptor();
        
        if (!detection) {
            showStatus('Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.', 'error');
            return null;
        }
        
        // Convert Float32Array descriptor to regular array
        const embedding = Array.from(detection.descriptor);
        
        // Verify embedding has exactly 128 dimensions
        if (embedding.length !== 128) {
            console.error(`Invalid embedding dimension: ${embedding.length}, expected 128`);
            showStatus('Terjadi kesalahan saat memproses wajah. Silakan coba lagi.', 'error');
            return null;
        }
        
        console.log('Face embedding extracted successfully:', embedding.length, 'dimensions');
        showStatus('✓ Wajah berhasil terdeteksi!', 'success');
        
        return embedding;
    } catch (error) {
        console.error('Error extracting face embedding:', error);
        showStatus('Terjadi kesalahan saat memproses wajah. Silakan coba lagi.', 'error');
        return null;
    }
}

/**
 * Show status message to user
 */
function showStatus(message, type = 'info') {
    const statusElement = document.getElementById('statusMessage');
    if (!statusElement) return;
    
    statusElement.classList.remove('hidden', 'bg-blue-500/20', 'border-blue-500/50', 'text-blue-200',
                                   'bg-green-500/20', 'border-green-500/50', 'text-green-200',
                                   'bg-red-500/20', 'border-red-500/50', 'text-red-200');
    
    if (type === 'success') {
        statusElement.classList.add('bg-green-500/20', 'border-green-500/50', 'text-green-200', 'border');
    } else if (type === 'error') {
        statusElement.classList.add('bg-red-500/20', 'border-red-500/50', 'text-red-200', 'border');
    } else {
        statusElement.classList.add('bg-blue-500/20', 'border-blue-500/50', 'text-blue-200', 'border');
    }
    
    statusElement.textContent = message;
    statusElement.classList.remove('hidden');
}

/**
 * Initialize camera capture functionality
 */
function initializeCameraCapture() {
    const startCameraBtn = document.getElementById('startCamera');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const completeBtn = document.getElementById('completeRegistration');
    
    if (!startCameraBtn || !video || !canvas) {
        console.error('Required DOM elements not found');
        return;
    }
    
    startCameraBtn.addEventListener('click', async () => {
        try {
            showStatus('Meminta akses kamera...', 'info');
            
            // Request camera access
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: 320, height: 240 } 
            });
            
            // Display video stream
            video.srcObject = stream;
            video.classList.remove('hidden');
            
            showStatus('Kamera aktif. Foto akan diambil dalam 3 detik...', 'info');
            console.log('Camera started, capturing in 3 seconds...');
            
            // Capture frame after 3 seconds
            setTimeout(async () => {
                // Draw video frame to canvas
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, 320, 240);
                
                // Stop video stream
                stream.getTracks().forEach(track => track.stop());
                video.classList.add('hidden');
                
                console.log('Frame captured, extracting face embedding...');
                
                // Extract face embedding from captured canvas
                faceEmbedding = await extractFaceEmbedding(canvas);
                
                // Enable complete button if embedding was successfully extracted
                if (faceEmbedding && completeBtn) {
                    completeBtn.disabled = false;
                    console.log('Complete registration button enabled');
                }
            }, 3000);
            
        } catch (error) {
            console.error('Error accessing camera:', error);
            
            // Display user-friendly error message
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                showStatus('Akses kamera ditolak. Silakan upload foto sebagai gantinya.', 'error');
            } else if (error.name === 'NotFoundError') {
                showStatus('Kamera tidak ditemukan. Silakan upload foto sebagai gantinya.', 'error');
            } else {
                showStatus('Tidak dapat mengakses kamera. Silakan upload foto sebagai gantinya.', 'error');
            }
        }
    });
}

/**
 * Initialize file upload functionality
 */
function initializeFileUpload() {
    const uploadInput = document.getElementById('uploadFace');
    const preview = document.getElementById('preview');
    const completeBtn = document.getElementById('completeRegistration');
    
    if (!uploadInput || !preview) {
        console.error('Required DOM elements not found');
        return;
    }
    
    uploadInput.addEventListener('change', async (event) => {
        const file = event.target.files[0];
        
        if (!file) {
            console.log('No file selected');
            return;
        }
        
        // Validate file type (JPEG, PNG, WebP)
        const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            showStatus('Format file tidak didukung. Silakan upload file JPEG, PNG, atau WebP.', 'error');
            return;
        }
        
        // Validate file size (max 5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (file.size > maxSize) {
            showStatus('Ukuran file terlalu besar. Maksimal 5MB.', 'error');
            return;
        }
        
        console.log('File selected:', file.name, file.type);
        showStatus('Memuat foto...', 'info');
        
        // Create object URL for preview
        const imageUrl = URL.createObjectURL(file);
        preview.src = imageUrl;
        preview.classList.remove('hidden');
        
        // Wait for image to load before extracting face embedding
        preview.onload = async () => {
            console.log('Image loaded, extracting face embedding...');
            
            // Extract face embedding from uploaded image
            faceEmbedding = await extractFaceEmbedding(preview);
            
            // Enable complete button if embedding was successfully extracted
            if (faceEmbedding && completeBtn) {
                completeBtn.disabled = false;
                console.log('Complete registration button enabled');
            }
            
            // Clean up object URL to free memory
            URL.revokeObjectURL(imageUrl);
        };
        
        // Handle image load errors
        preview.onerror = () => {
            console.error('Error loading image');
            showStatus('Gagal memuat gambar. Silakan coba file lain.', 'error');
            URL.revokeObjectURL(imageUrl);
        };
    });
}

/**
 * Initialize registration completion
 */
function initializeRegistrationCompletion() {
    const completeBtn = document.getElementById('completeRegistration');
    const completeText = document.getElementById('completeText');
    const completeLoading = document.getElementById('completeLoading');
    
    if (!completeBtn) {
        console.error('Complete registration button not found');
        return;
    }
    
    completeBtn.addEventListener('click', async () => {
        if (!faceEmbedding) {
            showStatus('Silakan scan wajah Anda terlebih dahulu.', 'error');
            return;
        }
        
        if (!sessionToken) {
            showStatus('Token sesi tidak ditemukan. Silakan mulai registrasi dari awal.', 'error');
            setTimeout(() => {
                window.location.href = '/register';
            }, 2000);
            return;
        }
        
        // Disable button and show loading
        completeBtn.disabled = true;
        completeText.classList.add('hidden');
        completeLoading.classList.remove('hidden');
        showStatus('Menyelesaikan registrasi...', 'info');
        
        try {
            const response = await fetch('/register/step-two', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    session_token: sessionToken,
                    face_embedding: faceEmbedding
                })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showStatus('✓ Registrasi berhasil! Mengalihkan ke dashboard...', 'success');
                
                // PRIVACY: Clear face embedding from memory after successful submission
                faceEmbedding = null;
                
                // Redirect to dashboard
                setTimeout(() => {
                    window.location.href = data.redirect || '/dashboard';
                }, 1500);
            } else if (response.status === 419) {
                // Session expired
                showStatus('Sesi telah berakhir. Mengalihkan ke halaman registrasi...', 'error');
                setTimeout(() => {
                    window.location.href = '/register';
                }, 2000);
            } else {
                // Other errors
                showStatus(data.message || 'Terjadi kesalahan. Silakan coba lagi.', 'error');
                
                // Re-enable button
                completeBtn.disabled = false;
                completeText.classList.remove('hidden');
                completeLoading.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error completing registration:', error);
            showStatus('Terjadi kesalahan jaringan. Silakan coba lagi.', 'error');
            
            // Re-enable button
            completeBtn.disabled = false;
            completeText.classList.remove('hidden');
            completeLoading.classList.add('hidden');
        }
    });
}

/**
 * Get session token from URL parameter
 */
function getSessionToken() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('token');
}

/**
 * Initialize when DOM is ready
 */
document.addEventListener('DOMContentLoaded', async () => {
    // Get session token from URL
    sessionToken = getSessionToken();
    
    if (!sessionToken) {
        showStatus('Token sesi tidak ditemukan. Mengalihkan ke halaman registrasi...', 'error');
        setTimeout(() => {
            window.location.href = '/register';
        }, 2000);
        return;
    }
    
    // Load face-api.js models
    if (typeof faceapi !== 'undefined') {
        await loadModels();
        
        // Initialize all functionality after models are loaded
        initializeCameraCapture();
        initializeFileUpload();
        initializeRegistrationCompletion();
    } else {
        console.error('face-api.js library not loaded');
        showStatus('Library AI tidak ditemukan. Silakan refresh halaman.', 'error');
    }
});
