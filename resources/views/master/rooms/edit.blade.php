@extends('layouts.app')

@section('title', 'Edit Ruang')
@section('header', 'Edit Ruang')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('master.rooms.index') }}" class="hover:text-gray-700">Ruang</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Edit Ruang</h3>

        <form method="POST" action="{{ route('master.rooms.update', $room) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Ruang</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $room->name) }}" placeholder="Contoh: R. TKJ 1" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Kode Ruang</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $room->code) }}" placeholder="Contoh: R-TKJ-01" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('code') border-red-500 @enderror">
                    @error('code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Ruang</label>
                <select name="type" id="type" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('type') border-red-500 @enderror">
                    <option value="">Pilih Tipe</option>
                    <option value="regular" {{ old('type', $room->type) === 'regular' ? 'selected' : '' }}>Ruang Kelas</option>
                    <option value="lab" {{ old('type', $room->type) === 'lab' ? 'selected' : '' }}>Laboratorium</option>
                    <option value="office" {{ old('type', $room->type) === 'office' ? 'selected' : '' }}>Ruang Guru</option>
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="building" class="block text-sm font-medium text-gray-700 mb-1.5">Gedung</label>
                    <input type="text" name="building" id="building" value="{{ old('building', $room->building) }}" placeholder="Contoh: Gedung A" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('building') border-red-500 @enderror">
                    @error('building')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="floor" class="block text-sm font-medium text-gray-700 mb-1.5">Lantai</label>
                    <input type="number" name="floor" id="floor" value="{{ old('floor', $room->floor) }}" min="0" placeholder="Contoh: 1" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('floor') border-red-500 @enderror">
                    @error('floor')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1.5">Kapasitas</label>
                <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $room->capacity) }}" min="1" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('capacity') border-red-500 @enderror">
                @error('capacity')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $room->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm text-gray-700">Aktifkan ruang ini</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('master.rooms.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection