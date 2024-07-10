<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create([
            'name' => 'customer'
        ]);
        Role::create([
            'name' => 'merchant'
        ]);
        Role::create([
            'name' => 'admin'
        ]);
    }
}
