<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Customer | Fotlist</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-black/30 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-white">📸 Fotlist</h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-gray-300">Halo, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-12">
        <!-- Welcome Section -->
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-white mb-4">🔍 Cari Foto Anda</h2>
            <p class="text-gray-300 text-lg">Temukan foto Anda dengan mudah menggunakan teknologi face recognition</p>
        </div>

        <!-- Search Form -->
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-8 mb-8">
            <form action="{{ route('customer.search-albums') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="mb-6 bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                        <p class="text-red-300">{{ $errors->first() }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                        <p class="text-red-300">{{ session('error') }}</p>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <!-- Event Name -->
                    <div>
                        <label for="event_name" class="block text-sm font-medium text-gray-200 mb-2">
                            📅 Nama Event
                        </label>
                        <input 
                            type="text" 
                            id="event_name" 
                            name="event_name" 
                            value="{{ old('event_name') }}"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Contoh: Pernikahan, Wisuda, Ulang Tahun">
                    </div>

                    <!-- Event Date -->
                    <div>
                        <label for="event_date" class="block text-sm font-medium text-gray-200 mb-2">
                            📆 Tanggal Event
                        </label>
                        <input 
                            type="date" 
                            id="event_date" 
                            name="event_date" 
                            value="{{ old('event_date') }}"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <button 
                    type="submit"
                    class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105">
                    🔍 Cari Album
                </button>
            </form>
        </div>

        <!-- Info Section -->
        <div class="bg-blue-500/20 border border-blue-500/50 rounded-xl p-6">
            <h3 class="text-white font-semibold mb-3 flex items-center gap-2">
                <span class="text-2xl">💡</span>
                Cara Kerja
            </h3>
            <ul class="text-gray-300 space-y-2">
                <li>• Masukkan nama event atau tanggal untuk mencari album</li>
                <li>• Pilih album yang sesuai dengan event Anda</li>
                <li>• Sistem akan otomatis menampilkan foto yang mengandung wajah Anda</li>
                <li>• Anda hanya perlu memilih dan membeli foto yang diinginkan</li>
            </ul>
        </div>
    </div>
</body>
</html>
