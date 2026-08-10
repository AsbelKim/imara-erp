<?php

namespace Database\Seeders;

use App\Models\StatutoryRate;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StatutoryRateSeeder extends Seeder
{
    /**
     * Run the seeder - seed 2026 statutory rates for Kenya
     */
    public function run(): void
    {
        // Clear existing rates
        StatutoryRate::truncate();

        // NSSF Tier I: up to KES 7,000 at 6%
        StatutoryRate::create([
            'rate_type'    => 'NSSF_TIER_I',
            'year'         => 2026,
            'percentage'   => 6.00,
            'ceiling'      => 7000,
            'floor'        => 0,
            'limit'        => 420,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'NSSF Tier I contribution - up to KES 7,000',
            'sort_order'   => 1,
        ]);

        // NSSF Tier II: KES 7,001 to 36,000 at 6%
        StatutoryRate::create([
            'rate_type'    => 'NSSF_TIER_II',
            'year'         => 2026,
            'percentage'   => 6.00,
            'floor'        => 7001,
            'ceiling'      => 36000,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'NSSF Tier II contribution - KES 7,001 to 36,000',
            'sort_order'   => 2,
        ]);

        // PAYE Personal Relief
        StatutoryRate::create([
            'rate_type'    => 'PAYE_RELIEF',
            'year'         => 2026,
            'amount'       => 2400,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'PAYE Personal Relief - KES 2,400 per month',
            'sort_order'   => 1,
        ]);

        // PAYE Tax Bands - 2026
        // Band 1: 0 - 24,000 at 10%
        StatutoryRate::create([
            'rate_type'    => 'PAYE_BAND',
            'year'         => 2026,
            'floor'        => 0,
            'ceiling'      => 24000,
            'percentage'   => 10.00,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'PAYE Band 1: 0-24,000 at 10%',
            'sort_order'   => 1,
        ]);

        // Band 2: 24,001 - 32,333 at 25%
        StatutoryRate::create([
            'rate_type'    => 'PAYE_BAND',
            'year'         => 2026,
            'floor'        => 24001,
            'ceiling'      => 32333,
            'percentage'   => 25.00,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'PAYE Band 2: 24,001-32,333 at 25%',
            'sort_order'   => 2,
        ]);

        // Band 3: 32,334 - 500,000 at 30%
        StatutoryRate::create([
            'rate_type'    => 'PAYE_BAND',
            'year'         => 2026,
            'floor'        => 32334,
            'ceiling'      => 500000,
            'percentage'   => 30.00,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'PAYE Band 3: 32,334-500,000 at 30%',
            'sort_order'   => 3,
        ]);

        // Band 4: 500,001 - 800,000 at 32.5%
        StatutoryRate::create([
            'rate_type'    => 'PAYE_BAND',
            'year'         => 2026,
            'floor'        => 500001,
            'ceiling'      => 800000,
            'percentage'   => 32.50,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'PAYE Band 4: 500,001-800,000 at 32.5%',
            'sort_order'   => 4,
        ]);

        // Band 5: Above 800,000 at 35%
        StatutoryRate::create([
            'rate_type'    => 'PAYE_BAND',
            'year'         => 2026,
            'floor'        => 800001,
            'ceiling'      => PHP_INT_MAX,
            'percentage'   => 35.00,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'PAYE Band 5: Above 800,000 at 35%',
            'sort_order'   => 5,
        ]);

        // SHIF Insurance Bands (2026) - replaces NHIF
        $shif_bands = [
            ['ceiling' => 5999,   'amount' => 150,  'band' => '1'],
            ['ceiling' => 7999,   'amount' => 300,  'band' => '2'],
            ['ceiling' => 11999,  'amount' => 400,  'band' => '3'],
            ['ceiling' => 14999,  'amount' => 500,  'band' => '4'],
            ['ceiling' => 19999,  'amount' => 600,  'band' => '5'],
            ['ceiling' => 24999,  'amount' => 750,  'band' => '6'],
            ['ceiling' => 29999,  'amount' => 850,  'band' => '7'],
            ['ceiling' => 34999,  'amount' => 900,  'band' => '8'],
            ['ceiling' => 39999,  'amount' => 950,  'band' => '9'],
            ['ceiling' => 44999,  'amount' => 1000, 'band' => '10'],
            ['ceiling' => 49999,  'amount' => 1100, 'band' => '11'],
            ['ceiling' => 59999,  'amount' => 1200, 'band' => '12'],
            ['ceiling' => 69999,  'amount' => 1300, 'band' => '13'],
            ['ceiling' => 79999,  'amount' => 1400, 'band' => '14'],
            ['ceiling' => 89999,  'amount' => 1500, 'band' => '15'],
            ['ceiling' => 99999,  'amount' => 1600, 'band' => '16'],
            ['ceiling' => PHP_INT_MAX, 'amount' => 1700, 'band' => '17'],
        ];

        foreach ($shif_bands as $band) {
            StatutoryRate::create([
                'rate_type'    => 'SHIF_BAND',
                'year'         => 2026,
                'ceiling'      => $band['ceiling'],
                'amount'       => $band['amount'],
                'effective_from' => Carbon::create(2026, 1, 1),
                'description'  => "SHIF Band {$band['band']}: up to KES {$band['ceiling']} = KES {$band['amount']}",
                'sort_order'   => (int) $band['band'],
            ]);
        }

        // Housing Levy: 1.5% of gross salary
        StatutoryRate::create([
            'rate_type'    => 'HOUSING_LEVY',
            'year'         => 2026,
            'percentage'   => 1.50,
            'effective_from' => Carbon::create(2026, 1, 1),
            'description'  => 'Housing Levy: 1.5% of gross salary',
            'sort_order'   => 1,
        ]);

        $this->command->info('Statutory rates seeded successfully for 2026!');
    }
}
