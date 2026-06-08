@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 max-w-2xl mx-auto">
        <a href="{{ route('admin.photographers.index') }}" class="text-purple-300 hover:text-white mb-4 inline-block font-semibold transition">← Kembali</a>
        <h1 class="text-3xl font-bold font-display gradient-text">Tambah Fotografer Baru</h1>
        <p class="text-purple-300/60 text-sm mt-1">Buat akun fotografer baru untuk platform</p>
    </div>

    <!-- Form -->
    <div class="glass-card rounded-3xl p-8 max-w-2xl mx-auto">
        <form method="POST" action="{{ route('admin.photographers.store') }}">
            @csrf

            <!-- Name Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-purple-200 mb-2">Nama Lengkap *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30 @error('name') border-red-500 @enderror"
                       placeholder="Masukkan nama fotografer">
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-purple-200 mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30 @error('email') border-red-500 @enderror"
                       placeholder="Masukkan email fotografer">
                @error('email')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-purple-200 mb-2">Password *</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30 @error('password') border-red-500 @enderror"
                       placeholder="Masukkan password (minimal 8 karakter)">
                @error('password')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation Field -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-purple-200 mb-2">Konfirmasi Password *</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30"
                       placeholder="Konfirmasi password">
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="btn-primary px-8 py-2.5 rounded-xl text-sm font-semibold transition">
                    Buat Fotografer
                </button>
                <a href="{{ route('admin.photographers.index') }}" class="btn-secondary px-8 py-2.5 rounded-xl text-sm font-semibold transition inline-block text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
