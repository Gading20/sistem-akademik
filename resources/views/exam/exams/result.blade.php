@extends('layouts.app')

@section('title', 'Hasil Ujian')
@section('header', 'Hasil Ujian')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('exam.exams.available') }}" class="hover:text-gray-700">Ujian Tersedia</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Hasil</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Score Header --}}
        <div class="p-8 text-center bg-gradient-to-br from-blue-50 to-indigo-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $examAttempt->exam->title }}</h3>
            <p class="text-sm text-gray-500 mb-6">{{ $examAttempt->exam->subject->name ?? '-' }}</p>
            <div class="inline-flex items-center justify-center w-32 h-32 rounded-full {{ ($examAttempt->score ?? 0) >= 70 ? 'bg-green-100' : 'bg-red-100' }}">
                <div>
                    <div class="text-4xl font-bold {{ ($examAttempt->score ?? 0) >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ $examAttempt->score ?? 0 }}</div>
                    <div class="text-sm {{ ($examAttempt->score ?? 0) >= 70 ? 'text-green-500' : 'text-red-500' }}">
                        @if(($examAttempt->score ?? 0) >= 70)
                            Lulus
                        @else
                            Tidak Lulus
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="p-6 border-b border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-4">Ringkasan Jawaban</h4>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-xl font-bold text-green-600">{{ $examAttempt->answers->where('is_correct', true)->count() }}</div>
                    <div class="text-xs text-gray-500">Benar</div>
                </div>
                <div class="text-center p-3 bg-red-50 rounded-lg">
                    <div class="text-xl font-bold text-red-600">{{ $examAttempt->answers->where('is_correct', false)->count() }}</div>
                    <div class="text-xs text-gray-500">Salah</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-xl font-bold text-gray-600">{{ $examAttempt->answers->whereNull('selected_option_id')->whereNull('essay_answer')->count() }}</div>
                    <div class="text-xs text-gray-500">Kosong</div>
                </div>
            </div>
        </div>

        {{-- Detail Info --}}
        <div class="p-6 border-b border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-4">Detail Ujian</h4>
            <dl class="space-y-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Tipe Ujian</dt>
                    <dd class="text-gray-900 font-medium">{{ $examAttempt->exam->type->label() }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Durasi</dt>
                    <dd class="text-gray-900 font-medium">{{ $examAttempt->exam->duration_minutes }} menit</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Jumlah Soal</dt>
                    <dd class="text-gray-900 font-medium">{{ $examAttempt->exam->examQuestions()->count() }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Waktu Mulai</dt>
                    <dd class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($examAttempt->started_at)->locale('id')->isoFormat('D MMM Y HH:mm:ss') }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Waktu Selesai</dt>
                    <dd class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($examAttempt->submitted_at)->locale('id')->isoFormat('D MMM Y HH:mm:ss') }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Durasi Pengerjaan</dt>
                    <dd class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($examAttempt->started_at)->diff(\Carbon\Carbon::parse($examAttempt->submitted_at))->format('%i menit %s detik') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Answers Review --}}
        @if($examAttempt->exam->show_result)
            <div class="p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Review Jawaban</h4>
                <div class="space-y-4">
                    @foreach($examAttempt->answers as $idx => $answer)
                        <div class="p-4 border rounded-lg {{ $answer->is_correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-semibold {{ $answer->is_correct ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">{{ $answer->question->question }}</p>
                                    <div class="mt-2 text-sm">
                                        <p class="{{ $answer->is_correct ? 'text-green-700' : 'text-red-700' }}">
                                            Jawaban Anda: <strong>{{ $answer->selectedOption->option_text ?? $answer->essay_answer ?? '(Kosong)' }}</strong>
                                        </p>
                                        @if(!$answer->is_correct && $answer->question->correctOption)
                                            <p class="text-green-700">
                                                Jawaban Benar: <strong>{{ $answer->question->correctOption->option_text }}</strong>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="mt-6 flex justify-center">
        <a href="{{ route('exam.exams.available') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
            Kembali ke Daftar Ujian
        </a>
    </div>
</div>
@endsection
