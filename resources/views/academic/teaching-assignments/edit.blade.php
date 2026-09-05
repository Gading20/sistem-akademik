@extends('layouts.app')

@section('title', 'Edit Penugasan Mengajar')
@section('header', 'Edit Penugasan Mengajar')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('academic.teaching-assignments.index') }}" class="hover:text-gray-700">Penugasan Mengajar</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Edit Penugasan Mengajar</h3>

        <form method="POST" action="{{ route('academic.teaching-assignments.update', $teachingAssignment) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                <select name="teacher_id" id="teacher_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('teacher_id') border-red-500 @enderror">
                    <option value="">Pilih Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('teacher_id', $teachingAssignment->teacher_id) == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->user->name }} ({{ $teacher->nip }})
                        </option>
                    @endforeach
                </select>
                @error('teacher_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1.5">Mata Pelajaran</label>
                <select name="subject_id" id="subject_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('subject_id') border-red-500 @enderror">
                    <option value="">Pilih Mata Pelajaran</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id', $teachingAssignment->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
                @error('subject_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                @php
                    $existingClassIds = \App\Models\TeachingAssignment::where('teacher_id', $teachingAssignment->teacher_id)
                        ->where('subject_id', $teachingAssignment->subject_id)
                        ->where('academic_year_id', $teachingAssignment->academic_year_id)
                        ->where('semester_id', $teachingAssignment->semester_id)
                        ->pluck('class_id')
                        ->toArray();
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 border border-gray-300 rounded-lg @error('class_ids') border-red-500 @enderror">
                    @foreach($classes as $class)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, old('class_ids', $existingClassIds)) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
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
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1.5">Tahun Ajaran</label>
                    <select name="academic_year_id" id="academic_year_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('academic_year_id') border-red-500 @enderror">
                        <option value="">Pilih Tahun Ajaran</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ old('academic_year_id', $teachingAssignment->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
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
                            <option value="{{ $semester->id }}" {{ old('semester_id', $teachingAssignment->semester_id) == $semester->id ? 'selected' : '' }}>
                                {{ ucfirst($semester->name) }} - {{ $semester->academicYear->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('semester_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('academic.teaching-assignments.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
