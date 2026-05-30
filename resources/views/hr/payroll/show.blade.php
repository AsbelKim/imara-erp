<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-gray-800">Payroll — {{ $payroll->period_label }}</h2>
                @php $badge = ['processed'=>'bg-blue-100 text-blue-800','paid'=>'bg-green-100 text-green-800','voided'=>'bg-red-100 text-red-700','draft'=>'bg-gray-100 text-gray-600']; @endphp
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge[$payroll->status] ?? '' }}">{{ ucfirst($payroll->status) }}</span>
            </div>
            <div class="flex items-center gap-3">
                @role('Super Admin')
                    @if($payroll->isVoidable())
                        <button onclick="document.getElementById('voidModal').classList.remove('hidden')"
                                class="bg-red-50 border border-red-300 text-red-600 hover:bg-red-100 text-sm px-3 py-1.5 rounded-lg transition">
                            Void This Run
                        </button>
                    @endif
                @endrole
                <a href="{{ route('hr.payroll.index') }}" class="text-sm text-gray-600 hover:underline">← Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

        {{-- Summary --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white shadow rounded-lg p-4">
                <p class="text-xs text-gray-500">Total Gross</p>
                <p class="text-xl font-bold text-gray-800">KES {{ number_format($payroll->total_gross) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-4">
                <p class="text-xs text-gray-500">Total Deductions</p>
                <p class="text-xl font-bold text-red-600">KES {{ number_format($payroll->total_deductions) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-4">
                <p class="text-xs text-gray-500">Total Net Pay</p>
                <p class="text-xl font-bold text-green-700">KES {{ number_format($payroll->total_net) }}</p>
            </div>
        </div>

        {{-- Payslips Table --}}
        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-3 text-left">Employee</th>
                        <th class="px-3 py-3 text-left">Dept</th>
                        <th class="px-3 py-3 text-right">Gross</th>
                        <th class="px-3 py-3 text-right">NSSF</th>
                        <th class="px-3 py-3 text-right">NHIF</th>
                        <th class="px-3 py-3 text-right">PAYE</th>
                        <th class="px-3 py-3 text-right">H.Levy</th>
                        <th class="px-3 py-3 text-right">Net Pay</th>
                        <th class="px-3 py-3 text-left">PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($payroll->payslips as $slip)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-medium text-gray-800">{{ $slip->employee->full_name }}</td>
                        <td class="px-3 py-2 text-gray-500 text-xs">{{ $slip->employee->department->name }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($slip->gross_salary) }}</td>
                        <td class="px-3 py-2 text-right text-red-500">{{ number_format($slip->nssf_employee) }}</td>
                        <td class="px-3 py-2 text-right text-red-500">{{ number_format($slip->nhif) }}</td>
                        <td class="px-3 py-2 text-right text-red-500">{{ number_format($slip->paye) }}</td>
                        <td class="px-3 py-2 text-right text-red-500">{{ number_format($slip->housing_levy) }}</td>
                        <td class="px-3 py-2 text-right font-semibold text-green-700">{{ number_format($slip->net_salary) }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ route('hr.payroll.payslip', [$payroll, $slip->employee]) }}" class="text-blue-600 hover:underline text-xs">Download</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@role('Super Admin')
@if($payroll->isVoidable())
<div id="voidModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
        <div class="flex items-center gap-3 text-red-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <h3 class="font-bold text-lg">Void {{ $payroll->period_label }}</h3>
        </div>
        <p class="text-sm text-gray-600">All <strong>{{ $payroll->payslips->count() }} payslips</strong> will be permanently deleted. This cannot be undone.</p>
        <form method="POST" action="{{ route('hr.payroll.void', $payroll) }}">
            @csrf @method('DELETE')
            <div class="space-y-3">
                <textarea name="void_reason" rows="3" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
                          placeholder="Reason for voiding this payroll run..."></textarea>
                <div class="flex gap-3">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2 rounded-lg">
                        Confirm Void
                    </button>
                    <button type="button" onclick="document.getElementById('voidModal').classList.add('hidden')"
                            class="border border-gray-300 text-gray-600 text-sm px-5 py-2 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endrole

</x-app-layout>
