@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.photographers.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Kembali</a>
        <h1 class="text-3xl font-bold text-gray-900">Tambah Fotografer Baru</h1>
        <p class="text-gray-600 mt-1">Buat akun fotografer baru untuk platform</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.photographers.store') }}">
            @csrf

            <!-- Name Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-900 mb-2">Nama Lengkap *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror"
                       placeholder="Masukkan nama fotografer">
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-900 mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('email') border-red-500 @enderror"
                       placeholder="Masukkan email fotografer">
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-900 mb-2">Password *</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('password') border-red-500 @enderror"
                       placeholder="Masukkan password (minimal 8 karakter)">
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation Field -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-900 mb-2">Konfirmasi Password *</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                       placeholder="Konfirmasi password">
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-medium transition">
                    Buat Fotografer
                </button>
                <a href="{{ route('admin.photographers.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-8 py-2 rounded-lg font-medium transition inline-block">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.photographers.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Kembali</a>
        <h1 class="text-3xl font-bold text-gray-900">Edit Fotografer</h1>
        <p class="text-gray-600 mt-1">Perbarui informasi fotografer</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.photographers.update', $photographer) }}">
            @csrf
            @method('PUT')

            <!-- Name Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-900 mb-2">Nama Lengkap *</label>
                <input type="text" name="name" value="{{ old('name', $photographer->name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror"
                       placeholder="Masukkan nama fotografer">
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-900 mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email', $photographer->email) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('email') border-red-500 @enderror"
                       placeholder="Masukkan email fotografer">
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-medium transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.photographers.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-8 py-2 rounded-lg font-medium transition inline-block">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Fotografer</h1>
            <p class="text-gray-600 mt-1">Kelola akun fotografer di platform</p>
        </div>
        <a href="{{ route('admin.photographers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
            + Tambah Fotografer
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.photographers.index') }}" class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <input type="text" name="search" placeholder="Cari nama atau email..." 
                       value="{{ $searchQuery }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="all" {{ $currentStatus == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ $currentStatus == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $currentStatus == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nama</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal Bergabung</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($photographers as $photographer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $photographer->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $photographer->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $photographer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($photographer->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $photographer->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.photographers.show', $photographer) }}" class="text-blue-600 hover:text-blue-800 font-medium">Lihat</a>
                            <a href="{{ route('admin.photographers.edit', $photographer) }}" class="text-purple-600 hover:text-purple-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('admin.photographers.toggle-status', $photographer) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="text-orange-600 hover:text-orange-800 font-medium" onclick="return confirm('Ubah status fotografer?')">
                                    {{ $photographer->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Tidak ada fotografer ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $photographers->links() }}
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.photographers.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Kembali</a>
        <h1 class="text-3xl font-bold text-gray-900">{{ $photographer->name }}</h1>
        <p class="text-gray-600 mt-1">{{ $photographer->email }}</p>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Status</p>
            <p class="text-2xl font-bold mt-2 {{ $photographer->status === 'active' ? 'text-green-600' : 'text-red-600' }}">
                {{ ucfirst($photographer->status) }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Total Album</p>
            <p class="text-2xl font-bold mt-2 text-blue-600">{{ $photographer->albums->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Saldo Dompet</p>
            <p class="text-2xl font-bold mt-2 text-purple-600">Rp {{ number_format($photographer->wallet_balance, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm font-medium">Total Pendapatan</p>
            <p class="text-2xl font-bold mt-2 text-yellow-600">Rp {{ number_format($earnings, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-8 flex gap-3">
        <a href="{{ route('admin.photographers.edit', $photographer) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
            Edit Fotografer
        </a>
        <form method="POST" action="{{ route('admin.photographers.toggle-status', $photographer) }}" style="display: inline;">
            @csrf
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-medium transition" onclick="return confirm('Ubah status fotografer?')">
                {{ $photographer->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </form>
    </div>

    <!-- Albums Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">Album Fotografer</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Judul Album</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Lokasi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal Event</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Jumlah Foto</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($albums as $album)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $album->title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $album->location }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $album->event_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $album->photos->count() }} foto</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('admin.albums.show', $album) }}" class="text-blue-600 hover:text-blue-800">Lihat</a>
                                <a href="{{ route('admin.albums.edit', $album) }}" class="text-purple-600 hover:text-purple-800">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada album</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t">
            {{ $albums->links() }}
        </div>
    </div>
</div>
@endsection
