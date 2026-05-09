@echo off
echo ========================================
echo Setup Storage untuk Upload Foto
echo ========================================
echo.

echo [1/3] Membuat symbolic link storage...
php artisan storage:link
echo.

echo [2/3] Membuat folder albums...
if not exist "storage\app\public\albums" mkdir "storage\app\public\albums"
echo Folder albums berhasil dibuat!
echo.

echo [3/3] Set permissions...
echo Pastikan folder storage memiliki write permission
echo.

echo ========================================
echo Setup Selesai!
echo ========================================
echo.
echo Anda sekarang bisa:
echo 1. Login sebagai admin (admin@fotlist.com / admin12345)
echo 2. Buat album baru di /admin/albums/create
echo 3. Upload foto di /admin/albums/{id}/upload
echo.
echo Dokumentasi lengkap: docs\Admin-Album-Upload-Guide.md
echo Quick guide: docs\QUICK-GUIDE-ADMIN.md
echo.
pause
