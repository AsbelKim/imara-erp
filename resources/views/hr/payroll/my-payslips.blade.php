<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">My Payslips</h2></x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Period</th>
                        <th class="px-4 py-3 text-right">Gross (KES)</th>
                        <th class="px-4 py-3 text-right">Deductions</th>
                        <th class="px-4 py-3 text-right">Net Pay</th>
                        <th class="px-4 py-3 text-left">PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($payslips as $slip)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $slip->payrollRun->period_label }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($slip->gross_salary) }}</td>
                        <td class="px-4 py-3 text-right text-red-500">{{ number_format($slip->total_deductions) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-green-700">{{ number_format($slip->net_salary) }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('portal.payslips.download', $slip) }}" class="text-blue-600 hover:underline text-xs">Download</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No payslips available yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $payslips->links() }}</div>
        </div>
    </div>
</x-app-layout>
