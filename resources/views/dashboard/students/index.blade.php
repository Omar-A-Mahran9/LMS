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
    <div class="card mb-5 mb-x-10">
        <!--begin::Card header-->
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ __('students list') }}</h3>
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
                        placeholder="{{ __('Search for  students') }}">
                </div>
                <!--end::Search-->
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" id="add_btn" data-bs-toggle="modal" data-bs-target="#crud_modal"
                    data-kt-docs-table-toolbar="base">
                    <!--begin::Add customer-->
                    <button type="button" class="btn btn-primary" data-bs-toggle="tooltip"
                        data-bs-original-title="Coming Soon" data-kt-initialized="1">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                        <span class="svg-icon svg-icon-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                    transform="rotate(-90 11.364 20.364)" fill="currentColor"></rect>
                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                    fill="currentColor"></rect>
                            </svg>
                        </span>
                        <!--end::Svg Icon-->{{ __('Add customer') }}</button> -
                    <!--end::Add customer-->
                </div>
                <!--end::Toolbar-->
                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                    {{--  <div class="fw-bold me-5">
                        <span class="me-2" data-kt-docs-table-select="selected_count"></span>{{ __('Selected item') }}
                    </div>
                    <button type="button" class="btn btn-danger"
                        data-kt-docs-table-select="blocked_selected">{{ __('Blocked') }}</button>  --}}
                </div>
                <!--end::Group actions-->
            </div>
            <!--end::Wrapper-->

            <!--begin::Datatable-->
            <table id="kt_datatable" class="table align-middle text-center table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class=" text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        {{--  <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="" data-kt-check="true"
                                    data-kt-check-target="#kt_datatable .form-check-input" value="1" />
                            </div>
                        </th>  --}}
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Image') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Status') }}</th>
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
    <!--end::Basic info-->

    {{-- begin::Add Country Modal --}}
    <form id="crud_form" class="ajax-form" action="{{ route('dashboard.students.store') }}" method="post"
        data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf
        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add new customer') }}</h5>
                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <span class="svg-icon svg-icon-2x"></span>
                        </div>
                        <!--end::Close-->
                    </div>

                    <div class="modal-body">
                        <div class="d-flex flex-column justify-content-center">
                            <label for="image_inp"
                                class="form-label required text-center fs-6 fw-bold mb-3">{{ __('Image') }}</label>
                            <x-dashboard.upload-image-inp name="image" :image="null" :directory="null"
                                placeholder="default.svg" type="editable"></x-dashboard.upload-image-inp>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-4 fv-row fv-plugins-icon-container">
                                <label for="first_name_inp"
                                    class="form-label required fs-6 fw-bold mb-3">{{ __('First name') }}</label>
                                <input type="text" name="first_name"
                                    class="form-control form-control-lg form-control-solid" id="first_name_inp"
                                    placeholder="{{ __('Student first name') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="first_name"></div>
                            </div>

                            <div class="col-md-4 fv-row fv-plugins-icon-container">
                                <label for="middle_name_inp"
                                    class="form-label fs-6 fw-bold mb-3">{{ __('Middle name') }}</label>
                                <input type="text" name="middle_name"
                                    class="form-control form-control-lg form-control-solid" id="middle_name_inp"
                                    placeholder="{{ __('Student middle name') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="middle_name"></div>
                            </div>

                            <div class="col-md-4 fv-row fv-plugins-icon-container">
                                <label for="last_name_inp"
                                    class="form-label required fs-6 fw-bold mb-3">{{ __('Last name') }}</label>
                                <input type="text" name="last_name"
                                    class="form-control form-control-lg form-control-solid" id="last_name_inp"
                                    placeholder="{{ __('Student last name') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="last_name"></div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <!-- Gender -->
                            <div class="col-md-4 fv-row fv-plugins-icon-container">
                                <label for="gender_inp"
                                    class="form-label required fs-6 fw-bold mb-3">{{ __('Gender') }}</label>
                                <select name="gender" id="gender_inp"
                                    class="form-select form-select-lg form-select-solid" data-control="select2"
                                    data-placeholder="{{ __('Select gender') }}"
                                    data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                    <option value="" selected></option>

                                    <option value="male">{{ __('Male') }}</option>
                                    <option value="female">{{ __('Female') }}</option>
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback" id="gender"></div>
                            </div>

                            <!-- Government -->
                            <div class="col-md-4 fv-row fv-plugins-icon-container">
                                <label for="government_id_inp"
                                    class="form-label required fs-6 fw-bold mb-3">{{ __('Government') }}</label>
                                <select name="government_id" id="government_id_inp"
                                    class="form-select form-select-lg form-select-solid" data-control="select2"
                                    data-placeholder="{{ __('Select government') }}"
                                    data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                    <option value="" selected></option>

                                    @foreach ($governments as $government)
                                        <option value="{{ $government->id }}">{{ $government->name }}</option>
                                    @endforeach
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback" id="government_id"></div>
                            </div>

                            <!-- Category -->
                            <div class="col-md-4 fv-row fv-plugins-icon-container">
                                <label for="category_id_inp"
                                    class="form-label required fs-6 fw-bold mb-3">{{ __('Category') }}</label>
                                <select name="category_id" id="category_id_inp"
                                    class="form-select form-select-lg form-select-solid" data-control="select2"
                                    data-placeholder="{{ __('Select category') }}"
                                    data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                    <option value="" selected></option>

                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback" id="category_id"></div>
                            </div>
                        </div>


                        <div class="row mb-5">
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label for="email_inp"
                                    class="form-label required fs-6 fw-bold mb-3">{{ __('Email') }}</label>
                                <input type="text" name="email" autocomplete="new-password"
                                    class="form-control form-control-lg form-control-solid" id="email_inp"
                                    placeholder="{{ __('Customer email') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="email"></div>
                            </div>

                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label for="phone_inp"
                                    class="form-label required fs-6 fw-bold mb-3">{{ __('Phone') }}</label>
                                <input type="number" name="phone"
                                    class="form-control form-control-lg form-control-solid no-arrow" id="phone_inp"
                                    placeholder="0xxxxxxxx">
                                <div class="fv-plugins-message-container invalid-feedback" id="phone"></div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <!-- Parent Phone -->
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label for="parent_phone_inp"
                                    class="form-label fs-6 fw-bold mb-3">{{ __('Parent phone') }}</label>
                                <input type="text" name="parent_phone"
                                    class="form-control form-control-lg form-control-solid" id="parent_phone_inp"
                                    placeholder="01xxxxxxxxx">
                                <div class="fv-plugins-message-container invalid-feedback" id="parent_phone"></div>
                            </div>

                            <!-- Parent Job -->
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label for="parent_job_inp"
                                    class="form-label fs-6 fw-bold mb-3">{{ __('Parent job') }}</label>
                                <input type="text" name="parent_job"
                                    class="form-control form-control-lg form-control-solid" id="parent_job_inp"
                                    placeholder="{{ __('e.g., مهندس') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="parent_job"></div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <!-- Password -->
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label for="password_inp"
                                    class="form-label fs-6 fw-bold mb-3">{{ __('Password') }}</label>
                                <div class="position-relative mb-3" data-kt-password-meter="true">
                                    <input class="form-control form-control-lg form-control-solid" type="password"
                                        name="password" autocomplete="new-password" id="password_inp"
                                        placeholder="{{ __('Password') }}" />
                                    <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                        data-kt-password-meter-control="visibility">
                                        <i class="bi bi-eye-slash fs-2"></i>
                                        <i class="bi bi-eye fs-2 d-none"></i>
                                    </span>
                                </div>
                                <div class="fv-plugins-message-container invalid-feedback" id="password"></div>
                            </div>

                            <!-- Password Confirmation -->
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label for="password_confirmation_inp"
                                    class="form-label fs-6 fw-bold mb-3">{{ __('Password confirmation') }}</label>
                                <div class="position-relative mb-3" data-kt-password-meter="true">
                                    <input class="form-control form-control-lg form-control-solid" type="password"
                                        name="password_confirmation" autocomplete="off" id="password_confirmation_inp"
                                        placeholder="{{ __('Password') }}" />
                                    <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                        data-kt-password-meter-control="visibility">
                                        <i class="bi bi-eye-slash fs-2"></i>
                                        <i class="bi bi-eye fs-2 d-none"></i>
                                    </span>
                                </div>
                                <div class="fv-plugins-message-container invalid-feedback" id="password_confirmation">
                                </div>
                            </div>
                        </div>



                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">
                                {{ __('Save') }}
                            </span>
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
    {{-- end::Add Country Modal --}}
    <div class="row attachments">
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/students.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>
    <script src="{{ asset('assets/dashboard/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#add_btn").click(function(e) {
                e.preventDefault();

                $("#form_title").text(__('Add new customer'));
                $("[name='_method']").remove();
                $("#crud_form").trigger('reset');
                $("#crud_form").attr('action', `/dashboard/students`);
                $("[for*='password']").addClass('required');
                $('.image-input-wrapper').css('background-image', `url('/placeholder_images/default.svg')`);
            });


        });
    </script>
@endpush
