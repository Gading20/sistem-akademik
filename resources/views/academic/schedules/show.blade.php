@extends('layouts.app')

@section('title', 'Detail Jadwal')
@section('header', 'Detail Jadwal')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('academic.schedules.index') }}" class="hover:text-gray-700">Jadwal</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Detail</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Detail Jadwal Pelajaran</h3>
            <div class="flex items-center gap-2">
                @can('update', $schedule)
                    <a href="{{ route('academic.schedules.edit', $schedule) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                @endcan
                <a href="{{ route('academic.schedules.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Kembali
                </a>
            </div>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hari</p>
                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($schedule->day) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jam</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $schedule->start_time }} - {{ $schedule->end_time }}</p>
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Kelas</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $schedule->teachingAssignment->classRoom->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Mata Pelajaran</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $schedule->teachingAssignment->subject->name ?? '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Guru</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $schedule->teachingAssignment->teacher->user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ruang</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $schedule->room->name ?? '-' }}</p>
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tahun Ajaran</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $schedule->teachingAssignment->academicYear->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Semester</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $schedule->teachingAssignment->semester->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
