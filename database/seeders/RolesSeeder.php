<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'label' => 'Super Admin'],
            ['name' => 'admin_sekolah', 'label' => 'Admin Sekolah'],
            ['name' => 'kepala_sekolah', 'label' => 'Kepala Sekolah'],
            ['name' => 'wakil_kepala_sekolah', 'label' => 'Wakil Kepala Sekolah'],
            ['name' => 'guru', 'label' => 'Guru'],
            ['name' => 'wali_kelas', 'label' => 'Wali Kelas'],
            ['name' => 'siswa', 'label' => 'Siswa'],
            ['name' => 'orang_tua', 'label' => 'Orang Tua / Wali'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['label' => $role['label']]
            );
        }
    }
}
