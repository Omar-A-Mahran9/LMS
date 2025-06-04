@extends('dashboard.partials.master')
@push('styles')
    <link href="{{ asset('assets/dashboard/css/datatables' . (isDarkMode() ? '.dark' : '') . '.bundle.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ asset('assets/dashboard/plugins/custom/datatables/datatables.bundle' . (isArabic() ? '.rtl' : '') . '.css') }}"
        rel="stylesheet" type="text/css" />
@endpush
@section('content')
    <div class="card mb-5 mb-x-10">
        <!--begin::Card header-->
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ __('questions list') }}</h3>
            </div>
            <!--end::Card title-->

            <div class="d-flex justify-content-center flex-wrap mb-5 mt-5">

                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end w-100" id="add_btn" data-bs-toggle="modal"
                    data-bs-target="#crud_modal" data-kt-docs-table-toolbar="base">
                    <!--begin::Add customer-->
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="tooltip"
                        data-bs-original-title="Coming Soon" data-kt-initialized="1">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                        <span class="svg-icon svg-icon-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                    transform="rotate(-90 11.364 20.364)" fill="currentColor">
                                </rect>
                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                    fill="currentColor"></rect>
                            </svg>
                        </span>
                        <!--end::Svg Icon-->{{ __('Add new question') }}
                    </button>
                    <!--end::Add customer-->
                </div>
                <!--end::Toolbar-->

            </div>
            <!--end::Info-->
        </div>
        <!--begin::Card header-->
        <!--begin::Content-->
        <div class="card-body">
            <!--begin::Wrapper-->
            <div class="d-flex flex-stack flex-wrap mb-5">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                    <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                    <span class="svg-icon svg-icon-1 position-absolute ms-6">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                                transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
                            <path
                                d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                fill="currentColor"></path>
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="{{ __('question') }}">
                </div>
                <!--end::Search-->

                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-docs-table-select="selected_count"></span>{{ __('Selected item') }}
                    </div>
                    <button type="button" class="btn btn-danger"
                        data-kt-docs-table-select="delete_selected">{{ __('delete') }}</button>
                </div>
                <!--end::Group actions-->
            </div>
            <!--end::Wrapper-->

            <!--begin::Datatable-->
            <table id="kt_datatable" class="table align-middle text-center table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class=" text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                    data-kt-check-target="#kt_datatable .form-check-input" value="1" />
                            </div>
                        </th>
                        <th>{{ __('Question') }}</th>
                        <th>{{ __('Quiz') }}</th>
                        <th>{{ __('Created at') }}</th>

                        <th class=" min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
            <!--end::Datatable-->
        </div>
        <!--end::Content-->
    </div>


    <form id="crud_form" class="ajax-form" method="post" action="{{ route('dashboard.questions.store') }}"
        enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf

        <div class="modal fade" id="crud_modal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add New Question') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        {{-- Question Arabic --}}
                        <div class="mb-3">
                            <label for="quiz_id_inp">{{ __('Select Quiz') }}</label>
                            <select name="quiz_id" id="quiz_id_inp" class="form-select" data-control="select2"
                                data-placeholder="{{ __('Select Quiz') }}" data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                <option value="" selected></option>
                                @foreach ($quizzes as $quiz)
                                    <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                                @endforeach
                            </select>



                            <div class="invalid-feedback" id="quiz_id"></div>

                        </div>

                        <div class="mb-3">
                            <label for="question_ar_inp">{{ __('Question (Arabic)') }}</label>
                            <input type="text" name="question_ar" class="form-control" id="question_ar_inp">
                            <div class="invalid-feedback" id="question_ar"></div>
                        </div>

                        {{-- Question English --}}
                        <div class="mb-3">
                            <label for="question_en_inp">{{ __('Question (English)') }}</label>
                            <input type="text" name="question_en" class="form-control" id="question_en_inp">
                            <div class="invalid-feedback" id="question_en"></div>
                        </div>

                        {{-- Question Type --}}
                        <div class="mb-3">
                            <label for="type_inp">{{ __('Question Type') }}</label>
                            <select name="type"id="type_inp" class="form-select" data-control="select2"
                                data-placeholder="{{ __('Select Type') }}" data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                <option value="multiple_choice">{{ __('Multiple Choice') }}</option>
                                <option value="true_false">{{ __('True / False') }}</option>
                                <option value="short_answer">{{ __('Short Answer') }}</option>
                            </select>
                            <div class="invalid-feedback" id="type"></div>
                        </div>

                        {{-- Repeater Error --}}
                        <div class="text-danger mb-2" id="answers"></div>

                        {{-- Multiple Choice Answers --}}
                        <div class="mb-3 answer-type answer-multiple_choice">
                            <label>{{ __('Answers') }}</label>
                            <div id="form_repeater">
                                <div data-repeater-list="answers">

                                    <div data-repeater-item class="row mb-2">
                                        <div class="col-md-4">
                                            <input type="text" name="text_ar" class="form-control answer-text-ar">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="text_en" class="form-control answer-text-en">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-center">
                                            <label class="form-check-label">

                                                <input type="checkbox" name="is_correct" value="1"
                                                    class="form-check-input">
                                                <div class="invalid-feedback"></div>

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
                            <div class="d-flex  gap-5 mt-5">
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="correct_tf" value="true"
                                        id="true_tf_inp">
                                    <label class="form-check-label" for="true_tf_inp">{{ __('True') }}</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="correct_tf" value="false"
                                        id="true_tf_inp">
                                    <label class="form-check-label" for="true_tf_inp">{{ __('False') }}</label>
                                </div>
                            </div>

                            <div class="invalid-feedback" id="correct_tf"></div>

                        </div>

                        {{-- Short Answer --}}
                        <div class="mb-3 answer-type answer-short_answer d-none">
                            <label for="short_answer_inp">{{ __('Expected Answer') }}</label>
                            <input type="text" name="short_answer" class="form-control" id="short_answer_inp">
                            <div class="invalid-feedback" id="short_answer"></div>

                        </div>

                        {{-- Points --}}
                        <div class="mb-3">
                            <label for="points_inp">{{ __('Points') }}</label>
                            <input type="number" name="points" class="form-control" value="1" min="1"
                                id="points_inp">
                            <div class="invalid-feedback" id="points"></div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('Save') }}</span>
                            <span class="indicator-progress">
                                {{ __('Please wait...') }} <span
                                    class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('scripts')
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/questions.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>

    <script src="{{ asset('assets/dashboard/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>

    <script>
        $(document).on('click', '.open-question-modal', function() {
            const quizId = $(this).data('quiz-id');
            $('#crud_form input[name="quiz_id"]').val(quizId);
        });
    </script>
    {{-- Plugins --}}
    <script src="{{ asset('assets/dashboard/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/components/form_repeater.js') }}"></script>

    {{-- Repeater Init --}}
    <script>
        // Dynamic question type switching
        $('#type_inp').on('change', function() {
            let type = $(this).val();
            $('.answer-type').addClass('d-none');
            $("#answers").html('');

            $('.answer-' + type).removeClass('d-none');
        }).trigger('change'); // Trigger on load
    </script>

    <script>
        $(document).ready(function() {
            $('#add_question_btn').on('click', function(e) {
                e.preventDefault();

                const $form = $('#question_form');

                // Reset the form
                $form[0].reset();

                // Clear Select2 selection
                $('#type_inp').val('').trigger('change');

                // Reset hidden fields if needed
                $form.find('input[type="hidden"]').val('');

                // Clear validation messages and classes
                $form.find('.invalid-feedback').text('');
                $form.find('.is-invalid').removeClass('is-invalid');
                $('#answers').text('');

                // Reset repeater content with one empty row
                const $repeaterList = $('#form_repeater [data-repeater-list]');
                $repeaterList.html(`
            <div data-repeater-item class="row mb-2">
                <div class="col-md-4">
                    <input type="text" name="text_ar" class="form-control answer-text-ar">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-4">
                    <input type="text" name="text_en" class="form-control answer-text-en">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <label class="form-check-label">
                        <input type="checkbox" name="is_correct" value="1" class="form-check-input">
                        <div class="invalid-feedback"></div>
                        {{ __('Correct') }}
                    </label>
                </div>
                <div class="col-md-2">
                    <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-danger">
                        {{ __('Delete') }}
                    </a>
                </div>
            </div>
        `);

                // Hide all answer sections
                $('.answer-type').addClass('d-none');

                // Reset modal title
                $('#questionModal .modal-title').text(`{{ __('Add New Question') }}`);

                // Reset form action to store route (in case reused)
                $form.attr('action', `{{ route('dashboard.questions.store') }}`);

                // Show modal
                $('#questionModal').modal('show');

                // Focus on English question input after modal opens
                setTimeout(() => {
                    $('#question_en_inp').trigger('focus');
                }, 300);
            });
        });
    </script>
@endpush
