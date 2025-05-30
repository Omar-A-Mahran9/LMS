@extends('dashboard.partials.master')

@push('styles')
    <link href="{{ asset('assets/dashboard/css/datatables' . (isDarkMode() ? '.dark' : '') . '.bundle.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ asset('assets/dashboard/plugins/custom/datatables/datatables.bundle' . (isArabic() ? '.rtl' : '') . '.css') }}"
        rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <h3>{{ $quiz->title_en }} - {{ __('Questions') }}</h3>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal">
        {{ __('Add Question') }}
    </button>

    {{-- Modal Form --}}
    <form id="question_form" class="ajax-form" method="POST" action="{{ route('dashboard.questions.store') }}">
        @csrf
        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

        <div class="modal fade" id="questionModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add New Question') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label>{{ __('Question (Arabic)') }}</label>
                            <input type="text" name="question_ar" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>{{ __('Question (English)') }}</label>
                            <input type="text" name="question_en" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>{{ __('Question Type') }}</label>
                            <select name="type" class="form-select" id="question_type">
                                <option value="multiple_choice">{{ __('Multiple Choice') }}</option>
                                <option value="true_false">{{ __('True / False') }}</option>
                                <option value="short_answer">{{ __('Short Answer') }}</option>
                            </select>
                        </div>

                        {{-- Multiple Choice Answers --}}
                        <div class="mb-3 answer-type answer-multiple_choice">
                            <label>{{ __('Answers') }}</label>
                            <div id="form_repeater">
                                <div data-repeater-list="answers">
                                    <div data-repeater-item class="row mb-2">
                                        <div class="col-md-4">
                                            <input type="text" name="text_ar" class="form-control"
                                                placeholder="{{ __('Answer (Arabic)') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="text_en" class="form-control"
                                                placeholder="{{ __('Answer (English)') }}">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-center">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="is_correct" value="1"
                                                    class="form-check-input">
                                                {{ __('Correct') }}
                                            </label>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-danger">
                                                {{ __('Delete') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <a href="javascript:;" data-repeater-create class="btn btn-sm btn-secondary mt-2">
                                        {{ __('Add Answer') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- True/False Answers --}}
                        <div class="mb-3 answer-type answer-true_false d-none">
                            <label>{{ __('Correct Answer') }}</label>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="correct_tf" value="true">
                                <label class="form-check-label">{{ __('True') }}</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="correct_tf" value="false">
                                <label class="form-check-label">{{ __('False') }}</label>
                            </div>
                        </div>

                        {{-- Short Answer --}}
                        <div class="mb-3 answer-type answer-short_answer d-none">
                            <label>{{ __('Expected Answer') }}</label>
                            <input type="text" name="short_answer" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>{{ __('Points') }}</label>
                            <input type="number" name="points" class="form-control" value="1" min="1">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    {{-- Plugins --}}
    <script src="{{ asset('assets/dashboard/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>

    {{-- Repeater Init --}}
    <script>
        $('#form_repeater').repeater({
            initEmpty: false,
            isFirstItemUndeletable: true,
            show: function() {
                $(this).slideDown();
                $(this).find('input').prop('readonly', false);
            },
            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);
            }
        });

        // Dynamic question type switching
        $('#question_type').on('change', function() {
            let type = $(this).val();
            $('.answer-type').addClass('d-none');
            $('.answer-' + type).removeClass('d-none');
        }).trigger('change'); // Trigger on load
    </script>
@endpush
