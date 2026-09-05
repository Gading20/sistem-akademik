@extends('layouts.app')

@section('title', 'Tambah Bank Soal')
@section('header', 'Tambah Bank Soal')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('exam.question-banks.index') }}" class="hover:text-gray-700">Bank Soal</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Tambah Baru</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Tambah Bank Soal</h3>

        <form method="POST" action="{{ route('exam.question-banks.store') }}" class="space-y-5">
            @csrf

            @if($isGuru)
                <input type="hidden" name="teacher_id" value="{{ old('teacher_id', auth()->user()->teacher?->id) }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                    <input type="text" value="{{ auth()->user()->teacher?->user->name ?? auth()->user()->name }}" disabled class="w-full px-3 py-2.5 text-sm border border-gray-300 bg-gray-50 rounded-lg text-gray-500">
                    <p class="mt-1 text-xs text-gray-500">Bank soal tercatat atas nama Anda.</p>
                </div>
            @else
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                    <select name="teacher_id" id="teacher_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('teacher_id') border-red-500 @enderror">
                        <option value="">Pilih Guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }} ({{ $teacher->nip }})</option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Bank Soal</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Contoh: Bank Soal Matematika Kelas X" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1.5">Mata Pelajaran</label>
                <select name="subject_id" id="subject_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('subject_id') border-red-500 @enderror">
                    <option value="">Pilih Mata Pelajaran</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
                @error('subject_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi</label>
                <textarea name="description" id="description" rows="3" placeholder="Deskripsi bank soal..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('exam.question-banks.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
