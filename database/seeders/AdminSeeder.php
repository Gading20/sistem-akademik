<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'super_admin')->first();

        User::updateOrCreate(
            ['email' => 'admin@smknurululum.sch.id'],
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('password'),
                'role_id' => $role->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
