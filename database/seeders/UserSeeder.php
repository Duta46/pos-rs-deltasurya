<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
              [
                'name'          => 'Super Admin',
                'email'         => 'superadmin@gmail.com',
                'password'      => bcrypt('12345678'),
            ],
            [
                'name'          => 'Kasir',
                'email'         => 'kasir@gmail.com',
                'password'      => bcrypt('12345678'),
            ],
            [
                'name'          => 'Marketing',
                'email'         => 'marketing@gmail.com',
                'password'      => bcrypt('12345678'),
            ]
        ]);
    }
}
