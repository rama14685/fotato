@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold font-display gradient-text">Daftar Event</h1>
            <p class="text-purple-300/60 mt-1 text-sm">Kelola event mendatang yang akan ditampilkan di landing page</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold transition inline-flex items-center gap-1.5">
            + Tambah Event
        </a>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-purple-950/30 border-b border-purple-500/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Nama</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Lokasi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Publik</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-500/5">
                    @forelse($events as $event)
                        <tr class="hover:bg-purple-500/5 transition duration-150">
                            <td class="px-6 py-4 text-sm font-medium text-white">{{ $event->name }}</td>
                            <td class="px-6 py-4 text-sm text-purple-200/70">
                                @if($event->start_date && $event->end_date)
                                    {{ $event->start_date->format('d M Y H:i') }} - {{ $event->end_date->format('d M Y H:i') }}
                                @else
                                    <span class="text-purple-300/20">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-purple-300/70">{{ $event->location }}</td>
                            <td class="px-6 py-4 text-sm text-purple-300/40">
                                @if($event->is_public)
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-400 border border-green-500/20">Ya</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-300 border border-purple-500/20">Tidak</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm space-x-3 flex items-center">
                                <a href="{{ route('admin.events.edit', $event) }}" class="text-blue-400 hover:text-white font-semibold transition">Edit</a>
                                <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 font-semibold transition" onclick="return confirm('Hapus event?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-purple-300/40 italic">
                                Belum ada event
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $events->links() }}
    </div>
</div>
@endsection
