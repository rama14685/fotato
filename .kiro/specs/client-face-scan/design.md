# Dokumen Desain: Client Face Scan

## 1. Overview

### 1.1 Tujuan Fitur
Fitur **Client Face Scan** memungkinkan pembeli/client untuk menemukan foto mereka sendiri di sebuah event dengan cara:
1. Melakukan scan wajah menggunakan kamera atau upload foto wajah
2. Memilih album/event yang ingin dicari
3. Sistem mencocokkan wajah client dengan foto-foto dalam album menggunakan face recognition
4. Menampilkan foto-foto yang mengandung wajah client untuk dibeli

### 1.2 Scope
Fokus pada **sisi client** (pembeli), khususnya:
- Interface untuk capture/upload foto wajah
- Proses ekstraksi face embedding dari foto client
- Algoritma matching wajah client dengan foto-foto dalam album
- Tampilan hasil pencarian foto

### 1.3 Asumsi
- Database sudah memiliki face embeddings untuk setiap foto dalam album (diproses oleh fotografer/admin)
- Face recognition menggunakan library eksternal (misalnya face-api.js di frontend atau Python backend)
- Matching dilakukan dengan menghitung similarity (cosine similarity) antara embedding wajah client dengan embedding di database

---

## 2. High-Level Design

### 2.1 Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENT BROWSER                        │
├─────────────────────────────────────────────────────────────┤
│  1. Face Scan Page (Blade View)                             │
│     - Camera capture / File upload                           │
│     - Album selection dropdown                               │
│     - face-api.js (face detection & embedding extraction)    │
│                                                              │
│  2. Search Results Page                                      │
│     - Display matched photos with similarity scores          │
│     - Add to cart functionality                              │
└──────────────────┬──────────────────────────────────────────┘
                   │ HTTP Request (POST /face-scan/search)
                   │ Payload: { embedding_vector, album_id }
                   ▼
┌─────────────────────────────────────────────────────────────┐
│                     LARAVEL BACKEND                          │
├─────────────────────────────────────────────────────────────┤
│  3. FaceScanController                                       │
│     - Receive client face embedding                          │
│     - Query face_embeddings table for selected album         │
│     - Calculate cosine similarity for each photo             │
│     - Return matched photos (similarity > threshold)         │
│                                                              │
│  4. Models: Photo, FaceEmbedding, Album                      │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│                         DATABASE                             │
├─────────────────────────────────────────────────────────────┤
│  - albums (id, title, location, event_date, ...)            │
│  - photos (id, album_id, original_path, watermark_path, ...)│
│  - face_embeddings (id, photo_id, embedding_vector)         │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Komponen Utama

#### 2.2.1 Frontend Components
- **Face Capture Component**: Interface untuk capture foto wajah via webcam atau upload file
- **Album Selector**: Dropdown untuk memilih album/event
- **Face Detection Module**: Menggunakan face-api.js untuk:
  - Deteksi wajah dari foto client
  - Ekstraksi face embedding (128-dimensional vector)
- **Results Display**: Menampilkan foto-foto yang cocok dengan score similarity

#### 2.2.2 Backend Components
- **FaceScanController**: Handle request pencarian foto berdasarkan face embedding
- **FaceMatchingService**: Service class untuk algoritma matching
- **FaceEmbedding Model**: Existing model untuk akses data embedding
- **Photo Model**: Existing model dengan relasi ke FaceEmbedding

### 2.3 Alur Data (Data Flow)

```
1. Client opens face scan page
   ↓
2. Client captures/uploads face photo
   ↓
3. face-api.js detects face and extracts embedding vector (128 floats)
   ↓
4. Client selects album/event
   ↓
5. Frontend sends POST request to /face-scan/search
   Payload: { embedding_vector: [0.123, -0.456, ...], album_id: 5 }
   ↓
6. FaceScanController receives request
   ↓
7. Query all face_embeddings for photos in selected album
   ↓
8. For each photo embedding:
   - Calculate cosine similarity with client embedding
   - Filter by threshold (e.g., similarity > 0.6)
   ↓
9. Sort results by similarity score (descending)
   ↓
10. Return matched photos with metadata
    ↓
11. Frontend displays results with "Add to Cart" buttons
```

### 2.4 Data Model

#### Existing Tables (No Changes Needed)
```sql
-- albums table
id, photographer_id, title, location, event_date, created_at, updated_at

-- photos table
id, album_id, original_path, watermark_path, price, created_at, updated_at

-- face_embeddings table
id, photo_id, embedding_vector (TEXT/JSON), created_at, updated_at
```

