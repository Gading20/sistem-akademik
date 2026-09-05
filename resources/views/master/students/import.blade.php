@extends('layouts.app')

@section('title', 'Import Siswa')
@section('header', 'Import Siswa')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('master.students.index') }}" class="hover:text-gray-700">Siswa</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Import CSV</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Import Data Siswa</h3>
        <p class="text-sm text-gray-500 mb-6">Unggah file CSV untuk menambah data siswa secara massal</p>

        {{-- Instructions --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-blue-900 mb-2">Petunjuk:</h4>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>1. Format file harus <strong>.csv</strong></li>
                <li>2. Kolom yang wajib: <strong>nisn, nis, name, gender</strong></li>
                <li>3. Kolom opsional: email, class_name, birth_place, birth_date, religion, address, phone</li>
                <li>4. Header CSV harus sesuai dengan nama kolom di bawah</li>
                <li>5. Separator koma (,)</li>
                <li>6. Gender: <strong>male</strong> atau <strong>female</strong></li>
                <li>7. Class_name diisi dengan nama kelas (contoh: X RPL 1)</li>
            </ul>
        </div>

        {{-- Download Template --}}
        <div class="mb-6">
            <a href="{{ route('master.students.import.template') }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Template CSV
            </a>
        </div>

        {{-- CSV Format Preview --}}
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Format CSV:</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200 rounded-lg">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-2 text-left font-medium text-gray-600">nisn</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">nis</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">name</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">email</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">gender</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">class_name</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">birth_place</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">birth_date</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">religion</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">address</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="px-3 py-2 text-gray-600">0012345678</td>
                            <td class="px-3 py-2 text-gray-600">2024001</td>
                            <td class="px-3 py-2 text-gray-600">Ahmad Rizky</td>
                            <td class="px-3 py-2 text-gray-600">ahmad@email.com</td>
                            <td class="px-3 py-2 text-gray-600">male</td>
                            <td class="px-3 py-2 text-gray-600">X RPL 1</td>
                            <td class="px-3 py-2 text-gray-600">Jakarta</td>
                            <td class="px-3 py-2 text-gray-600">2008-05-15</td>
                            <td class="px-3 py-2 text-gray-600">Islam</td>
                            <td class="px-3 py-2 text-gray-600">Jl. Merdeka No. 1</td>
                            <td class="px-3 py-2 text-gray-600">081234567890</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <form method="POST" action="{{ route('master.students.import.process') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1.5">Pilih File CSV</label>
                <div class="flex items-center justify-center w-full">
                    <label for="file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> atau seret file ke sini</p>
                            <p class="text-xs text-gray-400">CSV/Excel (Maks. 5MB)</p>
                        </div>
                        <input id="file" name="file" type="file" accept=".csv,.xlsx,.xls" class="hidden">
                    </label>
                </div>
                @error('file')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('master.students.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Import</button>
            </div>
        </form>
    </div>
</div>
@endsection