@extends('layouts.app')

@section('title', 'Tambah Kelas')
@section('header', 'Tambah Kelas')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('master.classes.index') }}" class="hover:text-gray-700">Kelas</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Tambah Baru</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Tambah Kelas</h3>

        <form method="POST" action="{{ route('master.classes.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Kelas</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Contoh: X TKJ 1" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-1.5">Tingkat</label>
                    <select name="grade_level" id="grade_level" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('grade_level') border-red-500 @enderror">
                        <option value="">Pilih Tingkat</option>
                        <option value="10" {{ old('grade_level') === '10' ? 'selected' : '' }}>X (10)</option>
                        <option value="11" {{ old('grade_level') === '11' ? 'selected' : '' }}>XI (11)</option>
                        <option value="12" {{ old('grade_level') === '12' ? 'selected' : '' }}>XII (12)</option>
                    </select>
                    @error('grade_level')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="section" class="block text-sm font-medium text-gray-700 mb-1.5">Bagian</label>
                    <input type="text" name="section" id="section" value="{{ old('section') }}" placeholder="Contoh: 1" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('section') border-red-500 @enderror">
                    @error('section')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="major_id" class="block text-sm font-medium text-gray-700 mb-1.5">Jurusan</label>
                <select name="major_id" id="major_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('major_id') border-red-500 @enderror">
                    <option value="">Pilih Jurusan</option>
                    @foreach($majors as $major)
                        <option value="{{ $major->id }}" {{ old('major_id') == $major->id ? 'selected' : '' }}>{{ $major->name }}</option>
                    @endforeach
                </select>
                @error('major_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1.5">Tahun Ajaran</label>
                    <select name="academic_year_id" id="academic_year_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('academic_year_id') border-red-500 @enderror">
                        <option value="">Pilih Tahun Ajaran</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
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
                            <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                        @endforeach
                    </select>
                    @error('semester_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1.5">Kapasitas</label>
                    <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 36) }}" min="1" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('capacity') border-red-500 @enderror">
                    @error('capacity')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm text-gray-700">Aktifkan kelas ini</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('master.classes.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection