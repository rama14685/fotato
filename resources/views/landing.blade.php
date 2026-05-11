<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotlist - Platform Foto Event Terbaik</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 25%, #2d2d2d 50%, #1a1a1a 75%, #0a0a0a 100%);
            background-attachment: fixed;
            color: #ffffff;
        }

        .hero-section {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .carousel-container {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .carousel-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .carousel-image.active {
            opacity: 0.35;
        }

        .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.5) 40%, rgba(0, 0, 0, 0.7) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #ffffff 0%, #b0b0b0 50%, #e0e0e0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ffffff 0%, #d0d0d0 100%);
            color: #000;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        .feature-card {
            padding: 30px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-8px);
        }

        .section-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 50px;
            background: linear-gradient(135deg, #ffffff 0%, #b0b0b0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-bar {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff 0%, #b0b0b0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
            flex-shrink: 0;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="nav-bar">
        <div class="container mx-auto px-4 flex justify-between items-center py-3">
            <div class="text-2xl font-bold gradient-text">📸 Fotlist</div>
            <div class="flex gap-6 items-center">
                <a href="#features" class="text-gray-300 hover:text-white transition text-sm font-medium nav-link">Fitur</a>
                <a href="#usage" class="text-gray-300 hover:text-white transition text-sm font-medium nav-link">Cara Penggunaan</a>
                <a href="#events" class="text-gray-300 hover:text-white transition text-sm font-medium nav-link">Events</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="carousel-container">
            <img src="{{ asset('images/landing1.jpg') }}" alt="Hero 1" class="carousel-image active">
            <img src="{{ asset('images/landing2.jpg') }}" alt="Hero 2" class="carousel-image">
            <img src="{{ asset('images/landing3.jpg') }}" alt="Hero 3" class="carousel-image">
            <div class="overlay"></div>
        </div>
        
        <!-- Hero Buttons & CTA -->
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 20; text-align: center;">
            <div style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 50px 40px; max-width: 500px;">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: #ffffff; margin-bottom: 10px; background: linear-gradient(135deg, #ffffff 0%, #b0b0b0 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Fotlist</h2>
                <p style="color: #d0d0d0; font-size: 1.1rem; margin-bottom: 30px; line-height: 1.6;">Platform terbaik untuk menemukan dan menjual foto event Anda dengan teknologi pengenalan wajah AI</p>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="{{ route('login') }}" class="btn-secondary text-lg" style="text-decoration: none; display: block;">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary text-lg" style="text-decoration: none; display: block;">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4" id="features">
        <div class="container mx-auto max-w-6xl">
            <h2 class="section-title text-center">✨ Fitur Unggulan</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card feature-card">
                    <div class="text-5xl mb-4">🎯</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Pencarian Wajah Cerdas</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Temukan foto Anda dengan mudah menggunakan teknologi pengenalan wajah AI. Cukup scan wajah Anda, dan sistem kami akan menemukan semua foto Anda di event.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card feature-card">
                    <div class="text-5xl mb-4">💳</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Pembayaran Aman</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Beli foto favorit Anda dengan sistem pembayaran yang aman dan mudah. Dompet digital terintegrasi untuk transaksi yang cepat dan praktis.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card feature-card">
                    <div class="text-5xl mb-4">📷</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Untuk Fotografer</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Platform sempurna untuk fotografer event. Upload foto, kelola album, dan jual foto Anda dengan mudah. Sistem otomatis mencocokkan foto dengan customer.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works / Usage Slider -->
    <section class="py-20 px-4 bg-gradient-to-b from-transparent via-gray-900/30 to-transparent" id="usage">
        <div class="container mx-auto max-w-6xl">
            <h2 class="section-title text-center">Cara Penggunaan</h2>

            <div class="mt-8">
                <div id="usage-slider" style="display:flex;gap:24px;align-items:center;">
                    <div style="flex:1;max-width:480px;">
                        <div style="overflow:hidden;border-radius:12px;">
                            <img id="usage-image" src="{{ asset('images/step1.png') }}" alt="Step" style="width:100%;height:100%;object-fit:cover;display:block;transition:opacity 0.5s ease;">
                        </div>
                    </div>
                    <div style="flex:1;min-width:300px;">
                        <div id="usage-description" class="glass-card p-6" style="transition: all 0.3s ease;">
                            <h3 class="text-xl font-bold text-white mb-2">Langkah 1</h3>
                            <p class="text-gray-400">Daftar dan buat akun. Saat mendaftar, Anda bisa melakukan scan wajah untuk menyimpan embedding yang akan membantu pencarian foto nanti.</p>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:16px;align-items:center;">
                            <button id="prev-step" style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,rgba(255,255,255,0.15),rgba(255,255,255,0.05));border:1px solid rgba(255,255,255,0.2);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;transition:all 0.3s;" onmouseover="this.style.background='linear-gradient(135deg,rgba(255,255,255,0.25),rgba(255,255,255,0.1))';this.style.transform='scale(1.1)';" onmouseout="this.style.background='linear-gradient(135deg,rgba(255,255,255,0.15),rgba(255,255,255,0.05))';this.style.transform='scale(1)';">←</button>
                            <div style="flex:1;height:2px;background:linear-gradient(90deg,rgba(255,255,255,0.1),rgba(255,255,255,0.3),rgba(255,255,255,0.1));border-radius:1px;">
                                <div id="progress-bar" style="height:100%;background:linear-gradient(90deg,#fff,#b0b0b0);width:25%;border-radius:1px;transition:width 0.3s;"></div>
                            </div>
                            <button id="next-step" style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#fff,#d0d0d0);border:none;color:#000;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:bold;transition:all 0.3s;" onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 10px 30px rgba(255,255,255,0.2)';" onmouseout="this.style.transform='scale(1)';this.style.boxShadow='none';">→</button>
                        </div>
                        <div style="text-center;margin-top:12px;font-size:12px;color:#9ca3af;">
                            <span id="step-counter">1</span> / 4
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events -->
    <section class="py-12 px-4" id="events">
        <div class="container mx-auto max-w-4xl">
            <h2 class="section-title text-center text-2xl" style="font-size:2rem;">Upcoming Events</h2>
            <div class="mt-6 space-y-4">
                @forelse($events as $ev)
                    <div class="glass-card p-4 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-white">{{ $ev->name }}</div>
                            <div class="text-gray-400 text-sm">{{ $ev->location }} — {{ optional($ev->start_date)->format('d M Y H:i') }}</div>
                        </div>
                        <div>
                            <a href="#" class="btn-secondary">Lihat Detail</a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center">Belum ada event mendatang.</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4">
        <div class="container mx-auto max-w-3xl text-center">
            <h2 class="text-4xl font-bold mb-6 gradient-text">Siap Memulai?</h2>
            <p class="text-gray-400 mb-8 text-lg">Bergabunglah dengan ribuan pengguna yang telah menemukan dan menjual foto mereka di Fotlist</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="btn-primary text-lg">🚀 Daftar Gratis Sekarang</a>
                <a href="#features" class="btn-secondary text-lg">Pelajari Lebih Lanjut</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-8 px-4">
        <div class="container mx-auto text-center text-gray-500">
            <p>&copy; 2026 Fotlist. Semua hak dilindungi. Dibuat dengan ❤️ untuk komunitas fotografi.</p>
        </div>
    </footer>

    <script>
        // Auto-rotate carousel images every 2 seconds
        let currentIndex = 0;
        const images = document.querySelectorAll('.carousel-image');
        const totalImages = images.length;

        setInterval(() => {
            images.forEach(img => img.classList.remove('active'));
            currentIndex = (currentIndex + 1) % totalImages;
            images[currentIndex].classList.add('active');
        }, 2000);
        
        // Smooth scroll for navbar links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Usage slider (step images and descriptions with auto-slide)
        (function(){
            const steps = [
                { img: '{{ asset("images/step1.png") }}', title: 'Langkah 1', text: 'Daftar dan buat akun. Saat mendaftar, Anda bisa melakukan scan wajah untuk menyimpan embedding.' },
                { img: '{{ asset("images/step2.png") }}', title: 'Langkah 2', text: 'Pilih event atau album yang relevan, lalu jalankan pencarian wajah untuk menemukan foto Anda.' },
                { img: '{{ asset("images/step3.png") }}', title: 'Langkah 3', text: 'Tambah foto ke keranjang, lakukan pembayaran yang aman, dan unduh foto resolusi penuh.' },
                { img: '{{ asset("images/step4.png") }}', title: 'Langkah 4', text: 'Kelola pembelian dan riwayat Anda di dashboard. Hubungi fotografer jika perlu bantuan.' },
            ];

            let stepIndex = 0;
            let autoSlideTimer = null;
            const imgEl = document.getElementById('usage-image');
            const descEl = document.getElementById('usage-description');
            const prevBtn = document.getElementById('prev-step');
            const nextBtn = document.getElementById('next-step');
            const counterEl = document.getElementById('step-counter');
            const progressEl = document.getElementById('progress-bar');

            function render() {
                const s = steps[stepIndex];
                imgEl.src = s.img;
                descEl.innerHTML = `<h3 class="text-xl font-bold text-white mb-2">${s.title}</h3><p class="text-gray-400">${s.text}</p>`;
                counterEl.textContent = stepIndex + 1;
                progressEl.style.width = ((stepIndex + 1) * 25) + '%';
            }

            function nextSlide() {
                stepIndex = (stepIndex + 1) % steps.length;
                render();
                resetAutoSlide();
            }

            function prevSlide() {
                stepIndex = (stepIndex - 1 + steps.length) % steps.length;
                render();
                resetAutoSlide();
            }

            function resetAutoSlide() {
                clearInterval(autoSlideTimer);
                startAutoSlide();
            }

            function startAutoSlide() {
                autoSlideTimer = setInterval(nextSlide, 3000);
            }

            prevBtn.addEventListener('click', prevSlide);
            nextBtn.addEventListener('click', nextSlide);

            // initial
            render();
            startAutoSlide();
        })();
    </script>
</body>
</html>
