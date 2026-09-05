@extends('layouts.app')

@section('title', 'Riwayat Kehadiran Siswa')
@section('header', 'Riwayat Kehadiran Siswa')

@section('content')
@php
    $canManageAttendance = auth()->user()?->can('viewAny', \App\Models\Attendance::class);
@endphp
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            @if($canManageAttendance)
                <a href="{{ route('academic.attendance.index') }}" class="hover:text-gray-700">Absensi</a>
            @else
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a>
            @endif
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Riwayat Kehadiran</span>
        </nav>
    </div>

    @if(!empty($academicYears) && $academicYears->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id', $academicYearId ?? null) == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Semester</label>
                    <select name="semester_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ request('semester_id', $semesterId ?? null) == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">Tampilkan</button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-lg font-semibold text-blue-60">{{ substr($student->user->name, 0, 1) }}</span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $student->user->name }}</h3>
                    <p class="text-sm text-gray-500">NIS: {{ $student->nis }} | Kelas: {{ $student->classRoom->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    <span class="text-gray-600">Hadir: <strong>{{ $summary['hadir'] ?? 0 }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-amber-500 rounded-full"></span>
                    <span class="text-gray-600">Izin: <strong>{{ $summary['izin'] ?? 0 }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <span class="text-gray-600">Sakit: <strong>{{ $summary['sakit'] ?? 0 }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                    <span class="text-gray-600">Alpha: <strong>{{ $summary['alpa'] ?? 0 }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                    <span class="text-gray-600">Terlambat: <strong>{{ $summary['terlambat'] ?? 0 }}</strong></span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Hari</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ \Carbon\Carbon::parse($attendance->date)->locale('id')->isoFormat('D MMMM Y') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($attendance->date)->locale('id')->isoFormat('dddd') }}</td>
                            <td class="px-4 py-3">
                                @if($attendance->status === 'hadir')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Hadir</span>
                                @elseif($attendance->status === 'izin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Izin</span>
                                @elseif($attendance->status === 'sakit')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Sakit</span>
                                @elseif($attendance->status === 'terlambat')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Terlambat</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Alpha</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $attendance->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <p class="text-sm text-gray-500">Belum ada data kehadiran</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
