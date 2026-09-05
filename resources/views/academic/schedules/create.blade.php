@extends('layouts.app')

@section('title', 'Tambah Jadwal')
@section('header', 'Tambah Jadwal')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('academic.schedules.index') }}" class="hover:text-gray-700">Jadwal</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Tambah Baru</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Tambah Jadwal</h3>

        <form method="POST" action="{{ route('academic.schedules.store') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="teaching_assignment_id" id="teaching_assignment_id" value="{{ old('teaching_assignment_id') }}">

            <div>
                <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-1.5">Guru</label>
                <select id="teacher_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Pilih Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="class-select" class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                <select id="class-select" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" disabled>
                    <option value="">Pilih Guru terlebih dahulu</option>
                </select>
                <p id="class-hint" class="mt-1 text-xs text-gray-500 hidden"></p>
            </div>

            <div>
                <label for="day" class="block text-sm font-medium text-gray-700 mb-1.5">Hari</label>
                <select name="day" id="day" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('day') border-red-500 @enderror">
                    <option value="">Pilih Hari</option>
                    @foreach(['senin','selasa','rabu','kamis','jumat','sabtu'] as $day)
                        <option value="{{ $day }}" {{ old('day') === $day ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                    @endforeach
                </select>
                @error('day')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1.5">Jam Mulai</label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('start_time') border-red-500 @enderror">
                    @error('start_time')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1.5">Jam Selesai</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('end_time') border-red-500 @enderror">
                    @error('end_time')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('academic.schedules.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const taData = {!! $taJson !!};

    const teacherSelect = document.getElementById('teacher_id');
    const classSelect = document.getElementById('class-select');
    const taInput = document.getElementById('teaching_assignment_id');
    const hint = document.getElementById('class-hint');

    teacherSelect.addEventListener('change', function () {
        const teacherId = this.value;
        classSelect.innerHTML = '<option value="">Pilih Kelas</option>';
        taInput.value = '';
        hint.classList.add('hidden');

        if (!teacherId) {
            classSelect.innerHTML = '<option value="">Pilih Guru terlebih dahulu</option>';
            classSelect.disabled = true;
            return;
        }

        const filtered = taData.filter(ta => ta.teacher_id == teacherId);
        if (filtered.length === 0) {
            classSelect.innerHTML = '<option value="">Tidak ada kelas</option>';
            classSelect.disabled = true;
            return;
        }

        filtered.forEach(ta => {
            const opt = document.createElement('option');
            opt.value = ta.class_id;
            opt.textContent = ta.class_name + ' - ' + ta.subject_name;
            opt.setAttribute('data-ta-id', ta.id);
            classSelect.appendChild(opt);
        });

        classSelect.disabled = false;

        if (filtered.length === 1) {
            classSelect.value = filtered[0].class_id;
            taInput.value = filtered[0].id;
            hint.textContent = filtered[0].class_name + ' - ' + filtered[0].subject_name;
            hint.classList.remove('hidden');
        }
    });

    classSelect.addEventListener('change', function () {
        const teacherId = teacherSelect.value;
        const classId = this.value;
        if (!teacherId || !classId) {
            taInput.value = '';
            hint.classList.add('hidden');
            return;
        }
        const match = taData.find(ta => ta.teacher_id == teacherId && ta.class_id == classId);
        if (match) {
            taInput.value = match.id;
            hint.textContent = match.class_name + ' - ' + match.subject_name;
            hint.classList.remove('hidden');
        } else {
            taInput.value = '';
            hint.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection
