<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Fotlist</title>
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
            max-width: 450px;
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

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
        }

        .remember-me input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #ffffff;
        }

        .remember-me label {
            cursor: pointer;
            color: #a0a0a0;
            font-size: 0.9rem;
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

        .forgot-link {
            color: #b0b0b0;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            color: #ffffff;
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
    </style>
</head>
<body>
    <div class="auth-container">
        <div>
            <a href="/" class="back-link">← Kembali ke Beranda</a>
            
            <div class="glass-form">
                <h1 class="form-title gradient-text">🔐 Masuk</h1>
                <p class="form-subtitle">Masuk ke akun Fotlist Anda</p>

                @if ($errors->any())
                    <div style="background: rgba(255, 107, 107, 0.1); border: 1px solid rgba(255, 107, 107, 0.3); border-radius: 8px; padding: 12px; margin-bottom: 20px;">
                        <p style="color: #ff6b6b; font-size: 0.9rem; margin: 0;">
                            ✓ Ada kesalahan pada form. Periksa kembali input Anda.
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

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
                            autofocus
                        >
                        @error('email')
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
                            placeholder="Masukkan password Anda"
                            required
                        >
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ingat saya di perangkat ini</label>
                    </div>

                    <button type="submit" class="btn-submit">🚀 Masuk Sekarang</button>
                </form>

                <div class="form-footer">
                    <p class="form-footer-text">
                        Lupa password?
                        <a href="{{ route('password.request') }}" class="forgot-link">Reset di sini</a>
                    </p>
                </div>

                <div class="form-footer">
                    <p class="form-footer-text">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="form-footer-link">Daftar sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
