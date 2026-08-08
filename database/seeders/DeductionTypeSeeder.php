<?php

namespace Database\Seeders;

use App\Models\DeductionType;
use Illuminate\Database\Seeder;

class DeductionTypeSeeder extends Seeder
{
    /**
     * Run the seeder
     */
    public function run(): void
    {
        // Clear existing
        DeductionType::truncate();

        $deductions = [
            [
                'name'         => 'PAYE',
                'type'         => 'fixed',
                'description'  => 'Personal Income Tax (PAYE)',
                'is_statutory' => true,
                'is_active'    => true,
                'sort_order'   => 1,
            ],
            [
                'name'         => 'NSSF',
                'type'         => 'fixed',
                'description'  => 'National Social Security Fund',
                'is_statutory' => true,
                'is_active'    => true,
                'sort_order'   => 2,
            ],
            [
                'name'         => 'SHIF',
                'type'         => 'fixed',
                'description'  => 'Social Health Insurance Fund',
                'is_statutory' => true,
                'is_active'    => true,
                'sort_order'   => 3,
            ],
            [
                'name'         => 'Housing Levy',
                'type'         => 'percentage',
                'description'  => 'Government Housing Levy (1.5%)',
                'is_statutory' => true,
                'is_active'    => true,
                'sort_order'   => 4,
            ],
            [
                'name'         => 'Loan Repayment',
                'type'         => 'fixed',
                'description'  => 'Employee loan monthly repayment',
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 5,
            ],
            [
                'name'         => 'Union Dues',
                'type'         => 'fixed',
                'description'  => 'Trade union membership dues',
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 6,
            ],
            [
                'name'         => 'Medical Insurance',
                'type'         => 'fixed',
                'description'  => 'Group medical insurance premium',
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 7,
            ],
            [
                'name'         => 'Life Insurance',
                'type'         => 'fixed',
                'description'  => 'Group life insurance premium',
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 8,
            ],
            [
                'name'         => 'Disciplinary Fine',
                'type'         => 'fixed',
                'description'  => 'Disciplinary fine or penalty',
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 9,
            ],
            [
                'name'         => 'Advance Salary Recovery',
                'type'         => 'fixed',
                'description'  => 'Recovery of advance salary',
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 10,
            ],
            [
                'name'         => 'Employee Contribution - Pension',
                'type'         => 'percentage',
                'description'  => 'Employee pension scheme contribution',
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 11,
            ],
            [
                'name'         => 'Court Order',
                'type'         => 'fixed',
                'description'  => 'Court-ordered deduction (e.g., alimony)',
                'is_statutory' => false,
                'is_active'    => true,
                'sort_order'   => 12,
            ],
        ];

        foreach ($deductions as $deduction) {
            DeductionType::create($deduction);
        }

        $this->command->info('Deduction types seeded successfully!');
    }
}
