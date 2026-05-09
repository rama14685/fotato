# Quick Guide: Admin Album & Upload Foto

## 🚀 Quick Start

### 1. Login Admin
```
URL: http://localhost/fotlist/admin
Email: admin@fotlist.com
Password: admin12345
```

### 2. Buat Album
```
1. Klik "Albums" di menu admin
2. Klik "Buat Album Baru"
3. Isi form:
   - Pilih Fotografer
   - Judul Album
   - Lokasi
   - Tanggal Event
4. Klik "Buat Album"
```

### 3. Upload Foto

**Metode A: Drag & Drop**
```
1. Buka album yang sudah dibuat
2. Klik "Upload Foto"
3. Drag foto dari komputer
4. Drop ke area upload
5. Set harga
6. Klik "Upload Foto"
```

**Metode B: Pilih File**
```
1. Klik "Pilih Satu File"
2. Pilih multiple file (Ctrl+Click)
3. Set harga
4. Klik "Upload Foto"
```

**Metode C: Upload Folder**
```
1. Klik "Pilih Folder"
2. Pilih folder berisi foto
3. Set harga
4. Klik "Upload Foto"
```

## 📋 Spesifikasi

- **Format**: JPEG, PNG, JPG, GIF
- **Max Size**: 10MB per file
- **Multiple Upload**: ✅ Ya
- **Folder Upload**: ✅ Ya
- **Drag & Drop**: ✅ Ya

## 🔗 Routes

```
GET  /admin/albums              → List albums
GET  /admin/albums/create       → Form buat album
POST /admin/albums              → Simpan album
GET  /admin/albums/{id}/upload  → Upload foto
POST /admin/albums/{id}/upload  → Proses upload
```

## 📁 Storage Location

```
storage/app/public/albums/{album_id}/originals/
```

## ⚠️ Troubleshooting

**Foto tidak muncul?**
```bash
php artisan storage:link
```

**Permission error?**
```bash
chmod -R 775 storage
```

**File terlalu besar?**
- Edit php.ini:
  - upload_max_filesize = 10M
  - post_max_size = 20M
- Restart Apache

## 📚 Dokumentasi Lengkap

Lihat: `docs/Admin-Album-Upload-Guide.md`
