<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignRoleToUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = \App\Models\User::where('email', 'superadmin@gmail.com')->first();
        $superAdmin->assignRole('Super Admin');

        $kasir = \App\Models\User::where('email', 'kasir@gmail.com')->first();
        $kasir->assignRole('Kasir');

        $marketing = \App\Models\User::where('email', 'marketing@gmail.com')->first();
        $marketing->assignRole('Marketing');
    }
}
