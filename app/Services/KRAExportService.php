<?php

namespace App\Services;

use App\Models\KraExport;
use App\Models\Payslip;
use App\Models\PayrollRun;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * KRAExportService handles generation of KRA compliance export formats
 * Supports P10, NSSF, SHIF, and PAYE exports
 */
class KRAExportService
{
    /**
     * Generate P10 payroll list export
     * Format: CSV with employee details and monthly earnings
     */
    public function generateP10Export(int $year, int $month): KraExport
    {
        $payslips = $this->getPayslipsForPeriod($year, $month);

        if ($payslips->isEmpty()) {
            throw new \Exception("No payslip records found for {$year}-{$month}");
        }

        $fileName = "p10_{$year}_{$month}_" . now()->timestamp . ".csv";
        $csvContent = $this->generateP10Csv($payslips);
        $filePath = "kra-exports/{$fileName}";

        Storage::disk('local')->put($filePath, $csvContent);

        return KraExport::create([
            'user_id' => auth()->id(),
            'export_type' => 'p10_list',
            'year' => $year,
            'month' => $month,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'record_count' => $payslips->count(),
            'total_amount' => $payslips->sum('gross_salary'),
            'status' => 'generated',
            'exported_at' => now(),
        ]);
    }

    /**
     * Generate NSSF employee contributions export
     */
    public function generateNSSFExport(int $year, int $month): KraExport
    {
        $payslips = $this->getPayslipsForPeriod($year, $month);

        if ($payslips->isEmpty()) {
            throw new \Exception("No payslip records found for {$year}-{$month}");
        }

        $fileName = "nssf_contrib_{$year}_{$month}_" . now()->timestamp . ".csv";
        $csvContent = $this->generateNSSFCsv($payslips);
        $filePath = "kra-exports/{$fileName}";

        Storage::disk('local')->put($filePath, $csvContent);

        return KraExport::create([
            'user_id' => auth()->id(),
            'export_type' => 'nssf_contributions',
            'year' => $year,
            'month' => $month,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'record_count' => $payslips->count(),
            'total_amount' => $payslips->sum('nssf_employee'),
            'status' => 'generated',
            'exported_at' => now(),
        ]);
    }

    /**
     * Generate SHIF contributions export (replaces NHIF)
     */
    public function generateSHIFExport(int $year, int $month): KraExport
    {
        $payslips = $this->getPayslipsForPeriod($year, $month);

        if ($payslips->isEmpty()) {
            throw new \Exception("No payslip records found for {$year}-{$month}");
        }

        $fileName = "shif_contrib_{$year}_{$month}_" . now()->timestamp . ".csv";
        $csvContent = $this->generateSHIFCsv($payslips);
        $filePath = "kra-exports/{$fileName}";

        Storage::disk('local')->put($filePath, $csvContent);

        return KraExport::create([
            'user_id' => auth()->id(),
            'export_type' => 'shif_contributions',
            'year' => $year,
            'month' => $month,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'record_count' => $payslips->count(),
            'total_amount' => $payslips->sum('nhif'), // Using nhif field for SHIF temporarily
            'status' => 'generated',
            'exported_at' => now(),
        ]);
    }

    /**
     * Generate PAYE summary export
     */
    public function generatePAYESummaryExport(int $year, int $month): KraExport
    {
        $payslips = $this->getPayslipsForPeriod($year, $month);

        if ($payslips->isEmpty()) {
            throw new \Exception("No payslip records found for {$year}-{$month}");
        }

        $fileName = "paye_summary_{$year}_{$month}_" . now()->timestamp . ".csv";
        $csvContent = $this->generatePAYESummaryCsv($payslips);
        $filePath = "kra-exports/{$fileName}";

        Storage::disk('local')->put($filePath, $csvContent);

        return KraExport::create([
            'user_id' => auth()->id(),
            'export_type' => 'paye_summary',
            'year' => $year,
            'month' => $month,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'record_count' => $payslips->count(),
            'total_amount' => $payslips->sum('paye'),
            'status' => 'generated',
            'exported_at' => now(),
        ]);
    }

    /**
     * Generate P10 CSV content
     */
    private function generateP10Csv($payslips): string
    {
        $output = "PAYROLL PERIOD,EMPLOYEE_NUMBER,FULL_NAME,BASIC_SALARY,GROSS_SALARY,PAYE_TAX,NSSF_EMPLOYEE,HOUSING_LEVY,NHIF,TOTAL_DEDUCTIONS,NET_SALARY,KRA_PIN\n";

        foreach ($payslips as $payslip) {
            $period = $payslip->payrollRun->period_label ?? "{$payslip->payrollRun->month}/{$payslip->payrollRun->year}";
            $output .= implode(',', [
                $period,
                $payslip->employee->employee_number,
                "\"{$payslip->employee->full_name}\"",
                number_format($payslip->basic_salary ?? 0, 2, '.', ''),
                number_format($payslip->gross_salary ?? 0, 2, '.', ''),
                number_format($payslip->paye ?? 0, 2, '.', ''),
                number_format($payslip->nssf_employee ?? 0, 2, '.', ''),
                number_format($payslip->housing_levy ?? 0, 2, '.', ''),
                number_format($payslip->nhif ?? 0, 2, '.', ''),
                number_format($payslip->total_deductions ?? 0, 2, '.', ''),
                number_format($payslip->net_salary ?? 0, 2, '.', ''),
                $payslip->employee->kra_pin ?? '',
            ]) . "\n";
        }

        return $output;
    }

