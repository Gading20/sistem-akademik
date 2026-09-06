<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClassFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Teacher $waliKelas;

    protected Major $major;

    protected AcademicYear $academicYear;

    protected Semester $semester;

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

        $this->major = Major::create(['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL']);

        $this->academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->semester = Semester::create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'ganjil',
            'start_date' => '2025-07-15',
            'end_date' => '2025-12-20',
            'is_active' => true,
        ]);

        $waliKelasUser = $this->makeUser('wali@test.sch.id');

        $this->waliKelas = Teacher::create([
            'user_id' => $waliKelasUser->id,
            'join_date' => '2020-07-01',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_class_with_wali_kelas(): void
    {
        $this->actingAs($this->admin)
            ->post(route('master.classes.store'), [
                'name' => 'X RPL 1',
                'major_id' => $this->major->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'wali_kelas_id' => $this->waliKelas->id,
                'capacity' => 36,
                'is_active' => true,
            ])
            ->assertRedirect(route('master.classes.index'));

        $class = ClassRoom::where('name', 'X RPL 1')->firstOrFail();

        $this->assertSame($this->waliKelas->id, $class->wali_kelas_id);
        $this->assertTrue($class->waliKelas->is($this->waliKelas));
    }

    public function test_class_index_shows_wali_kelas_name(): void
    {
        ClassRoom::create([
            'name' => 'X RPL 1',
            'major_id' => $this->major->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'wali_kelas_id' => $this->waliKelas->id,
            'capacity' => 36,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('master.classes.index'))
            ->assertOk()
            ->assertSee('X RPL 1')
            ->assertSee('Wali Uji');
    }

    public function test_admin_can_update_class_wali_kelas(): void
    {
        $class = ClassRoom::create([
            'name' => 'X RPL 1',
            'major_id' => $this->major->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'capacity' => 36,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->put(route('master.classes.update', $class), [
                'name' => 'X RPL 1',
                'major_id' => $this->major->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'wali_kelas_id' => $this->waliKelas->id,
            ])
            ->assertRedirect(route('master.classes.index'));

        $this->assertSame($this->waliKelas->id, $class->fresh()->wali_kelas_id);
    }

    public function test_class_store_rejects_unknown_wali_kelas(): void
    {
        $this->actingAs($this->admin)
            ->from(route('master.classes.create'))
            ->post(route('master.classes.store'), [
                'name' => 'X RPL 1',
                'major_id' => $this->major->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'wali_kelas_id' => 99999,
            ])
            ->assertSessionHasErrors('wali_kelas_id');
    }

    protected function makeUser(string $email): User
    {
        $role = Role::where('name', RoleEnum::GURU->value)->firstOrFail();

        return User::create([
            'name' => 'Wali Uji',
            'email' => $email,
            'password' => Hash::make('password'),
            'role_id' => $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
