<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian Album | Fotlist</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-black/30 backdrop-blur-lg border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('customer.dashboard') }}" class="text-2xl font-bold text-white hover:text-gray-300">
                        📸 Fotlist
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-gray-300">{{ Auth::user()->name }}</span>
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
    <div class="max-w-7xl mx-auto px-4 py-12">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-gray-300 hover:text-white transition">
                ← Kembali ke Pencarian
            </a>
        </div>

        <!-- Search Summary -->
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-white mb-2">Hasil Pencarian</h2>
            <div class="text-gray-300">
                @if($searchQuery['event_name'])
                    <p>📅 Event: <span class="font-semibold">{{ $searchQuery['event_name'] }}</span></p>
                @endif
                @if($searchQuery['event_date'])
                    <p>📆 Tanggal: <span class="font-semibold">{{ \Carbon\Carbon::parse($searchQuery['event_date'])->format('d F Y') }}</span></p>
                @endif
                <p class="mt-2">Ditemukan <span class="font-bold text-blue-400">{{ $albums->count() }}</span> album</p>
            </div>
        </div>

        <!-- Albums Grid -->
        @if($albums->isEmpty())
            <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-xl p-8 text-center">
                <p class="text-yellow-200 text-lg">Tidak ada album yang sesuai dengan pencarian Anda</p>
                <a href="{{ route('customer.dashboard') }}" class="inline-block mt-4 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition">
                    Coba Pencarian Lain
                </a>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($albums as $album)
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl overflow-hidden hover:border-blue-500/50 transition group">
                        <!-- Album Thumbnail -->
                        <div class="aspect-video bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center">
                            <span class="text-6xl">📷</span>
                        </div>

                        <!-- Album Info -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-blue-400 transition">
                                {{ $album->title }}
                            </h3>
                            <div class="text-gray-300 text-sm space-y-1 mb-4">
                                <p>📍 {{ $album->location }}</p>
                                <p>📅 {{ \Carbon\Carbon::parse($album->event_date)->format('d F Y') }}</p>
                                <p>👤 {{ $album->photographer?->name ?? 'Admin' }}</p>
                                <p>🖼️ {{ $album->photo_count }} foto</p>
                            </div>

                            <a href="{{ route('customer.view-album', $album->id) }}" 
                               class="block w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg text-center transition transform hover:scale-105">
                                🔍 Lihat Foto Saya
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
