@extends('layouts.app')

@section('title', 'Laporan Kehadiran')
@section('header', 'Laporan Kehadiran')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Laporan Kehadiran</h2>
        <p class="text-sm text-gray-500">Rekap kehadiran siswa</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('reports.attendance') }}">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="w-full sm:w-48">
                        <select name="class_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-40">
                        <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="Dari tanggal" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="w-full sm:w-40">
                        <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="Sampai tanggal" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Filter</button>
                    <a href="{{ route('reports.attendance', ['class_id' => request('class_id'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'export' => 'pdf']) }}" class="px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors">Export PDF</a>
                </div>
            </form>
        </div>

        @if(isset($attendances) && count($attendances) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">NIS</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Siswa</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($attendances as $attendance)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $attendance->student->nis ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $attendance->student->user->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($attendance->date)->locale('id')->isoFormat('D MMMM Y') }}</td>
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <p class="text-sm text-gray-500">Pilih filter untuk menampilkan laporan</p>
            </div>
        @endif
    </div>
</div>
@endsection
