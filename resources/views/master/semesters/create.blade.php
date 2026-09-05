@extends('layouts.app')

@section('title', 'Tambah Semester')
@section('header', 'Tambah Semester')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('master.semesters.index') }}" class="hover:text-gray-700">Semester</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Tambah Baru</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Tambah Semester</h3>

        <form method="POST" action="{{ route('master.semesters.store') }}" class="space-y-5">
            @csrf

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
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Semester</label>
                <select name="name" id="name" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('name') border-red-500 @enderror">
                    <option value="">Pilih Jenis Semester</option>
                    <option value="ganjil" {{ old('name') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="genap" {{ old('name') === 'genap' ? 'selected' : '' }}>Genap</option>
                </select>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('start_date') border-red-500 @enderror">
                    @error('start_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm text-gray-700">Aktifkan semester ini</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('master.semesters.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection