@extends('layouts.app')

@section('title', 'Soal')
@section('header', 'Soal')

@section('content')
@php
    $typeLabels = collect(\App\Enums\QuestionTypeEnum::cases())->keyBy('value')->map(fn ($t) => $t->label());
    $typeColors = [
        'mcq' => 'bg-blue-100 text-blue-700',
        'mcq_complex' => 'bg-indigo-100 text-indigo-700',
        'true_false' => 'bg-green-100 text-green-700',
        'matching' => 'bg-cyan-100 text-cyan-700',
        'short_answer' => 'bg-amber-100 text-amber-700',
        'essay' => 'bg-purple-100 text-purple-700',
        'file_upload' => 'bg-pink-100 text-pink-700',
        'practical' => 'bg-orange-100 text-orange-700',
    ];
@endphp
<div class="space-y-6" x-data="{ deleteModal: false, deleteUrl: '' }">
    <div class="mb-2">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('exam.question-banks.index') }}" class="hover:text-gray-700">Bank Soal</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">{{ $questionBank->name }}</span>
        </nav>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $questionBank->name }}</h2>
            <p class="text-sm text-gray-500">{{ $questionBank->subject->name ?? '-' }} | {{ $questions->total() }} soal</p>
        </div>
        <a href="{{ route('exam.questions.create', $questionBank) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Tambah Soal
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tipe Soal</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Pertanyaan</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Kesulitan</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Bobot</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($questions as $question)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $questions->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$question->type] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $typeLabels[$question->type] ?? $question->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-900 max-w-md truncate">{{ $question->question }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($question->difficulty === 'easy')
                                    <span class="text-green-600 text-xs font-medium">Mudah</span>
                                @elseif($question->difficulty === 'medium')
                                    <span class="text-amber-600 text-xs font-medium">Sedang</span>
                                @else
                                    <span class="text-red-600 text-xs font-medium">Sulit</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $question->points }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('exam.questions.edit', [$questionBank, $question]) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button type="button" @click="deleteModal = true; deleteUrl = '{{ route('exam.questions.destroy', [$questionBank, $question]) }}'" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-gray-500">Belum ada soal. Klik "Tambah Soal" untuk membuat soal pertama.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($questions->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $questions->links() }}
            </div>
        @endif
    </div>

    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50" @click="deleteModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <div class="text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Soal?</h3>
                    <p class="text-sm text-gray-500 mb-6">Soal akan dihapus secara permanen.</p>
                    <div class="flex gap-3 justify-center">
                        <button @click="deleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                        <form :action="deleteUrl" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
