<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherProfileFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->seed(RolesSeeder::class);

        $role = Role::where('name', RoleEnum::SUPER_ADMIN->value)->firstOrFail();

        $this->admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@test.sch.id',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_create_teacher_with_gender_male(): void
    {
        $this->actingAs($this->admin)
            ->post(route('master.teachers.store'), [
                'name' => 'Budi Santoso, S.Kom',
                'email' => 'budi@test.sch.id',
                'nip' => '1985010120100101',
                'gender' => 'male',
                'nik' => '3501010101010001',
                'join_date' => '2020-07-01',
                'employment_status' => 'active',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertRedirect(route('master.teachers.index'));

        $teacher = Teacher::whereHas('user', fn ($q) => $q->where('email', 'budi@test.sch.id'))->firstOrFail();

        $this->assertSame('male', $teacher->gender);
        $this->assertSame('3501010101010001', $teacher->nik);
        $this->assertSame('active', $teacher->employment_status);
    }

    public function test_teacher_index_shows_male_gender_as_laki_laki(): void
    {
        $this->actingAs($this->admin)
            ->post(route('master.teachers.store'), [
                'name' => 'Ahmad Fauzi, S.Pd',
                'email' => 'ahmad@test.sch.id',
                'nip' => '1985010120100102',
                'gender' => 'male',
                'join_date' => '2020-07-01',
            ]);

        $this->actingAs($this->admin)
            ->get(route('master.teachers.index'))
            ->assertOk()
            ->assertSee('Ahmad Fauzi, S.Pd')
            ->assertSee('Laki-laki');
    }

    public function test_teacher_create_requires_gender(): void
    {
        $this->actingAs($this->admin)
            ->from(route('master.teachers.create'))
            ->post(route('master.teachers.store'), [
                'name' => 'Tanpa Gender',
                'nip' => '1985010120100103',
                'join_date' => '2020-07-01',
            ])
            ->assertSessionHasErrors('gender');
    }

    public function test_teacher_create_rejects_mismatched_password_confirmation(): void
    {
        $this->actingAs($this->admin)
            ->from(route('master.teachers.create'))
            ->post(route('master.teachers.store'), [
                'name' => 'Password Salah',
                'nip' => '1985010120100104',
                'gender' => 'female',
                'join_date' => '2020-07-01',
                'password' => 'secret123',
                'password_confirmation' => 'secret999',
            ])
            ->assertSessionHasErrors('password');
    }
}
