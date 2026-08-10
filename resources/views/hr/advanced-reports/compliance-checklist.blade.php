@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">HR Compliance Checklist</h1>
            <p class="text-gray-600">Annual compliance audit for {{ $year }}</p>
        </div>

        <!-- Compliance Score -->
        <div class="bg-white rounded-lg shadow p-8 mb-8">
            <div class="text-center mb-8">
                <div class="relative w-32 h-32 mx-auto">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                        <circle cx="50" cy="50" r="45" fill="none" stroke="{% if $complianceScore >= 80 %}#10b981{% elseif $complianceScore >= 60 %}#f59e0b{% else %}#ef4444{% endif %}"
                                stroke-width="8" stroke-dasharray="{{ ($complianceScore / 100) * 283 }} 283" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-3xl font-bold text-gray-900">{{ number_format($complianceScore, 0) }}%</span>
                    </div>
                </div>
            </div>
            <p class="text-center text-gray-600">
                {{ $completedItems }} of {{ $totalItems }} compliance items verified
            </p>
        </div>

        <!-- Compliance Categories -->
        <div class="space-y-6">
            @foreach ($checklist as $category)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
                        <h2 class="text-lg font-semibold text-gray-900">{{ $category['category'] }}</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="space-y-3">
                            @foreach ($category['items'] as $item)
                                <div class="flex items-center justify-between p-3 rounded-lg {{ $item['status'] ? 'bg-green-50' : 'bg-red-50' }}">
                                    <div class="flex items-center">
                                        @if ($item['status'])
                                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                        <span class="font-medium text-gray-900">{{ $item['name'] }}</span>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $item['status'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                        {{ $item['status'] ? 'Verified' : 'Pending' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Legend -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Compliance Score Interpretation</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <span class="inline-block w-4 h-4 bg-green-500 rounded mr-2"></span>
                    <span class="text-sm text-gray-700">80-100%: Excellent</span>
                </div>
                <div>
                    <span class="inline-block w-4 h-4 bg-yellow-500 rounded mr-2"></span>
                    <span class="text-sm text-gray-700">60-79%: Good</span>
                </div>
                <div>
                    <span class="inline-block w-4 h-4 bg-red-500 rounded mr-2"></span>
                    <span class="text-sm text-gray-700">Below 60%: Action Required</span>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('hr.advanced-reports.dashboard') }}" class="text-blue-600 hover:text-blue-900">← Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
