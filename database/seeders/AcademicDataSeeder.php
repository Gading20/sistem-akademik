<?php

namespace Database\Seeders;

use App\Enums\ExamStatusEnum;
use App\Enums\ExamTypeEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\SemesterEnum;
use App\Models\AcademicYear;
use App\Models\ClassMember;
use App\Models\ClassRoom;
use App\Models\Competency;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Major;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademicDataSeeder extends Seeder
{
    public function run(): void
    {
        $siswaRole = Role::where('name', RoleEnum::SISWA->value)->first();
        $guruRole = Role::where('name', RoleEnum::GURU->value)->first();

        $ay1 = AcademicYear::create([
            'name' => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date' => '2025-06-30',
            'is_active' => false,
        ]);

        $ay2 = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-14',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $s1_2024 = Semester::create([
            'academic_year_id' => $ay1->id,
            'name' => SemesterEnum::GANJIL,
            'start_date' => '2024-07-15',
            'end_date' => '2024-12-20',
            'is_active' => false,
        ]);

        $s2_2024 = Semester::create([
            'academic_year_id' => $ay1->id,
            'name' => SemesterEnum::GENAP,
            'start_date' => '2025-01-06',
            'end_date' => '2025-06-13',
            'is_active' => false,
        ]);

        $s1_2025 = Semester::create([
            'academic_year_id' => $ay2->id,
            'name' => SemesterEnum::GANJIL,
            'start_date' => '2025-07-14',
            'end_date' => '2025-12-19',
            'is_active' => true,
        ]);

        $s2_2025 = Semester::create([
            'academic_year_id' => $ay2->id,
            'name' => SemesterEnum::GENAP,
            'start_date' => '2026-01-05',
            'end_date' => '2026-06-12',
            'is_active' => false,
        ]);

        $tkj = Major::create(['name' => 'Teknik Komputer dan Jaringan', 'code' => 'TKJ', 'description' => 'Program keahlian bidang teknologi informasi']);
        $rpl = Major::create(['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL', 'description' => 'Program keahlian pengembangan perangkat lunak']);
        $akl = Major::create(['name' => 'Akuntansi dan Keuangan Lembaga', 'code' => 'AKL', 'description' => 'Program keahlian bidang akuntansi']);
        $otkp = Major::create(['name' => 'Otomatisasi dan Tata Kelola Perkantoran', 'code' => 'OTKP', 'description' => 'Program keahlian tata kelola perkantoran']);

        $competencies = [
            $tkj->id => [
                ['name' => 'Jaringan Komputer', 'code' => 'TKJ-01'],
                ['name' => 'Sistem Operasi', 'code' => 'TKJ-02'],
            ],
            $rpl->id => [
                ['name' => 'Pemrograman Web', 'code' => 'RPL-01'],
                ['name' => 'Basis Data', 'code' => 'RPL-02'],
            ],
            $akl->id => [
                ['name' => 'Akuntansi Dasar', 'code' => 'AKL-01'],
                ['name' => 'Laporan Keuangan', 'code' => 'AKL-02'],
            ],
            $otkp->id => [
                ['name' => 'Tata Kelola Perkantoran', 'code' => 'OTKP-01'],
                ['name' => 'Kepengurusan Dokumen', 'code' => 'OTKP-02'],
            ],
        ];

        foreach ($competencies as $majorId => $comps) {
            foreach ($comps as $comp) {
                Competency::create(array_merge($comp, ['major_id' => $majorId]));
            }
        }

        $roomData = [
            ['name' => 'Ruang 101', 'code' => 'R101', 'capacity' => 36, 'building' => 'Gedung A', 'floor' => 1],
            ['name' => 'Ruang 102', 'code' => 'R102', 'capacity' => 36, 'building' => 'Gedung A', 'floor' => 1],
            ['name' => 'Lab Komputer', 'code' => 'LAB01', 'capacity' => 30, 'building' => 'Gedung B', 'floor' => 2],
            ['name' => 'Ruang Guru', 'code' => 'RG01', 'capacity' => 20, 'building' => 'Gedung A', 'floor' => 3],
        ];

        foreach ($roomData as $r) {
            Room::create($r);
        }

        $subjectData = [
            ['name' => 'Bahasa Indonesia', 'code' => 'IND'],
            ['name' => 'Bahasa Inggris', 'code' => 'ENG'],
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Pendidikan Agama Islam', 'code' => 'PAI'],
            ['name' => 'Pendidikan Kewarganegaraan', 'code' => 'PKn'],
            ['name' => 'Pemrograman Web', 'code' => 'PWB', 'major_id' => $rpl->id],
            ['name' => 'Jaringan Komputer', 'code' => 'JKO', 'major_id' => $tkj->id],
            ['name' => 'Akuntansi Dasar', 'code' => 'AKD', 'major_id' => $akl->id],
            ['name' => 'Tata Kelola Perkantoran', 'code' => 'TKP', 'major_id' => $otkp->id],
            ['name' => 'Basis Data', 'code' => 'BDA', 'major_id' => $rpl->id],
        ];

        $createdSubjects = [];
        foreach ($subjectData as $s) {
            $createdSubjects[] = Subject::create($s);
        }

        $teacherNames = [
            ['name' => 'Ahmad Fauzi, S.Pd', 'email' => 'ahmad@smknurululum.sch.id', 'gender' => 'male'],
            ['name' => 'Siti Rahmawati, S.Pd', 'email' => 'siti@smknurululum.sch.id', 'gender' => 'female'],
            ['name' => 'Budi Santoso, S.Kom', 'email' => 'budi@smknurululum.sch.id', 'gender' => 'male'],
            ['name' => 'Dewi Lestari, S.E.', 'email' => 'dewi@smknurululum.sch.id', 'gender' => 'female'],
            ['name' => 'Eko Prasetyo, S.Pd', 'email' => 'eko@smknurululum.sch.id', 'gender' => 'male'],
        ];

        $teachers = [];
        foreach ($teacherNames as $i => $tData) {
            $user = User::create([
                'name' => $tData['name'],
                'email' => $tData['email'],
                'password' => Hash::make('password'),
                'role_id' => $guruRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $teachers[] = Teacher::create([
                'user_id' => $user->id,
                'nip' => '19850101201001'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'nuptk' => '1234567890'.str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'gender' => $tData['gender'] ?? null,
                'subject_id' => $createdSubjects[$i]->id,
                'join_date' => '2010-07-01',
                'employment_status' => 'active',
                'is_active' => true,
            ]);
        }

        $classNames = [
            ['name' => 'X TKJ 1', 'major_id' => $tkj->id],
            ['name' => 'X TKJ 2', 'major_id' => $tkj->id],
            ['name' => 'X RPL 1', 'major_id' => $rpl->id],
            ['name' => 'X RPL 2', 'major_id' => $rpl->id],
            ['name' => 'X AKL 1', 'major_id' => $akl->id],
            ['name' => 'X AKL 2', 'major_id' => $akl->id],
            ['name' => 'X OTKP 1', 'major_id' => $otkp->id],
            ['name' => 'X OTKP 2', 'major_id' => $otkp->id],
        ];

        $competencyList = [];
        foreach ($competencies as $majorId => $comps) {
            foreach ($comps as $comp) {
                $competencyList[] = Competency::where('code', $comp['code'])->first();
            }
        }

        $classes = [];
        foreach ($classNames as $cData) {
            $majorId = $cData['major_id'];
            $compList = collect($competencyList)->where('major_id', $majorId);
            $classes[] = ClassRoom::create([
                'name' => $cData['name'],
                'major_id' => $majorId,
                'competency_id' => $compList->first()->id ?? null,
                'academic_year_id' => $ay2->id,
                'semester_id' => $s1_2025->id,
                'capacity' => 36,
                'is_active' => true,
            ]);
        }

        $studentNames = [
            ['name' => 'Ahmad Rizky', 'gender' => 'male'],
            ['name' => 'Siti Nurhaliza', 'gender' => 'female'],
            ['name' => 'Muhammad Fadil', 'gender' => 'male'],
            ['name' => 'Aisyah Putri', 'gender' => 'female'],
            ['name' => 'Abdullah Alfarizi', 'gender' => 'male'],
            ['name' => 'Fatimah Azzahra', 'gender' => 'female'],
            ['name' => 'Hendra Wijaya', 'gender' => 'male'],
            ['name' => 'Dian Permata', 'gender' => 'female'],
            ['name' => 'Rizal Ramadhan', 'gender' => 'male'],
            ['name' => 'Nurul Hidayah', 'gender' => 'female'],
            ['name' => 'Fajar Nugroho', 'gender' => 'male'],
            ['name' => 'Maya Sari', 'gender' => 'female'],
            ['name' => 'Dimas Pratama', 'gender' => 'male'],
            ['name' => 'Lestari Wulandari', 'gender' => 'female'],
            ['name' => 'Arif Setiawan', 'gender' => 'male'],
            ['name' => 'Putri Maharani', 'gender' => 'female'],
            ['name' => 'Bayu Firmansyah', 'gender' => 'male'],
            ['name' => 'Ratna Dewi', 'gender' => 'female'],
            ['name' => 'Gilang Ramadhan', 'gender' => 'male'],
            ['name' => 'Anisa Rahmawati', 'gender' => 'female'],
        ];

        $students = [];
        foreach ($studentNames as $i => $sData) {
            $classIndex = $i % count($classes);
            $class = $classes[$classIndex];

            $user = User::create([
                'name' => $sData['name'],
                'email' => strtolower(str_replace(' ', '.', $sData['name'])).'@student.smknurululum.sch.id',
                'password' => Hash::make('password'),
                'role_id' => $siswaRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $students[] = Student::create([
                'user_id' => $user->id,
                'nis' => str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'nisn' => '00'.str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'class_id' => $class->id,
                'birth_place' => 'Jakarta',
                'birth_date' => '2008-01-15',
                'gender' => $sData['gender'],
                'religion' => 'Islam',
                'address' => 'Jl. Pendidikan No. '.($i + 1),
                'admission_date' => '2025-07-14',
                'status' => 'active',
            ]);
        }

        foreach ($students as $student) {
            ClassMember::create([
                'class_id' => $student->class_id,
                'student_id' => $student->id,
                'academic_year_id' => $ay2->id,
                'semester_id' => $s1_2025->id,
                'is_active' => true,
            ]);
        }

        foreach ($teachers as $teacher) {
            $assignedClass = $classes[array_rand($classes)];
            $assignedSubject = $teacher->subject_id ? collect($createdSubjects)->firstWhere('id', $teacher->subject_id) : $createdSubjects[0];

            TeachingAssignment::create([
                'teacher_id' => $teacher->id,
                'subject_id' => $assignedSubject->id,
                'class_id' => $assignedClass->id,
                'academic_year_id' => $ay2->id,
                'semester_id' => $s1_2025->id,
            ]);
        }

        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $times = [
            ['start' => '07:30', 'end' => '09:00'],
            ['start' => '09:15', 'end' => '10:45'],
            ['start' => '11:00', 'end' => '12:30'],
        ];

        $taList = TeachingAssignment::all();
        $roomIds = Room::pluck('id')->toArray();
        foreach ($taList->take(6) as $i => $ta) {
            $dayIndex = $i % count($days);
            $timeIndex = $i % count($times);

            Schedule::create([
                'teaching_assignment_id' => $ta->id,
                'room_id' => $roomIds[array_rand($roomIds)],
                'day' => $days[$dayIndex],
                'start_time' => $times[$timeIndex]['start'],
                'end_time' => $times[$timeIndex]['end'],
            ]);
        }

        $questionBank = QuestionBank::create([
            'name' => 'Bank Soal Pemrograman Web',
            'subject_id' => $createdSubjects[5]->id,
            'teacher_id' => $teachers[2]->id,
            'description' => 'Bank soal untuk mata pelajaran Pemrograman Web',
        ]);

        $questionsData = [
            [
                'question' => 'Apa kepanjangan dari HTML?',
                'type' => QuestionTypeEnum::MCQ,
                'difficulty' => 'easy',
                'points' => 10,
                'options' => [
                    ['option_text' => 'Hyper Text Markup Language', 'is_correct' => true],
                    ['option_text' => 'High Tech Modern Language', 'is_correct' => false],
                    ['option_text' => 'Hyper Transfer Markup Language', 'is_correct' => false],
                    ['option_text' => 'Home Tool Markup Language', 'is_correct' => false],
                ],
            ],
            [
                'question' => 'Tag yang digunakan untuk membuat link pada HTML adalah...',
                'type' => QuestionTypeEnum::MCQ,
                'difficulty' => 'easy',
                'points' => 10,
                'options' => [
                    ['option_text' => '<link>', 'is_correct' => false],
                    ['option_text' => '<a>', 'is_correct' => true],
                    ['option_text' => '<href>', 'is_correct' => false],
                    ['option_text' => '<url>', 'is_correct' => false],
                ],
            ],
            [
                'question' => 'CSS adalah singkatan dari Cascading Style Sheets.',
                'type' => QuestionTypeEnum::TRUE_FALSE,
                'difficulty' => 'easy',
                'points' => 10,
                'options' => [
                    ['option_text' => 'Benar', 'is_correct' => true],
                    ['option_text' => 'Salah', 'is_correct' => false],
                ],
            ],
            [
                'question' => 'Jelaskan fungsi dari tag <div> dalam HTML!',
                'type' => QuestionTypeEnum::ESSAY,
                'difficulty' => 'medium',
                'points' => 20,
            ],
            [
                'question' => 'Apa atribut src digunakan pada tag <img>?',
                'type' => QuestionTypeEnum::SHORT_ANSWER,
                'difficulty' => 'easy',
                'points' => 10,
            ],
        ];

        $createdQuestions = [];
        foreach ($questionsData as $qData) {
            $question = Question::create([
                'question_bank_id' => $questionBank->id,
                'type' => $qData['type'],
                'difficulty' => $qData['difficulty'],
                'question' => $qData['question'],
                'points' => $qData['points'],
            ]);

            if (isset($qData['options'])) {
                $order = 1;
                foreach ($qData['options'] as $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $opt['option_text'],
                        'is_correct' => $opt['is_correct'],
                        'order' => $order++,
                    ]);
                }
            }

            $createdQuestions[] = $question;
        }

        $exam = Exam::create([
            'subject_id' => $createdSubjects[5]->id,
            'teacher_id' => $teachers[2]->id,
            'academic_year_id' => $ay2->id,
            'semester_id' => $s1_2025->id,
            'title' => 'Quiz Pemrograman Web - HTML Dasar',
            'description' => 'Quiz mengenai dasar-dasar HTML',
            'type' => ExamTypeEnum::QUIZ,
            'status' => ExamStatusEnum::DRAFT,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHours(2),
            'duration_minutes' => 60,
            'attempt_limit' => 1,
            'random_question' => false,
            'random_option' => false,
            'shuffle_options' => false,
            'show_result' => true,
            'passing_score' => 60,
        ]);

        foreach ($createdQuestions as $i => $question) {
            ExamQuestion::create([
                'exam_id' => $exam->id,
                'question_id' => $question->id,
                'order' => $i + 1,
                'points' => $question->points,
            ]);
        }
    }
}
