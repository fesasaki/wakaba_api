<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['id' => 1],
            [
                'active' => true,
                'started' => true,
                'approved' => true,
                'user_type' => 100,
                'name' => 'Administrador do Sistema',
                'phone' => '43999001381',
                'email' => 'felipe.sasaki@arquest.com.br',
                'username' => 'adminuser',
                'password' => '0b1w4nK3n0b1'
            ]
        );
    }
}
