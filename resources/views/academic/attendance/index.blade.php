@extends('layouts.app')

@section('title', 'Absensi')
@section('header', 'Absensi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Absensi Siswa</h2>
            <p class="text-sm text-gray-500">Kelola kehadiran siswa per kelas</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('academic.attendance.index') }}">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="w-full sm:w-48">
                        <select name="class_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-48">
                        <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Tampilkan</button>
                </div>
            </form>
        </div>

        @if(isset($students) && count($students) > 0)
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $classes->firstWhere('id', request('class_id'))?->name ?? '-' }} - {{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Isi kehadiran untuk setiap siswa</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="setAllStatus('hadir')" class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 transition-colors" @disabled($schedules->isEmpty())>Semua Hadir</button>
                        <button type="button" onclick="setAllStatus('alpa')" class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200 transition-colors" @disabled($schedules->isEmpty())>Semua Tidak Hadir</button>
                    </div>
                </div>
            </div>

            @if($schedules->isEmpty())
                <div class="px-4 py-8 text-center bg-amber-50">
                    <p class="text-sm text-amber-700">Tidak ada jadwal pelajaran untuk kelas ini di hari tersebut. Buat jadwal terlebih dahulu.</p>
                </div>
            @else
            <form method="POST" action="{{ route('academic.attendance.bulk-record') }}" x-data="{ saving: false }" @submit="saving = true">
                @csrf
                <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                <input type="hidden" name="date" value="{{ request('date', date('Y-m-d')) }}">

                @if($schedules->count() > 1)
                    <div class="px-4 py-3 border-b border-gray-200">
                        <label for="schedule_id" class="block text-sm font-medium text-gray-700 mb-1.5">Jadwal</label>
                        <select name="schedule_id" id="schedule_id" class="w-full max-w-md px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                            <option value="">Pilih Jadwal</option>
                            @foreach($schedules as $schedule)
                                <option value="{{ $schedule->id }}" {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                    {{ $schedule->start_time }} - {{ $schedule->end_time }} | {{ $schedule->teachingAssignment->subject->name ?? '-' }} ({{ $schedule->teachingAssignment->teacher->user->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif($schedules->count() === 1)
                    <input type="hidden" name="schedule_id" value="{{ $schedules->first()->id }}">
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600">NIS</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Siswa</th>
                                <th class="text-center px-4 py-3 font-medium text-gray-600">Hadir</th>
                                <th class="text-center px-4 py-3 font-medium text-gray-600">Izin</th>
                                <th class="text-center px-4 py-3 font-medium text-gray-600">Sakit</th>
                                <th class="text-center px-4 py-3 font-medium text-gray-600">Alpha</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($students as $student)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $student->nis }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $student->user->name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="hadir" {{ ($student->attendance?->status ?? '') === 'hadir' ? 'checked' : '' }} class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="izin" {{ ($student->attendance?->status ?? '') === 'izin' ? 'checked' : '' }} class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="sakit" {{ ($student->attendance?->status ?? '') === 'sakit' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="alpa" {{ ($student->attendance?->status ?? '') === 'alpa' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="attendances[{{ $student->id }}][note]" value="{{ $student->attendance?->note ?? '' }}" placeholder="Keterangan..." class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                        <input type="hidden" name="attendances[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                                        <input type="hidden" name="attendances[{{ $student->id }}][schedule_id]" value="{{ $schedules->first()?->id }}">
                                        <input type="hidden" name="attendances[{{ $student->id }}][date]" value="{{ request('date', date('Y-m-d')) }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end">
                    <button type="submit" :disabled="saving" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Simpan Absensi
                    </button>
                </div>
            </form>
            @endif
        @elseif(request('class_id'))
            <div class="px-4 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <p class="text-sm text-gray-500">Tidak ada data siswa untuk kelas ini</p>
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-sm text-gray-500">Pilih kelas dan tanggal untuk mengisi absensi</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function setAllStatus(status) {
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(radio => {
            radio.checked = true;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const scheduleSelect = document.getElementById('schedule_id');
        if (scheduleSelect) {
            scheduleSelect.addEventListener('change', function () {
                document.querySelectorAll('input[name*="[schedule_id]"]').forEach(function (input) {
                    input.value = scheduleSelect.value;
                });
            });
        }
    });
</script>
@endpush
@endsection
