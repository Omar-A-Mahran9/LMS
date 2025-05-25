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
                <h3 class="fw-bold m-0">{{ __('Courses list') }}</h3>
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
                        <!--end::Svg Icon-->{{ __('Add Addon') }}
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
                        class="form-control form-control-solid w-250px ps-15" placeholder="{{ __('Search for Addons') }}">
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
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Image') }}</th>
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

    {{-- begin::Add Country Modal --}}
    <form id="crud_form" class="ajax-form w-75" action="{{ route('dashboard.courses.store') }}" method="post"
        enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf

        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add new Course') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>

                    <div class="modal-body">

                        {{-- Images --}}
                        <div class="row mb-4">
                            <div class="col-6 d-flex flex-column justify-content-center">
                                <label for="image_inp"
                                    class="form-label  text-center fs-6 fw-bold mb-3">{{ __('Thumbnail Image') }}</label>
                                <x-dashboard.upload-image-inp name="image" :image="null" :directory="'courses'"
                                    placeholder="default.svg" type="editable" />
                            </div>
                            <div class="col-6 d-flex flex-column justify-content-center">
                                <label for="slide_image_inp"
                                    class="form-label  text-center fs-6 fw-bold mb-3">{{ __('Slide Image') }}</label>
                                <x-dashboard.upload-image-inp name="slide_image" :image="null" :directory="'courses'"
                                    placeholder="default.svg" type="editable" />
                            </div>
                        </div>

                        {{-- Title --}}
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

                        {{-- Description --}}
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

                        {{-- Notes --}}
                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="note_ar_inp" class="form-label">{{ __('Note (Arabic)') }}</label>
                    

                                <textarea name="note_ar" id="note_ar_inp" data-kt-autosize="true" class="tinymce"></textarea>

                                <div class="fv-plugins-message-container invalid-feedback" id="note_ar"></div>

                            </div>
                            <div class="col-6">
                                <label for="note_en_inp" class="form-label">{{ __('Note (English)') }}</label>
                               

                                                                    <textarea name="note_en" id="note_en_inp" data-kt-autosize="true" class="tinymce"></textarea>

                                <div class="fv-plugins-message-container invalid-feedback" id="note_en"></div>

                            </div>
                        </div>


                        {{-- Video URL --}}
                        <div class="row mb-4">
                            <div class="col-4 fv-row">
                                <label for="video_url_inp" class="form-label">{{ __('Promo Video URL') }}</label>
                                <input type="url" name="video_url" id="video_url_inp" class="form-control"
                                    placeholder="{{ __('Enter video URL') }}">

                                <div class="fv-plugins-message-container invalid-feedback" id="video_url"></div>

                            </div>

                            {{-- Relationships --}}
                            <div class="col-4">
                                <label for="instructor_id_inp" class="form-label">{{ __('Instructor') }}</label>
                                <select name="instructor_id" id="instructor_id_inp" class="form-select"
                                    data-control="select2" data-placeholder="{{ __('Select Instructor') }}"
                                    data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                    <option value="" selected></option>
                                    @foreach ($instructors as $instructor)
                                        <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                                    @endforeach
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback" id="instructor_id"></div>

                            </div>
                            <div class="col-4">
                                <label for="category_id_inp" class="form-label">{{ __('Category') }}</label>
                                <select name="category_id" id="category_id_inp" class="form-select"
                                    data-control="select2" data-placeholder="{{ __('Select Category') }}"
                                    data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                    <option value="" selected></option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback" id="category_id"></div>

                            </div>


                        </div>
                        {{-- Pricing --}}
                        <div class="row mb-4">
                            {{-- Price --}}
                            <div class="col-4">
                                <div class="d-flex align-items-center justify-content-between">

                                    <label for="price_inp" class="form-label">{{ __('Price') }}</label>
                                    <label class="form-check form-switch form-check-custom form-check-solid  mb-2">
                                        <input class="form-check-input" name="is_free" type="checkbox" value="1"
                                            id="is_free_switch">
                                        <span class="form-check-label"
                                            for="is_free_switch">{{ __('Is Free Course?') }}</span>
                                    </label>
                                </div>
                                <input type="number" name="price" id="price_inp" class="form-control" min="0"
                                    step="0.01" value="0">
                                <div class="fv-plugins-message-container invalid-feedback" id="price"></div>

                            </div>
                            {{-- Discount Percentage --}}
                            <div class="col-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="discount_percentage_inp"
                                        class="form-label">{{ __('Discount Percentage') }}</label>
                                    <label class="form-check form-switch form-check-custom form-check-solid mb-2">
                                        <input class="form-check-input" name="have_discount" type="checkbox"
                                            value="1" id="have_discount_switch">

                                        <span class="form-check-label"
                                            for="have_discount_switch">{{ __('Has Discount?') }}</span>
                                    </label>

                                </div>

                                <input type="number" name="discount_percentage" id="discount_percentage_inp"
                                    class="form-control" min="1" max="100" disabled>
                                <div class="fv-plugins-message-container invalid-feedback" id="discount_percentage"></div>

                            </div>

                            {{-- Enrollment --}}
                            <div class="col-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="max_students_inp" class="form-label">{{ __('Max Students') }}</label>

                                    <label class="form-check form-switch form-check-custom form-check-solid mb-2">
                                        <input class="form-check-input" name="is_enrollment_open" type="checkbox"
                                            value="1" id="enrollment_open_switch" checked>
                                        <span class="form-check-label"
                                            for="enrollment_open_switch">{{ __('Enrollment Open?') }}</span>
                                    </label>

                                </div>
                                <input type="number" name="max_students" id="max_students_inp" class="form-control"
                                    min="1" placeholder="{{ __('Leave empty for unlimited') }}">
                            </div>


                        </div>

                        {{-- SEO --}}
                        <div class="row mb-4">
                            <div class="col-4">
                                <label for="slug_inp" class="form-label ">{{ __('Slug') }}</label>
                                <input type="text" name="slug" id="slug_inp" class="form-control"
                                    placeholder="{{ __('Unique slug') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="slug"></div>

                            </div>
                            <div class="col-4">
                                <label for="meta_title_inp" class="form-label">{{ __('Meta Title') }}</label>
                                <input type="text" name="meta_title" id="meta_title_inp" class="form-control"
                                    placeholder="{{ __('Meta title for SEO') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="meta_title"></div>

                            </div>
                            <div class="col-4">
                                <label for="meta_description_inp" class="form-label">{{ __('Meta Description') }}</label>
                                <textarea name="meta_description" id="meta_description_inp" class="form-control" rows="2"
                                    placeholder="{{ __('Meta description for SEO') }}"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback" id="meta_description">
                                </div>

                            </div>
                        </div>








                        {{-- Duration and Dates --}}
                        <div class="row mb-4">

                            <div class="col-6">
                                <label for="start_date_inp" class="form-label">{{ __('Start Date') }}</label>
                                <input type="date" name="start_date" id="start_date_inp" class="form-control">
                                <div class="fv-plugins-message-container invalid-feedback" id="start_date"></div>

                            </div>
                            <div class="col-6">
                                <label for="end_date_inp" class="form-label">{{ __('End Date') }}</label>
                                <input type="date" name="end_date" id="end_date_inp" class="form-control">
                                <div class="fv-plugins-message-container invalid-feedback" id="end_date"></div>

                            </div>

                        </div>
                        {{-- Flags --}}
                        <div class="row mb-4 mt-5">
                            <div class="col-3">
                                <label for="subcategory_ids_inp" class="form-label">{{ __('Subcategories') }}</label>
                                <select name="subcategory_ids[]" id="subcategory_ids_inp" class="form-select"
                                    data-control="select2" data-placeholder="{{ __('Select Subcategories') }}"
                                    data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}" multiple>
                                    <option value="" selected></option>
                                    @foreach ($subcategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback" id="subcategory_ids"></div>

                            </div>
                            <div class="col-3 d-flex align-items-center mt-2">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="show_in_home" type="checkbox" value="1"
                                        id="show_in_home_switch">
                                    <span class="form-check-label text-dark"
                                        for="show_in_home_switch">{{ __('Show in Home') }}</span>
                                </label>
                            </div>

                            <div class="col-3 d-flex align-items-center mt-2">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="featured" type="checkbox" value="1"
                                        id="featured_switch">
                                    <span class="form-check-label text-dark"
                                        for="featured_switch">{{ __('Featured') }}</span>
                                </label>
                            </div>
                            <div class="col-3 d-flex align-items-center mt-2">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="certificate_available" type="checkbox"
                                        value="1" id="certificate_switch">
                                    <span class="form-check-label text-dark"
                                        for="certificate_switch">{{ __('Certificate Available?') }}</span>
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
@endsection
@push('scripts')
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/addon.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>

    <script src="{{ asset('assets/dashboard/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>

    <script>
        $(document).ready(() => {

            initTinyMc();

            new Tagify(document.getElementById('tags_inp'), {
                originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(',')
            });

        });
    </script>

    <script>
        function toggleDiscountFields() {
            const isFree = $('#is_free_switch').is(':checked');
            const hasDiscount = $('#have_discount_switch').is(':checked');

            $('#price_inp').prop('disabled', isFree);
            $('#have_discount_switch').prop('disabled', isFree);
            $('#discount_percentage_inp').prop('disabled', isFree || !hasDiscount);
        }

        $(document).ready(function() {
            toggleDiscountFields();

            $('#is_free_switch, #have_discount_switch').on('change', function() {
                toggleDiscountFields();
            });
        });
    </script>
@endpush
