# Panduan Fitur Admin: Membuat Album & Upload Foto

## 📋 Daftar Isi
1. [Fitur yang Tersedia](#fitur-yang-tersedia)
2. [Cara Membuat Album](#cara-membuat-album)
3. [Cara Upload Foto](#cara-upload-foto)
4. [Struktur File](#struktur-file)
5. [API Endpoints](#api-endpoints)

---

## ✨ Fitur yang Tersedia

### 1. Manajemen Album
- ✅ Membuat album baru
- ✅ Edit informasi album
- ✅ Hapus album (beserta semua foto)
- ✅ Lihat detail album
- ✅ Filter & search album

### 2. Upload Foto
- ✅ Upload per file (multiple selection)
- ✅ Upload per folder sekaligus
- ✅ Drag & drop support
- ✅ Preview foto yang diupload
- ✅ Hapus foto individual
- ✅ Set harga per foto

### 3. Fitur Keamanan
- ✅ Admin authentication required
- ✅ Audit log untuk semua aktivitas
- ✅ Validasi file (type, size)
- ✅ CSRF protection

---

## 📝 Cara Membuat Album

### Langkah 1: Akses Halaman Album
```
URL: http://localhost/fotlist/admin/albums
```

### Langkah 2: Klik "Buat Album Baru"
Isi form dengan informasi berikut:
- **Fotografer**: Pilih fotografer dari dropdown
- **Judul Album**: Nama album (contoh: "CFD Simpang Lima")
- **Lokasi**: Lokasi event (contoh: "Simpang Lima, Semarang")
- **Tanggal Event**: Pilih tanggal dan waktu event

### Langkah 3: Submit
Klik tombol "Buat Album" untuk menyimpan.

### Kode Controller (AlbumController.php)
```php
public function store(StoreAlbumRequest $request): RedirectResponse
{
    $validated = $request->validated();
    $album = Album::create($validated);

    AdminAuditLog::logAction(
        auth()->id(),
        'album_created',
        'album',
        $album->id,
        "Album '{$album->title}' created"
    );

    return redirect()->route('admin.albums.show', $album)
                   ->with('success', 'Album berhasil dibuat.');
}
```

---

## 📸 Cara Upload Foto

### Metode 1: Drag & Drop
1. Buka halaman upload: `/admin/albums/{album_id}/upload`
2. Drag file foto dari komputer Anda
3. Drop ke area upload
4. Set harga per foto
5. Klik "Upload Foto"

### Metode 2: Pilih File (Multiple)
1. Klik tombol "Pilih Satu File"
2. Pilih multiple file dengan Ctrl+Click (Windows) atau Cmd+Click (Mac)
3. Set harga per foto
4. Klik "Upload Foto"

### Metode 3: Upload Folder
1. Klik tombol "Pilih Folder"
2. Pilih folder yang berisi foto-foto
3. Semua foto dalam folder akan diupload sekaligus
4. Set harga per foto
5. Klik "Upload Foto"

### Spesifikasi Upload
- **Format**: JPEG, PNG, JPG, GIF
- **Ukuran Max**: 10MB per file
- **Multiple Upload**: Ya (unlimited)
- **Folder Upload**: Ya (dengan webkitdirectory)

### Kode Controller (PhotoController.php)
```php
public function store(Request $request, Album $album): JsonResponse
{
    $request->validate([
        'photos' => 'required|array',
        'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
        'price' => 'required|numeric|min:0',
    ]);

    $uploadedPhotos = [];
    $errors = [];

    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $file) {
            try {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                $path = Storage::disk('public')->putFileAs(
                    "albums/{$album->id}/originals",
                    $file,
                    $filename
                );

                $photo = Photo::create([
                    'album_id' => $album->id,
                    'original_path' => $path,
                    'watermark_path' => null,
                    'price' => $request->price,
                ]);

                $uploadedPhotos[] = [
                    'id' => $photo->id,
                    'filename' => $filename,
                    'size' => $this->formatBytes($file->getSize()),
                ];

            } catch (\Exception $e) {
                $errors[] = "Error: " . $e->getMessage();
            }
        }
    }

    return response()->json([
        'success' => true,
        'message' => count($uploadedPhotos) . ' foto berhasil diupload',
        'photos' => $uploadedPhotos,
        'errors' => $errors,
    ]);
}
```

---

## 📁 Struktur File

### Controllers
```
app/Http/Controllers/Admin/
├── AlbumController.php      # CRUD album
├── PhotoController.php      # Upload & manage foto
├── AdminDashboardController.php
├── PhotographerController.php
├── RevenueController.php
└── AuditLogController.php
```

### Views
```
resources/views/admin/albums/
├── index.blade.php          # List semua album
├── create.blade.php         # Form buat album
├── edit.blade.php           # Form edit album
├── show.blade.php           # Detail album
└── upload.blade.php         # Upload foto (drag & drop)
```

### Models
```
app/Models/
├── Album.php                # Model album
├── Photo.php                # Model foto
├── User.php                 # Model user (photographer)
└── AdminAuditLog.php        # Audit log
```

### Storage
```
storage/app/public/albums/
└── {album_id}/
    ├── originals/           # Foto original
    │   ├── 1234567890_abc123.jpg
    │   └── 1234567891_def456.jpg
    └── watermarks/          # Foto dengan watermark (generated later)
```

---

## 🔌 API Endpoints

### Album Management
```
GET    /admin/albums                    # List albums
GET    /admin/albums/create             # Form create album
POST   /admin/albums                    # Store album
GET    /admin/albums/{album}            # Show album detail
GET    /admin/albums/{album}/edit       # Form edit album
PUT    /admin/albums/{album}            # Update album
DELETE /admin/albums/{album}            # Delete album
```

### Photo Management
```
GET    /admin/albums/{album}/upload     # Upload form
POST   /admin/albums/{album}/upload     # Store photos (AJAX)
DELETE /admin/photos/{photo}            # Delete photo (AJAX)
GET    /admin/albums/{album}/photos     # Get album photos (AJAX)
```

### Request & Response Examples

#### Upload Foto (POST)
**Request:**
```javascript
FormData {
  photos[]: File,
  photos[]: File,
  photos[]: File,
  price: 50000
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "3 foto berhasil diupload",
  "photos": [
    {
      "id": 1,
      "filename": "1234567890_abc123.jpg",
      "original_name": "photo1.jpg",
      "size": "2.5 MB"
    }
  ],
  "errors": [],
  "album_id": 5
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Tidak ada file yang berhasil diupload",
  "errors": [
    "File photo.jpg terlalu besar (max 10MB)"
  ]
}
```

---

## 🔒 Middleware & Authorization

### Admin Middleware
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('albums', AlbumController::class);
    Route::get('albums/{album}/upload', [PhotoController::class, 'create']);
    Route::post('albums/{album}/upload', [PhotoController::class, 'store']);
});
```

### Request Validation (StoreAlbumRequest.php)
```php
public function authorize(): bool
{
    return auth()->check() && auth()->user()->role === 'admin';
}

public function rules(): array
{
    return [
        'photographer_id' => 'required|exists:users,id',
        'title' => 'required|string|max:255',
        'location' => 'required|string|max:255',
        'event_date' => 'required|date',
    ];
}
```

---

## 🎯 Fitur JavaScript (Upload Page)

### Drag & Drop Handler
```javascript
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    selectedFiles = Array.from(e.dataTransfer.files)
        .filter(file => file.type.startsWith('image/'));
    updateSubmitButton();
    showFileInfo();
});
```

### Folder Upload Support
```html
<input type="file" 
       id="folderFile" 
       accept="image/*" 
       webkitdirectory 
       directory 
       mozdirectory>
```

### AJAX Upload
```javascript
const formData = new FormData();
selectedFiles.forEach(file => {
    formData.append('photos[]', file);
});
formData.append('price', priceInput.value);

const response = await fetch(uploadUrl, {
    method: 'POST',
    body: formData,
    headers: {
        'X-CSRF-TOKEN': csrfToken,
    }
});
```

---

## 📊 Audit Log

Setiap aktivitas admin dicatat dalam audit log:

```php
AdminAuditLog::logAction(
    auth()->id(),           // Admin user ID
    'photo_uploaded',       // Action type
    'photo',                // Entity type
    $photo->id,            // Entity ID
    "Photo uploaded to album: {$album->title}"  // Description
);
```

**Action Types:**
- `album_created`
- `album_updated`
- `album_deleted`
- `photo_uploaded`
- `photo_deleted`

---

## 🚀 Cara Menggunakan

### 1. Login sebagai Admin
```
Email: admin@fotlist.com
Password: admin12345
```

### 2. Akses Dashboard Admin
```
URL: http://localhost/fotlist/admin
```

### 3. Buat Album Baru
```
URL: http://localhost/fotlist/admin/albums/create
```

### 4. Upload Foto
```
URL: http://localhost/fotlist/admin/albums/{album_id}/upload
```

---

## 🛠️ Troubleshooting

### Error: "File terlalu besar"
- Pastikan file < 10MB
- Cek php.ini: `upload_max_filesize` dan `post_max_size`

### Error: "Storage link not found"
- Jalankan: `php artisan storage:link`

### Error: "Permission denied"
- Pastikan folder `storage/app/public` writable
- Chmod 775: `chmod -R 775 storage`

### Foto tidak muncul
- Cek symbolic link: `storage/app/public` → `public/storage`
- Pastikan file ada di `storage/app/public/albums/{album_id}/originals/`

---

## 📝 Catatan Penting

1. **Backup**: Selalu backup database sebelum hapus album
2. **Storage**: Monitor disk space untuk foto
3. **Performance**: Gunakan queue untuk watermark generation
4. **Security**: Jangan expose original photos ke public
5. **Validation**: Selalu validasi file type dan size

---

## 🎉 Selesai!

Fitur admin untuk membuat album dan upload foto sudah siap digunakan. Semua fitur sudah terintegrasi dengan:
- ✅ Authentication & Authorization
- ✅ Audit Logging
- ✅ Error Handling
- ✅ AJAX Upload
- ✅ Drag & Drop
- ✅ Folder Upload

Untuk pertanyaan lebih lanjut, silakan cek kode di:
- `app/Http/Controllers/Admin/AlbumController.php`
- `app/Http/Controllers/Admin/PhotoController.php`
- `resources/views/admin/albums/upload.blade.php`
