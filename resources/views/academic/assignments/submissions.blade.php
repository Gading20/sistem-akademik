@extends('layouts.app')

@section('title', 'Pengumpulan Tugas')
@section('header', 'Pengumpulan Tugas')

@section('content')
<div class="space-y-6" x-data="{ gradingModal: false, submissionId: '', score: '' }">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('academic.assignments.index') }}" class="hover:text-gray-700">Tugas</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Pengumpulan</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $assignment->title }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $assignment->subject->name ?? '-' }} | {{ $assignment->classRoom->name ?? '-' }}</p>
            <div class="flex items-center gap-4 mt-3 text-sm text-gray-600">
                <span>Deadline: {{ \Carbon\Carbon::parse($assignment->deadline)->locale('id')->isoFormat('D MMMM Y HH:mm') }}</span>
                <span>|</span>
                <span>Pengumpulan: {{ $submissions->count() }} / {{ $assignment->total_students ?? 0 }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Siswa</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal Mengumpul</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">File</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Keterangan</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Nilai</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($submissions as $submission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $submission->student->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($submission->submitted_at)->locale('id')->isoFormat('D MMM Y HH:mm') }}</td>
                            <td class="px-4 py-3">
                                @if($submission->file_path)
                                    <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $submission->note ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($submission->score !== null)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $submission->score }} / {{ $assignment->max_score }}</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Belum dinilai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" @click="gradingModal = true; submissionId = '{{ $submission->id }}'; score = '{{ $submission->score ?? '' }}'" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Beri Nilai">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm text-gray-500">Belum ada pengumpulan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="gradingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50" @click="gradingModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Beri Nilai</h3>
                <form :action="'{{ route('academic.assignments.grade', $assignment) }}'" method="POST">
                    @csrf
                    <input type="hidden" name="submission_id" :value="submissionId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nilai (0 - {{ $assignment->max_score }})</label>
                        <input type="number" name="score" :value="score" min="0" max="{{ $assignment->max_score }}" required class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Komentar (Opsional)</label>
                        <textarea name="feedback" rows="2" placeholder="Komentar untuk siswa..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="gradingModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Nilai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
