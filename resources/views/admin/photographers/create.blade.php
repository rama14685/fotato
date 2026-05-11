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
