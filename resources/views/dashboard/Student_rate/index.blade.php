@extends('dashboard.partials.master')
@push('styles')
    <link href="{{ asset('assets/dashboard/css/datatables' . (isDarkMode() ? '.dark' : '') . '.bundle.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ asset('assets/dashboard/plugins/custom/datatables/datatables.bundle' . (isArabic() ? '.rtl' : '') . '.css') }}"
        rel="stylesheet" type="text/css" />
@endpush
@section('content')
    <div class="row g-6 g-xl-9 mb-10 ">
        <!--begin::Col-->
        <div class="col-md-6 col-xl-6 ">
            <!--begin::Card-->
            <div class="card border-hover-primary h-100">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-9">
                    <!--begin::Card Title-->
                    <div class="card-title m-0">
                        <!--begin::Avatar-->
                        <div class=" w-35px h-35px bg-light m-auto d-flex justify-content-center align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="27" height="27" viewBox="0 0 18 18"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M11.0073 11.6724C7.93315 11.6724 5.30565 12.1382 5.30565 13.999C5.30565 15.8599 7.91565 16.339 11.0073 16.339C14.0823 16.339 16.709 15.8774 16.709 14.0157C16.709 12.154 14.0998 11.6724 11.0073 11.6724Z"
                                    stroke="#1C1D22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M11.0071 9.01652C13.0254 9.01652 14.6621 7.38069 14.6621 5.36236C14.6621 3.34402 13.0254 1.70819 11.0071 1.70819C8.98961 1.70819 7.35294 3.34402 7.35294 5.36236C7.34544 7.37319 8.97044 9.00902 10.9813 9.01652H11.0071Z"
                                    stroke="#1C1D22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M5.26367 8.06802C3.92951 7.88052 2.90201 6.73552 2.89951 5.34969C2.89951 3.98385 3.89534 2.85052 5.20117 2.63635"
                                    stroke="#1C1D22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M3.50391 11.2769C2.21141 11.4694 1.30891 11.9227 1.30891 12.856C1.30891 13.4985 1.73391 13.9152 2.42057 14.176"
                                    stroke="#1C1D22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h4 class="fw-bold me-auto px-4 py-3">{{ __('Customers Rates') }}</h4>

                        <!--end::Avatar-->
                    </div>
                    <!--end::Car Title-->
                    <!--begin::Card toolbar-->
                    {{-- <div class="card-toolbar">
                        <span class="badge badge-light-primary fw-bold me-auto px-4 py-3">In Progress</span>
                    </div> --}}
                    <!--end::Card toolbar-->
                </div>
                <!--end:: Card header-->
                <!--begin:: Card body-->
                <div class="card-body p-9">
                    <!--begin::Name-->

                    <div class="d-flex  justify-content-between flex-wrap ">
                        <!--begin::Due-->
                        <div class=" rounded min-w-125px py-1 px-4 me-7">
                            <div class="fs-2 fw-bold">{{ __('Customers Rates count') }}</div>
                            <div class="fs-4  ">{{ $count_Student_rate }}</div>
                        </div>
                        <!--end::Due-->


                    </div>
                    <!--end::Info-->



                </div>
                <!--end:: Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <!--begin::Col-->
        <div class="col-md-6 col-xl-6">
            <!--begin::Card-->
            <div class="card border-hover-primary h-100">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-9">
                    <!--begin::Card Title-->
                    <div class="card-title m-0">
                        <!--begin::Avatar-->
                        <div class=" w-35px h-35px bg-light m-auto d-flex justify-content-center align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="27" height="27" viewBox="0 0 18 18"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M11.0073 11.6724C7.93315 11.6724 5.30565 12.1382 5.30565 13.999C5.30565 15.8599 7.91565 16.339 11.0073 16.339C14.0823 16.339 16.709 15.8774 16.709 14.0157C16.709 12.154 14.0998 11.6724 11.0073 11.6724Z"
                                    stroke="#1C1D22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M11.0071 9.01652C13.0254 9.01652 14.6621 7.38069 14.6621 5.36236C14.6621 3.34402 13.0254 1.70819 11.0071 1.70819C8.98961 1.70819 7.35294 3.34402 7.35294 5.36236C7.34544 7.37319 8.97044 9.00902 10.9813 9.01652H11.0071Z"
                                    stroke="#1C1D22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M5.26367 8.06802C3.92951 7.88052 2.90201 6.73552 2.89951 5.34969C2.89951 3.98385 3.89534 2.85052 5.20117 2.63635"
                                    stroke="#1C1D22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M3.50391 11.2769C2.21141 11.4694 1.30891 11.9227 1.30891 12.856C1.30891 13.4985 1.73391 13.9152 2.42057 14.176"
                                    stroke="#1C1D22" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h4 class="fw-bold me-auto px-4 py-3">{{ __('Customers Rates') }}</h4>

                        <!--end::Avatar-->
                    </div>
                    <!--end::Car Title-->
                    <!--begin::Card toolbar-->
                    {{-- <div class="card-toolbar">
                    <span class="badge badge-light-primary fw-bold me-auto px-4 py-3">In Progress</span>
                </div> --}}
                    <!--end::Card toolbar-->
                </div>
                <!--end:: Card header-->
                <!--begin:: Card body-->
                <div class="card-body p-9">
                    <!--begin::Name-->

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
                                        <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                                            rx="1" transform="rotate(-90 11.364 20.364)" fill="currentColor">
                                        </rect>
                                        <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                            fill="currentColor"></rect>
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->{{ __('add rate') }}
                            </button>
                            <!--end::Add customer-->
                        </div>
                        <!--end::Toolbar-->
                    </div>
                    <!--end::Info-->



                </div>
                <!--end:: Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Col-->

    </div>
    <!--begin::Basic info-->
    <div class="card mb-5 mb-x-10">
        <!--begin::Card header-->
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
            data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ __('Customers Rates List') }}</h3>
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
                        class="form-control form-control-solid w-250px ps-15" placeholder="{{ __('Search for rate') }}">
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
                        <th>{{ __('Rate') }}</th>
                        <th>{{ __('Statue') }}</th>
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
    {{-- begin::Add Rate Modal --}}
    <form id="crud_form" class="ajax-form" action="{{ route('dashboard.customers_rate.store') }}" method="POST"
        enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf
        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add new rate') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="row justify-content-center align-items-center">
                            {{-- Image Upload --}}
                            <div class="col-6 d-flex flex-column ">
                                <label for="image_inp" class="form-label required text-center fs-6 fw-bold mb-3">
                                    {{ __('Image') }}
                                </label>
                                <x-dashboard.upload-image-inp name="image" :image="null" :directory="null"
                                    placeholder="default.svg" type="editable" />
                            </div>

                        </div>



                        {{-- Full Name --}}
                        <div class="fv-row mb-5">
                            <label class="required form-label">{{ __('Full Name') }}</label>
                            <input type="text" name="full_name" id="full_name"
                                class="form-control form-control-solid" placeholder="{{ __('Enter full name') }}">
                            <div class="fv-plugins-message-container invalid-feedback" id="full_name"></div>
                        </div>

                        {{-- Rate --}}
                        <div class="fv-row mb-5">
                            <label class="required form-label">{{ __('Choose a rate') }}</label>
                            <select class="form-select form-select-solid" name="rate" id="rate_inp"
                                data-control="select2" data-placeholder="{{ __('Choose a rate') }}"
                                data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                <option value="" selected>{{ __('Choose a rate') }}</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}"> {{ $i }} </option>
                                @endfor
                            </select>
                            <div class="fv-plugins-message-container invalid-feedback" id="rate"></div>
                        </div>
                        {{-- Text Feedback --}}

                        {{-- Category --}}
                        <div class="fv-row mb-5">
                            <label class="required form-label">{{ __('Category') }}</label>
                            <select class="form-select form-select-solid" name="category_id" id="category_id"
                                data-control="select2" data-placeholder="{{ __('Select category') }}"
                                data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                <option value="" selected disabled>{{ __('Select category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="fv-plugins-message-container invalid-feedback" id="category_id"></div>
                        </div>
                        <div class="fv-row mb-5">
                            <label class="form-label">{{ __('Text') }}</label>
                            <textarea name="text" class="form-control form-control-solid" rows="4"
                                placeholder="{{ __('Write your feedback...') }}"></textarea>
                            <div class="fv-plugins-message-container invalid-feedback" id="text"></div>
                        </div>

                        {{-- Status --}}
                        <div class="fv-row mb-5">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold">{{ __('Status') }}</label>
                                </div>
                                <input type="hidden" name="status" value="reject">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="status" type="checkbox" value="approve"
                                        id="status_inp">
                                </label>
                            </div>
                            <div class="fv-plugins-message-container invalid-feedback" id="status"></div>
                        </div>


                        {{-- Audio Upload --}}
                        <div class="fv-row mb-5">
                            <label class="form-label">{{ __('Audio') }}</label>

                            {{-- Audio player preview if audio exists --}}
                            <div id="audio_preview" class="mb-3" style="display: none;">
                                <audio controls style="width: 100%;">
                                    <source id="audio_source" src="" type="audio/mpeg">
                                    {{ __('Your browser does not support the audio element.') }}
                                </audio>
                            </div>

                            {{-- Audio file input --}}
                            <input type="file" name="audio" class="form-control form-control-solid"
                                accept="audio/*">
                            <div class="fv-plugins-message-container invalid-feedback" id="audio"></div>
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('Save') }}</span>
                            <span class="indicator-progress">
                                {{ __('Please wait...') }}
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
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
    <script src="{{ asset('assets/dashboard/js/datatables/rate.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>
    <script src="{{ asset('assets/dashboard/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>

    <script>
        $(document).ready(function() {
            $("#add_btn").click(function(e) {
                e.preventDefault();

                $("#form_title").text(__('Add new rate'));
                $("[name='_method']").remove();
                $("#crud_form").trigger('reset');
                $("#crud_form").attr('action', `/dashboard/customers_rate`);
                $('.image-input-wrapper').css('background-image', `url('/placeholder_images/default.svg')`);
            });


        });
    </script>
@endpush
