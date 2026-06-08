@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.events.index') }}" class="text-purple-300 hover:text-white mb-4 inline-block font-semibold transition">← Kembali</a>
        <h1 class="text-3xl font-black font-display gradient-text">{{ $event->name }}</h1>
        <p class="text-purple-300/60 text-sm mt-1">
            {{ $event->location }} &bull; {{ optional($event->start_date)->format('d M Y H:i') }}
        </p>
    </div>

    <!-- Description Card -->
    <div class="glass-card rounded-3xl p-8 max-w-2xl">
        <p class="text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-4">Deskripsi Event</p>
        <div class="text-white leading-relaxed">
            {!! nl2br(e($event->description)) !!}
        </div>
    </div>
</div>
@endsection
