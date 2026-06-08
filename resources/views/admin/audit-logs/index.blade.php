@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold font-display gradient-text">Audit Log Admin</h1>
        <p class="text-purple-300/60 text-sm mt-1">Catatan semua aktivitas admin untuk keamanan dan compliance</p>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-3xl p-6 mb-6">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Action Type Filter -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-2">Tipe Aksi</label>
                <select name="action_type" class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                    <option value="" class="bg-[#0d061a]">Semua Tipe Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" class="bg-[#0d061a]" {{ $filters['action_type'] == $type ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Admin Filter -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-2">Admin</label>
                <select name="admin_id" class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
                    <option value="" class="bg-[#0d061a]">Semua Admin</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" class="bg-[#0d061a]" {{ $filters['admin_id'] == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full px-4 py-2 bg-purple-950/20 border border-purple-500/20 rounded-xl focus:outline-none focus:border-purple-500/50 text-white">
            </div>

            <!-- Filter Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full btn-primary px-6 py-2 rounded-xl text-sm font-semibold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-purple-950/30 border-b border-purple-500/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Waktu</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Admin</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Tipe Aksi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Target Entitas</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Deskripsi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">IP Address</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-purple-300/60">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-500/5">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-purple-500/5 transition duration-150">
                            <td class="px-6 py-4 text-sm text-white">
                                <div>
                                    <p class="font-semibold">{{ $log->created_at->format('d/m/Y') }}</p>
                                    <p class="text-purple-300/40 text-xs mt-0.5">{{ $log->created_at->format('H:i:s') }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-purple-200/70">{{ $log->admin->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 bg-purple-500/10 text-purple-300 text-xs rounded-full font-semibold border border-purple-500/20">
                                    {{ ucfirst(str_replace('_', ' ', $log->action_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-purple-300/55">
                                @if($log->target_entity_type)
                                    <span class="font-medium text-xs font-mono bg-purple-500/5 px-2 py-0.5 rounded border border-purple-500/10">{{ ucfirst($log->target_entity_type) }} #{{ $log->target_entity_id }}</span>
                                @else
                                    <span class="text-purple-300/20">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-purple-200/70 max-w-xs truncate">{{ $log->description }}</td>
                            <td class="px-6 py-4 text-sm text-purple-300/40 font-mono">{{ $log->ip_address }}</td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-purple-300 hover:text-white font-semibold transition">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-purple-300/40 italic">
                                Tidak ada audit log ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $auditLogs->links() }}
    </div>
</div>
@endsection
