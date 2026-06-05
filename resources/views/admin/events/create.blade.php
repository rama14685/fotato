@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Buat Event Baru</h1>

    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 gap-4 max-w-2xl">
            <div>
                <label class="block text-sm font-medium">Nama Event</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Deskripsi</label>
                <textarea name="description" class="w-full border p-2 rounded">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium">Lokasi</label>
                <input type="text" name="location" value="{{ old('location') }}" class="w-full border p-2 rounded">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm">Start</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block text-sm">End</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" class="w-full border p-2 rounded">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Gambar Event (Poster)</label>
                <input type="file" name="image" class="w-full border p-2 rounded text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <span class="text-xs text-gray-500">Format gambar (jpg, jpeg, png), maks 2MB.</span>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_public" value="1" checked> Publik</label>
            </div>
            <div class="flex gap-2">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
                <a href="{{ route('admin.events.index') }}" class="bg-gray-300 px-4 py-2 rounded">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
