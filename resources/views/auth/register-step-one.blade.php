<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi - Step 1 | Fotlist</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Step Indicator -->
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">1</div>
                    <span class="ml-2 text-white font-semibold">Informasi Dasar</span>
                </div>
                <div class="w-16 h-1 bg-gray-600"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center text-gray-400 font-bold">2</div>
                    <span class="ml-2 text-gray-400 font-semibold">Scan Wajah</span>
                </div>
            </div>
            <p class="text-gray-300 text-sm">Step 1 dari 2</p>
        </div>

        <!-- Registration Form -->
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-white mb-2">📝 Registrasi</h1>
                <p class="text-gray-300">Isi informasi dasar Anda</p>
            </div>

            <form id="registerForm" class="space-y-4">
                @csrf

                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-200 mb-2">Nama Lengkap</label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Masukkan nama lengkap Anda">
                    <p id="name-error" class="text-red-400 text-sm mt-1 hidden"></p>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-200 mb-2">Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="email@example.com">
                    <p id="email-error" class="text-red-400 text-sm mt-1 hidden"></p>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-200 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Minimal 8 karakter">
                    <p id="password-error" class="text-red-400 text-sm mt-1 hidden"></p>
                </div>

                <!-- Password Confirmation Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-200 mb-2">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Ulangi password Anda">
                    <p id="password_confirmation-error" class="text-red-400 text-sm mt-1 hidden"></p>
                </div>

                <!-- Role Field -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-200 mb-2">Daftar Sebagai</label>
                    <select id="role" name="role" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="" class="bg-gray-800">Pilih role...</option>
                        <option value="customer" class="bg-gray-800">Customer (Pembeli Foto)</option>
                        <option value="photographer" class="bg-gray-800">Fotografer</option>
                    </select>
                    <p id="role-error" class="text-red-400 text-sm mt-1 hidden"></p>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                    class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submitText">Lanjutkan ke Step 2 →</span>
                    <span id="loadingText" class="hidden">Memproses...</span>
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-300">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-semibold">Masuk di sini</a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="mt-6 text-center">
            <a href="/" class="text-gray-400 hover:text-gray-300">← Kembali ke Beranda</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingText = document.getElementById('loadingText');

            // Client-side validation
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            function validatePassword(password) {
                return password.length >= 8;
            }

            function showError(fieldName, message) {
                const errorElement = document.getElementById(`${fieldName}-error`);
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                }
            }

            function hideError(fieldName) {
                const errorElement = document.getElementById(`${fieldName}-error`);
                if (errorElement) {
                    errorElement.classList.add('hidden');
                }
            }

            function hideAllErrors() {
                ['name', 'email', 'password', 'password_confirmation', 'role'].forEach(hideError);
            }

            // Real-time validation
            document.getElementById('email').addEventListener('blur', function() {
                if (this.value && !validateEmail(this.value)) {
                    showError('email', 'Format email tidak valid.');
                } else {
                    hideError('email');
                }
            });

            document.getElementById('password').addEventListener('blur', function() {
                if (this.value && !validatePassword(this.value)) {
                    showError('password', 'Password minimal 8 karakter.');
                } else {
                    hideError('password');
                }
            });

            document.getElementById('password_confirmation').addEventListener('blur', function() {
                const password = document.getElementById('password').value;
                if (this.value && this.value !== password) {
                    showError('password_confirmation', 'Konfirmasi password tidak cocok.');
                } else {
                    hideError('password_confirmation');
                }
            });

            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                hideAllErrors();

                const formData = {
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    password_confirmation: document.getElementById('password_confirmation').value,
                    role: document.getElementById('role').value,
                };

                // Client-side validation
                let hasError = false;

                if (!formData.name) {
                    showError('name', 'Nama wajib diisi.');
                    hasError = true;
                }

                if (!formData.email) {
                    showError('email', 'Email wajib diisi.');
                    hasError = true;
                } else if (!validateEmail(formData.email)) {
                    showError('email', 'Format email tidak valid.');
                    hasError = true;
                }

                if (!formData.password) {
                    showError('password', 'Password wajib diisi.');
                    hasError = true;
                } else if (!validatePassword(formData.password)) {
                    showError('password', 'Password minimal 8 karakter.');
                    hasError = true;
                }

                if (formData.password !== formData.password_confirmation) {
                    showError('password_confirmation', 'Konfirmasi password tidak cocok.');
                    hasError = true;
                }

                if (!formData.role) {
                    showError('role', 'Role wajib dipilih.');
                    hasError = true;
                }

                if (hasError) {
                    return;
                }

                // Disable button and show loading
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                loadingText.classList.remove('hidden');

                try {
                    const response = await fetch('{{ route('register.step-one') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Redirect to step 2 with session token
                        window.location.href = `{{ route('register.step-two') }}?token=${data.session_token}`;
                    } else {
                        // Display validation errors
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                showError(field, data.errors[field][0]);
                            });
                        } else if (data.message) {
                            alert(data.message);
                        }

                        // Re-enable button
                        submitBtn.disabled = false;
                        submitText.classList.remove('hidden');
                        loadingText.classList.add('hidden');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                    
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    loadingText.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
