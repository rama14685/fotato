@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Audit Log Admin</h1>
        <p class="text-gray-600 mt-1">Catatan semua aktivitas admin untuk keamanan dan compliance</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Action Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Tipe Aksi</label>
                <select name="action_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">Semua Tipe Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ $filters['action_type'] == $type ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Admin Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Admin</label>
                <select name="admin_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">Semua Admin</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ $filters['admin_id'] == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <!-- Filter Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Waktu</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Admin</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tipe Aksi</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Target Entitas</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">IP Address</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($auditLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>
                                <p class="font-medium">{{ $log->created_at->format('d/m/Y') }}</p>
                                <p class="text-gray-600">{{ $log->created_at->format('H:i:s') }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $log->admin->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">
                                {{ ucfirst(str_replace('_', ' ', $log->action_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($log->target_entity_type)
                                {{ ucfirst($log->target_entity_type) }} #{{ $log->target_entity_id }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $log->description }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $log->ip_address }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            Tidak ada audit log ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $auditLogs->links() }}
    </div>
</div>
@endsection
