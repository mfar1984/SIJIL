{{-- Question builder. Expects: $survey, $questions, $questionTypes --}}

@if($questions->isEmpty())
    <div class="text-center py-10 border border-dashed border-gray-300 rounded mb-4">
        <span class="material-icons-outlined text-gray-300" style="font-size: 40px !important; width: 40px; height: 40px;">quiz</span>
        <p class="text-sm text-gray-500 mt-2">No questions yet</p>
        <p class="text-xs text-gray-400 mt-1">Add your first question below. A survey needs at least one question before it can be published.</p>
    </div>
@else
    <div id="question-list" class="space-y-2 mb-4">
        @foreach($questions as $question)
            <div class="border border-gray-200 rounded" data-question-id="{{ $question->id }}"
                 x-data="{ editing: false }">
                <div class="flex items-start gap-3 p-3">
                    <span class="text-xs text-gray-400 font-medium w-5 shrink-0 text-right pt-0.5">{{ $loop->iteration }}</span>

                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-800">
                            {{ $question->question_text }}
                            @if($question->required)
                                <span class="text-red-500">*</span>
                            @endif
                        </p>

                        @if($question->description)
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $question->description }}</p>
                        @endif

                        <div class="flex items-center flex-wrap gap-2 mt-2">
                            <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded-full">
                                {{ $question->type_label }}
                            </span>

                            @if($question->needsOptions())
                                <span class="text-[11px] text-gray-400">
                                    {{ count($question->options ?? []) }} options:
                                    {{ Str::limit(implode(', ', $question->options ?? []), 60) }}
                                </span>
                            @elseif($question->question_type === 'rating')
                                <span class="text-[11px] text-gray-400">
                                    {{ $question->scale_min }}–{{ $question->scale_max }}
                                    @if($question->scale_min_label || $question->scale_max_label)
                                        ({{ $question->scale_min_label ?: '?' }} → {{ $question->scale_max_label ?: '?' }})
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        @can('survey_questions.manage')
                        <button type="button" class="p-1 rounded hover:bg-gray-100 js-move-up" title="Move up">
                            <span class="material-icons-outlined text-gray-400 text-xs">arrow_upward</span>
                        </button>
                        <button type="button" class="p-1 rounded hover:bg-gray-100 js-move-down" title="Move down">
                            <span class="material-icons-outlined text-gray-400 text-xs">arrow_downward</span>
                        </button>
                        <button type="button" @click="editing = !editing"
                                class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                            <span class="material-icons-outlined text-yellow-600 text-xs">edit</span>
                        </button>
                        <form method="POST" action="{{ route('survey.questions.destroy', [$survey, $question]) }}"
                              onsubmit="return confirm('Delete this question?')" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>

                @can('survey_questions.manage')
                <div x-show="editing" x-cloak class="border-t border-gray-200 bg-gray-50 p-3">
                    @include('survey.partials.question-form', [
                        'action' => route('survey.questions.update', [$survey, $question]),
                        'method' => 'PUT',
                        'submitLabel' => 'Save question',
                        'question' => $question,
                        'questionTypes' => $questionTypes,
                    ])
                </div>
                @endcan
            </div>
        @endforeach
    </div>
@endif

@can('survey_questions.manage')
<div class="border border-gray-200 rounded">
    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
            <span class="material-icons-outlined text-primary-DEFAULT mr-2">add_circle</span>
            Add a question
        </h2>
    </div>
    <div class="p-4">
        @include('survey.partials.question-form', [
            'action' => route('survey.questions.store', $survey),
            'method' => 'POST',
            'submitLabel' => 'Add question',
            'question' => null,
            'questionTypes' => $questionTypes,
        ])
    </div>
</div>

<script>
    // Reordering: move a card in the DOM, then persist the new order.
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('question-list');

        if (!list) {
            return;
        }

        function persistOrder() {
            const ids = Array.from(list.querySelectorAll('[data-question-id]'))
                .map(el => parseInt(el.dataset.questionId, 10));

            fetch(@js(route('survey.questions.order', $survey)), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ questions: ids }),
            })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                renumber();
            })
            .catch(() => {
                alert('Could not save the new order. Reloading.');
                window.location.reload();
            });
        }

        function renumber() {
            list.querySelectorAll('[data-question-id]').forEach((card, index) => {
                const label = card.querySelector('span.w-5');
                if (label) label.textContent = index + 1;
            });
        }

        list.addEventListener('click', function (event) {
            const up = event.target.closest('.js-move-up');
            const down = event.target.closest('.js-move-down');

            if (!up && !down) {
                return;
            }

            const card = (up || down).closest('[data-question-id]');

            if (up && card.previousElementSibling) {
                card.parentNode.insertBefore(card, card.previousElementSibling);
                persistOrder();
            } else if (down && card.nextElementSibling) {
                card.parentNode.insertBefore(card.nextElementSibling, card);
                persistOrder();
            }
        });
    });
</script>
@endcan