**Note**: `embedding_vector` disimpan sebagai JSON string array, contoh:
```json
"[0.123, -0.456, 0.789, ..., 0.321]"
```

---

## 3. Low-Level Design

### 3.1 Frontend Implementation

#### 3.1.1 Face Scan Page (Blade View)
**File**: `resources/views/face-scan/index.blade.php`

```html
<x-app-layout>
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Cari Foto Anda</h1>
        
        <!-- Step 1: Capture/Upload Face -->
        <div class="mb-6">
            <h2 class="text-xl mb-2">1. Scan Wajah Anda</h2>
            <div class="flex gap-4">
                <button id="startCamera" class="btn-primary">Gunakan Kamera</button>
                <input type="file" id="uploadFace" accept="image/*" class="btn-secondary">
            </div>
            <video id="video" width="320" height="240" autoplay style="display:none;"></video>
            <canvas id="canvas" width="320" height="240"></canvas>
            <img id="preview" style="max-width: 320px; display:none;">
        </div>

        <!-- Step 2: Select Album -->
        <div class="mb-6">
            <h2 class="text-xl mb-2">2. Pilih Event/Album</h2>
            <select id="albumSelect" class="form-select">
                <option value="">-- Pilih Album --</option>
                @foreach($albums as $album)
                    <option value="{{ $album->id }}">
                        {{ $album->title }} - {{ $album->location }} ({{ $album->event_date->format('d M Y') }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Step 3: Search Button -->
        <button id="searchBtn" class="btn-primary" disabled>Cari Foto Saya</button>
        <div id="loading" style="display:none;">Mencari foto...</div>

        <!-- Step 4: Results -->
        <div id="results" class="grid grid-cols-3 gap-4 mt-6"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="{{ asset('js/face-scan.js') }}"></script>
</x-app-layout>
```

#### 3.1.2 Face Scan JavaScript
**File**: `public/js/face-scan.js`

```javascript
let faceEmbedding = null;

// Load face-api.js models
async function loadModels() {
    await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
    await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
    await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
}

// Extract face embedding from image
async function extractFaceEmbedding(imageElement) {
    const detection = await faceapi
        .detectSingleFace(imageElement)
        .withFaceLandmarks()
        .withFaceDescriptor();
    
    if (!detection) {
        alert('Wajah tidak terdeteksi. Coba lagi.');
        return null;
    }
    
    return Array.from(detection.descriptor); // 128-dimensional array
}

// Camera capture
document.getElementById('startCamera').addEventListener('click', async () => {
    const video = document.getElementById('video');
    video.style.display = 'block';
    
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    video.srcObject = stream;
    
    // Capture button
    setTimeout(() => {
        const canvas = document.getElementById('canvas');
        canvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
        stream.getTracks().forEach(track => track.stop());
        video.style.display = 'none';
        
        extractFaceEmbedding(canvas).then(embedding => {
            faceEmbedding = embedding;
            document.getElementById('searchBtn').disabled = false;
        });
    }, 3000);
});

// File upload
document.getElementById('uploadFace').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    const img = document.getElementById('preview');
    img.src = URL.createObjectURL(file);
    img.style.display = 'block';
    
    img.onload = async () => {
        faceEmbedding = await extractFaceEmbedding(img);
        document.getElementById('searchBtn').disabled = false;
    };
});

// Search photos
document.getElementById('searchBtn').addEventListener('click', async () => {
    const albumId = document.getElementById('albumSelect').value;
    if (!albumId) {
        alert('Pilih album terlebih dahulu');
        return;
    }
    
    document.getElementById('loading').style.display = 'block';
    
    const response = await fetch('/face-scan/search', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            embedding_vector: faceEmbedding,
            album_id: albumId
        })
    });
    
    const data = await response.json();
    document.getElementById('loading').style.display = 'none';
    
    displayResults(data.photos);
});

function displayResults(photos) {
    const resultsDiv = document.getElementById('results');
    resultsDiv.innerHTML = '';
    
    if (photos.length === 0) {
        resultsDiv.innerHTML = '<p>Tidak ada foto yang cocok ditemukan.</p>';
        return;
    }
    
    photos.forEach(photo => {
        const card = `
            <div class="border p-4 rounded">
                <img src="${photo.watermark_path}" class="w-full mb-2">
                <p class="text-sm">Similarity: ${(photo.similarity * 100).toFixed(1)}%</p>
                <p class="font-bold">Rp ${photo.price}</p>
                <button onclick="addToCart(${photo.id})" class="btn-primary mt-2">
                    Tambah ke Keranjang
                </button>
            </div>
        `;
        resultsDiv.innerHTML += card;
    });
}

// Initialize
loadModels();
```

