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
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ __('Student notifications') }}</h3>
            </div>

            @can('create_notifications')
                <div class="d-flex align-items-center my-5">
                    <button type="button" class="btn btn-primary" id="add_btn" data-bs-toggle="modal"
                        data-bs-target="#crud_modal">
                        <i class="ki-outline ki-send fs-2"></i>
                        {{ __('Send a message to students') }}
                    </button>
                </div>
            @endcan
        </div>
        <!--end::Card header-->

        <div class="card-body pt-0">
            <!--begin::Notice-->
            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mb-6">
                <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                <div class="fs-6 text-gray-700">
                    {{ __('Students are notified automatically when a new class is published (only the students of that class\'s grade) and when a new course is published (all students). Notifications appear on the website in the bell icon.') }}
                    {{ __('A student is also notified when their subscription is activated.') }}
                </div>
            </div>
            <!--end::Notice-->

            <!--begin::Wrapper-->
            <div class="d-flex flex-stack flex-wrap mb-5">
                <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute ms-5"></i>
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15"
                        placeholder="{{ __('Search notifications') }}">

                    <select id="filter_type" class="form-select form-select-solid w-200px ms-4" data-control="select2"
                        data-hide-search="true">
                        <option value="all">{{ __('All types') }}</option>
                        <option value="message">{{ __('Messages') }}</option>
                        <option value="new_class">{{ __('New class') }}</option>
                        <option value="new_course">{{ __('New course') }}</option>
                        <option value="subscription">{{ __('Subscription activated') }}</option>
                    </select>
                </div>

                <!-- datatable-config.js toggles this "base" toolbar with the "selected" one below;
                     without it every table draw threw "Cannot read properties of null (reading 'classList')" -->
                <div data-kt-docs-table-toolbar="base"></div>

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
                        <th class="text-start">{{ __('Notification') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Sent to') }}</th>
                        <th>{{ __('Read by') }}</th>
                        <th>{{ __('Sent at') }}</th>
                        <th class="min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
            <!--end::Datatable-->
        </div>
    </div>

    {{-- begin::Send message modal --}}
    <form id="crud_form" class="ajax-form" action="{{ route('dashboard.notifications.store') }}" method="post"
        data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf
        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Send a message to students') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>

                    <div class="modal-body">
                        <!-- Target -->
                        <div class="fv-row mb-7">
                            <label class="form-label required fs-6 fw-bold mb-3">{{ __('Send to') }}</label>
                            <div class="d-flex flex-wrap gap-6">
                                <label class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="target" value="all" checked>
                                    <span class="form-check-label fw-semibold">{{ __('All students') }}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="target" value="categories">
                                    <span class="form-check-label fw-semibold">{{ __('Students of specific grades') }}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="radio" name="target" value="students">
                                    <span class="form-check-label fw-semibold">{{ __('Specific students') }}</span>
                                </label>
                            </div>
                            <div class="fv-plugins-message-container invalid-feedback" id="target"></div>
                        </div>

                        <!-- Grades -->
                        <div class="fv-row mb-7 d-none" id="categories_wrapper">
                            <label for="category_ids_inp" class="form-label required fs-6 fw-bold mb-3">{{ __('Grades') }}</label>
                            <select name="category_ids[]" id="category_ids_inp" class="form-select form-select-solid"
                                multiple data-placeholder="{{ __('Choose grades') }}">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="fv-plugins-message-container invalid-feedback" id="category_ids"></div>
                        </div>

                        <!-- Students -->
                        <div class="fv-row mb-7 d-none" id="students_wrapper">
                            <label for="student_ids_inp" class="form-label required fs-6 fw-bold mb-3">{{ __('Students') }}</label>
                            <select name="student_ids[]" id="student_ids_inp" class="form-select form-select-solid"
                                multiple data-placeholder="{{ __('Search by name or phone') }}"></select>
                            <div class="fv-plugins-message-container invalid-feedback" id="student_ids"></div>
                        </div>

                        <!-- Title -->
                        <div class="row">
                            <div class="col-md-6 fv-row mb-7">
                                <label for="title_ar_inp" class="form-label required fs-6 fw-bold mb-3">{{ __('Title (Arabic)') }}</label>
                                <input type="text" name="title_ar" id="title_ar_inp" class="form-control form-control-solid"
                                    maxlength="255" placeholder="{{ __('Title (Arabic)') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="title_ar"></div>
                            </div>
                            <div class="col-md-6 fv-row mb-7">
                                <label for="title_en_inp" class="form-label fs-6 fw-bold mb-3">{{ __('Title (English)') }}</label>
                                <input type="text" name="title_en" id="title_en_inp" class="form-control form-control-solid"
                                    maxlength="255" placeholder="{{ __('Optional') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="title_en"></div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="row">
                            <div class="col-md-6 fv-row mb-7">
                                <label for="body_ar_inp" class="form-label required fs-6 fw-bold mb-3">{{ __('Message (Arabic)') }}</label>
                                <textarea name="body_ar" id="body_ar_inp" rows="4" maxlength="2000"
                                    class="form-control form-control-solid" placeholder="{{ __('Message (Arabic)') }}"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback" id="body_ar"></div>
                            </div>
                            <div class="col-md-6 fv-row mb-7">
                                <label for="body_en_inp" class="form-label fs-6 fw-bold mb-3">{{ __('Message (English)') }}</label>
                                <textarea name="body_en" id="body_en_inp" rows="4" maxlength="2000"
                                    class="form-control form-control-solid" placeholder="{{ __('Optional') }}"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback" id="body_en"></div>
                            </div>
                        </div>

                        <!-- Link -->
                        <div class="fv-row mb-0">
                            <label for="link_inp" class="form-label fs-6 fw-bold mb-3">{{ __('Link (optional)') }}</label>
                            <input type="text" name="link" id="link_inp" class="form-control form-control-solid" dir="ltr"
                                placeholder="/courses/5  {{ __('or') }}  https://...">
                            <div class="form-text">{{ __('Where the student goes when clicking the notification.') }}</div>
                            <div class="fv-plugins-message-container invalid-feedback" id="link"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('Send') }}</span>
                            <span class="indicator-progress">
                                {{ __('Please wait....') }} <span
                                    class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- end::Send message modal --}}
@endsection
@push('scripts')
    <script>
        let canDeleteNotifications = @json(auth('admin')->user()?->can('delete_notifications') ?? false);
    </script>
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/notifications.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>
    <script>
        $(document).ready(function() {
            const $modal = $('#crud_modal');

            $('#category_ids_inp').select2({
                dropdownParent: $modal,
                width: '100%'
            });

            $('#student_ids_inp').select2({
                dropdownParent: $modal,
                width: '100%',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('dashboard.notifications.students-search') }}',
                    dataType: 'json',
                    delay: 300,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => data,
                },
            });

            function toggleTarget() {
                const target = $('[name="target"]:checked').val();
                $('#categories_wrapper').toggleClass('d-none', target !== 'categories');
                $('#students_wrapper').toggleClass('d-none', target !== 'students');
            }

            $('[name="target"]').on('change', toggleTarget);

            $('#add_btn').on('click', function() {
                $('#crud_form').trigger('reset');
                $('#category_ids_inp').val(null).trigger('change');
                $('#student_ids_inp').val(null).trigger('change');
                toggleTarget();
            });
        });
    </script>
@endpush
