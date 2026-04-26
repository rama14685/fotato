/**
 * Face Scan JavaScript Module
 * Handles face detection, embedding extraction, and photo search functionality
 * using face-api.js library
 * 
 * PRIVACY DESIGN: Client face embeddings are stored IN MEMORY ONLY.
 * 
 * The faceEmbedding variable below stores the client's face embedding temporarily
 * in JavaScript memory. It is:
 * - NEVER saved to localStorage, sessionStorage, or cookies
 * - NEVER persisted to any backend database or file
 * - Only used for the immediate search operation
 * - Automatically cleared when the page is refreshed or closed
 * 
 * This ensures client biometric data remains private and ephemeral.
 * Requirements: 8.2 (no storage of client embeddings), 8.3 (immediate use only)
 */

let faceEmbedding = null;  // PRIVACY: Temporary in-memory storage only
let modelsLoaded = false;

/**
 * Get the current face embedding (for testing)
 * @returns {Array<number>|null}
 */
function getFaceEmbedding() {
    return faceEmbedding;
}

/**
 * Set the face embedding (for testing)
 * @param {Array<number>|null} embedding
 */
function setFaceEmbedding(embedding) {
    faceEmbedding = embedding;
}

/**
 * Load face-api.js models from /models directory
 * Loads three required models:
 * - ssdMobilenetv1: Face detection
 * - faceLandmark68Net: Facial landmark detection
 * - faceRecognitionNet: Face embedding extraction (128-dimensional)
 * 
 * @param {object} faceApiInstance - The face-api.js instance to use (for testing)
 * @returns {Promise<void>}
 */
async function loadModels(faceApiInstance = null) {
    // Use provided instance or global faceapi
    const api = faceApiInstance || (typeof faceapi !== 'undefined' ? faceapi : null);
    
    if (!api) {
        throw new Error('face-api.js library not loaded');
    }
    
    try {
        console.log('Loading face-api.js models...');
        
        await Promise.all([
            api.nets.ssdMobilenetv1.loadFromUri('/models'),
            api.nets.faceLandmark68Net.loadFromUri('/models'),
            api.nets.faceRecognitionNet.loadFromUri('/models')
        ]);
        
        modelsLoaded = true;
        console.log('All face-api.js models loaded successfully');
    } catch (error) {
        console.error('Error loading face-api.js models:', error);
        alert('Failed to load face detection models. Please refresh the page.');
        throw error;
    }
}

/**
 * Extract face embedding from an image element
 * Detects a single face, extracts landmarks, and generates a 128-dimensional descriptor
 * 
 * PRIVACY: The extracted embedding is stored only in the faceEmbedding variable (in-memory).
 * It is never sent to any analytics service, never logged to console in production,
 * and never persisted to any storage mechanism.
 * 
 * @param {HTMLImageElement|HTMLCanvasElement} imageElement - Image or canvas element containing a face
 * @param {object} faceApiInstance - The face-api.js instance to use (for testing)
 * @returns {Promise<Array<number>|null>} 128-dimensional embedding array or null if no face detected
 */
async function extractFaceEmbedding(imageElement, faceApiInstance = null) {
    // Use provided instance or global faceapi
    const api = faceApiInstance || (typeof faceapi !== 'undefined' ? faceapi : null);
    
    if (!api) {
        console.error('face-api.js library not loaded');
        return null;
    }
    
    if (!modelsLoaded) {
        console.error('Models not loaded yet');
        return null;
    }
    
    try {
        const detection = await api
            .detectSingleFace(imageElement)
            .withFaceLandmarks()
            .withFaceDescriptor();
        
        if (!detection) {
            alert('Wajah tidak terdeteksi. Coba lagi dengan foto yang lebih jelas.');
            return null;
        }
        
        // Convert Float32Array descriptor to regular array
        const embedding = Array.from(detection.descriptor);
        
        // Verify embedding has exactly 128 dimensions
        if (embedding.length !== 128) {
            console.error(`Invalid embedding dimension: ${embedding.length}, expected 128`);
            return null;
        }
        
        console.log('Face embedding extracted successfully:', embedding.length, 'dimensions');
        return embedding;
    } catch (error) {
        console.error('Error extracting face embedding:', error);
        alert('Terjadi kesalahan saat memproses wajah. Silakan coba lagi.');
        return null;
    }
}

/**
 * Get the current models loaded status
 * @returns {boolean}
 */
function getModelsLoadedStatus() {
    return modelsLoaded;
}

/**
 * Reset the models loaded status (for testing)
 */
function resetModelsLoadedStatus() {
    modelsLoaded = false;
}

