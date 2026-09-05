@extends('layouts.app')

@section('title', 'Rapor Siswa')
@section('header', 'Rapor Siswa')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ printing: false }">
    <div class="mb-6 flex items-center justify-between">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('reports.report-cards.index') }}" class="hover:text-gray-700">Rapor</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">{{ $student->user->name }}</span>
        </nav>
        <button @click="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak Rapor
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" id="report-card">
        {{-- Header --}}
        <div class="p-8 text-center border-b border-gray-200">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">NU</span>
                </div>
                <div class="text-left">
                    <h1 class="text-xl font-bold text-gray-900">SMK Nurul Ulum</h1>
                    <p class="text-sm text-gray-500">Jl. Contoh Alamat No. 123, Kota</p>
                    <p class="text-sm text-gray-500">Telp: (021) 12345678 | Email: info@smknuru.sch.id</p>
                </div>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 mt-4">LAPORAN HASIL BELAJAR</h2>
            <p class="text-sm text-gray-500">{{ $semester->name ?? '-' }} | {{ $academicYear->name ?? '-' }}</p>
        </div>

        {{-- Student Info --}}
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="flex gap-2 mb-2">
                        <span class="w-32 text-gray-500">Nama Siswa</span>
                        <span class="text-gray-900 font-medium">: {{ $student->user->name }}</span>
                    </div>
                    <div class="flex gap-2 mb-2">
                        <span class="w-32 text-gray-500">NIS / NISN</span>
                        <span class="text-gray-900 font-medium">: {{ $student->nis }} / {{ $student->nisn }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-32 text-gray-500">Tanggal Lahir</span>
                        <span class="text-gray-900 font-medium">: {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->locale('id')->isoFormat('D MMMM Y') : '-' }}</span>
                    </div>
                </div>
                <div>
                    <div class="flex gap-2 mb-2">
                        <span class="w-32 text-gray-500">Kelas</span>
                        <span class="text-gray-900 font-medium">: {{ $student->classRoom->name ?? '-' }}</span>
                    </div>
                    <div class="flex gap-2 mb-2">
                        <span class="w-32 text-gray-500">Jurusan</span>
                        <span class="text-gray-900 font-medium">: {{ $student->major->name ?? '-' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-32 text-gray-500">Wali Kelas</span>
                        <span class="text-gray-900 font-medium">: {{ $homeroomTeacher->user->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grades Table --}}
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Daftar Nilai</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-center px-3 py-2 font-medium text-gray-600 w-8">No</th>
                            <th class="text-left px-3 py-2 font-medium text-gray-600">Mata Pelajaran</th>
                            @foreach($configComponents as $comp)
                                <th class="text-center px-3 py-2 font-medium text-gray-600">{{ $comp }}</th>
                            @endforeach
                            <th class="text-center px-3 py-2 font-medium text-gray-600">Nilai Akhir</th>
                            <th class="text-center px-3 py-2 font-medium text-gray-600">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($grades as $subjectName => $subjectGrade)
                            <tr>
                                <td class="px-3 py-2 text-center text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2 text-gray-900">{{ $subjectName }}</td>
                                @foreach($configComponents as $comp)
                                    <td class="px-3 py-2 text-center text-gray-600">{{ $subjectGrade['components'][$comp] ?? '-' }}</td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-semibold text-gray-900">{{ $subjectGrade['final'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-center">
                                    @if(isset($subjectGrade['final']))
                                        @php
                                            $f = $subjectGrade['final'];
                                            if ($f >= 90) $p = 'A';
                                            elseif ($f >= 80) $p = 'B';
                                            elseif ($f >= 70) $p = 'C';
                                            elseif ($f >= 60) $p = 'D';
                                            else $p = 'E';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $f >= 70 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $p }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Summary --}}
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Kehadiran</h4>
                    <div class="text-sm space-y-1">
                        <div class="flex justify-between"><span class="text-gray-500">Hadir</span><span class="font-medium">{{ $attendanceSummary['present'] ?? 0 }} hari</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Izin</span><span class="font-medium">{{ $attendanceSummary['permission'] ?? 0 }} hari</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Sakit</span><span class="font-medium">{{ $attendanceSummary['sick'] ?? 0 }} hari</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Alpha</span><span class="font-medium">{{ $attendanceSummary['absent'] ?? 0 }} hari</span></div>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Ekstrakurikuler & Catatan</h4>
                    <p class="text-sm text-gray-600">{{ $extracurricular ?? '-' }}</p>
                    <p class="text-sm text-gray-600 mt-2">{{ $notes ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Signatures --}}
        <div class="p-6">
            <div class="grid grid-cols-3 gap-8 text-center text-sm">
                <div>
                    <p class="text-gray-500 mb-16">Wali Kelas,</p>
                    <div class="border-t border-gray-300 pt-2">
                        <p class="font-semibold text-gray-900">{{ $homeroomTeacher->user->name ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-gray-500 mb-16">Kepala Sekolah,</p>
                    <div class="border-t border-gray-300 pt-2">
                        <p class="font-semibold text-gray-900">{{ $principal->name ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-gray-500 mb-16">Orang Tua/Wali,</p>
                    <div class="border-t border-gray-300 pt-2">
                        <p class="font-semibold text-gray-900">&nbsp;</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body { background: white !important; }
        aside, header, footer, nav, .no-print { display: none !important; }
        .lg\:pl-64 { padding-left: 0 !important; }
        main { padding: 0 !important; }
        #report-card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush
@endsection
