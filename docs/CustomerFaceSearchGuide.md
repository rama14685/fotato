# Customer Face Search - Implementation Guide

## Overview

Fitur Customer Face Search memungkinkan pembeli untuk mencari dan melihat foto mereka secara otomatis menggunakan teknologi face recognition. Sistem akan mencocokkan wajah pembeli (yang tersimpan saat registrasi) dengan wajah dalam foto album.

## Architecture

```
Customer Registration (Step 2)
    ↓
Face Embedding Stored (Encrypted)
    ↓
Admin Uploads Photos
    ↓
Background Job: Face Detection
    ↓
Face Embeddings Stored for Photos
    ↓
Customer Searches Albums
    ↓
Customer Selects Album
    ↓
Face Matching Service (Cosine Similarity)
    ↓
Filtered Photos Displayed (Sorted by Similarity)
```

## Components

### 1. Face Detection Job (`ProcessPhotoFaceDetection`)

**Purpose**: Automatically detect faces in uploaded photos and extract embeddings.

**Location**: `app/Jobs/ProcessPhotoFaceDetection.php`

**Trigger**: Dispatched when admin uploads photos

**Process**:
1. Load photo from storage
2. Call Python script for face detection
3. Extract 128-dimensional face embeddings
4. Encrypt and store embeddings in database
5. Retry up to 3 times on failure

**Dependencies**:
- Python 3.x
- face_recognition library (`pip install face_recognition`)
- Python script: `scripts/detect_faces.py`

### 2. Face Matching Service

**Purpose**: Compare customer face embedding with photo embeddings using cosine similarity.

**Location**: `app/Services/FaceMatching/FaceMatchingService.php`

**Already Implemented**: ✅ (from face-matching-service spec)

**Key Methods**:
- `matchFaces($customerEmbedding, $photoEmbeddings, $threshold)`: Returns matched photos sorted by similarity

**Algorithm**: Cosine Similarity
```
similarity = dot_product(A, B) / (magnitude(A) × magnitude(B))
```

**Default Threshold**: 0.6 (60% similarity)

### 3. Customer Face Search Controller

**Purpose**: Handle customer dashboard, album search, and photo filtering.

**Location**: `app/Http/Controllers/CustomerFaceSearchController.php`

**Routes**:
- `GET /customer/dashboard` - Display search form
- `POST /customer/search-albums` - Search albums by name/date
- `GET /customer/album/{id}` - View filtered photos in album
- `GET /customer/album/{id}/all` - View all photos (no filtering)

**Key Methods**:
- `index()`: Display customer dashboard
- `searchAlbums()`: Search albums by event name and/or date
- `viewAlbum()`: Display photos filtered by face matching
- `viewAllPhotos()`: Display all photos without filtering
- `getCustomerEmbedding()`: Decrypt and validate customer's face embedding

### 4. Views

**Customer Dashboard** (`resources/views/customer/dashboard.blade.php`):
- Search form with event name and date fields
- Instructions on how to use the system

**Album Results** (`resources/views/customer/albums.blade.php`):
- Display albums matching search criteria
- Album cards with metadata (title, date, location, photographer, photo count)

**Filtered Photos** (`resources/views/customer/photos.blade.php`):
- Display photos matching customer's face
- Similarity percentage badge on each photo
- Add to cart functionality
- Pagination for large result sets
- Fallback to "View All Photos" if no matches

## Database Schema

### `user_face_embeddings` Table
```sql
id                  BIGINT PRIMARY KEY
user_id             BIGINT (FK to users.id)
embedding_vector    TEXT (encrypted JSON array of 128 floats)
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

### `face_embeddings` Table
```sql
id                  BIGINT PRIMARY KEY
photo_id            BIGINT (FK to photos.id)
embedding_vector    TEXT (encrypted JSON array of 128 floats)
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

### `users` Table (existing)
```sql
face_embedding_id   BIGINT NULLABLE (FK to user_face_embeddings.id)
```

## Installation & Setup

### 1. Install Python Dependencies

```bash
# Install Python 3 (if not already installed)
# Windows: Download from python.org
# Linux: sudo apt-get install python3 python3-pip

# Install face_recognition library
pip install face_recognition

# Verify installation
python scripts/detect_faces.py --help
```

### 2. Configure Queue Worker

Face detection runs as background jobs. Configure queue worker:

```bash
# .env
QUEUE_CONNECTION=database

# Run migrations for jobs table
php artisan queue:table
php artisan migrate

# Start queue worker
php artisan queue:work --tries=3
```

### 3. Test Face Detection

```bash
# Test Python script manually
python scripts/detect_faces.py "path/to/test/photo.jpg"

# Expected output:
# {"embeddings": [[0.123, -0.456, ...]], "face_count": 1}
```