/**
 * Initialize camera capture functionality
 * Sets up event listener for camera button to capture face photo
 * Requirements: 1.2, 2.1
 */
function initializeCameraCapture() {
    const startCameraBtn = document.getElementById('startCamera');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const searchBtn = document.getElementById('searchBtn');
    
    if (!startCameraBtn || !video || !canvas) {
        console.error('Required DOM elements not found');
        return;
    }
    
    startCameraBtn.addEventListener('click', async () => {
        try {
            // Request camera access
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: 320, height: 240 } 
            });
            
            // Display video stream
            video.srcObject = stream;
            video.style.display = 'block';
            
            console.log('Camera started, capturing in 3 seconds...');
            
            // Capture frame after 3 seconds
            setTimeout(async () => {
                // Draw video frame to canvas
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, 320, 240);
                
                // Stop video stream
                stream.getTracks().forEach(track => track.stop());
                video.style.display = 'none';
                
                console.log('Frame captured, extracting face embedding...');
                
                // Extract face embedding from captured canvas
                faceEmbedding = await extractFaceEmbedding(canvas);
                
                // Enable search button if embedding was successfully extracted
                if (faceEmbedding && searchBtn) {
                    searchBtn.disabled = false;
                    console.log('Search button enabled');
                }
            }, 3000);
            
        } catch (error) {
            console.error('Error accessing camera:', error);
            
            // Display user-friendly error message
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                alert('Akses kamera ditolak. Silakan periksa izin atau upload foto sebagai gantinya.');
            } else if (error.name === 'NotFoundError') {
                alert('Kamera tidak ditemukan. Silakan upload foto sebagai gantinya.');
            } else {
                alert('Tidak dapat mengakses kamera. Silakan upload foto sebagai gantinya.');
            }
        }
    });
}

/**
 * Initialize file upload functionality
 * Sets up event listener for file input to handle image uploads
 * Requirements: 1.3, 1.4, 2.1
 */
function initializeFileUpload() {
    const uploadInput = document.getElementById('uploadFace');
    const preview = document.getElementById('preview');
    const searchBtn = document.getElementById('searchBtn');
    
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
            alert('Format file tidak didukung. Silakan upload file JPEG, PNG, atau WebP.');
            return;
        }
        
        console.log('File selected:', file.name, file.type);
        
        // Create object URL for preview
        const imageUrl = URL.createObjectURL(file);
        preview.src = imageUrl;
        preview.style.display = 'block';
        
        // Wait for image to load before extracting face embedding
        preview.onload = async () => {
            console.log('Image loaded, extracting face embedding...');
            
            // Extract face embedding from uploaded image
            faceEmbedding = await extractFaceEmbedding(preview);
            
            // Enable search button if embedding was successfully extracted
            if (faceEmbedding && searchBtn) {
                searchBtn.disabled = false;
                console.log('Search button enabled');
            }
            
            // Clean up object URL to free memory
            URL.revokeObjectURL(imageUrl);
        };
        
        // Handle image load errors
        preview.onerror = () => {
            console.error('Error loading image');
            alert('Gagal memuat gambar. Silakan coba file lain.');
            URL.revokeObjectURL(imageUrl);
        };
    });
}

/**
 * Display an error message to the user in a visible element.
 * Creates or updates an error display element in the DOM.
 * Requirements: 10.3, 10.4
 *
 * @param {string} message - The error message to display
 */
function showError(message) {
    let errorEl = document.getElementById('errorMessage');
    if (!errorEl) {
        // Create error element if it doesn't exist
        errorEl = document.createElement('div');
        errorEl.id = 'errorMessage';
        errorEl.style.cssText = 'display:block; color:#ef4444; background:#fef2f2; border:1px solid #fca5a5; border-radius:0.5rem; padding:0.75rem 1rem; margin-top:1rem;';
        const searchBtn = document.getElementById('searchBtn');
        if (searchBtn && searchBtn.parentNode) {
            searchBtn.parentNode.insertBefore(errorEl, searchBtn.nextSibling);
        } else {
            document.body.appendChild(errorEl);
        }
    }
    errorEl.textContent = message;
    errorEl.style.display = 'block';
}


let currentPage = 1;
let currentPagination = null;

