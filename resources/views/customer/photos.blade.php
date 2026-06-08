<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $album->title }} | Fotlist</title>
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
            <a href="javascript:history.back()" class="inline-flex items-center text-gray-300 hover:text-white transition">
                ← Kembali ke Album
            </a>
        </div>

        <!-- Album Header -->
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl p-6 mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">{{ $album->title }}</h1>
            <div class="text-gray-300">
                <p>📍 {{ $album->location }}</p>
                <p>📅 {{ \Carbon\Carbon::parse($album->event_date)->format('d F Y') }}</p>
                <p>👤 Fotografer: {{ $album->photographer?->name ?? 'Admin' }}</p>
            </div>
        </div>

        @if(isset($noFaceData) && $noFaceData)
            <!-- No Face Data Message -->
            <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-xl p-8 text-center">
                <p class="text-yellow-200 text-lg mb-4">{{ $message }}</p>
                <a href="{{ route('customer.dashboard') }}" class="inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition">
                    Kembali ke Pencarian
                </a>
            </div>
        @elseif(isset($noMatches) && $noMatches)
            <!-- No Matches Message -->
            <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-xl p-8 text-center">
                <p class="text-yellow-200 text-lg mb-2">Tidak ada foto yang cocok dengan wajah Anda di album ini</p>
                <p class="text-gray-300 text-sm mb-4">Threshold similarity: {{ $threshold * 100 }}%</p>
                <div class="flex gap-4 justify-center">
                    <a href="{{ route('customer.view-all-photos', $album->id) }}" 
                       class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition">
                        Lihat Semua Foto
                    </a>
                    <a href="{{ route('customer.dashboard') }}" 
                       class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                        Kembali ke Pencarian
                    </a>
                </div>
            </div>
        @else
            <!-- Match Summary -->
            <div class="bg-green-500/20 border border-green-500/50 rounded-xl p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-200 text-lg font-semibold">
                            ✓ Ditemukan {{ $totalPhotos }} foto yang cocok dengan wajah Anda!
                        </p>
                        <p class="text-gray-300 text-sm">Threshold similarity: {{ $threshold * 100 }}%</p>
                    </div>
                    <a href="{{ route('customer.view-all-photos', $album->id) }}" 
                       class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition text-sm">
                        Lihat Semua Foto
                    </a>
                </div>
            </div>

            <!-- Photos Grid -->
            <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
                @foreach($photos as $photo)
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl overflow-hidden hover:border-blue-500/50 transition group">
                        <!-- Photo Image -->
                        <div class="aspect-square bg-gradient-to-br from-gray-700 to-gray-800 relative">
                            <img src="{{ Storage::url($photo->watermark_path ?: $photo->original_path) }}" 
                                 alt="Photo {{ $photo->id }}"
                                 class="w-full h-full object-cover">
                            
                            <!-- Similarity Badge -->
                            <div class="absolute top-2 right-2 px-3 py-1 bg-green-500 rounded-full text-white text-xs font-bold">
                                {{ $photo->similarity_percentage }}% Match
                            </div>
                        </div>

                        <!-- Photo Info -->
                        <div class="p-4">
                            <p class="text-white font-semibold mb-2">Rp {{ number_format($photo->price, 0, ',', '.') }}</p>
                            <button class="w-full px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg transition transform hover:scale-105">
                                🛒 Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(isset($totalPages) && $totalPages > 1)
                <div class="flex justify-center gap-2">
                    @for($i = 1; $i <= $totalPages; $i++)
                        <a href="{{ route('customer.view-album', ['album' => $album->id, 'page' => $i]) }}" 
                           class="px-4 py-2 {{ $i == $currentPage ? 'bg-blue-500' : 'bg-white/10 hover:bg-white/20' }} text-white rounded-lg transition">
                            {{ $i }}
                        </a>
                    @endfor
                </div>
            @endif
        @endif
    </div>
</body>
</html>
