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
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ __('Access Codes List') }}</h3>
            </div>

            <div class="d-flex justify-content-center flex-wrap mb-5 mt-5">
                <div class="d-flex justify-content-end w-100" id="add_btn" data-bs-toggle="modal"
                    data-bs-target="#crud_modal" data-kt-docs-table-toolbar="base">
                    <button type="button" class="btn btn-primary w-100">
                        <span class="svg-icon svg-icon-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                    transform="rotate(-90 11.364 20.364)" fill="currentColor"></rect>
                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                    fill="currentColor"></rect>
                            </svg>
                        </span>
                        {{ __('Add Access Code') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="d-flex flex-stack flex-wrap mb-5">
                <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
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
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="{{ __('Search Access Codes') }}">
                </div>

                <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-docs-table-select="selected_count"></span>{{ __('Selected') }}
                    </div>
                    <button type="button" class="btn btn-danger"
                        data-kt-docs-table-select="delete_selected">{{ __('Delete') }}</button>
                </div>
            </div>

            <table id="kt_datatable" class="table align-middle text-center table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                    data-kt-check-target="#kt_datatable .form-check-input" value="1" />
                            </div>
                        </th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Usage Limit') }}</th>
                        <th>{{ __('Used Count') }}</th>
                        <th>{{ __('Single Use') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th class="min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
        </div>
    </div>

    <form id="crud_form" class="ajax-form w-75" action="{{ route('dashboard.generateCode.store') }}" method="post"
        enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf
        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add Access Code') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close"><i class="ki-outline ki-cross fs-1"></i></div>
                    </div>

                    <div class="modal-body">
                        <div class="mb-4">
                            <label for="class_id" class="form-label">{{ __('Class') }}</label>
                            <select name="class_id" id="class_id" class="form-select">
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->title }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="class_id_error"></div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-8">
                                <label for="code" class="form-label">{{ __('Access Code') }}</label>
                                <input type="text" name="code" id="code" class="form-control"
                                    placeholder="{{ __('Leave blank to auto-generate') }}">
                                <div class="invalid-feedback" id="code_error"></div>
                            </div>

                            <div class="col-4 d-flex align-items-center mt-4">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="is_active" type="checkbox" value="1"
                                        id="is_active_switch" checked>
                                    <span class="form-check-label text-dark"
                                        for="is_active_switch">{{ __('Active') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="usage_limit" class="form-label">{{ __('Usage Limit') }}</label>
                                <input type="number" name="usage_limit" id="usage_limit" class="form-control"
                                    min="1" placeholder="{{ __('Leave blank for unlimited') }}">
                                <div class="invalid-feedback" id="usage_limit_error"></div>
                            </div>

                            <div class="col-6 d-flex align-items-center mt-4">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="single_use" type="checkbox" value="1"
                                        id="single_use_switch" checked>
                                    <span class="form-check-label text-dark"
                                        for="single_use_switch">{{ __('Single Use') }}</span>
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
                                {{ __('Please wait...') }} <span class="spinner-border spinner-border-sm ms-2"></span>
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
    <script src="{{ asset('assets/dashboard/js/datatables/access_codes.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>

    <script>
        $(document).ready(function() {
            $("#add_btn").click(function(e) {
                e.preventDefault();
                $("[title='_method']").remove();
                $("#crud_form")[0].reset();
                $("#crud_form").find('.invalid-feedback').text('');
                $("#crud_form").find('.is-invalid').removeClass('is-invalid');
                $("#crud_form").attr('action', "{{ route('dashboard.generateCode.store') }}");
                $("#form_title").text("{{ __('Add Access Code') }}");
                $("#crud_modal").modal('show');
            });
        });
    </script>
@endpush
