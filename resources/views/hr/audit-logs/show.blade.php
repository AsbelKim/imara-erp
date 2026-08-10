@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Audit Log Details</h1>
                <p class="text-gray-600 mt-2">Log ID: {{ $auditLog->id }}</p>
            </div>
            <a href="{{ route('hr.audit-logs.index') }}" class="text-gray-600 hover:text-gray-900">Back</a>
        </div>

        <!-- Main Details -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">Action</h3>
                    <p class="text-lg font-semibold">
                        <span class="px-2 py-1 rounded-full text-sm
                            {{ $auditLog->action == 'created' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $auditLog->action == 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $auditLog->action == 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($auditLog->action) }}
                        </span>
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">User</h3>
                    <p class="text-lg text-gray-900">{{ $auditLog->user?->name ?? 'System' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">Model</h3>
                    <p class="text-lg text-gray-900 font-mono">{{ class_basename($auditLog->model_type) }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">Model ID</h3>
                    <p class="text-lg text-gray-900">{{ $auditLog->model_id }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">Date/Time</h3>
                    <p class="text-lg text-gray-900">{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-600 mb-1">IP Address</h3>
                    <p class="text-lg text-gray-900 font-mono">{{ $auditLog->ip_address ?? 'Unknown' }}</p>
                </div>
            </div>

            @if ($auditLog->description)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-600 mb-2">Description</h3>
                    <p class="text-gray-900">{{ $auditLog->description }}</p>
                </div>
            @endif
        </div>

        <!-- Changes (if any) -->
        @if ($auditLog->old_values || $auditLog->new_values)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Old Values -->
                @if ($auditLog->old_values)
                    <div class="bg-red-50 rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-red-100 border-b border-red-200">
                            <h2 class="text-lg font-semibold text-red-900">Before</h2>
                        </div>
                        <div class="px-6 py-6">
                            <dl class="space-y-4">
                                @foreach ($auditLog->old_values as $key => $value)
                                    <div>
                                        <dt class="text-sm font-medium text-red-600 mb-1">{{ $key }}</dt>
                                        <dd class="text-gray-900 break-words">
                                            @if (is_array($value))
                                                <code class="text-xs">{{ json_encode($value) }}</code>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                @endif

                <!-- New Values -->
                @if ($auditLog->new_values)
                    <div class="bg-green-50 rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-green-100 border-b border-green-200">
                            <h2 class="text-lg font-semibold text-green-900">After</h2>
                        </div>
                        <div class="px-6 py-6">
                            <dl class="space-y-4">
                                @foreach ($auditLog->new_values as $key => $value)
                                    <div>
                                        <dt class="text-sm font-medium text-green-600 mb-1">{{ $key }}</dt>
                                        <dd class="text-gray-900 break-words">
                                            @if (is_array($value))
                                                <code class="text-xs">{{ json_encode($value) }}</code>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Related Changes -->
        @if ($relatedLogs->count() > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Related Changes</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($relatedLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $log->action == 'created' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $log->action == 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $log->action == 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $log->user?->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $log->description ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
