@extends('layouts.app')

@section('title', 'Edit Pengumuman')
@section('header', 'Edit Pengumuman')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('announcements.index') }}" class="hover:text-gray-700">Pengumuman</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Edit Pengumuman</h3>

        <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Judul Pengumuman</label>
                <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}" placeholder="Judul pengumuman" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1.5">Isi Pengumuman</label>
                <textarea name="content" id="content" rows="8" placeholder="Tulis isi pengumuman..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('content') border-red-500 @enderror">{{ old('content', $announcement->content) }}</textarea>
                @error('content')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-3">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Pin pengumuman ini</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $announcement->is_published) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Terbitkan</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('announcements.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
