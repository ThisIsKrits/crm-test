<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name'     => 'superadmin',
            ],
            [
                'name'  => 'employee',
            ],
            [
                'name'  => 'manager'
            ]
        ];

        foreach ($data as $value) {
            Role::create($value);
        }
    }
}
