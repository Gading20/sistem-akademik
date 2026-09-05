@extends('layouts.app')

@section('title', 'Edit Tahun Ajaran')
@section('header', 'Edit Tahun Ajaran')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('master.academic-years.index') }}" class="hover:text-gray-700">Tahun Ajaran</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Edit Tahun Ajaran</h3>

        <form method="POST" action="{{ route('master.academic-years.update', $academicYear) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Tahun Ajaran</label>
                <input type="text" name="name" id="name" value="{{ old('name', $academicYear->name) }}" placeholder="Contoh: 2024/2025" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_year" class="block text-sm font-medium text-gray-700 mb-1.5">Tahun Mulai</label>
                    <input type="number" name="start_year" id="start_year" value="{{ old('start_year', $academicYear->start_date->format('Y')) }}" min="2000" max="2100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('start_year') border-red-500 @enderror">
                    @error('start_year')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_year" class="block text-sm font-medium text-gray-700 mb-1.5">Tahun Selesai</label>
                    <input type="number" name="end_year" id="end_year" value="{{ old('end_year', $academicYear->end_date->format('Y')) }}" min="2000" max="2100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('end_year') border-red-500 @enderror">
                    @error('end_year')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm text-gray-700">Aktifkan tahun ajaran ini</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('master.academic-years.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection