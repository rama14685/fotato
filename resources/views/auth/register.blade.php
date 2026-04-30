<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Fotlist</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 25%, #2d2d2d 50%, #1a1a1a 75%, #0a0a0a 100%);
            background-attachment: fixed;
            color: #ffffff;
        }

        .auth-container {
            min-h-screen;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .glass-form {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .gradient-text {
            background: linear-gradient(135deg, #ffffff 0%, #b0b0b0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-subtitle {
            color: #a0a0a0;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #e0e0e0;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .form-input::placeholder {
            color: #707070;
        }

        .form-error {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .role-selection {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 15px 0;
        }

        .role-option {
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .role-option:hover {
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.08);
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option input[type="radio"]:checked + .role-content {
            color: #ffffff;
        }

        .role-option input[type="radio"]:checked + .role-content .role-emoji {
            font-size: 1.5rem;
        }

        .role-option input:checked + .role-content .role-name {
            color: #ffffff;
            font-weight: 700;
        }

        .role-option input:checked + ~ .role-check {
            display: block;
        }

        .role-content {
            pointer-events: none;
        }

        .role-emoji {
            font-size: 1.3rem;
            margin-bottom: 5px;
            transition: font-size 0.3s ease;
        }

        .role-name {
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 3px;
            transition: all 0.3s ease;
        }

        .role-desc {
            font-size: 0.75rem;
            color: #a0a0a0;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ffffff 0%, #d0d0d0 100%);
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
            margin-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 25px;
        }

        .form-footer-text {
            color: #a0a0a0;
            font-size: 0.9rem;
        }

        .form-footer-link {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-left: 5px;
        }

        .form-footer-link:hover {
            color: #e0e0e0;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            color: #b0b0b0;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #ffffff;
        }

        .role-option.selected {
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div>
            <a href="/" class="back-link">← Kembali ke Beranda</a>
            
            <div class="glass-form">
                <h1 class="form-title gradient-text">🎉 Daftar</h1>
                <p class="form-subtitle">Bergabunglah dengan komunitas Fotlist</p>

                @if ($errors->any())
                    <div style="background: rgba(255, 107, 107, 0.1); border: 1px solid rgba(255, 107, 107, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                        <p style="color: #ff6b6b; font-size: 0.9rem; margin: 0;">
                            ✓ Ada kesalahan pada form. Periksa kembali input Anda.
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    <!-- Name -->
                    <div class="form-group">
                        <label for="name" class="form-label">👤 Nama Lengkap</label>
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            value="{{ old('name') }}"
                            class="form-input" 
                            placeholder="Nama Anda"
                            required 
                            autofocus
                        >
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">📧 Email</label>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            class="form-input" 
                            placeholder="nama@email.com"
                            required
                        >
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Role Selection -->
                    <div class="form-group">
                        <label class="form-label">👥 Saya ingin sebagai:</label>
                        <div class="role-selection">
                            <label class="role-option" id="buyer-option">
                                <input type="radio" name="role" value="customer" {{ old('role') === 'customer' ? 'checked' : '' }} required>
                                <div class="role-content">
                                    <div class="role-emoji">🛒</div>
                                    <div class="role-name">Pembeli</div>
                                    <div class="role-desc">Cari & beli foto</div>
                                </div>
                            </label>

                            <label class="role-option" id="photographer-option">
                                <input type="radio" name="role" value="photographer" {{ old('role') === 'photographer' ? 'checked' : '' }} required>
                                <div class="role-content">
                                    <div class="role-emoji">📸</div>
                                    <div class="role-name">Fotografer</div>
                                    <div class="role-desc">Jual foto</div>
                                </div>
                            </label>
                        </div>
                        @error('role')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">🔑 Password</label>
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Min. 8 karakter"
                            required
                        >
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">🔑 Konfirmasi Password</label>
                        <input 
                            id="password_confirmation" 
                            type="password" 
                            name="password_confirmation" 
                            class="form-input" 
                            placeholder="Ulangi password Anda"
                            required
                        >
                        @error('password_confirmation')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-submit">🚀 Daftar Sekarang</button>
                </form>

                <div class="form-footer">
                    <p class="form-footer-text">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="form-footer-link">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Role selection styling
        const buyerOption = document.getElementById('buyer-option');
        const photographerOption = document.getElementById('photographer-option');
        const buyerInput = buyerOption?.querySelector('input');
        const photographerInput = photographerOption?.querySelector('input');

        if (buyerInput && photographerInput) {
            function updateSelection() {
                if (buyerInput.checked) {
                    buyerOption.classList.add('selected');
                    photographerOption.classList.remove('selected');
                } else {
                    photographerOption.classList.add('selected');
                    buyerOption.classList.remove('selected');
                }
            }

            buyerInput.addEventListener('change', updateSelection);
            photographerInput.addEventListener('change', updateSelection);
            updateSelection(); // Initial update
        }
    </script>
</body>
</html>
