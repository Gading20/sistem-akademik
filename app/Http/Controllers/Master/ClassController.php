<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreClassRequest;
use App\Http\Requests\Master\UpdateClassRequest;
use App\Models\AcademicYear;
use App\Models\ClassMember;
use App\Models\ClassRoom;
use App\Models\Competency;
use App\Models\Major;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ClassRoom::class);

        $search = $request->input('search');
        $academicYearId = $request->input('academic_year_id');

        $classes = ClassRoom::with(['major', 'competency', 'academicYear', 'waliKelas.user'])
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $majors = Major::orderBy('name')->get();

        return view('master.classes.index', compact('classes', 'academicYears', 'majors', 'search', 'academicYearId'));
    }

    public function create(): View
    {
        $this->authorize('create', ClassRoom::class);

        $majors = Major::orderBy('name')->get();
        $competencies = Competency::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();
        $teachers = Teacher::with('user')->active()->get();

        return view('master.classes.create', compact('majors', 'competencies', 'academicYears', 'semesters', 'teachers'));
    }

    public function store(StoreClassRequest $request): RedirectResponse
    {
        $this->authorize('create', ClassRoom::class);

        $class = ClassRoom::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            ClassRoom::class,
            $class->id,
            null,
            $class->toArray()
        );

        return redirect()->route('master.classes.index')
            ->with('success', 'Kelas berhasil dibuat.');
    }

    public function edit(ClassRoom $class): View
    {
        $this->authorize('update', $class);

        $majors = Major::orderBy('name')->get();
        $competencies = Competency::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();
        $teachers = Teacher::with('user')->active()->get();

        return view('master.classes.edit', compact('class', 'majors', 'competencies', 'academicYears', 'semesters', 'teachers'));
    }

    public function update(UpdateClassRequest $request, ClassRoom $class): RedirectResponse
    {
        $this->authorize('update', $class);

        $oldData = $class->toArray();
        $class->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            ClassRoom::class,
            $class->id,
            $oldData,
            $class->fresh()->toArray()
        );

        return redirect()->route('master.classes.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(ClassRoom $class): RedirectResponse
    {
        $this->authorize('delete', $class);

        $class->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            ClassRoom::class,
            $class->id
        );

        return redirect()->route('master.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }

    public function manageMembers(ClassRoom $class): View
    {
        $this->authorize('update', $class);

        $class->load(['major', 'academicYear']);
        $members = ClassMember::with('student.user')
            ->where('class_id', $class->id)
            ->where('is_active', true)
            ->get();

        $availableStudents = Student::active()
            ->whereNotIn('id', $members->pluck('student_id'))
            ->with('user')
            ->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();

        return view('master.classes.members', compact('class', 'members', 'availableStudents', 'academicYears', 'semesters'));
    }

    public function addMember(Request $request, ClassRoom $class): RedirectResponse
    {
        $this->authorize('update', $class);

        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $existing = ClassMember::where('class_id', $class->id)
            ->where('student_id', $request->student_id)
            ->where('is_active', true)
            ->exists();

        if ($existing) {
            return back()->withErrors(['student_id' => 'Siswa sudah terdaftar di kelas ini.']);
        }

        // Periode keanggotaan mengikuti tahun ajaran & semester kelas tsb
        // (kelas selalu berada dalam satu tahun ajaran/semester tertentu).
        ClassMember::create([
            'class_id' => $class->id,
            'student_id' => $request->student_id,
            'academic_year_id' => $class->academic_year_id,
            'semester_id' => $class->semester_id,
            'is_active' => true,
        ]);

        // Sinkronkan kolom class_id pada data siswa agar semua fitur
        // (daftar ujian, daftar siswa per kelas) memakai sumber yang sama.
        Student::where('id', $request->student_id)->update(['class_id' => $class->id]);

        return redirect()->route('master.classes.members', $class)
            ->with('success', 'Anggota kelas berhasil ditambahkan.');
    }

    public function removeMember(ClassRoom $class, ClassMember $member): RedirectResponse
    {
        $this->authorize('update', $class);

        $member->update(['is_active' => false]);

        // Jika kelas yang dilepas adalah kelas aktif siswa, bersihkan
        // penunjuk class_id agar siswa tidak lagi menerima ujian kelas itu.
        Student::where('id', $member->student_id)
            ->where('class_id', $class->id)
            ->update(['class_id' => null]);

        $this->auditLogService->log(
            Auth::user(),
            'removed_member',
            ClassRoom::class,
            $class->id,
            $member->toArray(),
            null
        );

        return redirect()->route('master.classes.members', $class)
            ->with('success', 'Anggota kelas berhasil dihapus.');
    }
}
