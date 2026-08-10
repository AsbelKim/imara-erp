<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\KraExport;
use App\Models\PayrollRun;
use App\Services\KRAExportService;
use Illuminate\Http\Request;

class KRAExportController extends Controller
{
    protected KRAExportService $exportService;

    public function __construct(KRAExportService $exportService)
    {
        $this->exportService = $exportService;
        $this->middleware('role:Super Admin|HR Manager');
    }

    /**
     * Show KRA exports dashboard
     */
    public function index(Request $request)
    {
        $selectedYear = $request->integer('year', now()->year);
        $selectedType = $request->get('type', '');
        $selectedStatus = $request->get('status', '');

        $query = KraExport::query()
            ->where('year', $selectedYear)
            ->orderByDesc('exported_at');

        if ($selectedType) {
            $query->where('export_type', $selectedType);
        }

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        $exports = $query->paginate(15);
        $statistics = $this->exportService->getExportStatistics($selectedYear);

        $years = PayrollRun::distinct()
            ->pluck('year')
            ->sort()
            ->reverse()
            ->toArray();

        $exportTypes = [
            'p10_list' => 'P10 Payroll List',
            'nssf_contributions' => 'NSSF Contributions',
            'shif_contributions' => 'SHIF Contributions',
            'paye_summary' => 'PAYE Summary',
        ];

        return view('hr.kra-exports.index', compact(
            'exports',
            'statistics',
            'selectedYear',
            'selectedType',
            'selectedStatus',
            'years',
            'exportTypes'
        ));
    }

    /**
     * Show export creation form
     */
    public function create(Request $request)
    {
        $selectedYear = $request->integer('year', now()->year);
        $selectedMonth = $request->integer('month', now()->month);

        $payrollRuns = PayrollRun::where('status', 'processed')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(24)
            ->get();

        $years = range(2024, now()->year + 1);
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return view('hr.kra-exports.create', compact(
            'payrollRuns',
            'years',
            'months',
            'selectedYear',
            'selectedMonth'
        ));
    }

    /**
     * Generate P10 export
     */
    public function generateP10(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2024',
            'month' => 'required|integer|between:1,12',
        ]);

        try {
            $export = $this->exportService->generateP10Export(
                $request->integer('year'),
                $request->integer('month')
            );

            return redirect()
                ->route('hr.kra-exports.show', $export)
                ->with('success', 'P10 export generated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate NSSF contributions export
     */
    public function generateNSSF(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2024',
            'month' => 'required|integer|between:1,12',
        ]);

        try {
            $export = $this->exportService->generateNSSFExport(
                $request->integer('year'),
                $request->integer('month')
            );

            return redirect()
                ->route('hr.kra-exports.show', $export)
                ->with('success', 'NSSF contributions export generated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate SHIF contributions export
     */
    public function generateSHIF(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2024',
            'month' => 'required|integer|between:1,12',
        ]);

        try {
            $export = $this->exportService->generateSHIFExport(
                $request->integer('year'),
                $request->integer('month')
            );

            return redirect()
                ->route('hr.kra-exports.show', $export)
                ->with('success', 'SHIF contributions export generated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate PAYE summary export
     */
    public function generatePAYE(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2024',
            'month' => 'required|integer|between:1,12',
        ]);

        try {
            $export = $this->exportService->generatePAYESummaryExport(
                $request->integer('year'),
                $request->integer('month')
            );

            return redirect()
                ->route('hr.kra-exports.show', $export)
                ->with('success', 'PAYE summary export generated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show export details
     */
    public function show(KraExport $kraExport)
    {
        $kraExport->load('user');
        return view('hr.kra-exports.show', compact('kraExport'));
    }

    /**
     * Download export file
     */
    public function download(KraExport $kraExport)
    {
        try {
            return $this->exportService->downloadExport($kraExport);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark export as submitted
     */
    public function markSubmitted(Request $request, KraExport $kraExport)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $kraExport->update([
            'notes' => $request->get('notes'),
        ]);

        $this->exportService->markAsSubmitted($kraExport);

        return redirect()
            ->back()
            ->with('success', 'Export marked as submitted to KRA');
    }

    /**
     * Delete export (only if not submitted)
     */
    public function destroy(KraExport $kraExport)
    {
        if ($kraExport->status !== 'generated') {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Cannot delete submitted exports']);
        }

        // Delete file
        if (\Storage::disk('local')->exists($kraExport->file_path)) {
            \Storage::disk('local')->delete($kraExport->file_path);
        }

        $kraExport->delete();

        return redirect()
            ->route('hr.kra-exports.index')
            ->with('success', 'Export deleted successfully');
    }

    /**
     * Get export statistics for dashboard
     */
    public function statistics(Request $request)
    {
        $year = $request->integer('year', now()->year);
        $stats = $this->exportService->getExportStatistics($year);

        return response()->json($stats);
    }
}
