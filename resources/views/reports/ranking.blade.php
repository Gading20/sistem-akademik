@extends('layouts.app')

@section('title', 'Ranking Kelas')
@section('header', 'Ranking Kelas')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Ranking Kelas</h2>
        <p class="text-sm text-gray-500">Peringkat siswa berdasarkan nilai rata-rata</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('reports.ranking') }}">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="w-full sm:w-48">
                        <select name="class_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Tampilkan</button>
                    <a href="{{ route('reports.ranking', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors">Export PDF</a>
                </div>
            </form>
        </div>

        @if(isset($rankings) && count($rankings) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-center px-4 py-3 font-medium text-gray-600 w-16">Rank</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">NIS</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Siswa</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Rata-rata</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rankings as $rank)
                            <tr class="hover:bg-gray-50 {{ $loop->iteration <= 3 ? 'bg-amber-50' : '' }}">
                                <td class="px-4 py-3 text-center">
                                    @if($loop->iteration === 1)
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-700 rounded-full text-sm font-bold">1</span>
                                    @elseif($loop->iteration === 2)
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-600 rounded-full text-sm font-bold">2</span>
                                    @elseif($loop->iteration === 3)
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-orange-100 text-orange-700 rounded-full text-sm font-bold">3</span>
                                    @else
                                        <span class="text-gray-500 font-medium">{{ $loop->iteration }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $rank['student']->nis }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $rank['student']->user->name }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ $rank['average'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $avg = $rank['average'];
                                        if ($avg >= 90) $p = 'A';
                                        elseif ($avg >= 80) $p = 'B';
                                        elseif ($avg >= 70) $p = 'C';
                                        elseif ($avg >= 60) $p = 'D';
                                        else $p = 'E';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $avg >= 70 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $p }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                <p class="text-sm text-gray-500">Pilih kelas untuk menampilkan ranking</p>
            </div>
        @endif
    </div>
</div>
@endsection
