@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Daftar Event</h1>
            <p class="text-gray-600">Kelola event mendatang yang akan ditampilkan di landing page</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">+ Tambah Event</a>
    </div>

    @if(session('success'))
        <div class="mb-4 text-green-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Lokasi</th>
                    <th class="px-4 py-3 text-left">Publik</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $event->name }}</td>
                        <td class="px-4 py-3">{{ optional($event->start_date)->format('d M Y H:i') }} - {{ optional($event->end_date)->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $event->location }}</td>
                        <td class="px-4 py-3">{{ $event->is_public ? 'Ya' : 'Tidak' }}</td>
                        <td class="px-4 py-3 space-x-2">
                            <a href="{{ route('admin.events.edit', $event) }}" class="text-blue-600">Edit</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" style="display:inline">@csrf @method('DELETE')<button onclick="return confirm('Hapus event?')" class="text-red-600">Hapus</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-6 text-center text-gray-500" colspan="5">Belum ada event</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $events->links() }}</div>
</div>
@endsection
