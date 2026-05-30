<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $hrManager  = Role::firstOrCreate(['name' => 'HR Manager']);
        $employee   = Role::firstOrCreate(['name' => 'Employee']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@imaralogic.co.ke'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('Admin@1234'),
            ]
        );
        $admin->assignRole($superAdmin);
    }
}
