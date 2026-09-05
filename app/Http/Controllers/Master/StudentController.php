<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreStudentRequest;
use App\Http\Requests\Master\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Services\AuditLogService;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $search = $request->input('search');
        $classId = $request->input('class_id');
        $status = $request->input('status');

        $students = Student::with(['user', 'classRoom'])
            ->when($search, function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            })
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        $classes = ClassRoom::orderBy('name')->get();

        return view('master.students.index', compact('students', 'classes', 'search', 'classId', 'status'));
    }

    public function create(): View
    {
        $this->authorize('create', Student::class);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('master.students.create', compact('classes', 'academicYears'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $student = $this->studentService->create($request->validated());

        return redirect()->route('master.students.index')
            ->with('success', 'Siswa berhasil dibuat.');
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('master.students.edit', compact('student', 'classes', 'academicYears'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $student = $this->studentService->update($student, $request->validated());

        return redirect()->route('master.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $student->user()->delete();
        $student->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Student::class,
            $student->id
        );

        return redirect()->route('master.students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }

    public function import(): View
    {
        $this->authorize('create', Student::class);

        return view('master.students.import');
    }

    public function processImport(Request $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if (in_array($extension, ['csv'])) {
            $handle = fopen($file->getPathname(), 'r');
            $headers = fgetcsv($handle);
            $students = [];

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $students[] = array_combine($headers, $row);
                }
            }

            fclose($handle);
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            $headers = array_shift($data);

            $students = array_map(function ($row) use ($headers) {
                return count($row) === count($headers) ? array_combine($headers, $row) : null;
            }, $data);
            $students = array_filter($students);
        }

        $results = $this->studentService->import($students);

        return redirect()->route('master.students.index')
            ->with('import_results', $results);
    }

    public function downloadTemplate()
    {
        $this->authorize('create', Student::class);

        $filename = 'template_import_siswa.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'nisn', 'nis', 'name', 'email', 'gender',
                'class_name', 'birth_place', 'birth_date',
                'religion', 'address', 'phone'
            ]);

            // Sample data
            fputcsv($file, [
                '0012345678', '2024001', 'Ahmad Rizky', 'ahmad@email.com', 'male',
                'X RPL 1', 'Jakarta', '2008-05-15',
                'Islam', 'Jl. Merdeka No. 1', '081234567890'
            ]);
            fputcsv($file, [
                '0012345679', '2024002', 'Siti Nurhaliza', 'siti@email.com', 'female',
                'X RPL 2', 'Bandung', '2008-06-20',
                'Islam', 'Jl. Asia Afrika No. 5', '081234567891'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function manageClassMembership(Student $student): View
    {
        $this->authorize('update', $student);

        $student->load('classMembers.classRoom', 'classMembers.academicYear');
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $classes = ClassRoom::active()->orderBy('name')->get();

        return view('master.students.index', compact('student', 'academicYears', 'classes'));
    }
}