### 3.2 Backend Implementation

#### 3.2.1 FaceScanController
**File**: `app/Http/Controllers/FaceScanController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Models\FaceEmbedding;
use Illuminate\Http\Request;

class FaceScanController extends Controller
{
    /**
     * Show face scan page
     */
    public function index()
    {
        $albums = Album::with('photographer')
            ->orderBy('event_date', 'desc')
            ->get();
        
        return view('face-scan.index', compact('albums'));
    }

    /**
     * Search photos by face embedding
     */
    public function search(Request $request)
    {
        $request->validate([
            'embedding_vector' => 'required|array|size:128',
            'embedding_vector.*' => 'numeric',
            'album_id' => 'required|exists:albums,id'
        ]);

        $clientEmbedding = $request->embedding_vector;
        $albumId = $request->album_id;

        // Get all photos with face embeddings in the selected album
        $photos = Photo::where('album_id', $albumId)
            ->whereHas('faceEmbedding')
            ->with('faceEmbedding')
            ->get();

        $matchedPhotos = [];

        foreach ($photos as $photo) {
            $photoEmbedding = json_decode($photo->faceEmbedding->embedding_vector, true);
            
            // Calculate cosine similarity
            $similarity = $this->cosineSimilarity($clientEmbedding, $photoEmbedding);
            
            // Threshold: only include if similarity > 0.6
            if ($similarity > 0.6) {
                $matchedPhotos[] = [
                    'id' => $photo->id,
                    'watermark_path' => asset('storage/' . $photo->watermark_path),
                    'price' => $photo->price,
                    'similarity' => $similarity
                ];
            }
        }

        // Sort by similarity (descending)
        usort($matchedPhotos, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return response()->json([
            'success' => true,
            'photos' => $matchedPhotos
        ]);
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        if (count($vec1) !== count($vec2)) {
            throw new \InvalidArgumentException('Vectors must have the same dimension');
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($vec1); $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $magnitude1 += $vec1[$i] ** 2;
            $magnitude2 += $vec2[$i] ** 2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}
```

#### 3.2.2 Routes
**File**: `routes/web.php`

```php
use App\Http\Controllers\FaceScanController;

Route::middleware(['auth'])->group(function () {
    Route::get('/face-scan', [FaceScanController::class, 'index'])->name('face-scan.index');
    Route::post('/face-scan/search', [FaceScanController::class, 'search'])->name('face-scan.search');
});
```

### 3.3 Algorithm: Cosine Similarity

**Formula**:
```
similarity(A, B) = (A · B) / (||A|| × ||B||)

Where:
- A · B = dot product = Σ(A[i] × B[i])
- ||A|| = magnitude of A = √(Σ(A[i]²))
- ||B|| = magnitude of B = √(Σ(B[i]²))
```

**Pseudocode**:
```
function cosineSimilarity(vector1, vector2):
    if length(vector1) != length(vector2):
        throw error "Vectors must have same dimension"
    
    dotProduct = 0
    magnitude1 = 0
    magnitude2 = 0
    
    for i from 0 to length(vector1) - 1:
        dotProduct += vector1[i] * vector2[i]
        magnitude1 += vector1[i]^2
        magnitude2 += vector2[i]^2
    
    magnitude1 = sqrt(magnitude1)
    magnitude2 = sqrt(magnitude2)
    
    if magnitude1 == 0 or magnitude2 == 0:
        return 0
    
    return dotProduct / (magnitude1 * magnitude2)
```

**Threshold**: Similarity > 0.6 dianggap sebagai match (dapat disesuaikan)

---

## 4. Security Considerations

### 4.1 Privacy
- **Face data tidak disimpan**: Embedding vector client hanya digunakan untuk matching, tidak disimpan di database
- **HTTPS required**: Transmisi embedding vector harus melalui HTTPS
- **Authentication**: Fitur hanya dapat diakses oleh user yang sudah login

### 4.2 Input Validation
- Validate embedding vector: harus array dengan 128 elemen numerik
- Validate album_id: harus exist di database
- Rate limiting: batasi jumlah request per user per menit

### 4.3 Performance
- **Indexing**: Pertimbangkan indexing pada `album_id` di tabel `photos`
- **Caching**: Cache daftar album untuk mengurangi query
- **Pagination**: Jika hasil matching > 50 foto, gunakan pagination

---

## 5. Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Image Format Acceptance

