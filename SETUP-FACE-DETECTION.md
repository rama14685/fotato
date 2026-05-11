# Setup Face Detection untuk Foto

## Masalah
Pesan "Album ini belum memiliki data wajah" muncul karena foto-foto belum diproses face detection.

## Solusi: Pilih Salah Satu Metode

### ✅ METODE 1: Node.js (RECOMMENDED - Lebih Mudah)

#### Step 1: Install Node.js Dependencies
```bash
npm install
# atau
npm install canvas face-api.js
```

#### Step 2: Test Node.js Script
```bash
node scripts/detect-faces-nodejs.js "storage/app/albums/49/originals/test.jpeg"
```

Expected output:
```json
{"embeddings":[[0.123,-0.456,...]],"face_count":1}
```

#### Step 3: Dispatch Jobs
```bash
php dispatch-face-detection-jobs.php
```

#### Step 4: Start Queue Worker
```bash
php artisan queue:work --tries=3
```

---

### ⚙️ METODE 2: Python (Alternatif - Lebih Akurat)

#### Step 1: Install Python
Download dari: https://www.python.org/downloads/

#### Step 2: Install face_recognition
```bash
pip install face_recognition
# atau
python -m pip install face_recognition
```

#### Step 3: Test Python Script
```bash
python scripts/detect_faces.py "storage/app/albums/49/originals/test.jpeg"
```

#### Step 4: Dispatch Jobs
```bash
php dispatch-face-detection-jobs.php
```

#### Step 5: Start Queue Worker
```bash
php artisan queue:work --tries=3
```

---

## Monitoring Progress

### Check Status
```bash
php check-face-embeddings.php
```

### Monitor Queue
```bash
# Terminal 1: Queue worker
php artisan queue:work --tries=3

# Terminal 2: Monitor logs
tail -f storage/logs/laravel.log
```

### Check Database
```bash
php artisan tinker
>>> App\Models\Photo::whereHas('faceEmbedding')->count()
>>> App\Models\FaceEmbedding::count()
```

---

## Troubleshooting

### Error: "Node.js not found"
**Solution**: Install Node.js dari https://nodejs.org/

### Error: "Python not found"
**Solution**: Install Python dan tambahkan ke PATH

### Error: "No faces detected"
**Possible causes**:
- Foto tidak mengandung wajah
- Foto terlalu blur
- Pencahayaan buruk
- Wajah terlalu kecil

**Solution**: 
- Gunakan foto dengan wajah yang jelas
- Pastikan resolusi cukup (min 640x480)
- Pencahayaan yang baik

### Jobs Stuck/Not Processing
**Solution**:
```bash
# Restart queue worker
php artisan queue:restart

# Clear failed jobs
php artisan queue:flush

# Re-dispatch jobs
php dispatch-face-detection-jobs.php
```

### Memory Error
**Solution**: Increase PHP memory limit in `php.ini`:
```ini
memory_limit = 512M
```

---

## Performance Tips

### Process in Batches
Jika ada banyak foto (>1000), process dalam batch:

```php
// dispatch-face-detection-jobs.php
$photos = Photo::whereDoesntHave('faceEmbedding')
    ->limit(100)  // Process 100 at a time
    ->get();
```

### Use Multiple Workers
```bash
# Terminal 1
php artisan queue:work --queue=default --tries=3

# Terminal 2
php artisan queue:work --queue=default --tries=3

# Terminal 3
php artisan queue:work --queue=default --tries=3
```

### Monitor Queue Status
```bash
php artisan queue:work --verbose
```

---

## Expected Processing Time

- **Node.js**: ~2-5 seconds per photo
- **Python**: ~2-5 seconds per photo
- **1000 photos**: ~1-2 hours (with 3 workers)

---

## Verification

After processing, verify:

1. **Check embeddings count**:
```bash
php check-face-embeddings.php
```

2. **Test customer dashboard**:
- Login as customer
- Search for album
- Select album
- Should see filtered photos with similarity badges

3. **Check logs**:
```bash
tail -f storage/logs/laravel.log | grep "Face embedding stored"
```

---

## Quick Start (Recommended)

```bash
# 1. Install Node.js dependencies
npm install

# 2. Test face detection
node scripts/detect-faces-nodejs.js "storage/app/albums/49/originals/test.jpeg"

# 3. Dispatch jobs for all photos
php dispatch-face-detection-jobs.php

# 4. Start queue worker (keep this running)
php artisan queue:work --tries=3

# 5. Monitor progress (in another terminal)
php check-face-embeddings.php
```

---

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log`
2. Verify Node.js/Python is installed: `node --version` or `python --version`
3. Test scripts manually with sample photo
4. Check queue status: `php artisan queue:work --verbose`
