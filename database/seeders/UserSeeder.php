<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::insert([
            'name'          => 'Admin',
            'contact_no'    => '9921965797',
            'email'         => 'yadavsushant1992@gmail.com',
            'password'      => bcrypt('12345'),
        ]);
    }
}
