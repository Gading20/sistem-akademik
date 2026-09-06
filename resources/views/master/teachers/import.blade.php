@extends('layouts.app')

@section('title', 'Import Guru')
@section('header', 'Import Guru')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Import Data Guru</h2>
            <p class="text-sm text-gray-500">Upload file CSV atau Excel untuk menambahkan guru secara massal</p>
        </div>
        <a href="{{ route('master.teachers.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
            Kembali
        </a>
    </div>

    @if(session('import_results'))
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start gap-3">
                @if(session('import_results')['failed'] > 0)
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                @else
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                @endif
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900">Hasil Import</h3>
                    <div class="mt-2 text-sm text-gray-600">
                        <p>Berhasil: <span class="font-semibold text-green-600">{{ session('import_results')['success'] }}</span> guru</p>
                        <p>Gagal: <span class="font-semibold text-red-600">{{ session('import_results')['failed'] }}</span> guru</p>
                    </div>
                    @if(!empty(session('import_results')['errors']))
                        <div class="mt-3">
                            <p class="text-sm font-medium text-gray-900 mb-1">Detail Error:</p>
                            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                                @foreach(session('import_results')['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Upload -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Upload File</h3>
                <p class="text-sm text-gray-500 mt-1">Pilih file CSV atau Excel yang berisi data guru</p>
            </div>
            <form method="POST" action="{{ route('master.teachers.import.process') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">File Import</label>
                    <input type="file" name="file" id="file" accept=".csv,.xlsx,.xls" required
                           class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">CSV, XLSX, atau XLS (Max: 5MB)</p>
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Upload & Import
                    </button>
                    <a href="{{ route('master.teachers.import.template') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        Download Template
                    </a>
                </div>
            </form>
        </div>

        <!-- Panduan -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Panduan Import</h3>
                <p class="text-sm text-gray-500 mt-1">Ikuti langkah-langkah berikut untuk import yang berhasil</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-semibold">1</div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Download Template</p>
                        <p class="text-sm text-gray-500">Klik tombol "Download Template" untuk mendapatkan format yang benar</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-semibold">2</div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Isi Data Guru</p>
                        <p class="text-sm text-gray-500">Lengkapi data guru sesuai kolom yang tersedia. Kolom wajib: name, gender, join_date</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-semibold">3</div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Upload File</p>
                        <p class="text-sm text-gray-500">Pilih file yang sudah diisi dan klik "Upload & Import"</p>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-sm font-medium text-gray-900 mb-2">Format Data:</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• <strong>Gender:</strong> male atau female</li>
                        <li>• <strong>Tanggal:</strong> YYYY-MM-DD (contoh: 1985-01-01)</li>
                        <li>• <strong>Email:</strong> Opsional, jika kosong akan otomatis: nip@teacher.sch.id</li>
                        <li>• <strong>Password Default:</strong> guru123</li>
                    </ul>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-sm font-medium text-amber-700 mb-2">⚠️ Catatan Penting:</p>
                    <ul class="text-sm text-amber-600 space-y-1">
                        <li>• NIP dan Email harus unik (tidak boleh duplikat)</li>
                        <li>• Nama mata pelajaran harus sesuai dengan yang ada di sistem</li>
                        <li>• Hapus baris contoh sebelum import</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
