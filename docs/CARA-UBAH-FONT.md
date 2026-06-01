# 📝 CARA MENGUBAH FONT DI FOTLIST

## 📍 Lokasi File Font

Font diatur di file: **`resources/css/app.css`**

---

## 🎨 Font Saat Ini

### **Font yang Digunakan:**
- **Body/Teks Biasa**: Montserrat
- **Heading/Judul**: Montserrat Alternates

### **Import dari Google Fonts:**
```css
@import url('https://fonts.googleapis.com/css2?family=Montserrat+Alternates:ital,wght@0,100..900;1,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');
```

---

## 🔧 Cara Mengubah Font

### **Metode 1: Ganti dengan Font Google Fonts Lain**

#### **Step 1: Pilih Font di Google Fonts**
1. Buka: https://fonts.google.com
2. Pilih font yang Anda inginkan
3. Klik "Get font" → "Get embed code"
4. Copy link `@import`

#### **Step 2: Edit `resources/css/app.css`**
```css
/* Ganti baris pertama dengan font pilihan Anda */
@import url('https://fonts.googleapis.com/css2?family=NAMA_FONT_ANDA&display=swap');

@theme {
  /* Ganti nama font di sini */
  --font-sans: 'NAMA_FONT_ANDA', sans-serif;
  --font-display: 'NAMA_FONT_ANDA', sans-serif;
}
```

#### **Contoh: Menggunakan Poppins**
```css
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');

@theme {
  --font-sans: 'Poppins', sans-serif;
  --font-display: 'Poppins', sans-serif;
}
```

#### **Contoh: Menggunakan Inter**
```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap');

@theme {
  --font-sans: 'Inter', sans-serif;
  --font-display: 'Inter', sans-serif;
}
```

#### **Contoh: Menggunakan Roboto**
```css
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap');

@theme {
  --font-sans: 'Roboto', sans-serif;
  --font-display: 'Roboto', sans-serif;
}
```

---

### **Metode 2: Menggunakan Font Lokal (Upload Font Sendiri)**

#### **Step 1: Download Font**
Download file font (.ttf, .woff, .woff2) yang Anda inginkan

#### **Step 2: Simpan di Folder Public**
```
public/fonts/
├── NamaFont-Regular.woff2
├── NamaFont-Bold.woff2
└── NamaFont-Light.woff2
```

#### **Step 3: Edit `resources/css/app.css`**
```css
/* Hapus @import Google Fonts, ganti dengan @font-face */
@font-face {
  font-family: 'NamaFont';
  src: url('/fonts/NamaFont-Regular.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
}

@font-face {
  font-family: 'NamaFont';
  src: url('/fonts/NamaFont-Bold.woff2') format('woff2');
  font-weight: 700;
  font-style: normal;
}

@theme {
  --font-sans: 'NamaFont', sans-serif;
  --font-display: 'NamaFont', sans-serif;
}
```

---

## 🔄 Setelah Mengubah Font

### **Step 1: Build Assets**
```bash
npm run build
```

### **Step 2: Clear Cache**
```bash
php artisan view:clear
php artisan cache:clear
```

### **Step 3: Refresh Browser**
```
Tekan Ctrl + Shift + R (hard refresh)
```

---

## 🎯 Rekomendasi Font

### **Untuk Website Modern & Clean:**
- **Inter** - Modern, clean, sangat readable
- **Poppins** - Friendly, rounded, modern
- **Roboto** - Clean, professional, Google's default

### **Untuk Website Elegant:**
- **Playfair Display** - Elegant, serif
- **Cormorant** - Sophisticated, serif
- **Lora** - Elegant, readable

### **Untuk Website Fun & Casual:**
- **Quicksand** - Rounded, friendly
- **Nunito** - Soft, rounded
- **Comfortaa** - Geometric, friendly

### **Untuk Website Professional:**
- **Open Sans** - Professional, clean
- **Lato** - Professional, modern
- **Source Sans Pro** - Clean, readable

---

## 📋 Template Lengkap

### **File: `resources/css/app.css`**

```css
/* 1. Import Font dari Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=FONT_PILIHAN_ANDA&display=swap');

/* 2. Import Tailwind */
@import "tailwindcss";

/* 3. Set Font Variables */
@theme {
  --font-sans: 'FONT_PILIHAN_ANDA', sans-serif;
  --font-display: 'FONT_PILIHAN_ANDA', sans-serif;

  /* Custom colors */
  --color-brand-bg: #0d061a;
  --color-brand-purple: #a855f7;
  --color-brand-purple-dark: #6b21a8;
  --color-brand-purple-light: #c084fc;
}

/* 4. Apply Font */
body {
  font-family: var(--font-sans);
  background-color: var(--color-brand-bg);
  color: #ffffff;
}

h1, h2, h3, h4, h5, h6, .font-display {
  font-family: var(--font-display);
}
```

---

## ⚠️ Troubleshooting

### **Font Tidak Berubah?**
1. Pastikan sudah run `npm run build`
2. Clear cache: `php artisan view:clear`
3. Hard refresh browser: `Ctrl + Shift + R`
4. Cek console browser untuk error

### **Font Tidak Load?**
1. Cek koneksi internet (untuk Google Fonts)
2. Cek path file font (untuk font lokal)
3. Cek typo di nama font

### **Font Terlihat Aneh?**
1. Pastikan import semua weight yang dibutuhkan (100-900)
2. Cek fallback font: `'NamaFont', sans-serif`

---

## 🎊 Selesai!

Sekarang Anda bisa mengubah font sesuai keinginan!

**File yang perlu diedit**: `resources/css/app.css`  
**Jangan lupa**: `npm run build` setelah edit!

---

## 📞 Quick Reference

```bash
# Edit font
nano resources/css/app.css

# Build assets
npm run build

# Clear cache
php artisan view:clear
php artisan cache:clear

# Hard refresh browser
Ctrl + Shift + R
```
