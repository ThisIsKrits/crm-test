<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'superadmin')->first();

        $data = [
            'email'  => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'role_id'   => $role->id,
        ];

        User::create($data);
    }
}
