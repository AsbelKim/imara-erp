<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\StatutoryRate;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatutoryRateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->authorizeResource(StatutoryRate::class);
    }

    /**
     * Display all statutory rates
     */
    public function index(Request $request)
    {
        $query = StatutoryRate::query();

        // Filter by rate type
        if ($request->filled('rate_type')) {
            $query->where('rate_type', $request->rate_type);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by active/inactive
        if ($request->filled('status')) {
            $now = now();
            if ($request->status === 'active') {
                $query->where('effective_from', '<=', $now)
                      ->where(function ($q) use ($now) {
                          $q->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $now);
                      });
            }
        }

        $rates = $query->orderBy('rate_type')
                       ->orderBy('sort_order')
                       ->paginate(50);

        $rateTypes = StatutoryRate::distinct('rate_type')->pluck('rate_type');
        $years = StatutoryRate::distinct('year')->pluck('year')->sort();

        return view('hr.statutory-rates.index', compact('rates', 'rateTypes', 'years'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $rateTypes = [
            'NSSF_TIER_I' => 'NSSF Tier I',
            'NSSF_TIER_II' => 'NSSF Tier II',
            'PAYE_BAND' => 'PAYE Band',
            'PAYE_RELIEF' => 'PAYE Relief',
            'SHIF_BAND' => 'SHIF Band',
            'HOUSING_LEVY' => 'Housing Levy',
        ];

        return view('hr.statutory-rates.create', compact('rateTypes'));
    }

    /**
     * Store statutory rate
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rate_type'      => 'required|string',
            'year'           => 'nullable|integer|min:2020',
            'month'          => 'nullable|integer|min:1|max:12',
            'amount'         => 'nullable|numeric|min:0',
            'percentage'     => 'nullable|numeric|min:0|max:100',
            'ceiling'        => 'nullable|numeric|min:0',
            'floor'          => 'nullable|numeric|min:0',
            'limit'          => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date|after:effective_from',
            'description'    => 'nullable|string',
            'sort_order'     => 'required|integer',
        ]);

        StatutoryRate::create($validated);

        return redirect()->route('statutory-rates.index')
                       ->with('success', 'Statutory rate created successfully');
    }

    /**
     * Show edit form
     */
    public function edit(StatutoryRate $statutoryRate)
    {
        $rateTypes = [
            'NSSF_TIER_I' => 'NSSF Tier I',
            'NSSF_TIER_II' => 'NSSF Tier II',
            'PAYE_BAND' => 'PAYE Band',
            'PAYE_RELIEF' => 'PAYE Relief',
            'SHIF_BAND' => 'SHIF Band',
            'HOUSING_LEVY' => 'Housing Levy',
        ];

        return view('hr.statutory-rates.edit', compact('statutoryRate', 'rateTypes'));
    }

    /**
     * Update statutory rate
     */
    public function update(Request $request, StatutoryRate $statutoryRate)
    {
        $validated = $request->validate([
            'rate_type'      => 'required|string',
            'year'           => 'nullable|integer|min:2020',
            'month'          => 'nullable|integer|min:1|max:12',
            'amount'         => 'nullable|numeric|min:0',
            'percentage'     => 'nullable|numeric|min:0|max:100',
            'ceiling'        => 'nullable|numeric|min:0',
            'floor'          => 'nullable|numeric|min:0',
            'limit'          => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date|after:effective_from',
            'description'    => 'nullable|string',
            'sort_order'     => 'required|integer',
        ]);

        $statutoryRate->update($validated);

        return redirect()->route('statutory-rates.show', $statutoryRate)
                       ->with('success', 'Statutory rate updated successfully');
    }

    /**
     * Show statutory rate details
     */
    public function show(StatutoryRate $statutoryRate)
    {
        return view('hr.statutory-rates.show', compact('statutoryRate'));
    }

    /**
     * Delete statutory rate
     */
    public function destroy(StatutoryRate $statutoryRate)
    {
        $statutoryRate->delete();

        return redirect()->route('statutory-rates.index')
                       ->with('success', 'Statutory rate deleted successfully');
    }
}
