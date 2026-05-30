<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Payroll Runs</h2>
            <a href="{{ route('hr.payroll.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">Run Payroll</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Period</th>
                        <th class="px-4 py-3 text-left">Total Gross (KES)</th>
                        <th class="px-4 py-3 text-left">Total Deductions</th>
                        <th class="px-4 py-3 text-left">Total Net</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Processed</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($runs as $run)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $run->period_label }}</td>
                        <td class="px-4 py-3">{{ number_format($run->total_gross) }}</td>
                        <td class="px-4 py-3 text-red-600">{{ number_format($run->total_deductions) }}</td>
                        <td class="px-4 py-3 font-semibold text-green-700">{{ number_format($run->total_net) }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = ['processed'=>'bg-blue-100 text-blue-800','paid'=>'bg-green-100 text-green-800','voided'=>'bg-red-100 text-red-700','draft'=>'bg-gray-100 text-gray-600'];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs {{ $badge[$run->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($run->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $run->processed_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 flex items-center gap-3">
                            @if($run->status !== 'voided')
                                <a href="{{ route('hr.payroll.show', $run) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            @endif
                            @role('Super Admin')
                                @if($run->isVoidable())
                                    <button onclick="openVoidModal({{ $run->id }}, '{{ $run->period_label }}')"
                                            class="text-red-500 hover:underline text-xs">Void</button>
                                @endif
                            @endrole
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No payroll runs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $runs->links() }}</div>
        </div>
    </div>
{{-- Void confirmation modal --}}
@role('Super Admin')
<div id="voidModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
        <div class="flex items-center gap-3 text-red-600">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <h3 class="font-bold text-lg">Void Payroll Run</h3>
        </div>
        <p class="text-sm text-gray-600">You are about to void <strong id="voidPeriodLabel"></strong>. This will permanently delete all payslips for this run. This action cannot be undone.</p>
        <form id="voidForm" method="POST">
            @csrf @method('DELETE')
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for voiding <span class="text-red-500">*</span></label>
                    <textarea name="void_reason" rows="3" required
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
                              placeholder="e.g. Wrong period selected, salary data updated..."></textarea>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                        Yes, Void This Run
                    </button>
                    <button type="button" onclick="closeVoidModal()" class="border border-gray-300 text-gray-600 text-sm px-5 py-2 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
function openVoidModal(id, label) {
    document.getElementById('voidPeriodLabel').textContent = label;
    document.getElementById('voidForm').action = `/hr/payroll/${id}/void`;
    document.getElementById('voidModal').classList.remove('hidden');
}
function closeVoidModal() {
    document.getElementById('voidModal').classList.add('hidden');
}
</script>
@endrole

</x-app-layout>
