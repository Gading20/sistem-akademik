<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreTeacherRequest;
use App\Http\Requests\Master\UpdateTeacherRequest;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AuditLogService;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Teacher::class);

        $search = $request->input('search');
        $subjectId = $request->input('subject_id');

        $teachers = Teacher::with(['user', 'subject'])
            ->when($search, function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            })
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->active()
            ->latest()
            ->paginate(15);

        $subjects = Subject::orderBy('name')->get();

        return view('master.teachers.index', compact('teachers', 'subjects', 'search', 'subjectId'));
    }

    public function create(): View
    {
        $this->authorize('create', Teacher::class);

        $subjects = Subject::active()->orderBy('name')->get();

        return view('master.teachers.create', compact('subjects'));
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $this->authorize('create', Teacher::class);

        $teacher = $this->teacherService->create($request->validated());

        return redirect()->route('master.teachers.index')
            ->with('success', 'Guru berhasil dibuat.');
    }

    public function edit(Teacher $teacher): View
    {
        $this->authorize('update', $teacher);

        $subjects = Subject::active()->orderBy('name')->get();

        return view('master.teachers.edit', compact('teacher', 'subjects'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $this->authorize('update', $teacher);

        $teacher = $this->teacherService->update($teacher, $request->validated());

        return redirect()->route('master.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $this->authorize('delete', $teacher);

        $teacher->user()->delete();
        $teacher->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Teacher::class,
            $teacher->id
        );

        return redirect()->route('master.teachers.index')
            ->with('success', 'Guru berhasil dihapus.');
    }

    public function import(): View
    {
        $this->authorize('create', Teacher::class);

        return view('master.teachers.import');
    }

    public function processImport(Request $request): RedirectResponse
    {
        $this->authorize('create', Teacher::class);

        set_time_limit(240);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if (in_array($extension, ['csv'])) {
            $handle = fopen($file->getPathname(), 'r');
            $headers = fgetcsv($handle);
            $teachers = [];

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $teachers[] = array_combine($headers, $row);
                }
            }

            fclose($handle);
        } else {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            $headers = array_shift($data);

            $teachers = array_map(function ($row) use ($headers) {
                return count($row) === count($headers) ? array_combine($headers, $row) : null;
            }, $data);
            $teachers = array_filter($teachers);
        }

        $results = $this->teacherService->import($teachers);

        $this->auditLogService->log(
            Auth::user(),
            'imported',
            Teacher::class,
            null,
            null,
            ['success' => $results['success'], 'failed' => $results['failed']]
        );

        return redirect()->route('master.teachers.index')
            ->with('import_results', $results);
    }

    public function downloadTemplate()
    {
        $this->authorize('create', Teacher::class);

        $filename = 'template_import_guru.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'nip', 'nuptk', 'name', 'email', 'gender',
                'subject_name', 'place_of_birth', 'date_of_birth',
                'address', 'phone', 'join_date',
            ]);

            // Sample data
            fputcsv($file, [
                '198501012010011001', '1234567890123456', 'Budi Santoso, S.Pd', 'budi@email.com', 'male',
                'Matematika', 'Jakarta', '1985-01-01',
                'Jl. Pendidikan No. 10', '081234567890', '2010-07-01',
            ]);
            fputcsv($file, [
                '198601012011012001', '1234567890123457', 'Ani Wijaya, S.Pd', 'ani@email.com', 'female',
                'Bahasa Indonesia', 'Bandung', '1986-01-01',
                'Jl. Guru No. 5', '081234567891', '2011-07-01',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