function initializeSearchFunctionality() {
    const searchBtn = document.getElementById('searchBtn');
    const albumSelect = document.getElementById('albumSelect');
    const loading = document.getElementById('loading');
    
    if (!searchBtn || !albumSelect || !loading) {
        console.error('Required DOM elements not found for search functionality');
        return;
    }
    
    searchBtn.addEventListener('click', async () => {
        // Reset pagination for new search
        currentPage = 1;
        currentPagination = null;
        
        // Validate that album is selected before search
        const albumId = albumSelect.value;
        if (!albumId) {
            alert('Pilih album terlebih dahulu');
            return;
        }
        
        // Validate that face embedding is available
        if (!faceEmbedding) {
            alert('Silakan scan wajah Anda terlebih dahulu');
            return;
        }
        
        console.log('Starting search with album ID:', albumId);
        
        // Display loading indicator during request
        loading.style.display = 'block';
        searchBtn.disabled = true;
        
        try {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }
            
            // PRIVACY: Send embedding to backend for immediate search only
            // The backend will use it in-memory and discard it after the response
            // The embedding is never stored in any database or persistent storage
            // Send POST request to /face-scan/search with embedding_vector and album_id
            const response = await fetch('/face-scan/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    embedding_vector: faceEmbedding,
                    album_id: parseInt(albumId),
                    page: currentPage
                })
            });
            
            if (!response.ok) {
                // Handle HTTP error responses based on status code
                if (response.status === 503) {
                    showError('Service temporarily unavailable. Please try again later');
                } else {
                    showError('Search failed. Please try again');
                }
                return;
            }
            
            const data = await response.json();
            
            console.log('Search completed successfully:', data);
            
            // Store pagination metadata
            currentPagination = data.pagination;
            
            // Call displayResults() with response data and pagination
            displayResults(data.photos || [], data.pagination);
            
        } catch (error) {
            console.error('Search error:', error);
            
            // Network error or fetch failure → service unavailable
            if (error instanceof TypeError || error.message.includes('fetch') || error.message.includes('Network')) {
                showError('Service temporarily unavailable. Please try again later');
            } else if (error.message.includes('CSRF')) {
                alert('Sesi keamanan bermasalah. Silakan refresh halaman dan coba lagi.');
            } else {
                showError('Search failed. Please try again');
            }
        } finally {
            // Hide loading indicator and re-enable search button
            loading.style.display = 'none';
            searchBtn.disabled = false;
        }
    });
}

/**
 * Display search results in the results container
 * Shows matched photos with similarity scores and add to cart functionality
 * Requirements: 6.1, 6.2, 6.4, 6.5, 6.6, 9.3
 * 
 * @param {Array} photos - Array of matched photos with id, watermark_path, price, similarity
 * @param {Object} pagination - Pagination metadata (current_page, total_pages, per_page, total_items)
 * @param {boolean} append - Whether to append results (for Load More) or replace them
 */
