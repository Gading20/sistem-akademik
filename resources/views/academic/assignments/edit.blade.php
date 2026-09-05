@extends('layouts.app')

@section('title', 'Edit Tugas')
@section('header', 'Edit Tugas')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('academic.assignments.index') }}" class="hover:text-gray-700">Tugas</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Edit Tugas</h3>

        <form method="POST" action="{{ route('academic.assignments.update', $assignment) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            @if($isGuru)
                <input type="hidden" name="teacher_id" value="{{ old('teacher_id', $assignment->teacher_id) }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                    <input type="text" value="{{ $assignment->teacher?->user->name ?? '-' }}" disabled class="w-full px-3 py-2.5 text-sm border border-gray-300 bg-gray-50 rounded-lg text-gray-500">
                    <p class="mt-1 text-xs text-gray-500">Tugas tetap tercatat atas nama pemilik tugas.</p>
                </div>
            @else
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                    <select name="teacher_id" id="teacher_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('teacher_id') border-red-500 @enderror">
                        <option value="">Pilih Guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id', $assignment->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }} ({{ $teacher->nip }})</option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Judul Tugas</label>
                <input type="text" name="title" id="title" value="{{ old('title', $assignment->title) }}" placeholder="Judul tugas" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi</label>
                <textarea name="description" id="description" rows="3" placeholder="Deskripsi tugas..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('description') border-red-500 @enderror">{{ old('description', $assignment->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1.5">Mata Pelajaran</label>
                    <select name="subject_id" id="subject_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('subject_id') border-red-500 @enderror">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                    <select name="class_id" id="class_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('class_id') border-red-500 @enderror">
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $assignment->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="deadline" class="block text-sm font-medium text-gray-700 mb-1.5">Batas Pengumpulan</label>
                    <input type="datetime-local" name="deadline" id="deadline" value="{{ old('deadline', $assignment->deadline ? \Carbon\Carbon::parse($assignment->deadline)->format('Y-m-d\TH:i') : '') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('deadline') border-red-500 @enderror">
                    @error('deadline')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="max_score" class="block text-sm font-medium text-gray-700 mb-1.5">Nilai Maksimal</label>
                    <input type="number" name="max_score" id="max_score" value="{{ old('max_score', $assignment->max_score) }}" min="0" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('max_score') border-red-500 @enderror">
                    @error('max_score')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1.5">Lampiran (Opsional)</label>
                @if($assignment->attachment)
                    <div class="mb-2 text-sm text-gray-600">
                        <a href="{{ asset('storage/' . $assignment->attachment) }}" target="_blank" class="text-blue-600 hover:underline">{{ basename($assignment->attachment) }}</a>
                    </div>
                @endif
                <input type="file" name="attachment" id="attachment" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('attachment') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Format: PDF, DOC, DOCX, JPG, PNG (Maks. 5MB)</p>
                @error('attachment')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('academic.assignments.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
