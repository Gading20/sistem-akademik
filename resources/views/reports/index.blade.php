@extends('layouts.app')

@section('title', 'Laporan')
@section('header', 'Laporan')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Laporan Akademik</h2>
        <p class="text-sm text-gray-500">Akses berbagai laporan sekolah</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('reports.attendance') }}" class="group p-6 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-200 transition-colors">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">Laporan Kehadiran</h3>
            <p class="text-sm text-gray-500 mt-1">Rekap kehadiran siswa per kelas dan periode</p>
        </a>

        <a href="{{ route('reports.grades') }}" class="group p-6 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">Laporan Nilai</h3>
            <p class="text-sm text-gray-500 mt-1">Rekap nilai siswa per mata pelajaran</p>
        </a>

        <a href="{{ route('reports.ranking') }}" class="group p-6 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-amber-200 transition-colors">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">Ranking Kelas</h3>
            <p class="text-sm text-gray-500 mt-1">Peringkat siswa berdasarkan nilai</p>
        </a>

        <a href="{{ route('reports.report-cards.index') }}" class="group p-6 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-200 transition-colors">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">Rapor</h3>
            <p class="text-sm text-gray-500 mt-1">Cetak rapor siswa</p>
        </a>

        <div class="p-6 bg-gray-50 rounded-xl border border-dashed border-gray-300 opacity-50">
            <div class="w-12 h-12 bg-gray-200 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-500">Jurnal Guru</h3>
            <p class="text-sm text-gray-400 mt-1">Segera hadir</p>
        </div>
    </div>
</div>
@endsection