function displayResults(photos, pagination = null, append = false) {
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsDiv = document.getElementById('results');
    
    if (!resultsContainer || !resultsDiv) {
        console.error('Results container elements not found');
        return;
    }
    
    // Show results container
    resultsContainer.style.display = 'block';
    
    // Clear previous results if not appending
    if (!append) {
        resultsDiv.innerHTML = '';
    }
    
    // Display "no matches found" message when results are empty
    if (!photos || photos.length === 0) {
        if (!append) {
            resultsDiv.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <div class="text-6xl mb-4">😔</div>
                    <h3 class="text-xl font-bold text-white mb-2">Tidak Ada Foto Ditemukan</h3>
                    <p class="text-gray-400">Maaf, tidak ada foto yang cocok dengan wajah Anda di album ini.</p>
                    <p class="text-gray-400 text-sm mt-2">Coba pilih album lain atau gunakan foto wajah yang lebih jelas.</p>
                </div>
            `;
        }
        return;
    }
    
    console.log(`Displaying ${photos.length} matched photos`);
    
    // Remove existing Load More button if present
    const existingLoadMore = document.getElementById('loadMoreBtn');
    if (existingLoadMore) {
        existingLoadMore.remove();
    }
    
    // For each photo, render card with watermarked image, similarity percentage, price
    photos.forEach(photo => {
        const similarityPercentage = (photo.similarity * 100).toFixed(1);
        
        const photoCard = document.createElement('div');
        photoCard.className = 'bg-white/5 backdrop-blur-xl border border-white/10 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105';
        
        photoCard.innerHTML = `
            <div class="relative">
                <img src="${photo.watermark_path}" alt="Matched Photo" class="w-full h-48 object-cover">
                <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                    ${similarityPercentage}% Match
                </div>
            </div>
            <div class="p-4">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-400 text-sm">Similarity Score</span>
                    <span class="text-green-400 font-bold">${similarityPercentage}%</span>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-2xl font-bold text-white">Rp ${photo.price.toLocaleString('id-ID')}</span>
                </div>
                <button onclick="addToCart(${photo.id})" class="w-full px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105">
                    🛒 Tambah ke Keranjang
                </button>
            </div>
        `;
        
        resultsDiv.appendChild(photoCard);
    });
    
    // Add "Load More" button if there are more pages
    if (pagination && pagination.current_page < pagination.total_pages) {
        const loadMoreContainer = document.createElement('div');
        loadMoreContainer.id = 'loadMoreBtn';
        loadMoreContainer.className = 'col-span-full text-center mt-6';
        
        loadMoreContainer.innerHTML = `
            <button onclick="loadMore()" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105">
                Load More (Page ${pagination.current_page + 1} of ${pagination.total_pages})
            </button>
            <p class="text-gray-400 text-sm mt-2">
                Showing ${pagination.current_page * pagination.per_page} of ${pagination.total_items} photos
            </p>
        `;
        
        resultsDiv.appendChild(loadMoreContainer);
    } else if (pagination && pagination.total_items > 0) {
        // Show completion message
        const completionMsg = document.createElement('div');
        completionMsg.className = 'col-span-full text-center mt-6';
        completionMsg.innerHTML = `
            <p class="text-gray-400 text-sm">
                All ${pagination.total_items} photos displayed
            </p>
        `;
        resultsDiv.appendChild(completionMsg);
    }
    
    // Scroll to results section (only on initial search, not on Load More)
    if (!append) {
        resultsContainer.scrollIntoView({ behavior: 'smooth' });
    }
}

/**
 * Load more results (next page)
 * Fetches the next page of search results and appends them to the current display
 * Requirements: 9.3
 */
async function loadMore() {
    if (!currentPagination || !faceEmbedding) {
        console.error('Cannot load more: missing pagination data or face embedding');
        return;
    }
    
    const albumSelect = document.getElementById('albumSelect');
    const loading = document.getElementById('loading');
    
    if (!albumSelect || !loading) {
        console.error('Required DOM elements not found');
        return;
    }
    
    const albumId = albumSelect.value;
    if (!albumId) {
        alert('Pilih album terlebih dahulu');
        return;
    }
    
    // Increment page
    currentPage++;
    
    console.log('Loading more results, page:', currentPage);
    
    // Display loading indicator
    loading.style.display = 'block';
    
    // Disable Load More button temporarily
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.style.opacity = '0.5';
        loadMoreBtn.style.pointerEvents = 'none';
    }
    
    try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }
        
        // Send POST request with page parameter
        const response = await fetch('/face-scan/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                embedding_vector: faceEmbedding,
                album_id: parseInt(albumId),
                page: currentPage
            })
        });
        
        if (!response.ok) {
            if (response.status === 503) {
                showError('Service temporarily unavailable. Please try again later');
            } else {
                showError('Failed to load more results. Please try again');
            }
            // Revert page increment on error
            currentPage--;
            return;
        }
        
        const data = await response.json();
        
        console.log('Load more completed successfully:', data);
        
        // Update pagination metadata
        currentPagination = data.pagination;
        
        // Append results to existing display
        displayResults(data.photos || [], data.pagination, true);
        
    } catch (error) {
        console.error('Load more error:', error);
        
        if (error instanceof TypeError || error.message.includes('fetch') || error.message.includes('Network')) {
            showError('Service temporarily unavailable. Please try again later');
        } else {
            showError('Failed to load more results. Please try again');
        }
        
        // Revert page increment on error
        currentPage--;
    } finally {
        // Hide loading indicator
        loading.style.display = 'none';
    }
}

/**
 * Add photo to shopping cart
 * Placeholder function for cart functionality
 * Requirements: 6.6
 * 
 * @param {number} photoId - ID of the photo to add to cart
 */
function addToCart(photoId) {
    console.log('Adding photo to cart:', photoId);
    
    // TODO: Implement actual cart functionality
    // This would typically make an AJAX request to add the photo to the user's cart
    alert(`Foto dengan ID ${photoId} akan ditambahkan ke keranjang (fitur belum diimplementasi)`);
}

// Export functions for testing (ES6 modules)
export { 
    loadModels, 
    extractFaceEmbedding, 
    getModelsLoadedStatus, 
    resetModelsLoadedStatus, 
    initializeCameraCapture, 
    initializeFileUpload,
    initializeSearchFunctionality,
    displayResults,
    addToCart,
    getFaceEmbedding,
    setFaceEmbedding,
    showError,
    loadMore
};

// Initialize when script loads in browser
if (typeof window !== 'undefined') {
    if (typeof faceapi !== 'undefined') {
        loadModels();
    } else {
        console.error('face-api.js library not loaded');
    }
    
    // Initialize all functionality when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initializeCameraCapture();
            initializeFileUpload();
            initializeSearchFunctionality();
        });
    } else {
        initializeCameraCapture();
        initializeFileUpload();
        initializeSearchFunctionality();
    }
}
