# Perubahan Role Fotografer

## Ringkasan Perubahan

Sistem telah diubah sehingga **hanya admin yang dapat membuat album dan mengunggah foto**. Fotografer sekarang hanya memiliki akses **read-only** ke dashboard mereka.

## Detail Perubahan

### 1. Role System
- **Admin**: Dapat membuat album, mengunggah foto, dan melihat semua album dari semua fotografer
- **Photographer**: Hanya dapat melihat dashboard dengan album dan foto milik mereka (read-only)
- **Customer**: Dapat melihat katalog, membeli foto, dan menggunakan face scan

### 2. Perubahan Controller

#### AlbumController
- `create()`: Hanya admin yang dapat mengakses form pembuatan album
- `store()`: Hanya admin yang dapat membuat album baru
  - Admin harus memilih fotografer saat membuat album (field `photographer_id` required)
- `show()`: Admin dapat melihat semua album, fotografer hanya dapat melihat album miliknya

#### PhotoController
- `create()`: Hanya admin yang dapat mengakses form upload foto
- `store()`: Hanya admin yang dapat mengunggah foto

#### DashboardController
- Admin: Melihat semua album dari semua fotografer dengan total statistik keseluruhan
- Photographer: Melihat hanya album miliknya dengan statistik pribadi (read-only)
- Customer: Redirect ke catalog

### 3. Middleware Baru

**AdminOnly Middleware** (`app/Http/Middleware/AdminOnly.php`)
- Memastikan hanya user dengan role 'admin' yang dapat mengakses route tertentu
- Mengembalikan error 403 jika non-admin mencoba mengakses

### 4. Perubahan Routes

Routes yang sekarang **hanya untuk admin**:
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/albums/create', [AlbumController::class, 'create']);
    Route::post('/albums', [AlbumController::class, 'store']);
    Route::get('/albums/{album}/photos/create', [PhotoController::class, 'create']);
    Route::post('/albums/{album}/photos', [PhotoController::class, 'store']);
});
```

Routes yang dapat diakses **admin dan photographer**:
```php
Route::middleware('auth')->group(function () {
    Route::get('/albums/{album}', [AlbumController::class, 'show']);
});
```

### 5. Perubahan Form Album

Saat admin membuat album, form harus menyertakan:
- **photographer_id** (dropdown untuk memilih fotografer)
- title
- location (optional)
- event_date (optional)

### 6. Dashboard View

Dashboard perlu diupdate untuk:
- Menampilkan tombol "Buat Album" dan "Upload Foto" **hanya untuk admin**
- Menampilkan informasi read-only untuk fotografer
- Menampilkan nama fotografer di setiap album (untuk admin)

## Cara Menggunakan

### Sebagai Admin
1. Login dengan akun admin
2. Di dashboard, klik "Buat Album Baru"
3. Pilih fotografer dari dropdown
4. Isi detail album (title, location, event_date)
5. Setelah album dibuat, klik album untuk upload foto

### Sebagai Photographer
1. Login dengan akun photographer
2. Dashboard menampilkan album yang di-assign ke fotografer tersebut
3. Fotografer dapat melihat detail album dan foto
4. Fotografer **tidak dapat** membuat album atau upload foto
5. Fotografer dapat melihat statistik pendapatan dari foto mereka

## Testing

Untuk menguji perubahan ini:

1. **Test Admin Access**:
   - Login sebagai admin
   - Pastikan dapat membuat album
   - Pastikan dapat upload foto
   - Pastikan dapat melihat semua album

2. **Test Photographer Access**:
   - Login sebagai photographer
   - Pastikan tidak dapat mengakses `/albums/create` (403 error)
   - Pastikan tidak dapat mengakses `/albums/{album}/photos/create` (403 error)
   - Pastikan dapat melihat album miliknya di dashboard
   - Pastikan dapat melihat detail album miliknya

3. **Test Customer Access**:
   - Login sebagai customer
   - Pastikan redirect ke catalog dari dashboard
   - Pastikan dapat melihat dan membeli foto

## Migration (Jika Diperlukan)

Tidak ada perubahan database schema yang diperlukan. Semua perubahan adalah di level aplikasi (controller, middleware, routes).

## Catatan Penting

- Pastikan setiap fotografer memiliki minimal satu user dengan role 'admin' untuk mengelola album dan foto mereka
- Admin bertanggung jawab untuk mengatur album ke fotografer yang tepat
- Fotografer tetap menerima pendapatan dari foto yang terjual dari album mereka
