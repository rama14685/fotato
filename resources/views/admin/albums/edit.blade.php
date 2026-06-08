@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.albums.index') }}" class="text-purple-300 hover:text-white mb-4 inline-block font-semibold transition">← Kembali</a>
        <h1 class="text-3xl font-bold font-display gradient-text">Edit Album</h1>
        <p class="text-purple-300/60 text-sm mt-1">Perbarui informasi album</p>
    </div>

    <!-- Form -->
    <div class="glass-card rounded-3xl p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.albums.update', $album) }}">
            @csrf
            @method('PUT')

            <!-- Title Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-purple-200 mb-2">Judul Album *</label>
                <input type="text" name="title" value="{{ old('title', $album->title) }}"
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30 @error('title') border-red-500 @enderror"
                       placeholder="Contoh: CFD Simpang Lima">
                @error('title')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Location Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-purple-200 mb-2">Lokasi *</label>
                <input type="text" name="location" value="{{ old('location', $album->location) }}"
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30 @error('location') border-red-500 @enderror"
                       placeholder="Contoh: Simpang Lima, Semarang">
                @error('location')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Event Date Field -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-purple-200 mb-2">Tanggal Event *</label>
                <input type="datetime-local" name="event_date" value="{{ old('event_date', $album->event_date->format('Y-m-d\TH:i')) }}"
                       class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white @error('event_date') border-red-500 @enderror">
                @error('event_date')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="btn-primary px-8 py-2.5 rounded-xl text-sm font-semibold transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.albums.index') }}" class="btn-secondary px-8 py-2.5 rounded-xl text-sm font-semibold transition inline-block text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
