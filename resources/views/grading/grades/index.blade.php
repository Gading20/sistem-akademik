@extends('layouts.app')

@section('title', 'Daftar Nilai')
@section('header', 'Daftar Nilai')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Daftar Nilai</h2>
        <p class="text-sm text-gray-500">Lihat daftar nilai siswa per kelas dan mata pelajaran</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('grading.grades.index') }}">
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
                        <select name="subject_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Tampilkan</button>
                </div>
            </form>
        </div>

        @if(isset($grades) && count($grades) > 0)
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">{{ $class->name ?? '' }} - {{ $subject->name ?? '' }}</p>
                    <a href="{{ route('grading.grades.input', ['class_id' => request('class_id'), 'subject_id' => request('subject_id')]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Input/Edit Nilai
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">NIS</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Siswa</th>
                            @foreach($components as $component)
                                <th class="text-center px-4 py-3 font-medium text-gray-600">{{ $component->name }}</th>
                            @endforeach
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Rata-rata</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($grades as $studentId => $studentGrades)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $studentGrades['student']->nis }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $studentGrades['student']->user->name }}</td>
                                @foreach($components as $component)
                                    <td class="px-4 py-3 text-center text-gray-600">
                                        {{ $studentGrades['scores'][$component->id] ?? '-' }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-center font-semibold text-gray-900">
                                    {{ $studentGrades['average'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if(isset($studentGrades['average']))
                                        @php
                                            $avg = $studentGrades['average'];
                                            if ($avg >= 90) $predikat = 'A';
                                            elseif ($avg >= 80) $predikat = 'B';
                                            elseif ($avg >= 70) $predikat = 'C';
                                            elseif ($avg >= 60) $predikat = 'D';
                                            else $predikat = 'E';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $avg >= 70 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $predikat }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + count($components) }}" class="px-4 py-12 text-center">
                                    <p class="text-sm text-gray-500">Belum ada data nilai</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-sm text-gray-500">Pilih kelas dan mata pelajaran untuk melihat nilai</p>
            </div>
        @endif
    </div>
</div>
@endsection
