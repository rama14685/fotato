@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold">{{ $event->name }}</h1>
    <p class="text-gray-600">{{ $event->location }} — {{ optional($event->start_date)->format('d M Y H:i') }}</p>
    <div class="mt-4 bg-white p-6 rounded shadow">{!! nl2br(e($event->description)) !!}</div>
    <a href="{{ route('admin.events.index') }}" class="inline-block mt-4 text-blue-600">← Kembali</a>
</div>
@endsection
