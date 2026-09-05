@extends('layouts.app')

@section('title', 'Input Nilai')
@section('header', 'Input Nilai')

@section('content')
<div class="space-y-6" x-data="{ saving: false }">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('grading.grades.index') }}" class="hover:text-gray-700">Daftar Nilai</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Input Nilai</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $class->name ?? '-' }} - {{ $subject->name ?? '-' }}</h3>
            <p class="text-sm text-gray-500 mt-1">Input nilai untuk {{ $students->count() }} siswa</p>
        </div>

        @if($students->count() > 0)
            <form method="POST" action="{{ route('grading.grades.store') }}" @submit="saving = true">
                @csrf
                <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left px-4 py-3 font-medium text-gray-600 sticky left-0 bg-gray-50">No</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 sticky left-10 bg-gray-50">Nama Siswa</th>
                                @foreach($components as $component)
                                    <th class="text-center px-3 py-3 font-medium text-gray-600 min-w-[120px]">
                                        {{ $component->name }}
                                        <span class="block text-xs font-normal text-gray-400">(Bobot: {{ $component->weight }}%)</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($students as $student)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500 sticky left-0 bg-white">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 sticky left-10 bg-white">{{ $student->user->name }}</td>
                                    @foreach($components as $component)
                                        <td class="px-3 py-3 text-center">
                                            <input type="number"
                                                name="grades[{{ $student->id }}][{{ $component->id }}]"
                                                value="{{ $existingGrades[$student->id][$component->id] ?? '' }}"
                                                min="0" max="100"
                                                placeholder="0-100"
                                                class="w-full px-2 py-1.5 text-sm text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end">
                    <button type="submit" :disabled="saving" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Simpan Semua Nilai
                    </button>
                </div>
            </form>
        @else
            <div class="px-4 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="text-sm text-gray-500">Tidak ada siswa di kelas ini</p>
            </div>
        @endif
    </div>
</div>
@endsection
