@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.audit-logs.index') }}" class="text-purple-300 hover:text-white mb-4 inline-block font-semibold transition">← Kembali</a>
        <h1 class="text-3xl font-black font-display gradient-text">Detail Audit Log</h1>
        <p class="text-purple-300/60 text-sm mt-1">Informasi detail aktivitas admin</p>
    </div>

    <!-- Detail Card -->
    <div class="glass-card rounded-3xl p-8 max-w-3xl">
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Admin -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-300/40">Admin</p>
                    <p class="text-lg font-bold text-white mt-1">{{ $auditLog->admin->name }}</p>
                </div>

                <!-- Waktu -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-300/40">Waktu</p>
                    <p class="text-lg font-bold text-white mt-1">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</p>
                </div>

                <!-- Tipe Aksi -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-300/40">Tipe Aksi</p>
                    <p class="text-lg font-bold text-purple-300 mt-1">{{ ucfirst(str_replace('_', ' ', $auditLog->action_type)) }}</p>
                </div>

                <!-- IP Address -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-300/40">IP Address</p>
                    <p class="text-lg font-bold text-purple-300/60 font-mono mt-1">{{ $auditLog->ip_address ?? '-' }}</p>
                </div>
            </div>
        </div>

        <hr class="my-6 border-purple-500/10">

        <!-- Target Entity -->
        @if($auditLog->target_entity_type)
            <div class="mb-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-300/40">Target Entitas</p>
                <p class="text-lg font-bold text-white mt-1 font-mono">
                    {{ ucfirst($auditLog->target_entity_type) }} #{{ $auditLog->target_entity_id }}
                </p>
            </div>
            <hr class="my-6 border-purple-500/10">
        @endif

        <!-- Description -->
        @if($auditLog->description)
            <div class="mb-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-300/40">Deskripsi</p>
                <p class="text-purple-200 mt-1 leading-relaxed">{{ $auditLog->description }}</p>
            </div>
            <hr class="my-6 border-purple-500/10">
        @endif

        <!-- Changes -->
        @if($auditLog->changes)
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-300/40 mb-4">Perubahan</p>
                <div class="bg-purple-950/20 border border-purple-500/10 rounded-2xl p-6 space-y-4">
                    @foreach($auditLog->changes as $field => $change)
                        <div class="pb-4 border-b border-purple-500/5 last:border-b-0 last:pb-0">
                            <p class="font-bold text-sm text-purple-300">{{ $field }}</p>
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-red-500/5 border border-red-500/10 rounded-xl p-3">
                                    <p class="text-[10px] uppercase font-bold text-red-400">Sebelumnya</p>
                                    <p class="text-xs font-mono mt-1 text-red-300 break-all">{{ is_array($change['from']) ? json_encode($change['from']) : ($change['from'] ?? '-') }}</p>
                                </div>
                                <div class="bg-green-500/5 border border-green-500/10 rounded-xl p-3">
                                    <p class="text-[10px] uppercase font-bold text-green-400">Sesudahnya</p>
                                    <p class="text-xs font-mono mt-1 text-green-300 break-all">{{ is_array($change['to']) ? json_encode($change['to']) : ($change['to'] ?? '-') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
