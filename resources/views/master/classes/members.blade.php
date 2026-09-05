@extends('layouts.app')

@section('title', 'Kelola Anggota Kelas')
@section('header', 'Kelola Anggota Kelas')

@section('content')
<div class="space-y-6" x-data="{ removeModal: false, removeUrl: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('master.classes.index') }}" class="hover:text-gray-700">Kelas</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-900 font-medium">Anggota</span>
            </nav>
            <h2 class="text-lg font-semibold text-gray-900">{{ $class->name }}</h2>
            <p class="text-sm text-gray-500">Kelola siswa anggota kelas</p>
        </div>
    </div>

    {{-- Add Member Form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Tambah Anggota</h3>
        <form method="POST" action="{{ route('master.classes.members.add', $class) }}">
            @csrf
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <select name="student_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" x-data="{ search: '' }" x-on:input="search = $event.target.value" x-bind:placeholder="'Pilih siswa...'">
                        <option value="">Pilih Siswa</option>
                        @foreach($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->user->name }} - {{ $student->nisn }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">Tambah</button>
            </div>
        </form>
    </div>

    {{-- Members List --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">Daftar Anggota ({{ $members->count() }} siswa)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">No</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">NISN</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Nama Siswa</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($members as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-900">{{ $member->student->nisn }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $member->student->user->name }}</td>
                            <td class="px-4 py-3">
                                @if($member->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Non Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end">
                                    <button type="button" @click="removeModal = true; removeUrl = '{{ route('master.classes.members.remove', [$class, $member]) }}'" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus dari Kelas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-sm text-gray-500">Belum ada anggota di kelas ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="removeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50" @click="removeModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <div class="text-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus dari Kelas?</h3>
                    <p class="text-sm text-gray-500 mb-6">Siswa akan dihapus dari kelas ini.</p>
                    <div class="flex gap-3 justify-center">
                        <button @click="removeModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                        <form :action="removeUrl" method="POST">
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