*For any* image file with a valid format (JPEG, PNG, WebP), the system SHALL accept the file for face scanning.

**Validates: Requirement 1.3**

### Property 2: Embedding Dimension Consistency

*For any* detected face, the extracted embedding vector SHALL have exactly 128 dimensions.

**Validates: Requirement 2.2**

### Property 3: Album Display Completeness

*For any* album displayed in the selection list, the rendered output SHALL contain the album title, location, and event date.

**Validates: Requirement 3.2**

### Property 4: Album Ordering

*For any* set of albums displayed, they SHALL be ordered by event date in descending order (most recent first).

**Validates: Requirement 3.3**

### Property 5: Search Request Payload Completeness

*For any* search request sent to the backend, the payload SHALL contain both embedding_vector and album_id fields.

**Validates: Requirement 4.1**

### Property 6: Similarity Calculation Completeness

*For any* album with N face embeddings, the face matching service SHALL calculate cosine similarity for all N embeddings against the client embedding.

**Validates: Requirement 4.3**

### Property 7: Threshold Filtering

*For any* search results returned, all photos SHALL have a similarity score greater than 0.6.

**Validates: Requirement 4.4**

### Property 8: Result Sorting

*For any* set of matched photos returned, they SHALL be sorted by similarity score in descending order.

**Validates: Requirements 4.5, 6.3**

### Property 9: Cosine Similarity Symmetry

*For any* two embedding vectors A and B, cosineSimilarity(A, B) SHALL equal cosineSimilarity(B, A).

**Validates: Requirement 5.3**

### Property 10: Cosine Similarity Range

*For any* two valid embedding vectors, the cosine similarity SHALL be in the range [-1, 1].

**Validates: Requirement 5.6**

### Property 11: Cosine Similarity Identity

*For any* embedding vector A, cosineSimilarity(A, A) SHALL equal 1.0.

**Validates: Requirement 5.3**

### Property 12: Zero Vector Handling

*For any* embedding vector A, cosineSimilarity(zero_vector, A) SHALL equal 0, where zero_vector is a vector of all zeros.

**Validates: Requirement 5.4**

### Property 13: Dimension Mismatch Error

*For any* two embedding vectors with different dimensions, the cosineSimilarity function SHALL throw an InvalidArgumentException.

**Validates: Requirement 5.5**

### Property 14: Photo Display Completeness

*For any* matched photo displayed in search results, the rendered output SHALL contain the watermarked image, similarity percentage, and price.

**Validates: Requirement 6.2**

### Property 15: Add to Cart Button Presence

*For any* photo displayed in search results, the rendered output SHALL include an "Add to Cart" button.

**Validates: Requirement 6.5**

### Property 16: Embedding Size Validation

*For any* search request with an embedding_vector that is not exactly 128 elements, the validation SHALL fail.

**Validates: Requirement 7.1**

### Property 17: Embedding Numeric Validation

*For any* search request with an embedding_vector containing non-numeric values, the validation SHALL fail.

**Validates: Requirement 7.2**

### Property 18: Validation Error Response Format

*For any* validation failure, the system SHALL return a 422 Unprocessable Entity response with error details.

**Validates: Requirement 7.4**

### Property 19: Client Embedding Non-Persistence

*For any* search operation, the client face embedding SHALL NOT be stored in the database after the search completes.

**Validates: Requirements 8.2, 8.3**

### Property 20: Error Logging Completeness

*For any* error condition that occurs, the system SHALL log error details for debugging purposes.

**Validates: Requirement 10.5**

---

## 6. Testing Strategy

### 6.1 Unit Tests
- Test `cosineSimilarity()` function dengan berbagai input
- Test validation rules di controller
- Test edge cases (empty vectors, zero vectors, dimension mismatch)

### 6.2 Integration Tests
- Test full flow: upload face → search → get results
- Test dengan album yang memiliki/tidak memiliki face embeddings
- Test dengan threshold berbeda

### 6.3 Property-Based Tests
- Generate random embedding vectors dan verify properties di section 5

---

## 7. Future Enhancements

### 7.1 Multiple Faces
- Deteksi multiple faces dalam satu foto client
- Allow user memilih wajah mana yang ingin dicari

### 7.2 Advanced Filtering
- Filter by date range
- Filter by location
- Filter by price range

### 7.3 Performance Optimization
- Implement vector database (e.g., Milvus, Pinecone) untuk faster similarity search
- Background job untuk pre-compute similarities

### 7.4 Machine Learning Improvements
- Fine-tune face recognition model untuk akurasi lebih tinggi
- Adaptive threshold berdasarkan quality foto
