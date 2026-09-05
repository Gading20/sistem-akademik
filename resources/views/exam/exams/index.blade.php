@extends('layouts.app')

@section('title', 'Ujian')
@section('header', 'Ujian')

@section('content')
<div class="space-y-6" x-data="{ deleteModal: false, deleteUrl: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Ujian</h2>
            <p class="text-sm text-gray-500">Kelola ujian dan tes</p>
        </div>
        <a href="{{ route('exam.exams.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Buat Ujian
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('exam.exams.index') }}">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ujian..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="w-full sm:w-48">                            <select name="type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="">Semua Jenis</option>
                                @foreach(\App\Enums\ExamTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                    </div>
                    <div class="w-full sm:w-40">
                        <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="published">Diterbitkan</option>
                            <option value="active">Berlangsung</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Cari</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Judul Ujian</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tipe</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Mata Pelajaran</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Durasi</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Soal</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($exams as $exam)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $exam->title }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $exam->type->label() }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $exam->subject->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $exam->duration_minutes }} menit</td>
                            <td class="px-4 py-3 text-center">
                                @if($exam->status->value === 'draft' && $exam->exam_questions_count === 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600" title="Belum ada soal terpilih untuk ujian ini">0 soal</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $exam->exam_questions_count }} soal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($exam->status->value === 'draft')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Draft</span>
                                @elseif($exam->status->value === 'published')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Diterbitkan</span>
                                @elseif($exam->status->value === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Berlangsung</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Selesai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if($exam->status->value === 'draft')
                                        @if($exam->exam_questions_count > 0)
                                            <form method="POST" action="{{ route('exam.exams.publish', $exam) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors" title="Terbitkan">Terbitkan</button>
                                            </form>
                                        @else
                                            <a href="{{ route('exam.exams.edit', $exam) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors" title="Pilih soal terlebih dahulu sebelum menerbitkan">Pilih Soal</a>
                                        @endif
                                    @endif
                                    <a href="{{ route('exam.exams.results', $exam) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Hasil">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('exam.exams.edit', $exam) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button type="button" @click="deleteModal = true; deleteUrl = '{{ route('exam.exams.destroy', $exam) }}'" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm text-gray-500">Belum ada ujian</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($exams->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $exams->links() }}
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Ujian?</h3>
                    <p class="text-sm text-gray-500 mb-6">Data ujian dan semua pengumpulan akan dihapus.</p>
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
