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
            data-bs-target="#kt_workhome_datatable" aria-expanded="true" aria-controls="kt_workhome_datatable">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ __('homeworks list') }}</h3>
            </div>
            <!--end::Card title-->

            <div class="d-flex justify-content-center flex-wrap mb-5 mt-5">

                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end w-100" id="add_btn" data-bs-toggle="modal"
                    data-bs-target="#crud_homework" data-kt-docs-table-toolbar="base">
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
                        <!--end::Svg Icon-->{{ __('Add new homework') }}
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
                        class="form-control form-control-solid w-250px ps-15" placeholder="{{ __('homeworks') }}">
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
            <table id="kt_workhome_datatable" class="table align-middle text-center table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class=" text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                    data-kt-check-target="#kt_workhome_datatable .form-check-input" value="1" />
                            </div>
                        </th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Course') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created at') }}</th>
                        <th>{{ __('views') }}</th>

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

    <form id="crud_form" class="ajax-form w-75" action="{{ route('dashboard.homeworks.store') }}" method="post"
        enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf
        <div class="modal fade" tabindex="-1" id="crud_homework">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add New homework') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>

                    <div class="modal-body">

                        {{-- Course & Section --}}
                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="course_id_inp" class="form-label">{{ __('Course') }}</label>
                                <select name="course_id" id="course_id_inp" class="form-select" data-control="select2"
                                    data-placeholder="{{ __('Select Course') }}"
                                    data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                    <option value="" selected></option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->title_en }}</option>
                                    @endforeach
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback" id="course_id"></div>
                            </div>

                        </div>

                        {{-- Titles --}}
                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="title_ar_inp" class="form-label">{{ __('Title (Arabic)') }}</label>
                                <input type="text" name="title_ar" id="title_ar_inp" class="form-control"
                                    placeholder="{{ __('Enter Arabic title') }}">
                                <div class="invalid-feedback" id="title_ar"></div>
                            </div>
                            <div class="col-6">
                                <label for="title_en_inp" class="form-label">{{ __('Title (English)') }}</label>
                                <input type="text" name="title_en" id="title_en_inp" class="form-control"
                                    placeholder="{{ __('Enter English title') }}">
                                <div class="invalid-feedback" id="title_en"></div>
                            </div>
                        </div>

                        {{-- Descriptions --}}
                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="description_ar_inp"
                                    class="form-label">{{ __('Description (Arabic)') }}</label>
                                <textarea name="description_ar" id="description_ar_inp" data-kt-autosize="true" class="tinymce"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback" id="description_ar"></div>
                            </div>
                            <div class="col-6">
                                <label for="description_en_inp"
                                    class="form-label">{{ __('Description (English)') }}</label>
                                <textarea name="description_en" id="description_en_inp" data-kt-autosize="true" class="tinymce"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback" id="description_en"></div>
                            </div>
                        </div>

                        {{-- homework Info --}}
                        <div class="row mb-4">
                            <div class="col-3">
                                <label for="duration_minutes_inp"
                                    class="form-label">{{ __('Duration (Minutes)') }}</label>
                                <input type="number" name="duration_minutes" id="duration_minutes_inp"
                                    class="form-control" min="0">
                                <div class="fv-plugins-message-container invalid-feedback" id="duration_minutes">
                                </div>
                            </div>
                            <div class="col-2 d-flex align-items-center mt-4">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="is_active" type="checkbox" value="1"
                                        id="is_active_switch" checked>
                                    <span class="form-check-label text-dark"
                                        for="is_active_switch">{{ __('Active') }}</span>
                                </label>
                            </div>

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
    <form id="question_form" class="ajax-form" method="post"
        action="{{ route('dashboard.homeworks-questions.store') }}" enctype="multipart/form-data"
        data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf

        <div class="modal fade" id="questionModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add New Question') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        {{-- Question Arabic --}}
                        <input type="hidden" name="home_work_id" value="">

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
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('scripts')
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/homeworks.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>

    <script src="{{ asset('assets/dashboard/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        $(document).ready(() => {

            initTinyMc();
        });
    </script>
    <script>
        $(document).on('click', '.open-question-modal', function() {
            const homeworkId = $(this).data('homework-id');
            $('#question_form input[name="home_work_id"]').val(homeworkId);
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
            $("#add_btn").click(function(e) {
                e.preventDefault();

                // Remove method override inputs (_method) used for PUT/PATCH on edit
                $("[title='_method']").remove();

                // Reset the form fields
                $("#crud_form")[0].reset();


                // Clear validation errors and invalid classes
                $("#crud_form").find('.invalid-feedback').text('');
                $("#crud_form").find('.is-invalid').removeClass('is-invalid');

                // Reset TinyMCE editors content if present
                if (typeof tinymce !== 'undefined') {
                    tinymce.editors.forEach(editor => editor.setContent(''));
                }

                // Reset checkboxes by title attribute if they have it (otherwise use IDs)
                $("#is_active_switch")
                    .prop('checked', false);

                $("#crud_form").attr('action', "{{ route('dashboard.homeworks.store') }}");

                // Reset modal title
                $("#form_title").text("{{ __('Add new homework') }}");

                // Optionally, reset date inputs
                $(" #duration_minutes_inp").val('');

                // Open modal if you want to show it on "Add"
                $("#crud_homework").modal('show');
            });
        });
    </script>
@endpush
