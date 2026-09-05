@extends('layouts.app')

@section('title', 'Edit Jurnal')
@section('header', 'Edit Jurnal')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('academic.journals.index') }}" class="hover:text-gray-700">Jurnal</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Edit Jurnal</h3>

        <form method="POST" action="{{ route('academic.journals.update', $journal) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @if($isGuru)
                <input type="hidden" name="teacher_id" value="{{ old('teacher_id', $journal->teacher_id) }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                    <input type="text" value="{{ $journal->teacher?->user->name ?? '-' }}" disabled class="w-full px-3 py-2.5 text-sm border border-gray-300 bg-gray-50 rounded-lg text-gray-500">
                    <p class="mt-1 text-xs text-gray-500">Jurnal tetap tercatat atas nama pemilik jurnal.</p>
                </div>
            @else
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                    <select name="teacher_id" id="teacher_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('teacher_id') border-red-500 @enderror">
                        <option value="">Pilih Guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id', $journal->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }} ({{ $teacher->nip }})</option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal</label>
                    <input type="date" name="date" id="date" value="{{ old('date', $journal->date) }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                    <select name="class_id" id="class_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('class_id') border-red-500 @enderror">
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $journal->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1.5">Mata Pelajaran</label>
                    <select name="subject_id" id="subject_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('subject_id') border-red-500 @enderror">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $journal->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="schedule_id" class="block text-sm font-medium text-gray-700 mb-1.5">Jadwal (Opsional)</label>
                    <select name="schedule_id" id="schedule_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('schedule_id') border-red-500 @enderror">
                        <option value="">Tidak Mengaitkan Jadwal</option>
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}" {{ old('schedule_id', $journal->schedule_id) == $schedule->id ? 'selected' : '' }}>
                                {{ ucfirst($schedule->day) }} {{ substr($schedule->start_time, 0, 5) }}-{{ substr($schedule->end_time, 0, 5) }}
                                • {{ $schedule->teachingAssignment->classRoom->name ?? '-' }}
                                • {{ $schedule->teachingAssignment->subject->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    @error('schedule_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="material" class="block text-sm font-medium text-gray-700 mb-1.5">Materi / Kegiatan Pembelajaran</label>
                <textarea name="material" id="material" rows="3" placeholder="Deskripsikan materi atau kegiatan pembelajaran..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('material') border-red-500 @enderror">{{ old('material', $journal->material) }}</textarea>
                @error('material')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="learning_objectives" class="block text-sm font-medium text-gray-700 mb-1.5">Tujuan Pembelajaran</label>
                <textarea name="learning_objectives" id="learning_objectives" rows="2" placeholder="Tujuan pembelajaran yang ingin dicapai..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('learning_objectives') border-red-500 @enderror">{{ old('learning_objectives', $journal->learning_objectives) }}</textarea>
                @error('learning_objectives')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="activities" class="block text-sm font-medium text-gray-700 mb-1.5">Kegiatan</label>
                <textarea name="activities" id="activities" rows="2" placeholder="Langkah-langkah kegiatan pembelajaran..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('activities') border-red-500 @enderror">{{ old('activities', $journal->activities) }}</textarea>
                @error('activities')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">Catatan</label>
                <textarea name="notes" id="notes" rows="2" placeholder="Catatan tambahan..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('notes') border-red-500 @enderror">{{ old('notes', $journal->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('academic.journals.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
