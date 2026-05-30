<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; margin: 0; padding: 20px; }
        .header { background: #1d4ed8; color: white; padding: 16px 20px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 4px 0 0; font-size: 11px; opacity: 0.85; }
        .section-title { background: #f3f4f6; padding: 6px 10px; font-weight: bold; font-size: 11px; text-transform: uppercase; color: #374151; margin: 16px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px 10px; }
        .info-table td:first-child { color: #6b7280; width: 45%; }
        .pay-table td { border-bottom: 1px solid #f3f4f6; }
        .pay-table td:last-child { text-align: right; }
        .deduction { color: #dc2626; }
        .total-row td { font-weight: bold; background: #f9fafb; border-top: 2px solid #d1d5db; }
        .net-row td { font-weight: bold; font-size: 14px; background: #dcfce7; color: #15803d; border-top: 2px solid #86efac; }
        .footer { margin-top: 30px; font-size: 10px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Imara Logic ERP</h1>
        <p>Payslip for {{ $payslip->payrollRun->period_label }}</p>
    </div>

    <div class="section-title">Employee Information</div>
    <table class="info-table">
        <tr><td>Employee Name</td><td><strong>{{ $payslip->employee->full_name }}</strong></td></tr>
        <tr><td>Employee No.</td><td>{{ $payslip->employee->employee_number }}</td></tr>
        <tr><td>Department</td><td>{{ $payslip->employee->department->name }}</td></tr>
        <tr><td>KRA PIN</td><td>{{ $payslip->employee->kra_pin ?? '—' }}</td></tr>
        <tr><td>NSSF No.</td><td>{{ $payslip->employee->nssf_number ?? '—' }}</td></tr>
        <tr><td>NHIF No.</td><td>{{ $payslip->employee->nhif_number ?? '—' }}</td></tr>
    </table>

    <div class="section-title">Earnings</div>
    <table class="pay-table">
        <tr><td>Basic Salary</td><td>KES {{ number_format($payslip->gross_salary, 2) }}</td></tr>
        <tr class="total-row"><td>Gross Pay</td><td>KES {{ number_format($payslip->gross_salary, 2) }}</td></tr>
    </table>

    <div class="section-title">Deductions</div>
    <table class="pay-table">
        <tr><td class="deduction">NSSF (Employee)</td><td class="deduction">KES {{ number_format($payslip->nssf_employee, 2) }}</td></tr>
        <tr><td class="deduction">NHIF</td><td class="deduction">KES {{ number_format($payslip->nhif, 2) }}</td></tr>
        <tr><td class="deduction">PAYE</td><td class="deduction">KES {{ number_format($payslip->paye, 2) }}</td></tr>
        <tr><td class="deduction">Housing Levy (1.5%)</td><td class="deduction">KES {{ number_format($payslip->housing_levy, 2) }}</td></tr>
        <tr class="total-row"><td>Total Deductions</td><td>KES {{ number_format($payslip->total_deductions, 2) }}</td></tr>
    </table>

    <br>
    <table class="pay-table">
        <tr class="net-row"><td>NET PAY</td><td>KES {{ number_format($payslip->net_salary, 2) }}</td></tr>
    </table>

    <br>
    <table class="info-table">
        <tr><td>Taxable Income</td><td>KES {{ number_format($payslip->taxable_income, 2) }}</td></tr>
        <tr><td>NSSF (Employer)</td><td>KES {{ number_format($payslip->nssf_employer, 2) }}</td></tr>
    </table>

    <div class="footer">
        This is a computer-generated payslip and does not require a signature. | Generated {{ now()->format('d M Y H:i') }} | Imara Logic ERP
    </div>
</body>
</html>