    /**
     * Generate NSSF contributions CSV
     */
    private function generateNSSFCsv($payslips): string
    {
        $output = "PAYROLL PERIOD,EMPLOYEE_NUMBER,FULL_NAME,NSSF_TIER_1_EMPLOYEE,NSSF_TIER_1_EMPLOYER,NSSF_TIER_2_EMPLOYEE,NSSF_TIER_2_EMPLOYER\n";

        foreach ($payslips as $payslip) {
            $period = $payslip->payrollRun->period_label ?? "{$payslip->payrollRun->month}/{$payslip->payrollRun->year}";
            // Default tier 1 employee is nssf_employee, tier 1 employer is typically double
            $tier1Employee = $payslip->nssf_employee ?? 0;
            $tier1Employer = $tier1Employee; // Typically 1:1 match

            $output .= implode(',', [
                $period,
                $payslip->employee->employee_number,
                "\"{$payslip->employee->full_name}\"",
                number_format($tier1Employee, 2, '.', ''),
                number_format($tier1Employer, 2, '.', ''),
                '0.00', // Tier 2 employee
                '0.00', // Tier 2 employer
            ]) . "\n";
        }

        return $output;
    }

    /**
     * Generate SHIF contributions CSV
     */
    private function generateSHIFCsv($payslips): string
    {
        $output = "PAYROLL PERIOD,EMPLOYEE_NUMBER,FULL_NAME,BASIC_SALARY,SHIF_CONTRIBUTION_RATE,SHIF_CONTRIBUTION_AMOUNT\n";

        foreach ($payslips as $payslip) {
            $period = $payslip->payrollRun->period_label ?? "{$payslip->payrollRun->month}/{$payslip->payrollRun->year}";
            $shifAmount = $payslip->nhif ?? 0; // Using nhif field as SHIF temporarily
            $shifRate = $payslip->basic_salary > 0 ? ($shifAmount / $payslip->basic_salary) * 100 : 0;

            $output .= implode(',', [
                $period,
                $payslip->employee->employee_number,
                "\"{$payslip->employee->full_name}\"",
                number_format($payslip->basic_salary ?? 0, 2, '.', ''),
                number_format($shifRate, 2, '.', ''),
                number_format($shifAmount, 2, '.', ''),
            ]) . "\n";
        }

        return $output;
    }

    /**
     * Generate PAYE summary CSV
     */
    private function generatePAYESummaryCsv($payslips): string
    {
        $output = "PAYROLL PERIOD,EMPLOYEE_NUMBER,FULL_NAME,BASIC_SALARY,GROSS_SALARY,PAYE_TAX,TAX_RELIEF,NET_TAX,KRA_PIN\n";

        foreach ($payslips as $payslip) {
            $period = $payslip->payrollRun->period_label ?? "{$payslip->payrollRun->month}/{$payslip->payrollRun->year}";
            $paye = $payslip->paye ?? 0;
            $relief = 0; // Default relief amount

            $output .= implode(',', [
                $period,
                $payslip->employee->employee_number,
                "\"{$payslip->employee->full_name}\"",
                number_format($payslip->basic_salary ?? 0, 2, '.', ''),
                number_format($payslip->gross_salary ?? 0, 2, '.', ''),
                number_format($paye, 2, '.', ''),
                number_format($relief, 2, '.', ''),
                number_format($paye - $relief, 2, '.', ''),
                $payslip->employee->kra_pin ?? '',
            ]) . "\n";
        }

        return $output;
    }

    /**
     * Get payslips for a specific period
     */
    private function getPayslipsForPeriod(int $year, int $month): \Illuminate\Database\Eloquent\Collection
    {
        return Payslip::with(['employee', 'payrollRun'])
            ->whereHas('payrollRun', function ($query) use ($year, $month) {
                $query->where('year', $year)
                    ->where('month', $month)
                    ->where('status', 'processed');
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * Download export file as CSV
     */
    public function downloadExport(KraExport $export): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!Storage::disk('local')->exists($export->file_path)) {
            throw new \Exception("Export file not found");
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$export->file_name}",
        ];

        return response()->streamDownload(
            fn() => echo Storage::disk('local')->get($export->file_path),
            $export->file_name,
            $headers
        );
    }

    /**
     * Mark export as submitted to KRA
     */
    public function markAsSubmitted(KraExport $export): KraExport
    {
        $export->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        return $export;
    }

    /**
     * Get export history with summary
     */
    public function getExportHistory(string $type = null, int $year = null, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        $query = KraExport::query()
            ->orderByDesc('exported_at')
            ->limit($limit);

        if ($type) {
            $query->byType($type);
        }

        if ($year) {
            $query->where('year', $year);
        }

        return $query->get();
    }

    /**
     * Get export statistics
     */
    public function getExportStatistics(int $year, int $month = null): array
    {
        $query = KraExport::where('year', $year);

        if ($month) {
            $query->where('month', $month);
        }

        $exports = $query->get();

        return [
            'total_exports' => $exports->count(),
            'p10_exports' => $exports->where('export_type', 'p10_list')->count(),
            'nssf_exports' => $exports->where('export_type', 'nssf_contributions')->count(),
            'shif_exports' => $exports->where('export_type', 'shif_contributions')->count(),
            'paye_exports' => $exports->where('export_type', 'paye_summary')->count(),
            'submitted' => $exports->where('status', 'submitted')->count(),
            'approved' => $exports->where('status', 'approved')->count(),
            'total_records' => $exports->sum('record_count'),
            'total_amount' => $exports->sum('total_amount'),
        ];
    }
}
