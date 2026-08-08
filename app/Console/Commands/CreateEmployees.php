<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Console\Command;

class CreateEmployees extends Command
{
    protected $signature = 'employees:create {--count=200 : Number of employees to create}';
    protected $description = 'Create random employees across departments';

    public function handle()
    {
        $count = $this->option('count');
        $departments = Department::pluck('id')->all();

        if (empty($departments)) {
            $this->error('No departments found. Create departments first.');
            return 1;
        }

        $firstNames = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'David', 'Barbara',
                      'William', 'Elizabeth', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen',
                      'Christopher', 'Nancy', 'Daniel', 'Betty', 'Matthew', 'Margaret', 'Anthony', 'Sandra', 'Mark', 'Ashley',
                      'Donald', 'Kathy', 'Steven', 'Donna', 'Paul', 'Carol', 'Andrew', 'Ruth', 'Joshua', 'Brenda',
                      'Kenneth', 'Cheryl', 'Kevin', 'Catherine', 'Brian', 'Diane', 'George', 'Julie', 'Edward', 'Joyce'];

        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
                     'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
                     'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
                     'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Peterson', 'Phillips', 'Campbell',
                     'Parker', 'Evans', 'Edwards', 'Collins', 'Reyes', 'Stewart', 'Morris', 'Morales', 'Murphy', 'Cook'];

        $this->info("Creating $count employees...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $departmentId = $departments[array_rand($departments)];

            Employee::create([
                'department_id' => $departmentId,
                'employee_number' => 'EMP' . str_pad((Employee::max('id') ?? 0) + $i + 1, 5, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName) . '.' . strtolower($lastName) . $i . '@imaralogic.co.ke',
                'phone' => '07' . rand(10000000, 99999999),
                'gender' => ['male', 'female', 'other'][array_rand(['male', 'female', 'other'])],
                'hire_date' => now()->subDays(rand(30, 1095)),
                'employment_type' => ['full_time', 'part_time', 'contract'][array_rand(['full_time', 'part_time', 'contract'])],
                'status' => 'active',
                'basic_salary' => rand(25000, 250000),
                'bank_name' => ['KCB', 'Equity', 'Safaricom', 'NCBA', 'Absa'][array_rand(['KCB', 'Equity', 'Safaricom', 'NCBA', 'Absa'])],
                'bank_account' => rand(1000000000, 9999999999),
                'address' => 'Nairobi, Kenya',
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ Successfully created $count employees!");

        return 0;
    }
}
