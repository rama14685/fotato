@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.albums.index') }}" class="text-gray-300 hover:text-white mb-4 inline-block">← Kembali</a>
        <h1 class="text-3xl font-bold gradient-text">Buat Album Baru</h1>
        <p class="text-gray-400 mt-1">Buat koleksi foto untuk fotografer</p>
    </div>

    <!-- Form -->
    <div class="glass-card rounded-lg p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.albums.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Photographer Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-white mb-2">Fotografer *</label>
                <select name="photographer_id" class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-white/30 text-white @error('photographer_id') border-red-500 @enderror">
                    <option value="">-- Pilih Fotografer --</option>
                    @foreach($photographers as $photographer)
                        <option value="{{ $photographer->id }}" {{ old('photographer_id') == $photographer->id ? 'selected' : '' }}>
                            {{ $photographer->name }}
                        </option>
                    @endforeach
                </select>
                @error('photographer_id')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-white mb-2">Judul Album *</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-white/30 text-white placeholder-gray-500 @error('title') border-red-500 @enderror"
                       placeholder="Contoh: CFD Simpang Lima">
                @error('title')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Location Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-white mb-2">Lokasi *</label>
                <input type="text" name="location" value="{{ old('location') }}"
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-white/30 text-white placeholder-gray-500 @error('location') border-red-500 @enderror"
                       placeholder="Contoh: Simpang Lima, Semarang">
                @error('location')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Event Date Field -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-white mb-2">Tanggal Event *</label>
                <input type="datetime-local" name="event_date" value="{{ old('event_date') }}"
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-white/30 text-white @error('event_date') border-red-500 @enderror">
                @error('event_date')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Thumbnail Upload -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-white mb-2">Thumbnail Album</label>
                <div class="border-2 border-dashed border-white/20 rounded-lg p-6 text-center hover:border-white/40 transition">
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="hidden" onchange="previewThumbnail(event)">
                    <label for="thumbnail" class="cursor-pointer">
                        <div id="thumbnail-preview" class="mb-4">
                            <div class="text-4xl mb-2">📸</div>
                            <p class="text-gray-400 text-sm">Klik untuk upload thumbnail</p>
                            <p class="text-gray-500 text-xs mt-1">JPG, PNG, GIF (Max 5MB)</p>
                        </div>
                    </label>
                </div>
                @error('thumbnail')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="btn-primary px-8 py-2 rounded-lg font-medium transition">
                    Buat Album
                </button>
                <a href="{{ route('admin.albums.index') }}" class="btn-secondary px-8 py-2 rounded-lg font-medium transition inline-block">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function previewThumbnail(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnail-preview').innerHTML = `
                <img src="${e.target.result}" class="max-h-48 mx-auto rounded-lg mb-2">
                <p class="text-gray-400 text-sm">${file.name}</p>
            `;
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
