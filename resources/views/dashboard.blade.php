<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Dashboard Fotografer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-sm sm:rounded-3xl mb-8">
                <div class="p-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-white">Halo, {{ Auth::user()->name }}! 👋</h3>
                        <p class="text-gray-400 mt-1">Selamat datang di ruang kerja Anda.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg flex flex-col items-center justify-center text-center hover:bg-white/10 transition">
                    <div class="text-gray-400 text-sm font-medium mb-2">Total Album</div>
                    <div class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">{{ $totalAlbums }}</div>
                </div>
                
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg flex flex-col items-center justify-center text-center hover:bg-white/10 transition">
                    <div class="text-gray-400 text-sm font-medium mb-2">Total Foto</div>
                    <div class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">{{ $totalPhotos }}</div>
                </div>
                
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-lg flex flex-col items-center justify-center text-center hover:bg-white/10 transition">
                    <div class="text-gray-400 text-sm font-medium mb-2">Saldo Pendapatan</div>
                    <div class="text-4xl font-extrabold text-green-400">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </div>
            </div>



        </div>
    </div>
</x-app-layout>