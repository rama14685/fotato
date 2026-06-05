<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - FOTATO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .grain-overlay {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.08;
            mix-blend-mode: overlay;
            pointer-events: none;
        }

        /* Prevent browser autofill styling from breaking the dark glassmorphic design */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-background-clip: text;
            -webkit-text-fill-color: #ffffff !important;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px #13072c !important;
        }
    </style>
</head>
<body class="bg-[#0c0517] text-white font-sans min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden selection:bg-purple-500/20 selection:text-white">
    
    <!-- Background Glows -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-purple-900/10 blur-[130px] rounded-full pointer-events-none z-0"></div>
    <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-indigo-950/15 blur-[130px] rounded-full pointer-events-none z-0"></div>

    <div class="w-full max-w-6xl z-10 my-8">
        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 text-xs md:text-sm text-gray-400 font-medium mb-6 px-1">
            <a href="/" class="hover:text-white transition-colors">Home</a>
            <span class="text-xs text-purple-300/40">
                <svg class="w-2.5 h-2.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </span>
            <span class="text-white">Sign in</span>
        </div>

        <!-- Main Card Container -->
        <div class="border border-purple-500/10 bg-[#0d061a]/75 backdrop-blur-xl rounded-[28px] p-4 md:p-6 lg:p-8 shadow-2xl shadow-black/40">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16">
                
                <!-- Left Poster Banner -->
                <div class="lg:col-span-6 relative rounded-[20px] overflow-hidden bg-gradient-to-tr from-[#5b21b6] via-[#7c3aed] to-[#3b82f6] min-h-[350px] lg:min-h-[480px] flex flex-col justify-between p-8 md:p-10 lg:p-12 shadow-lg shadow-purple-500/5 bg-cover bg-center" style="background-image: url('{{ asset('images/signin.png') }}');">
                    <!-- Grain texture overlay -->
                    <div class="absolute inset-0 grain-overlay"></div>
                    
                    <!-- Content (Logo) -->
                    <div class="relative z-10">
                        <span class="text-white text-2xl font-black tracking-wider font-display">FOTATO</span>
                    </div>

                    <!-- Content (Slogans) -->
                    <div class="relative z-10 mt-auto">
                        <h1 class="text-white text-3xl md:text-[42px] font-bold font-display leading-[1.15] mb-4 tracking-tight">
                            Where <span class="text-[#FFE600]">Concert</span> Memories <span class="text-[#FFE600]">Become</span> Yours.
                        </h1>
                        <p class="text-purple-100/80 text-sm font-sans font-light leading-relaxed">
                            Miliki Momen Sebelum Orang Lain Memilikinya.
                        </p>
                    </div>
                </div>

                <!-- Right Sign In Form -->
                <div class="lg:col-span-6 flex flex-col justify-center py-8 px-6 md:px-12 lg:pr-16 lg:pl-8">
                    <div class="w-full max-w-[360px] mx-auto">
                        <h2 class="text-3xl md:text-[38px] font-bold font-display text-white mb-2.5 tracking-tight text-center">Sign In</h2>
                        <p class="text-purple-300/40 text-sm mb-10 font-sans text-center">Silahkan masuk untuk melanjutkan</p>

                        <!-- Error Notifications -->
                        @if ($errors->any())
                            <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl p-4 mb-6 text-sm flex items-start gap-3">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                <span>Email atau password salah. Silakan coba kembali.</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="space-y-6">
                            @csrf

                            <!-- Email Input -->
                            <div>
                                <label for="email" class="block text-gray-300 text-xs font-semibold uppercase tracking-wider mb-2 font-sans">Email</label>
                                <div class="relative flex items-center">
                                    <!-- Mail Icon -->
                                    <span class="absolute left-4 text-purple-300/50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                        </svg>
                                    </span>
                                    <input 
                                        id="email" 
                                        type="email" 
                                        name="email" 
                                        value="{{ old('email') }}"
                                        class="w-full bg-[#13072c]/40 border border-purple-500/20 text-white rounded-xl py-3.5 pl-12 pr-4 text-sm font-sans placeholder:text-gray-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all" 
                                        placeholder="Masukkan email Anda"
                                        required 
                                        autofocus
                                    >
                                </div>
                                @error('email')
                                    <div class="text-red-400 text-xs mt-1.5 flex items-center gap-1.5 font-sans">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password Input -->
                            <div>
                                <label for="password" class="block text-gray-300 text-xs font-semibold uppercase tracking-wider mb-2 font-sans">Password</label>
                                <div class="relative flex items-center">
                                    <!-- Lock Icon -->
                                    <span class="absolute left-4 text-purple-300/50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                    </span>
                                    <input 
                                        id="password" 
                                        type="password" 
                                        name="password" 
                                        class="w-full bg-[#13072c]/40 border border-purple-500/20 text-white rounded-xl py-3.5 pl-12 pr-12 text-sm font-sans placeholder:text-gray-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all" 
                                        placeholder="Masukkan password Anda"
                                        required
                                    >
                                    <!-- Eye Icon (Toggle Visibility) -->
                                    <button 
                                        type="button"
                                        id="toggle-password"
                                        class="absolute right-4 text-purple-300/50 hover:text-white transition-colors focus:outline-none"
                                    >
                                        <!-- Eye Open SVG -->
                                        <svg class="w-5 h-5 eye-open-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <!-- Eye Closed SVG (Hidden initially) -->
                                        <svg class="w-5 h-5 eye-closed-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-red-400 text-xs mt-1.5 flex items-center gap-1.5 font-sans">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Remember Me and Forgot Password -->
                            <div class="flex justify-between items-center text-xs font-sans text-purple-300/60">
                                <label class="flex items-center gap-2 cursor-pointer hover:text-white transition-colors">
                                    <input 
                                        type="checkbox" 
                                        id="remember" 
                                        name="remember" 
                                        class="rounded border-purple-500/20 bg-[#13072c]/40 text-[#7D53EC] focus:ring-offset-0 focus:ring-[#7D53EC]/40 w-4 h-4 cursor-pointer"
                                    >
                                    <span>Ingatkan Saya</span>
                                </label>
                                <a href="{{ route('password.request') }}" class="hover:text-white transition-colors underline decoration-purple-500/20 underline-offset-4">Lupa Password?</a>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-center pt-2">
                                <button 
                                    type="submit" 
                                    class="w-48 bg-gradient-to-r from-[#9d7ef2] to-[#7a4be7] hover:from-[#aa8df5] hover:to-[#885bec] text-black font-display font-bold py-3.5 rounded-full transition-all hover:scale-[1.01] hover:shadow-lg hover:shadow-purple-500/15 text-center flex items-center justify-center cursor-pointer"
                                >
                                    Sign in
                                </button>
                            </div>
                        </form>

                        <!-- Footer -->
                        <div class="text-center mt-8 text-xs text-purple-300/40 font-sans">
                            Pengguna Baru? <a href="{{ route('register') }}" class="text-[#FFE600] font-semibold hover:underline decoration-[#FFE600]/30 underline-offset-4 ml-1">Sign Up</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JavaScript for Password Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const openIcon = toggleBtn.querySelector('.eye-open-icon');
            const closedIcon = toggleBtn.querySelector('.eye-closed-icon');

            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    openIcon.classList.add('hidden');
                    closedIcon.classList.remove('hidden');
                } else {
                    passwordInput.type = 'password';
                    openIcon.classList.remove('hidden');
                    closedIcon.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
