# 🔐 Cara Login dan Akses Admin Dashboard

## ✅ User Admin Sudah Ada!

**Email**: admin@fotlist.com  
**Password**: admin12345  
**Role**: admin  
**Status**: active

---

## 📍 URL yang Harus Diakses

### ❌ JANGAN akses URL ini (untuk photographer):
```
http://localhost/fotlist/dashboard
```

### ✅ AKSES URL ini (untuk admin):
```
http://localhost/fotlist/admin
atau
http://localhost/fotlist/admin/dashboard
```

---

## 🚀 Langkah-Langkah Login Admin

### Step 1: Login
1. Buka: `http://localhost/fotlist/login`
2. Masukkan:
   - Email: `admin@fotlist.com`
   - Password: `admin12345`
3. Klik "Login"

### Step 2: Akses Admin Dashboard
Setelah login, ada 2 cara:

**Cara 1: Langsung ke URL Admin**
```
http://localhost/fotlist/admin
```

**Cara 2: Dari Dashboard Biasa**
- Jika redirect ke `/dashboard`, ubah URL menjadi `/admin`

---

## 📋 Menu yang Tersedia di Admin Dashboard

Setelah masuk ke `/admin`, Anda akan melihat:

### 1. **Statistik Dashboard**
- Total Fotografer
- Total Album
- Total Foto
- Total Revenue

### 2. **Akses Cepat** (Kotak Hijau)
- ➕ **Tambah Fotografer** → `/admin/photographers/create`
- 📂 **Buat Album** → `/admin/albums/create` ⭐
- 👥 **Kelola Fotografer** → `/admin/photographers`
- 🎞️ **Kelola Album** → `/admin/albums` ⭐

### 3. **Menu Navigasi Bawah**
- 📊 Analytics Revenue
- 📋 Audit Logs
- 👨💼 Manajemen Fotografer

---

## 🎯 Cara Membuat Album & Upload Foto

### Metode 1: Dari Dashboard Admin
1. Login sebagai admin
2. Akses: `http://localhost/fotlist/admin`
3. Klik kotak **"Buat Album"** (kotak hijau)
4. Isi form album
5. Setelah album dibuat, klik tombol **"Upload Foto"**

### Metode 2: Dari Menu Kelola Album
1. Login sebagai admin
2. Akses: `http://localhost/fotlist/admin/albums`
3. Klik tombol **"Buat Album Baru"**
4. Isi form album
5. Setelah album dibuat, klik tombol **"Upload Foto"**

### Metode 3: Langsung ke URL
1. Login sebagai admin
2. Buat album: `http://localhost/fotlist/admin/albums/create`
3. Setelah album dibuat, akses: `http://localhost/fotlist/admin/albums/{album_id}/upload`

---

## 🔍 Troubleshooting

### Problem: Setelah login masih ke `/dashboard` biasa
**Solusi**: Ubah URL manual ke `/admin`

### Problem: Error 403 Forbidden
**Solusi**: Pastikan user memiliki role 'admin'
```bash
php artisan tinker
User::where('email', 'admin@fotlist.com')->update(['role' => 'admin']);
```

### Problem: Halaman admin tidak ada menu
**Solusi**: Pastikan mengakses `/admin` bukan `/dashboard`

### Problem: Route tidak ditemukan
**Solusi**: Clear cache
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

---

## 📸 Screenshot Lokasi Menu

Setelah login dan akses `/admin`, Anda akan melihat:

```
┌─────────────────────────────────────────┐
│     Admin Dashboard                      │
├─────────────────────────────────────────┤
│  [📷 Fotografer] [📁 Album] [🖼️ Foto]   │
├─────────────────────────────────────────┤
│  Akses Cepat:                           │
│  ┌─────────────────────────────────┐   │
│  │ ➕ Tambah Fotografer            │   │
│  ├─────────────────────────────────┤   │
│  │ 📂 Buat Album          ⭐       │   │ ← KLIK INI
│  ├─────────────────────────────────┤   │
│  │ 👥 Kelola Fotografer            │   │
│  ├─────────────────────────────────┤   │
│  │ 🎞️ Kelola Album        ⭐       │   │ ← ATAU INI
│  └─────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

---

## ✅ Checklist

- [x] User admin sudah dibuat (admin@fotlist.com)
- [x] Password: admin12345
- [x] Role: admin
- [x] Status: active
- [x] Admin dashboard tersedia di `/admin`
- [x] Menu "Buat Album" tersedia
- [x] Menu "Upload Foto" tersedia
- [x] Routes admin sudah terdaftar

---

## 🎯 Quick Access URLs

Simpan URL ini untuk akses cepat:

```
Login:          http://localhost/fotlist/login
Admin Dashboard: http://localhost/fotlist/admin
Buat Album:     http://localhost/fotlist/admin/albums/create
List Album:     http://localhost/fotlist/admin/albums
```

---

## 💡 Tips

1. **Bookmark** URL `/admin` setelah login
2. Jika redirect ke `/dashboard`, **ubah manual** ke `/admin`
3. Menu "Buat Album" ada di **kotak hijau** di dashboard admin
4. Setelah buat album, tombol **"Upload Foto"** akan muncul di detail album

---

Sekarang coba login dan akses `/admin` langsung! 🚀
