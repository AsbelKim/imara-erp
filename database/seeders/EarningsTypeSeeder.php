<?php

namespace Database\Seeders;

use App\Models\EarningsType;
use Illuminate\Database\Seeder;

class EarningsTypeSeeder extends Seeder
{
    /**
     * Run the seeder
     */
    public function run(): void
    {
        // Clear existing
        EarningsType::truncate();

        $earnings = [
            [
                'name'         => 'Basic Salary',
                'type'         => 'fixed',
                'description'  => 'Employee basic monthly salary',
                'is_taxable'   => true,
                'is_statutory' => true,
                'is_active'    => true,
                'sort_order'   => 1,
            ],
            [
                'name'         => 'Housing Allowance',
                'type'         => 'fixed',
                'description'  => 'Monthly housing allowance',
                'is_taxable'   => true,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 2,
            ],
            [
                'name'         => 'Transport Allowance',
                'type'         => 'fixed',
                'description'  => 'Monthly transport allowance',
                'is_taxable'   => true,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 3,
            ],
            [
                'name'         => 'Meal Allowance',
                'type'         => 'fixed',
                'description'  => 'Daily meal allowance',
                'is_taxable'   => true,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 4,
            ],
            [
                'name'         => 'Communication Allowance',
                'type'         => 'fixed',
                'description'  => 'Mobile and phone allowance',
                'is_taxable'   => true,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 5,
            ],
            [
                'name'         => 'Overtime',
                'type'         => 'fixed',
                'description'  => 'Overtime pay',
                'is_taxable'   => true,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 6,
            ],
            [
                'name'         => 'Bonus',
                'type'         => 'fixed',
                'description'  => 'Performance or annual bonus',
                'is_taxable'   => true,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 7,
            ],
            [
                'name'         => 'Commission',
                'type'         => 'percentage',
                'description'  => 'Commission as percentage of basic salary',
                'is_taxable'   => true,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 8,
            ],
            [
                'name'         => 'Performance Incentive',
                'type'         => 'fixed',
                'description'  => 'Performance-based incentive',
                'is_taxable'   => true,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 9,
            ],
            [
                'name'         => 'Leave Allowance',
                'type'         => 'fixed',
                'description'  => 'Annual leave allowance (non-working)',
                'is_taxable'   => false,
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 10,
            ],
        ];

        foreach ($earnings as $earning) {
            EarningsType::create($earning);
        }

        $this->command->info('Earnings types seeded successfully!');
    }
}
