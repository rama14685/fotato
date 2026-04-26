<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fotlist - Temukan Momenmu</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen relative overflow-hidden">

    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/30 rounded-full mix-blend-screen filter blur-[120px] opacity-80 animate-pulse z-0"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-600/30 rounded-full mix-blend-screen filter blur-[120px] opacity-80 z-0"></div>

    <nav class="relative z-10 w-full p-6 flex justify-between items-center max-w-7xl mx-auto">
        <div class="text-2xl font-bold tracking-tighter bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-purple-500">
            Fotlist.
        </div>
        <div class="space-x-3 flex items-center">
            <a href="{{ route('catalog.index') }}" class="text-gray-300 hover:text-white transition">🛍️ Belanja</a>
            <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition">Masuk</a>
            <a href="{{ route('register') }}" class="px-5 py-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-full transition font-medium">📸 Jadilah Fotografer</a>
        </div>
    </nav>

    <main class="relative z-10 flex flex-col items-center justify-center pt-24 px-4 text-center">
        
        <div class="max-w-3xl w-full bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-10 md:p-14 shadow-[0_8px_32px_0_rgba(0,0,0,0.36)]">
            
            <h1 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight">
                Momen Anda di Jalan, <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">Tersimpan Sempurna.</span>
            </h1>
            
            <p class="text-gray-400 text-lg mb-10 max-w-xl mx-auto">
                Difoto oleh fotografer jalanan hari ini? Cari foto Anda berdasarkan lokasi dan waktu, lalu tebus dengan resolusi tinggi.
            </p>

            <form method="GET" action="{{ route('catalog.index') }}" class="flex flex-col md:flex-row gap-3">
                <input type="text" name="location" placeholder="Contoh: Kota Lama Semarang" class="w-full px-5 py-3 bg-gray-900/50 border border-gray-700 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 text-white placeholder-gray-500 transition">
                <input type="date" name="date_from" class="w-full md:w-auto px-5 py-3 bg-gray-900/50 border border-gray-700 rounded-xl focus:outline-none focus:border-purple-500 text-white transition color-scheme-dark">
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition transform hover:scale-105">
                    🔍 Cari Foto
                </button>
            </form>

            <div class="mt-8 flex flex-col md:flex-row items-center justify-center gap-4">
                <a href="{{ route('catalog.index') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold rounded-xl transition">
                    🛒 Mulai Belanja Foto
                </a>
            </div>

        </div>
    </main>

</body>
</html>