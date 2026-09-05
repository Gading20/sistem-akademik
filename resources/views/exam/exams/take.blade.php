<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $examAttempt->exam->title }} - Ujian Berlangsung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                },
            },
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .question-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5rem 0; }
    </style>
</head>
@php
    $typeLabels = collect(\App\Enums\QuestionTypeEnum::cases())->keyBy('value')->map(fn ($t) => $t->label());
@endphp
<body class="h-full font-sans bg-gray-50 text-gray-900 antialiased"
    x-data="examApp()"
    @keydown.escape.window="showExitModal = true"
>
    {{-- Confirm Exit Modal --}}
    <div x-show="showExitModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/70"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 text-center">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Keluar dari Ujian?</h3>
                <p class="text-sm text-gray-500 mb-6">Jawaban yang sudah tersimpan otomatis akan tetap aman. Yakin ingin keluar?</p>
                <div class="flex gap-3 justify-center">
                    <button @click="showExitModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Lanjut Ujian</button>
                    <a href="{{ route('exam.exams.available') }}" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Keluar</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Submit Confirmation Modal --}}
    <div x-show="showSubmitModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/70"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Submit</h3>
                <div class="text-sm text-gray-500 mb-4 space-y-1">
                    <p>Soal terjawab: <strong x-text="answeredCount"></strong> / <strong x-text="totalQuestions"></strong></p>
                    <p x-show="answeredCount < totalQuestions" class="text-amber-600 font-medium">
                        <strong x-text="totalQuestions - answeredCount"></strong> soal belum dijawab
                    </p>
                </div>
                <div class="flex gap-3 justify-center">
                    <button @click="showSubmitModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Kembali</button>
                    <button @click="submitExam()" :disabled="submitting" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <span x-show="!submitting">Submit Sekarang</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Mengirim...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Time Up Modal --}}
    <div x-show="timeUp" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/70"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 text-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Waktu Habis!</h3>
                <p class="text-sm text-gray-500 mb-6">Ujian akan otomatis disubmit.</p>
                <div class="flex justify-center">
                    <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col h-full">
        {{-- Header --}}
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <h1 class="text-sm font-semibold text-gray-900 truncate">{{ $examAttempt->exam->title }}</h1>
                <span class="text-xs text-gray-500 hidden sm:inline flex-shrink-0">{{ $examAttempt->exam->subject->name ?? '' }}</span>
            </div>
            <div class="flex items-center gap-4 flex-shrink-0">
                <div class="flex items-center gap-2" :class="remainingTime <= 300 ? 'text-red-600' : 'text-gray-700'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-mono font-semibold" x-text="formatTime(remainingTime)"></span>
                </div>
                <div class="hidden sm:flex items-center gap-1.5 text-xs text-gray-500">
                    <span x-show="!saving" class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Tersimpan
                    </span>
                    <span x-show="saving" class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Menyimpan...
                    </span>
                </div>
                <button @click="showSubmitModal = true" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Selesai
                </button>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            {{-- Questions --}}
            <main class="flex-1 overflow-y-auto p-6">
                <div class="max-w-3xl mx-auto space-y-6" id="questionList">
                    @foreach($questions as $qIndex => $item)
                        @php
                            $q = $item->question;
                            $qid = $q->id;
                            $saved = $answers[$qid] ?? null;
                            $savedOption = $saved?->selected_option_id;
                            $essayValue = $saved?->essay_answer ?? '';
                            $complexSaved = [];
                            if ($q->type === 'mcq_complex' && ($saved?->answer)) {
                                $decoded = json_decode($saved->answer, true);
                                $complexSaved = is_array($decoded) ? $decoded : [];
                            }
                            $options = $q->options->sortBy('order');
                        @endphp
                        <section class="bg-white rounded-xl border border-gray-200 p-6" id="question-{{ $qid }}">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold flex-shrink-0">{{ $qIndex + 1 }}</span>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $typeLabels[$q->type] ?? $q->type }}</span>
                                    <span class="text-xs text-gray-500">Bobot: {{ $q->points }} poin</span>
                                </div>
                            </div>

                            <div class="text-sm text-gray-900 whitespace-pre-line question-content mb-6">{{ $q->question }}</div>

                            @if($q->type === 'mcq' || $q->type === 'true_false')
                                <div class="space-y-3">
                                    @foreach($options as $optIndex => $option)
                                        <label class="flex items-start gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer transition-colors hover:border-blue-300">
                                            <input type="radio" name="ans_{{ $qid }}" value="{{ $option->id }}"
                                                {{ (int) $savedOption === (int) $option->id ? 'checked' : '' }}
                                                onchange="saveAnswer({{ $qid }}, { selected_option_id: this.value })"
                                                class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">
                                                <span class="font-medium text-gray-900">{{ chr(65 + $optIndex) }}.</span>
                                                {{ $option->option_text }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif($q->type === 'mcq_complex')
                                <p class="text-xs text-gray-500 mb-3">Pilih semua jawaban yang benar.</p>
                                <div class="space-y-3">
                                    @foreach($options as $optIndex => $option)
                                        <label class="flex items-start gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer transition-colors hover:border-blue-300">
                                            <input type="checkbox" name="ans_{{ $qid }}" value="{{ $option->id }}"
                                                {{ in_array((string) $option->id, $complexSaved, true) ? 'checked' : '' }}
                                                onchange="saveComplex({{ $qid }}, this)"
                                                class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">
                                                <span class="font-medium text-gray-900">{{ chr(65 + $optIndex) }}.</span>
                                                {{ $option->option_text }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif(in_array($q->type, ['essay', 'short_answer', 'file_upload', 'matching', 'practical'], true))
                                <div>
                                    <textarea name="ans_{{ $qid }}" rows="{{ $q->type === 'essay' ? 6 : 3 }}"
                                        placeholder="{{ $q->type === 'essay' ? 'Tulis jawaban Anda...' : 'Tulis jawaban singkat...' }}"
                                        class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                                    >{{ $essayValue }}</textarea>
                                    <p class="text-xs text-gray-400 mt-1">Jawaban tersimpan otomatis saat selesai mengetik.</p>
                                </div>
                            @else
                                <p class="text-sm text-gray-400">Jenis soal ini belum mendukung pengerjaan online.</p>
                            @endif
                        </section>
                    @endforeach
                </div>
            </main>

            {{-- Sidebar Navigation --}}
            <aside class="hidden lg:flex w-72 bg-white border-l border-gray-200 flex-col">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Navigasi Soal</h3>
                    <p class="text-xs text-gray-500">Klik nomor untuk berpindah ke soal</p>
                </div>
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-5 gap-2" id="questionNav">
                        @foreach($questions as $qIndex => $item)
                            <button type="button" data-qid="{{ $item->question->id }}"
                                onclick="goToQuestion('question-{{ $item->question->id }}')"
                                class="qnav-item w-full aspect-square rounded-lg text-sm font-medium border-2 border-gray-200 bg-white text-gray-600 hover:border-gray-300 transition-all">
                                {{ $qIndex + 1 }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 space-y-3">
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-100 border-2 border-green-500"></span> Terjawab</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-white border-2 border-gray-200"></span> Belum</span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <span x-text="answeredCount"></span> dari <span x-text="totalQuestions"></span> soal terjawab
                    </div>
                    <button @click="showSubmitModal = true" class="w-full py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Selesai & Submit
                    </button>
                </div>
            </aside>
        </div>
    </div>

    <script>
        const ATTEMPT_ANSWER_URL = @json(route('exam.exams.answer', $examAttempt));
        const ATTEMPT_SUBMIT_URL = @json(route('exam.exams.submit', $examAttempt));

        function examApp() {
            return {
                totalQuestions: {{ count($questions) }},
                answeredCount: 0,
                remainingTime: {{ $remainingTime }},
                saving: false,
                submitting: false,
                showExitModal: false,
                showSubmitModal: false,
                timeUp: false,
                timer: null,

                init() {
                    this.refreshAnswered();
                    const saveTexts = document.querySelectorAll('textarea[name^="ans_"]');
                    saveTexts.forEach((el) => {
                        let debounce = null;
                        el.addEventListener('input', () => {
                            clearTimeout(debounce);
                            debounce = setTimeout(() => {
                                const qid = el.name.replace('ans_', '');
                                saveAnswer(parseInt(qid, 10), { essay_answer: el.value });
                            }, 800);
                        });
                    });

                    this.timer = setInterval(() => {
                        if (this.remainingTime > 0) {
                            this.remainingTime--;
                            if (this.remainingTime <= 0) {
                                this.timeUp = true;
                                setTimeout(() => this.submitExam(), 3000);
                            }
                        }
                    }, 1000);
                },

                formatTime(seconds) {
                    const h = Math.floor(seconds / 3600);
                    const m = Math.floor((seconds % 3600) / 60);
                    const s = seconds % 60;
                    if (h > 0) return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                },

                refreshAnswered() {
                    let count = 0;
                    document.querySelectorAll('[data-qid]').forEach((el) => {
                        const qid = el.dataset.qid;
                        const group = document.querySelectorAll(`input[name="ans_${qid}"], textarea[name="ans_${qid}"]`);
                        let answered = false;
                        group.forEach((input) => {
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                if (input.checked) answered = true;
                            } else if (input.value.trim() !== '') {
                                answered = true;
                            }
                        });
                        el.classList.toggle('answered', answered);
                        el.classList.toggle('border-green-500', answered);
                        el.classList.toggle('bg-green-50', answered);
                        el.classList.toggle('text-green-700', answered);
                        if (answered) count++;
                    });
                    this.answeredCount = count;
                },

                goToQuestion(id) {
                    const el = document.getElementById(id);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                },

                submitExam() {
                    if (this.submitting) return;
                    this.submitting = true;
                    clearInterval(this.timer);

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = ATTEMPT_SUBMIT_URL;

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                    form.appendChild(csrfInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        function refreshAnswered() {
            const app = Alpine.$data(document.querySelector('[x-data]'));
            if (app && typeof app.refreshAnswered === 'function') app.refreshAnswered();
        }

        async function saveAnswer(questionId, payload) {
            const app = Alpine.$data(document.querySelector('[x-data]'));
            if (app) app.saving = true;

            try {
                const body = new URLSearchParams({ question_id: questionId });
                for (const [key, value] of Object.entries(payload)) {
                    if (value !== undefined && value !== null && value !== '') body.append(key, value);
                }
                await fetch(ATTEMPT_ANSWER_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body,
                });
            } finally {
                if (app) {
                    app.saving = false;
                    app.refreshAnswered();
                }
            }
        }

        function saveComplex(questionId, checkbox) {
            const selected = Array.from(document.querySelectorAll(`input[name="ans_${questionId}"]:checked`))
                .map((el) => parseInt(el.value, 10));
            saveAnswer(questionId, { answer: JSON.stringify(selected) });
        }
    </script>
</body>
</html>
