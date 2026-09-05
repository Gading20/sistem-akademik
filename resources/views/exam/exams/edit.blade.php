@extends('layouts.app')

@section('title', 'Edit Ujian')
@section('header', 'Edit Ujian')

@section('content')
@php
    $selectedClassIds = old('class_ids', $exam->classes->pluck('id')->all());
    $selectedQuestionIds = old('questions', $exam->examQuestions->pluck('question_id')->all());
@endphp
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('exam.exams.index') }}" class="hover:text-gray-700">Ujian</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Edit Ujian</h3>

        <form method="POST" action="{{ route('exam.exams.update', $exam) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @if($isGuru)
                <input type="hidden" name="teacher_id" value="{{ old('teacher_id', $exam->teacher_id) }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                    <input type="text" value="{{ $exam->teacher?->user->name ?? '-' }}" disabled class="w-full px-3 py-2.5 text-sm border border-gray-300 bg-gray-50 rounded-lg text-gray-500">
                    <p class="mt-1 text-xs text-gray-500">Ujian tetap tercatat atas nama pemilik ujian.</p>
                </div>
            @else
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                    <select name="teacher_id" id="teacher_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('teacher_id') border-red-500 @enderror">
                        <option value="">Pilih Guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id', $exam->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }} ({{ $teacher->nip }})</option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Judul Ujian</label>
                <input type="text" name="title" id="title" value="{{ old('title', $exam->title) }}" placeholder="Judul ujian" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Ujian</label>
                    <select name="type" id="type" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('type') border-red-500 @enderror">
                        <option value="">Pilih Jenis</option>
                        @foreach(\App\Enums\ExamTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}" {{ old('type', $exam->type->value) == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1.5">Mata Pelajaran</label>
                    <select name="subject_id" id="subject_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('subject_id') border-red-500 @enderror">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $exam->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1.5">Tahun Ajaran</label>
                    <select name="academic_year_id" id="academic_year_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('academic_year_id') border-red-500 @enderror">
                        <option value="">Pilih Tahun Ajaran</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id', $exam->academic_year_id) == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                    @error('academic_year_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="semester_id" class="block text-sm font-medium text-gray-700 mb-1.5">Semester</label>
                    <select name="semester_id" id="semester_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('semester_id') border-red-500 @enderror">
                        <option value="">Pilih Semester</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ old('semester_id', $exam->semester_id) == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                        @endforeach
                    </select>
                    @error('semester_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kelas Tujuan</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 border border-gray-300 rounded-lg @error('class_ids') border-red-500 @enderror">
                    @foreach($classes as $class)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, $selectedClassIds) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-700">{{ $class->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('class_ids')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_at" class="block text-sm font-medium text-gray-700 mb-1.5">Waktu Mulai</label>
                    <input type="datetime-local" name="start_at" id="start_at" value="{{ old('start_at', $exam->start_at ? \Carbon\Carbon::parse($exam->start_at)->format('Y-m-d\TH:i') : '') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('start_at') border-red-500 @enderror">
                    @error('start_at')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_at" class="block text-sm font-medium text-gray-700 mb-1.5">Waktu Selesai</label>
                    <input type="datetime-local" name="end_at" id="end_at" value="{{ old('end_at', $exam->end_at ? \Carbon\Carbon::parse($exam->end_at)->format('Y-m-d\TH:i') : '') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('end_at') border-red-500 @enderror">
                    @error('end_at')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1.5">Durasi (menit)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="5" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('duration_minutes') border-red-500 @enderror">
                    @error('duration_minutes')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="attempt_limit" class="block text-sm font-medium text-gray-700 mb-1.5">Maks. Percobaan</label>
                    <input type="number" name="attempt_limit" id="attempt_limit" value="{{ old('attempt_limit', $exam->attempt_limit) }}" min="1" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('attempt_limit') border-red-500 @enderror">
                    @error('attempt_limit')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="passing_score" class="block text-sm font-medium text-gray-700 mb-1.5">Nilai Minimal (KKM)</label>
                    <input type="number" name="passing_score" id="passing_score" value="{{ old('passing_score', $exam->passing_score) }}" min="0" max="100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('passing_score') border-red-500 @enderror">
                    @error('passing_score')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi / Instruksi</label>
                <textarea name="description" id="description" rows="3" placeholder="Instruksi ujian..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('description') border-red-500 @enderror">{{ old('description', $exam->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="random_question" value="1" {{ old('random_question', $exam->random_question) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Acak urutan soal</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="random_option" value="1" {{ old('random_option', $exam->random_option) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Acak urutan pilihan jawaban</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="shuffle_options" value="1" {{ old('shuffle_options', $exam->shuffle_options) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Acak pilihan pada soal pilihan ganda</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="show_result" value="1" {{ old('show_result', $exam->show_result) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Tampilkan nilai setelah selesai</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="show_answer_after_submit" value="1" {{ old('show_answer_after_submit', $exam->show_answer_after_submit) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Tampilkan kunci jawaban setelah submit</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Pilih Soal</label>
                <div class="space-y-3 border border-gray-300 rounded-lg p-4">
                    @forelse($questionBanks as $bank)
                        @if($bank->questions->isNotEmpty())
                        <details class="group">
                            <summary class="flex items-center justify-between cursor-pointer list-none">
                                <span class="text-sm font-medium text-gray-800">{{ $bank->name }}</span>
                                <span class="text-xs text-gray-500">{{ $bank->questions->count() }} soal</span>
                            </summary>
                            <div class="mt-2 space-y-1.5 max-h-64 overflow-y-auto pl-1">
                                @foreach($bank->questions as $question)
                                    <label class="flex items-start gap-2 cursor-pointer rounded-lg hover:bg-gray-50 px-2 py-1.5">
                                        <input type="checkbox" name="questions[]" value="{{ $question->id }}" {{ in_array($question->id, $selectedQuestionIds) ? 'checked' : '' }} class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">
                                            <span class="block line-clamp-2">{{ $question->question }}</span>
                                            <span class="text-xs text-gray-400">{{ $question->difficulty }} • {{ $question->points }} poin</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                        @endif
                    @empty
                        <p class="text-sm text-gray-500">Belum ada bank soal. Buat soal terlebih dahulu pada menu Bank Soal.</p>
                    @endforelse
                </div>
                @error('questions')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('exam.exams.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
