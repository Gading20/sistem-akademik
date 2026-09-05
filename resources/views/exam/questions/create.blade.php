@extends('layouts.app')

@section('title', 'Tambah Soal')
@section('header', 'Tambah Soal')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('exam.question-banks.index') }}" class="hover:text-gray-700">Bank Soal</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('exam.questions.index', $questionBank) }}" class="hover:text-gray-700">{{ $questionBank->name }}</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Tambah Soal</span>
        </nav>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6" x-data="questionForm()">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Tambah Soal</h3>

        <form method="POST" action="{{ route('exam.questions.store', $questionBank) }}" class="space-y-5" onsubmit="return cleanOptions(this)">
            @csrf
            <input type="hidden" name="question_bank_id" value="{{ $questionBank->id }}">

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Soal</label>
                    <select name="type" id="type" x-model="questionType" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('type') border-red-500 @enderror">
                        @foreach(\App\Enums\QuestionTypeEnum::cases() as $qType)
                            <option value="{{ $qType->value }}" {{ old('type') == $qType->value ? 'selected' : '' }}>{{ $qType->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1.5">Tingkat Kesulitan</label>
                    <select name="difficulty" id="difficulty" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('difficulty') border-red-500 @enderror">
                        <option value="easy" {{ old('difficulty', 'medium') === 'easy' ? 'selected' : '' }}>Mudah</option>
                        <option value="medium" {{ old('difficulty', 'medium') === 'medium' ? 'selected' : '' }}>Sedang</option>
                        <option value="hard" {{ old('difficulty', 'medium') === 'hard' ? 'selected' : '' }}>Sulit</option>
                    </select>
                    @error('difficulty')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="points" class="block text-sm font-medium text-gray-700 mb-1.5">Bobot (poin)</label>
                    <input type="number" name="points" id="points" value="{{ old('points', 10) }}" min="0.01" step="0.01" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('points') border-red-500 @enderror">
                    @error('points')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="question" class="block text-sm font-medium text-gray-700 mb-1.5">Pertanyaan</label>
                <textarea name="question" id="question" rows="4" placeholder="Tulis pertanyaan..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('question') border-red-500 @enderror">{{ old('question') }}</textarea>
                @error('question')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="explanation" class="block text-sm font-medium text-gray-700 mb-1.5">Pembahasan (Opsional)</label>
                <textarea name="explanation" id="explanation" rows="2" placeholder="Penjelasan singkat untuk pembahasan..." class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none @error('explanation') border-red-500 @enderror">{{ old('explanation') }}</textarea>
                @error('explanation')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Options for multiple-choice / true-false style questions --}}
            <div x-show="requiresOptions()" x-cloak>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilihan Jawaban</label>
                        <p class="text-xs text-gray-500" x-text="questionType === 'mcq_complex' ? 'Centang semua pilihan yang benar (boleh lebih dari satu).' : 'Centang pilihan yang merupakan jawaban benar.'"></p>
                    </div>
                    <button type="button" @click="addOption()" class="text-sm text-blue-600 hover:text-blue-700 font-medium">+ Tambah Pilihan</button>
                </div>
                <div class="space-y-3">
                    <template x-for="(option, index) in options" :key="index">
                        <div class="flex items-start gap-3" data-option-row>
                            <input type="hidden" :name="'options[' + index + '][is_correct]'" value="0">
                            <input type="checkbox" :name="'options[' + index + '][is_correct]'" value="1"
                                   :checked="option.correct" @change="toggleCorrect(index, $event.target.checked)"
                                   class="mt-2.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div class="flex-1">
                                <input type="text" :name="'options[' + index + '][option_text]'" x-model="option.text"
                                       :placeholder="'Pilihan ' + String.fromCharCode(65 + index)"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>
                            <button type="button" @click="removeOption(index)" x-show="options.length > 2" class="mt-2 p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <p class="mt-2 text-xs text-gray-500">Minimal 2 pilihan jawaban. Simpan hanya akan berhasil bila opsi terisi.</p>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Soal aktif (dapat dipilih pada ujian)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('exam.questions.index', $questionBank) }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function cleanOptions(form) {
        form.querySelectorAll('[data-option-row]').forEach((row) => {
            const textInput = row.querySelector('input[name$="[option_text]"]');
            if (textInput && textInput.value.trim() === '') {
                row.querySelectorAll('input').forEach((input) => { input.disabled = true; });
            }
        });
        return true;
    }

    function questionForm() {
        return {
            questionType: '{{ old('type', 'mcq') }}',
            options: {!! json_encode(array_map(fn ($o) => ['text' => $o, 'correct' => false], old('options_text', ['', '', '', '']))) !!},
            optionTypes: ['mcq', 'mcq_complex', 'true_false'],
            requiresOptions() {
                return this.optionTypes.includes(this.questionType);
            },
            addOption() {
                this.options.push({ text: '', correct: false });
            },
            removeOption(index) {
                this.options.splice(index, 1);
            },
            toggleCorrect(index, checked) {
                this.options[index].correct = checked;
                if (checked && this.questionType !== 'mcq_complex') {
                    this.options.forEach((option, i) => {
                        if (i !== index) option.correct = false;
                    });
                }
            }
        }
    }
</script>
@endpush
@endsection