### 4. Dispatch Job for Existing Photos

If you have existing photos without face embeddings:

```php
use App\Models\Photo;
use App\Jobs\ProcessPhotoFaceDetection;

// Dispatch jobs for all photos
Photo::whereDoesntHave('faceEmbedding')->chunk(100, function ($photos) {
    foreach ($photos as $photo) {
        ProcessPhotoFaceDetection::dispatch($photo);
    }
});
```

## Usage Flow

### For Customers:

1. **Register with Face Scan**
   - Complete registration with face scan (Step 2)
   - Face embedding stored encrypted in database

2. **Search for Albums**
   - Login and access customer dashboard
   - Enter event name and/or date
   - Click "Cari Album"

3. **View Matched Photos**
   - Select album from search results
   - System automatically filters photos containing customer's face
   - Photos sorted by similarity score (highest first)
   - Each photo shows similarity percentage badge

4. **Purchase Photos**
   - Add matched photos to cart
   - Proceed to checkout and payment

### For Admins:

1. **Upload Photos**
   - Upload photos to album (via admin panel)
   - Face detection job automatically dispatched

2. **Monitor Processing**
   - Check queue status: `php artisan queue:work`
   - Check logs: `storage/logs/laravel.log`

3. **Verify Face Embeddings**
   ```php
   // Check photos with face embeddings
   $photosWithFaces = Photo::whereHas('faceEmbedding')->count();
   
   // Check photos without face embeddings
   $photosWithoutFaces = Photo::whereDoesntHave('faceEmbedding')->count();
   ```

## Configuration

### Similarity Threshold

Default threshold is 0.6 (60% similarity). To adjust:

```php
// config/face_matching.php
'similarity_threshold' => env('FACE_MATCHING_THRESHOLD', 0.6),
```

Or override per request:
```
GET /customer/album/123?threshold=0.7
```

### Performance Settings

```php
// config/face_matching.php
'performance' => [
    'max_processing_time_seconds' => 10,
    'chunk_size_large_albums' => 500,
    'large_album_threshold' => 5000,
],
```

## Troubleshooting

### No Faces Detected

**Problem**: Python script returns empty embeddings array

**Solutions**:
1. Check photo quality (resolution, lighting)
2. Verify face is clearly visible
3. Try different face detection model in Python script
4. Check Python script logs

### Face Matching Too Slow

**Problem**: Album with 1000+ photos takes >10 seconds

**Solutions**:
1. Enable Redis for caching
2. Increase PHP memory limit
3. Use chunked processing for large albums
4. Optimize database indexes

### Customer Has No Face Embedding

**Problem**: Customer redirected to complete face registration

**Solutions**:
1. Verify customer completed Step 2 of registration
2. Check `user_face_embeddings` table for customer's record
3. Check `users.face_embedding_id` is not null

### Python Script Not Found

**Problem**: Job fails with "Python script not found"

**Solutions**:
1. Verify `scripts/detect_faces.py` exists
2. Check file permissions (executable)
3. Verify Python is in system PATH

## Security Considerations

1. **Encryption**: All face embeddings stored encrypted using Laravel Crypt
2. **Authentication**: Customer dashboard requires authentication
3. **Authorization**: Customers can only access their own face data
4. **HTTPS**: All face data transmitted over HTTPS
5. **Rate Limiting**: Search limited to prevent abuse
6. **Privacy**: Raw embeddings never logged or exposed in API

## Performance Benchmarks

- **Face Detection**: ~2-5 seconds per photo
- **Face Matching**: <10 seconds for 1000 photos
- **Album Search**: <1 second
- **Photo Filtering**: <2 seconds for 500 photos

## Testing

### Unit Tests

```bash
# Test face matching service
php artisan test --filter=FaceMatching

# Test customer controller
php artisan test --filter=CustomerFaceSearch
```

### Integration Tests

```bash
# Test complete flow
php artisan test --filter=CustomerFaceSearchIntegration
```

### Manual Testing

1. Register as customer with face scan
2. Admin uploads photos with faces
3. Wait for face detection jobs to complete
4. Search for albums as customer
5. Verify filtered photos contain customer's face
6. Verify similarity scores are accurate

## Future Enhancements

1. **Multiple Faces Per Photo**: Support photos with multiple people
2. **Face Clustering**: Group similar faces together
3. **Real-time Face Detection**: Use JavaScript for instant feedback
4. **Advanced Filters**: Filter by similarity range, date range, etc.
5. **Face Recognition API**: Expose API for mobile apps
6. **Batch Download**: Download all matched photos at once

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Check queue status: `php artisan queue:work`
3. Verify Python installation: `python --version`
4. Test face detection manually: `python scripts/detect_faces.py test.jpg`
