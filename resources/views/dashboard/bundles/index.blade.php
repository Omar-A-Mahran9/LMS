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

        <div class="card-header border-0">

            <div class="card-title m-0">

                <h3 class="fw-bold m-0">

                    {{ __('Bundles List') }}

                </h3>

            </div>

            <div class="d-flex justify-content-center flex-wrap mb-5 mt-5">

                <div class="d-flex justify-content-end" id="add_btn" data-bs-toggle="modal" data-bs-target="#crud_modal">

                    <button type="button" class="btn btn-primary">

                        <span class="svg-icon svg-icon-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">

                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                    transform="rotate(-90 11.364 20.364)" fill="currentColor"></rect>

                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                    fill="currentColor"></rect>

                            </svg>
                        </span>

                        {{ __('Add Bundle') }}

                    </button>

                </div>

                <div>

                    <a href="{{ route('dashboard.bundles.exportPDF') }}" class="btn btn-secondary ms-3" target="_blank">

                        <i class="fas fa-file-pdf me-2"></i>

                    </a>

                </div>

            </div>

        </div>

        <div class="card-body">

            <div class="d-flex flex-stack flex-wrap mb-5">

                <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">

                    <span class="svg-icon svg-icon-1 position-absolute ms-6">

                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">

                            <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                                transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>

                            <path
                                d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                fill="currentColor"></path>

                        </svg>

                    </span>

                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="{{ __('Search Bundles') }}">

                </div>

                <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">

                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-docs-table-select="selected_count"></span>

                        {{ __('Selected') }}
                    </div>

                    <button type="button" class="btn btn-danger" data-kt-docs-table-select="delete_selected">

                        {{ __('Delete') }}

                    </button>

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

                        <th>{{ __('Image') }}</th>

                        <th>{{ __('Title') }}</th>

                        <th>{{ __('Classes Count') }}</th>

                        <th>{{ __('Codes Count') }}</th>

                        <th>{{ __('Status') }}</th>

                        <th>{{ __('Created At') }}</th>

                        <th>{{ __('Actions') }}</th>

                    </tr>

                </thead>

                <tbody class="text-gray-600 fw-semibold"></tbody>

            </table>

        </div>

    </div>





    <form id="crud_form" class="ajax-form" action="{{ route('dashboard.bundles.store') }}" method="POST"
        enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">

        @csrf

        <div class="modal fade" tabindex="-1" id="crud_modal">

            <div class="modal-dialog modal-xl modal-dialog-scrollable">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title" id="form_title">

                            {{ __('Add Bundle') }}

                        </h5>

                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">

                            <i class="ki-outline ki-cross fs-1"></i>

                        </div>

                    </div>

                    <div class="modal-body">
                        <div class="row  ">
                            <div class="col-12 d-flex flex-column justify-content-center">
                                <label for="image_inp"
                                    class="form-label  text-center fs-6 fw-bold mb-3">{{ __('Thumbnail Image') }}</label>
                                <x-dashboard.upload-image-inp name="image" :image="null" :directory="'bundles'"
                                    placeholder="default.svg" type="editable" />
                            </div>

                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="title_ar_inp" class="form-label ">{{ __('Title (Arabic)') }}</label>
                                <input type="text" name="title_ar" id="title_ar_inp" class="form-control"
                                    placeholder="{{ __('Enter title in Arabic') }}">
                                <div class="invalid-feedback" id="title_ar"></div>
                            </div>
                            <div class="col-6">
                                <label for="title_en_inp" class="form-label ">{{ __('Title (English)') }}</label>
                                <input type="text" name="title_en" id="title_en_inp" class="form-control"
                                    placeholder="{{ __('Enter title in English') }}">
                                <div class="invalid-feedback" id="title_en"></div>
                            </div>
                        </div>


                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="description_ar_inp"
                                    class="form-label">{{ __('Description (Arabic)') }}</label>

                                <textarea name="description_ar" id="description_ar_inp" data-kt-autosize="true" class="tinymce"></textarea>

                                <div class="fv-plugins-message-container invalid-feedback" id="description_ar"></div>

                            </div>
                            <!--begin::Col-->

                            <div class="col-6">
                                <label for="description_en_inp"
                                    class="form-label">{{ __('Description (English)') }}</label>

                                <textarea name="description_en" id="description_en_inp" data-kt-autosize="true" class="tinymce"></textarea>

                                <div class="fv-plugins-message-container invalid-feedback" id="description_en"></div>

                            </div>


                        </div>






                        <div class="mb-4">

                            <label class="form-label">

                                {{ __('Classes') }}

                            </label>

                            <select name="classes[]" id="classes_inp" multiple class="form-select"
                                data-control="select2" data-placeholder="{{ __('Select Classes') }}">

                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">

                                        {{ $class->title }}

                                    </option>
                                @endforeach

                            </select>

                            <div class="invalid-feedback" id="classes"></div>

                        </div>

                        <div class="row mb-4">


                            <div class="col-6" id="code_count_wrapper">
                                <label for="code_count_inp"
                                    class="form-label">{{ __('Number of Codes to Generate') }}</label>
                                <input type="number" name="code_count" id="code_count_inp" class="form-control"
                                    min="1" placeholder="{{ __('Enter number of codes (e.g. 40)') }}">
                                <div class="invalid-feedback" id="code_count"></div>
                            </div>



                            <div class="col-6">

                                <label class="form-label">

                                    {{ __('Usage Limit') }}

                                </label>

                                <input type="number" name="usage_limit" class="form-control" min="1">
                                <div class="invalid-feedback" id="usage_limit"></div>

                            </div>

                        </div>

                        <div class="form-check form-switch mb-4">

                            <input class="form-check-input" type="checkbox" name="single_use" value="1" checked>

                            <label class="form-check-label">

                                {{ __('Single Use') }}

                            </label>

                        </div>



                        <div class="form-check form-switch mb-4">

                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active_switch"
                                value="1" checked>

                            <label class="form-check-label">

                                {{ __('Active') }}

                            </label>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">

                            {{ __('Close') }}

                        </button>

                        <button type="submit" class="btn btn-primary">

                            <span class="indicator-label">

                                {{ __('Save') }}

                            </span>

                            <span class="indicator-progress">

                                {{ __('Please wait...') }}

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
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>

    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>

    <script src="{{ asset('assets/dashboard/js/datatables/bundles.js') }}"></script>

    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>


    <script src="{{ asset('assets/dashboard/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        $(document).ready(() => {

            initTinyMc();

        });
    </script>
    <script>
        $(document).ready(function() {

            $("#add_btn").click(function(e) {

                e.preventDefault();

                $("#crud_form")[0].reset();

                $('#classes_inp').val(null).trigger('change');

                $("#crud_form")
                    .find('.invalid-feedback')
                    .text('');

                $("#crud_form")
                    .find('.is-invalid')
                    .removeClass('is-invalid');

                $("#crud_form").attr(
                    'action',
                    "{{ route('dashboard.bundles.store') }}"
                );

                $("#crud_form")
                    .find('input[name="_method"]')
                    .remove();

                $("#form_title").text(
                    "{{ __('Add Bundle') }}"
                );

                $("#crud_modal").modal('show');
            });

        });
    </script>
@endpush
