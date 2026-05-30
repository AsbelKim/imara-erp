<x-portal-layout title="My Payslips">
    <div class="space-y-4 pt-2">

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3 text-left">Period</th>
                        <th class="px-5 py-3 text-right">Gross (KES)</th>
                        <th class="px-5 py-3 text-right">Deductions</th>
                        <th class="px-5 py-3 text-right">Net Pay</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payslips as $slip)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $slip->payrollRun->period_label }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">{{ number_format($slip->gross_salary) }}</td>
                        <td class="px-5 py-3 text-right text-red-500">{{ number_format($slip->total_deductions) }}</td>
                        <td class="px-5 py-3 text-right font-bold text-green-600 text-base">{{ number_format($slip->net_salary) }}</td>
                        <td class="px-5 py-3 flex gap-3">
                            <a href="{{ route('portal.payslips.show', $slip) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="{{ route('portal.payslips.download', $slip) }}" class="text-blue-600 hover:underline text-xs">Download PDF</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">No payslips available yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $payslips->links() }}</div>
        </div>

    </div>
</x-portal-layout>
