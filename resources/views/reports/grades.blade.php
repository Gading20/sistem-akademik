@extends('layouts.app')

@section('title', 'Laporan Nilai')
@section('header', 'Laporan Nilai')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Laporan Nilai</h2>
        <p class="text-sm text-gray-500">Rekap nilai siswa per mata pelajaran</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('reports.grades') }}">
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
                            <option value="">Semua Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Filter</button>
                    <a href="{{ route('reports.grades', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors">Export PDF</a>
                </div>
            </form>
        </div>

        @if(isset($reportData) && count($reportData) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">NIS</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Siswa</th>
                            @foreach($subjects as $subject)
                                <th class="text-center px-4 py-3 font-medium text-gray-600">{{ Str::limit($subject->name, 15) }}</th>
                            @endforeach
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Rata-rata</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($reportData as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $row['student']->nis }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row['student']->user->name }}</td>
                                @foreach($subjects as $subject)
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $row['grades'][$subject->id] ?? '-' }}</td>
                                @endforeach
                                <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $row['average'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if(isset($row['average']))
                                        @php
                                            $avg = $row['average'];
                                            if ($avg >= 90) $p = 'A';
                                            elseif ($avg >= 80) $p = 'B';
                                            elseif ($avg >= 70) $p = 'C';
                                            elseif ($avg >= 60) $p = 'D';
                                            else $p = 'E';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $avg >= 70 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $p }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-sm text-gray-500">Pilih kelas untuk menampilkan laporan</p>
            </div>
        @endif
    </div>
</div>
@endsection
