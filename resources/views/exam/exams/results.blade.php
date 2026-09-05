@extends('layouts.app')

@section('title', 'Hasil Ujian')
@section('header', 'Hasil Ujian')

@section('content')
<div class="space-y-6">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('exam.exams.index') }}" class="hover:text-gray-700">Ujian</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Hasil</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $exam->title }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $exam->subject->name ?? '-' }} | {{ $exam->type->label() }} | {{ $exam->duration_minutes }} menit</p>
        </div>

        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $total_attempts ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Peserta</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($average_score ?? 0, 1) }}</div>
                    <div class="text-sm text-gray-500">Rata-rata</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ $highest_score ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Tertinggi</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">{{ $lowest_score ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Terendah</div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Siswa</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Kelas</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Benar</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Salah</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Kosong</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Nilai</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attempts as $attempt)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $attempt->student->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $attempt->student->classRoom->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center text-green-600">{{ $attempt->answers->where('is_correct', true)->count() }}</td>
                            <td class="px-4 py-3 text-center text-red-600">{{ $attempt->answers->where('is_correct', false)->count() }}</td>
                            <td class="px-4 py-3 text-center text-gray-400">{{ $attempt->answers->whereNull('selected_option_id')->whereNull('essay_answer')->count() }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-lg font-bold {{ ($attempt->score ?? 0) >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ $attempt->score ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(($attempt->score ?? 0) >= 70)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Lulus</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Tidak Lulus</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <p class="text-sm text-gray-500">Belum ada peserta yang mengerjakan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attempts->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $attempts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
