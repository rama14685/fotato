@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.events.index') }}" class="text-purple-300 hover:text-white mb-4 inline-block font-semibold transition">← Kembali</a>
        <h1 class="text-3xl font-bold font-display gradient-text">Edit Event</h1>
        <p class="text-purple-300/60 text-sm mt-1">Perbarui informasi event</p>
    </div>

    <!-- Form -->
    <div class="glass-card rounded-3xl p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <!-- Name Field -->
                <div>
                    <label class="block text-sm font-medium text-purple-200 mb-2">Nama Event *</label>
                    <input type="text" name="name" value="{{ old('name', $event->name) }}"
                           class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30" required>
                </div>

                <!-- Description Field -->
                <div>
                    <label class="block text-sm font-medium text-purple-200 mb-2">Deskripsi</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30">{{ old('description', $event->description) }}</textarea>
                </div>

                <!-- Location Field -->
                <div>
                    <label class="block text-sm font-medium text-purple-200 mb-2">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $event->location) }}"
                           class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white placeholder-purple-300/30">
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-purple-200 mb-2">Mulai</label>
                        <input type="datetime-local" name="start_date" value="{{ old('start_date', optional($event->start_date)->format('Y-m-d\TH:i')) }}"
                               class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-purple-200 mb-2">Selesai</label>
                        <input type="datetime-local" name="end_date" value="{{ old('end_date', optional($event->end_date)->format('Y-m-d\TH:i')) }}"
                               class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                    </div>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-purple-200 mb-2">Gambar Event (Poster)</label>
                    @if($event->image_path)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ $event->name }}" class="h-32 object-cover rounded-xl border border-purple-500/20">
                        </div>
                    @endif
                    <input type="file" name="image" 
                           class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-purple-200 text-sm file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-500/20 file:text-purple-300 hover:file:bg-purple-500/35 cursor-pointer">
                    <span class="block text-xs text-purple-300/40 mt-1.5">Format gambar (jpg, jpeg, png), maks 2MB. Kosongkan jika tidak ingin mengubah.</span>
                </div>

                <!-- Public Status -->
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-purple-200 cursor-pointer">
                        <input type="checkbox" name="is_public" value="1" {{ $event->is_public ? 'checked' : '' }}
                               class="rounded border-purple-500/20 bg-purple-950/20 text-purple-600 focus:ring-purple-500">
                        <span>Publikasikan Event (Tampilkan di Landing Page)</span>
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4 mt-2">
                    <button type="submit" class="btn-primary px-8 py-2.5 rounded-xl text-sm font-semibold transition">
                        Simpan
                    </button>
                    <a href="{{ route('admin.events.index') }}" class="btn-secondary px-8 py-2.5 rounded-xl text-sm font-semibold transition inline-block text-center">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
