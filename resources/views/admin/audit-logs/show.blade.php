@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.audit-logs.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Kembali</a>
        <h1 class="text-3xl font-bold text-gray-900">Detail Audit Log</h1>
        <p class="text-gray-600 mt-1">Informasi detail aktivitas admin</p>
    </div>

    <!-- Detail Card -->
    <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
        <div class="mb-8">
            <div class="grid grid-cols-2 gap-6">
                <!-- Admin -->
                <div>
                    <p class="text-sm text-gray-600 font-medium">Admin</p>
                    <p class="text-lg font-bold text-gray-900 mt-1">{{ $auditLog->admin->name }}</p>
                </div>

                <!-- Waktu -->
                <div>
                    <p class="text-sm text-gray-600 font-medium">Waktu</p>
                    <p class="text-lg font-bold text-gray-900 mt-1">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</p>
                </div>

                <!-- Tipe Aksi -->
                <div>
                    <p class="text-sm text-gray-600 font-medium">Tipe Aksi</p>
                    <p class="text-lg font-bold text-blue-600 mt-1">{{ ucfirst(str_replace('_', ' ', $auditLog->action_type)) }}</p>
                </div>

                <!-- IP Address -->
                <div>
                    <p class="text-sm text-gray-600 font-medium">IP Address</p>
                    <p class="text-lg font-bold text-gray-900 mt-1">{{ $auditLog->ip_address ?? '-' }}</p>
                </div>
            </div>
        </div>

        <hr class="my-8">

        <!-- Target Entity -->
        @if($auditLog->target_entity_type)
            <div class="mb-8">
                <p class="text-sm text-gray-600 font-medium">Target Entitas</p>
                <p class="text-lg font-bold text-gray-900 mt-1">
                    {{ ucfirst($auditLog->target_entity_type) }} #{{ $auditLog->target_entity_id }}
                </p>
            </div>

            <hr class="my-8">
        @endif

        <!-- Description -->
        @if($auditLog->description)
            <div class="mb-8">
                <p class="text-sm text-gray-600 font-medium">Deskripsi</p>
                <p class="text-gray-900 mt-1">{{ $auditLog->description }}</p>
            </div>

            <hr class="my-8">
        @endif

        <!-- Changes -->
        @if($auditLog->changes)
            <div>
                <p class="text-sm text-gray-600 font-medium mb-4">Perubahan</p>
                <div class="bg-gray-50 rounded-lg p-4">
                    @foreach($auditLog->changes as $field => $change)
                        <div class="mb-4 pb-4 border-b last:border-b-0">
                            <p class="font-medium text-gray-900">{{ $field }}</p>
                            <div class="mt-2 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-600">Sebelumnya</p>
                                    <p class="text-sm font-mono text-red-600">{{ $change['from'] ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Sesudahnya</p>
                                    <p class="text-sm font-mono text-green-600">{{ $change['to'] ?? '-' }}</p>
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
