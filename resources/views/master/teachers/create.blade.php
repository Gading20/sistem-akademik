@extends('layouts.app')

@section('title', 'Tambah Guru')
@section('header', 'Tambah Guru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('master.teachers.index') }}" class="hover:text-gray-700">Guru</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Tambah Baru</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Tambah Guru</h3>

        <form method="POST" action="{{ route('master.teachers.store') }}" class="space-y-5">
            @csrf

            {{-- User Account --}}
            <div class="pb-5 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Akun Pengguna</h4>
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Nama lengkap guru" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="email@contoh.com" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" id="password" placeholder="Minimal 8 karakter" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Teacher Data --}}
            <div class="pb-5 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Data Guru</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1.5">NIP</label>
                        <input type="text" name="nip" id="nip" value="{{ old('nip') }}" placeholder="Nomor Induk Pegawai" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('nip') border-red-500 @enderror">
                        @error('nip')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nik" class="block text-sm font-medium text-gray-700 mb-1.5">NIK</label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik') }}" placeholder="Nomor Induk Kependudukan" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('nik') border-red-500 @enderror">
                        @error('nik')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin</label>
                        <select name="gender" id="gender" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('gender') border-red-500 @enderror">
                            <option value="">Pilih</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1.5">Mata Pelajaran Utama</label>
                        <select name="subject_id" id="subject_id" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('subject_id') border-red-500 @enderror">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="place_of_birth" class="block text-sm font-medium text-gray-700 mb-1.5">Tempat Lahir</label>
                        <input type="text" name="place_of_birth" id="place_of_birth" value="{{ old('place_of_birth') }}" placeholder="Contoh: Surabaya" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('place_of_birth') border-red-500 @enderror">
                        @error('place_of_birth')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Lahir</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('date_of_birth') border-red-500 @enderror">
                        @error('date_of_birth')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="qualification" class="block text-sm font-medium text-gray-700 mb-1.5">Kualifikasi</label>
                    <input type="text" name="qualification" id="qualification" value="{{ old('qualification') }}" placeholder="Contoh: S.Pd" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('qualification') border-red-500 @enderror">
                    @error('qualification')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="specialization" class="block text-sm font-medium text-gray-700 mb-1.5">Spesialisasi</label>
                    <input type="text" name="specialization" id="specialization" value="{{ old('specialization') }}" placeholder="Contoh: Teknik Komputer" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('specialization') border-red-500 @enderror">
                    @error('specialization')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Telepon</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="Nomor telepon" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">Alamat</label>
                    <textarea name="address" id="address" rows="2" placeholder="Alamat lengkap" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Employment Data --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Data Kepegawaian</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="employment_status" class="block text-sm font-medium text-gray-700 mb-1.5">Status Kepegawaian</label>
                        <select name="employment_status" id="employment_status" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('employment_status') border-red-500 @enderror">
                            <option value="">Pilih Status</option>
                            <option value="active" {{ old('employment_status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('employment_status') === 'inactive' ? 'selected' : '' }}>Non Aktif</option>
                            <option value="retired" {{ old('employment_status') === 'retired' ? 'selected' : '' }}>Pensiun</option>
                        </select>
                        @error('employment_status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="join_date" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Masuk</label>
                        <input type="date" name="join_date" id="join_date" value="{{ old('join_date', date('Y-m-d')) }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('join_date') border-red-500 @enderror">
                        @error('join_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('master.teachers.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection