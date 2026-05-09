<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto - {{ $album->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .upload-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .album-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 12px;
            color: white;
            margin-bottom: 30px;
        }

        .album-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .album-header p {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .upload-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            margin-bottom: 30px;
        }

        .drop-zone {
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 60px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .drop-zone.drag-over {
            border-color: #667eea;
            background: #eef2ff;
        }

        .drop-zone-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .drop-zone-text h3 {
            font-size: 1.2rem;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .drop-zone-text p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .upload-methods {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .upload-method {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-method:hover {
            border-color: #667eea;
            background: #eef2ff;
        }

        .upload-method input {
            display: none;
        }

        .upload-method-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .upload-method-text {
            font-size: 0.9rem;
            color: #4b5563;
        }

        .price-section {
            margin-top: 30px;
            padding: 20px;
            background: #f3f4f6;
            border-radius: 8px;
        }

        .price-section label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1f2937;
        }

        .price-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .upload-progress {
            margin-top: 30px;
        }

        .progress-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }

        .progress-item.success {
            border-left-color: #10b981;
        }

        .progress-item.error {
            border-left-color: #ef4444;
        }

        .progress-item-name {
            font-weight: 500;
            color: #1f2937;
        }

        .progress-item-status {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .progress-bar {
            width: 200px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: #667eea;
            transition: width 0.2s ease;
            border-radius: 2px;
        }

        .uploaded-photos {
            margin-top: 40px;
        }

        .uploaded-photos h2 {
            font-size: 1.3rem;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            background: #f3f4f6;
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-item-delete {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.2s ease;
        }

        .photo-item:hover .photo-item-delete {
            display: flex;
        }

        .photo-item-delete:hover {
            background: rgba(239, 68, 68, 1);
        }

        .button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .button:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .button-secondary {
            background: #6b7280;
        }

        .button-secondary:hover {
            background: #4b5563;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            justify-content: center;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #7f1d1d;
        }

        .alert-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e3a8a;
        }
    </style>
</head>
<body style="background: #f3f4f6;">
    <div class="upload-container">
        <!-- Breadcrumb -->
        <div style="margin-bottom: 20px;">
            <a href="{{ route('admin.albums.index') }}" style="color: #667eea; text-decoration: none;">← Kembali ke Album</a>
        </div>

        <!-- Album Header -->
        <div class="album-header">
            <h1>📸 {{ $album->title }}</h1>
            <p>{{ $album->location }} • {{ $album->event_date->format('d M Y') }}</p>
            <p style="margin-top: 10px; font-size: 0.9rem;">Total Foto: <strong>{{ $album->photos->count() }}</strong></p>
        </div>

        <!-- Upload Card -->
        <div class="upload-card">
            <h2 style="font-size: 1.5rem; margin-bottom: 20px; color: #1f2937;">Upload Foto</h2>

            <div id="messageContainer"></div>

            <!-- Drop Zone -->
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="drop-zone" id="dropZone">
                    <div class="drop-zone-icon">📁</div>
                    <div class="drop-zone-text">
                        <h3>Drag dan drop foto di sini</h3>
                        <p>atau klik untuk memilih file</p>
                    </div>
                </div>

                <!-- Upload Methods -->
                <div class="upload-methods">
                    <label class="upload-method">
                        <div class="upload-method-icon">📷</div>
                        <div class="upload-method-text">Pilih Satu File</div>
                        <input type="file" id="singleFile" accept="image/*" multiple>
                    </label>

                    <label class="upload-method">
                        <div class="upload-method-icon">📂</div>
                        <div class="upload-method-text">Pilih Folder</div>
                        <input type="file" id="folderFile" accept="image/*" webkitdirectory directory mozdirectory>
                    </label>
                </div>

                <!-- Price Section -->
                <div class="price-section">
                    <label for="price">Harga per Foto (Rp)</label>
                    <input type="number" id="price" name="price" class="price-input" placeholder="Contoh: 50000" value="50000" required>
                </div>

                <!-- Submit Button -->
                <div class="action-buttons">
                    <button type="submit" class="button" id="submitBtn" disabled>
                        🚀 Upload Foto
                    </button>
                    <a href="{{ route('admin.albums.show', $album) }}" class="button button-secondary">Selesai</a>
                </div>
            </form>

            <!-- Upload Progress -->
            <div class="upload-progress" id="uploadProgress" style="display: none;"></div>
        </div>

        <!-- Uploaded Photos -->
        <div class="uploaded-photos" id="uploadedPhotos" style="display: none;">
            <h2>Foto yang Diupload</h2>
            <div class="photo-grid" id="photoGrid"></div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const uploadForm = document.getElementById('uploadForm');
        const singleFile = document.getElementById('singleFile');
        const folderFile = document.getElementById('folderFile');
        const priceInput = document.getElementById('price');
        const submitBtn = document.getElementById('submitBtn');
        const uploadProgress = document.getElementById('uploadProgress');
        const messageContainer = document.getElementById('messageContainer');
        const uploadedPhotos = document.getElementById('uploadedPhotos');
        const photoGrid = document.getElementById('photoGrid');

        let selectedFiles = [];

        // Drag and drop events
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            selectedFiles = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
            updateSubmitButton();
            showFileInfo();
        });

        // Click to upload
        dropZone.addEventListener('click', () => singleFile.click());

        singleFile.addEventListener('change', (e) => {
            selectedFiles = Array.from(e.target.files);
            updateSubmitButton();
            showFileInfo();
        });

        folderFile.addEventListener('change', (e) => {
            selectedFiles = Array.from(e.target.files);
            updateSubmitButton();
            showFileInfo();
        });

        function updateSubmitButton() {
            submitBtn.disabled = selectedFiles.length === 0;
            submitBtn.textContent = selectedFiles.length > 0 
                ? `🚀 Upload ${selectedFiles.length} Foto` 
                : '🚀 Upload Foto';
        }

        function showFileInfo() {
            const totalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
            const sizeMB = (totalSize / 1024 / 1024).toFixed(2);
            dropZone.innerHTML = `
                <div class="drop-zone-icon">✅</div>
                <div class="drop-zone-text">
                    <h3>${selectedFiles.length} file dipilih</h3>
                    <p>${sizeMB} MB total</p>
                </div>
            `;
        }

        // Form submission
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (selectedFiles.length === 0) {
                showMessage('Pilih file terlebih dahulu', 'error');
                return;
            }

            const formData = new FormData();
            selectedFiles.forEach(file => {
                formData.append('photos[]', file);
            });
            formData.append('price', priceInput.value);

            submitBtn.disabled = true;
            uploadProgress.style.display = 'block';
            messageContainer.innerHTML = '';

            try {
                const response = await fetch(`{{ route('admin.albums.upload', $album) }}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    displayUploadedPhotos(data.photos);
                    selectedFiles = [];
                    updateSubmitButton();
                    singleFile.value = '';
                    folderFile.value = '';
                    dropZone.innerHTML = `
                        <div class="drop-zone-icon">📁</div>
                        <div class="drop-zone-text">
                            <h3>Drag dan drop foto di sini</h3>
                            <p>atau klik untuk memilih file</p>
                        </div>
                    `;
                } else {
                    showMessage(data.message, 'error');
                }

                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(error => {
                        showMessage(error, 'error');
                    });
                }

            } catch (error) {
                showMessage('Error: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                uploadProgress.style.display = 'none';
            }
        });

        function showMessage(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-error' : 'alert-info');
            const alertHTML = `<div class="alert ${alertClass}">${message}</div>`;
            messageContainer.innerHTML += alertHTML;
        }

        function displayUploadedPhotos(photos) {
            uploadedPhotos.style.display = 'block';
            photos.forEach(photo => {
                const photoElement = document.createElement('div');
                photoElement.className = 'photo-item';
                photoElement.innerHTML = `
                    <img src="{{ asset('storage') }}/${photo.filename.replace(photo.original_name, photo.filename)}" alt="Photo">
                    <button type="button" class="photo-item-delete" data-id="${photo.id}" title="Hapus">×</button>
                `;
                photoGrid.appendChild(photoElement);
            });
        }

        // Delete photo
        document.addEventListener('click', async (e) => {
            if (e.target.classList.contains('photo-item-delete')) {
                e.preventDefault();
                const photoId = e.target.dataset.id;
                if (confirm('Hapus foto ini?')) {
                    try {
                        const response = await fetch(`/admin/photos/${photoId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            e.target.closest('.photo-item').remove();
                            showMessage(data.message, 'success');
                        }
                    } catch (error) {
                        showMessage('Error menghapus foto', 'error');
                    }
                }
            }
        });
    </script>
</body>
</html>
