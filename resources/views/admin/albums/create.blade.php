@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.albums.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Kembali</a>
        <h1 class="text-3xl font-bold text-gray-900">Buat Album Baru</h1>
        <p class="text-gray-600 mt-1">Buat koleksi foto untuk fotografer</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.albums.store') }}">
            @csrf

            <!-- Photographer Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-900 mb-2">Fotografer *</label>
                <select name="photographer_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('photographer_id') border-red-500 @enderror">
                    <option value="">-- Pilih Fotografer --</option>
                    @foreach($photographers as $photographer)
                        <option value="{{ $photographer->id }}" {{ old('photographer_id') == $photographer->id ? 'selected' : '' }}>
                            {{ $photographer->name }}
                        </option>
                    @endforeach
                </select>
                @error('photographer_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-900 mb-2">Judul Album *</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('title') border-red-500 @enderror"
                       placeholder="Contoh: CFD Simpang Lima">
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Location Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-900 mb-2">Lokasi *</label>
                <input type="text" name="location" value="{{ old('location') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('location') border-red-500 @enderror"
                       placeholder="Contoh: Simpang Lima, Semarang">
                @error('location')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Event Date Field -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-900 mb-2">Tanggal Event *</label>
                <input type="datetime-local" name="event_date" value="{{ old('event_date') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('event_date') border-red-500 @enderror">
                @error('event_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-medium transition">
                    Buat Album
                </button>
                <a href="{{ route('admin.albums.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-8 py-2 rounded-lg font-medium transition inline-block">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
