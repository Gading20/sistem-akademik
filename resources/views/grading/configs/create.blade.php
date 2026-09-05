@extends('layouts.app')

@section('title', 'Tambah Konfigurasi Penilaian')
@section('header', 'Tambah Konfigurasi Penilaian')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('grading.configs.index') }}" class="hover:text-gray-700">Konfigurasi Penilaian</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Tambah Baru</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Tambah Konfigurasi</h3>

        <form method="POST" action="{{ route('grading.configs.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                    <select name="class_id" id="class_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('class_id') border-red-500 @enderror">
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
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
                            <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>{{ $semester->type->label() }} - {{ $semester->academicYear->name }}</option>
                        @endforeach
                    </select>
                    @error('semester_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="method" class="block text-sm font-medium text-gray-700 mb-1.5">Metode Penilaian</label>
                <select name="method" id="method" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('method') border-red-500 @enderror">
                    <option value="">Pilih Metode</option>
                    <option value="automatic" {{ old('method') === 'automatic' ? 'selected' : '' }}>Otomatis</option>
                    <option value="manual" {{ old('method') === 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
                @error('method')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Bobot Penilaian (%)</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tugas_weight" class="block text-xs text-gray-500 mb-1">Tugas</label>
                        <input type="number" name="tugas_weight" id="tugas_weight" value="{{ old('tugas_weight', 30) }}" min="0" max="100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('tugas_weight') border-red-500 @enderror">
                        @error('tugas_weight')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="quiz_weight" class="block text-xs text-gray-500 mb-1">Quiz</label>
                        <input type="number" name="quiz_weight" id="quiz_weight" value="{{ old('quiz_weight', 20) }}" min="0" max="100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('quiz_weight') border-red-500 @enderror">
                        @error('quiz_weight')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="uts_weight" class="block text-xs text-gray-500 mb-1">UTS</label>
                        <input type="number" name="uts_weight" id="uts_weight" value="{{ old('uts_weight', 25) }}" min="0" max="100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('uts_weight') border-red-500 @enderror">
                        @error('uts_weight')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="uas_weight" class="block text-xs text-gray-500 mb-1">UAS</label>
                        <input type="number" name="uas_weight" id="uas_weight" value="{{ old('uas_weight', 25) }}" min="0" max="100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('uas_weight') border-red-500 @enderror">
                        @error('uas_weight')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="practical_weight" class="block text-xs text-gray-500 mb-1">Praktik (opsional)</label>
                        <input type="number" name="practical_weight" id="practical_weight" value="{{ old('practical_weight', 0) }}" min="0" max="100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('practical_weight') border-red-500 @enderror">
                        @error('practical_weight')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="project_weight" class="block text-xs text-gray-500 mb-1">Project (opsional)</label>
                        <input type="number" name="project_weight" id="project_weight" value="{{ old('project_weight', 0) }}" min="0" max="100" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('project_weight') border-red-500 @enderror">
                        @error('project_weight')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <span class="text-sm text-gray-500">Total Bobot:</span>
                    <span id="totalWeight" class="text-sm font-semibold text-green-600">100%</span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('grading.configs.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function updateTotal() {
        const tugas = parseInt(document.getElementById('tugas_weight').value) || 0;
        const quiz = parseInt(document.getElementById('quiz_weight').value) || 0;
        const uts = parseInt(document.getElementById('uts_weight').value) || 0;
        const uas = parseInt(document.getElementById('uas_weight').value) || 0;
        const practical = parseInt(document.getElementById('practical_weight').value) || 0;
        const project = parseInt(document.getElementById('project_weight').value) || 0;
        const total = tugas + quiz + uts + uas + practical + project;
        const el = document.getElementById('totalWeight');
        el.textContent = total + '%';
        el.className = 'text-sm font-semibold ' + (total === 100 ? 'text-green-600' : 'text-red-600');
    }

    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', updateTotal);
    });

    updateTotal();
</script>
@endpush
@endsection
