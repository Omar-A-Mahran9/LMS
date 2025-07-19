@extends('dashboard.partials.master')
@push('styles')
    <link href="{{ asset('assets/dashboard/css/datatables' . (isDarkMode() ? '.dark' : '') . '.bundle.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ asset('assets/dashboard/plugins/custom/datatables/datatables.bundle' . (isArabic() ? '.rtl' : '') . '.css') }}"
        rel="stylesheet" type="text/css" />
@endpush
@section('content')
    <!--begin::Basic info-->
    <!--begin::Basic info-->
    <div class="card mb-5 mb-x-10">
        <!--begin::Card header-->

        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ __('Enrollments list') }}</h3>
            </div>
            <!--end::Card title-->


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
                        class="form-control form-control-solid w-250px ps-15"
                        placeholder="{{ __('Search for enrollments') }}">
                </div>
                <!--end::Search-->

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
                            <!--end::Svg Icon-->{{ __('Add Enroll') }}
                        </button>
                        <!--end::Add customer-->
                    </div>
                    <!--end::Toolbar-->
                </div>
                <!--end::Info-->
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
                    <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                    data-kt-check-target="#kt_datatable .form-check-input" value="1" />
                            </div>
                        </th>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Course') }}</th>
                        <th>{{ __('Payment Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Enroll At') }}</th>
                        <th class="min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>

        </div>
        <!--end::Content-->
    </div>
    <!--end::Basic info-->
    <form id="crud_form" class="ajax-form" action="{{ route('dashboard.enrollments.store') }}" method="post"
        data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf
        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add new Enroll') }}</h5>
                        <div class="d-flex">
                            <div class="col-6 d-flex align-items-center me-5 ">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="is_active" type="checkbox" value="1"
                                        id="is_active_switch" checked>
                                    <span class="form-check-label text-dark"
                                        for="is_active_switch">{{ __('Active') }}</span>
                                </label>
                            </div>
                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                                aria-label="Close">
                                <i class="ki-outline ki-cross fs-1"></i>
                            </div>

                        </div>

                    </div>

                    <div class="modal-body">
                        <div class="fv-row mb-4">
                            <label class="form-label " for="student_id_inp">{{ __('Student') }}</label>
                            <select name="student_id" class="form-select form-select-solid"
                                data-dir="{{ getDirection() }}" data-control="select2"
                                data-placeholder="{{ __('Select student') }}" data-allow-clear="true"
                                id="student_id_inp">
                                <option value=""></option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->first_name . ' ' . $student->last_name }}</option>
                                @endforeach
                            </select>

                            <div class="invalid-feedback" id="student_id"></div>
                        </div>

                        <div class="fv-row mb-4">
                            <label class="form-label" for="course_id_inp">{{ __('Course') }}</label>
                            <select name="course_id" class="form-select form-select-solid"
                                data-dir="{{ getDirection() }}" data-control="select2"
                                data-placeholder="{{ __('Select course') }}" data-allow-clear="true" id="course_id_inp">
                                <option value=""></option>

                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">
                                        {{ $course->title }}{{ '     ' }}
                                        ({{ $course->is_active ? __('Active') : __('Inactive') }})
                                    </option>
                                @endforeach
                            </select>

                            <div class="invalid-feedback" id="course_id"></div>
                        </div>

                        <div class="fv-row mb-4">
                            <label for="payment_method_inp"
                                class="form-label required">{{ __('Payment Method') }}</label>
                            <select name="payment_method"class="form-select form-select-solid"
                                data-dir="{{ getDirection() }}" data-control="select2"
                                data-placeholder="{{ __('Select payment') }}" data-allow-clear="true"
                                id="payment_method_inp">
                                <option value=""></option>

                                <option value="wallet_transfer">{{ __('Wallet Transfer') }}</option>
                                <option value="pay_in_center">{{ __('Pay in Center') }}</option>
                                <option value="contact_with_support">{{ __('Contact with Support') }}</option>
                            </select>
                            <div class="invalid-feedback" id="payment_method"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('Save') }}</span>
                            <span class="indicator-progress">{{ __('Please wait...') }}
                                <span class="spinner-border spinner-border-sm ms-2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('scripts')
    <script>
        const langActive = "{{ __('Active') }}";
        const langInactive = "{{ __('Inactive') }}";

        $(document).ready(function() {
            $('#student_id_inp').on('change', function() {
                var studentId = $(this).val();
                var $courseSelect = $('#course_id_inp');

                $courseSelect.html('<option value=""></option>').trigger('change');

                if (!studentId) {
                    return;
                }

                $.ajax({
                    url: '/dashboard/enrollments/courses-for-student/' + studentId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            var options = '<option value=""></option>';
                            $.each(data.data, function(index, course) {
                                options += '<option value="' + course.id + '">' +
                                    course.title + ' (' + (course.is_active ?
                                        langActive : langInactive) + ')</option>';
                            });

                            $courseSelect.html(options).val(null).trigger('change');
                        } else {
                            console.error('Failed to fetch courses');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching courses:', error);
                    }
                });
            });
        });
    </script>


    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/enrollments.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>
    <script>
        const addNewEnrollText = "{{ __('Add new Enroll') }}";

        $(document).ready(function() {
            $("#add_btn").click(function(e) {
                e.preventDefault();
                $("#is_active_switch").prop('checked', false);
                $("#course_id_inp").val("").trigger('change');
                $("#student_id_inp").val("").trigger('change');
                $("#payment_method_inp").val("").trigger('change');

                $("#form_title").text(addNewEnrollText);
                $("[name='_method']").remove();
                $("#crud_form").trigger('reset');
                $("#crud_form").attr('action', `/dashboard/enrollments`);
            });
        });
    </script>
@endpush
