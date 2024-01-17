<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('123123')
        ]);
        $superAdmin->assignRole('Super Admin');

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('123123')
        ]);
        $admin->assignRole('Admin');

        $productManager = User::create([
            'name' => 'Product Manager',
            'email' => 'manager@test.com',
            'password' => Hash::make('123123')
        ]);
        $productManager->assignRole('Product Manager');
    }
}
