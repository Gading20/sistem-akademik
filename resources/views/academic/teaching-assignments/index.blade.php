@extends('layouts.app')

@section('title', 'Penugasan Mengajar')
@section('header', 'Penugasan Mengajar')

@section('content')
<div class="space-y-6" x-data="{ deleteModal: false, deleteUrl: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Penugasan Mengajar</h2>
            <p class="text-sm text-gray-500">Kelola penugasan mengajar guru ke kelas dan mata pelajaran</p>
        </div>
        @can('create', \App\Models\TeachingAssignment::class)
        <a href="{{ route('academic.teaching-assignments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Tambah Penugasan
        </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('academic.teaching-assignments.index') }}">
                <div class="flex flex-col sm:flex-row gap-3">
                    @cannot('create', \App\Models\TeachingAssignment::class)
                    <div class="w-full sm:w-48">
                        <select name="teacher_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Semua Guru</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endcannot
                    <div class="w-full sm:w-48">
                        <select name="class_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-48">
                        <select name="academic_year_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Semua Tahun Ajaran</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
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
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Guru</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Mata Pelajaran</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Kelas</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tahun Ajaran</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Semester</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($teachingAssignments as $ta)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $ta->teacher->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $ta->subject->name ?? '-' }}</td>
                            <td class="px-4 py-3" x-data="{ open: false }">
                                @if($ta->classes->count() === 1)
                                    <span class="text-sm text-gray-900">{{ $ta->classes->first()->name }}</span>
                                @else
                                    <div x-data="{ open: false }">
                                        <button type="button" @click="open = !open" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                            {{ $ta->classes->count() }} Kelas
                                            <svg class="w-3 h-3" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-cloak class="absolute mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 py-1 min-w-[120px]">
                                            @foreach($ta->classes as $class)
                                                <div class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">{{ $class->name }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $ta->academicYear->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ ucfirst($ta->semester->name ?? '-') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @php $firstTA = \App\Models\TeachingAssignment::find($ta->id); @endphp
                                    @if($firstTA)
                                    @can('update', $firstTA)
                                    <a href="{{ route('academic.teaching-assignments.edit', $ta->id) }}" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('delete', $firstTA)
                                    <button type="button" @click="deleteModal = true; deleteUrl = '{{ route('academic.teaching-assignments.destroy', $ta->id) }}'" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="text-sm text-gray-500">Belum ada penugasan mengajar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachingAssignments->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $teachingAssignments->links() }}
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Penugasan?</h3>
                    <p class="text-sm text-gray-500 mb-6">Penugasan mengajar akan dihapus secara permanen.</p>
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
