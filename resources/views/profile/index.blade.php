@extends('layouts.app')

@section('title', 'Profil Saya')
@section('header', 'Profil Saya')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Profile Info --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Profil Saya</h3>
            <p class="text-sm text-gray-500">Kelola informasi profil Anda</p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-6 mb-6">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-bold text-blue-600">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-sm text-gray-500">{{ auth()->user()->role?->label ?? 'Unknown' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Telepon</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                    <input type="text" value="{{ auth()->user()->role?->label ?? 'Unknown' }}" disabled class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Ubah Password</h3>
            <p class="text-sm text-gray-500">Perbarui password akun Anda</p>
        </div>

        <form method="POST" action="{{ route('profile.password') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Saat Ini</label>
                <input type="password" name="current_password" id="current_password" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('current_password') border-red-500 @enderror">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                    <input type="password" name="password" id="password" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Ubah Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
