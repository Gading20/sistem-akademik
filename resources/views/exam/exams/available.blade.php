@extends('layouts.app')

@section('title', 'Ujian Tersedia')
@section('header', 'Ujian Tersedia')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Ujian Tersedia</h2>
        <p class="text-sm text-gray-500">Daftar ujian yang dapat Anda kerjakan</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('exam.exams.available') }}">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ujian..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Cari</button>
                </div>
            </form>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($exams as $exam)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-base font-semibold text-gray-900">{{ $exam->title }}</h3>
                                @if($exam->status->value === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Berlangsung</span>
                                @elseif($exam->status->value === 'published' && $exam->not_started)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Belum Dimulai</span>
                                @elseif($exam->status->value === 'published')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Dibuka</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $exam->subject->name ?? '-' }} | {{ $exam->type->label() }}</p>
                            <div class="flex items-center gap-4 mt-3 text-sm text-gray-600">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $exam->duration_minutes }} menit
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $exam->exam_questions_count }} soal
                                </span>
                                @if($exam->start_at)
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($exam->start_at)->locale('id')->isoFormat('D MMM HH:mm') }} - {{ \Carbon\Carbon::parse($exam->end_at)->locale('id')->isoFormat('HH:mm') }}
                                    </span>
                                @endif
                            </div>
                            @if($exam->description)
                                <p class="text-sm text-gray-500 mt-2">{{ Str::limit($exam->description, 150) }}</p>
                            @endif
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            @if($exam->has_attempted && $exam->last_attempt)
                                <a href="{{ route('exam.exams.result', $exam->last_attempt) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                    Lihat Hasil
                                </a>
                            @elseif($exam->can_attempt && $exam->not_started)
                                <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed" title="Ujian akan terbuka otomatis sesuai jadwal mulai">
                                    Kerjakan
                                </span>
                            @elseif($exam->can_attempt)
                                <form method="POST" action="{{ route('exam.exams.start', $exam) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        Kerjakan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm text-gray-500">Tidak ada ujian tersedia saat ini</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
