@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Buat Event Baru</h1>

    <form method="POST" action="{{ route('admin.events.store') }}">
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
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_public" checked> Publik</label>
            </div>
            <div class="flex gap-2">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
                <a href="{{ route('admin.events.index') }}" class="bg-gray-300 px-4 py-2 rounded">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
