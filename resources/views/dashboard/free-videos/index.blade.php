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
                <h3 class="fw-bold m-0">{{ __('Free videos list') }}</h3>
            </div>
            <!--end::Card title-->

            <div class="d-flex justify-content-center flex-wrap mb-5 mt-5">

                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end w-100" id="add_btn" data-bs-toggle="modal"
                    data-bs-target="#crud_modal" data-kt-docs-table-toolbar="base">
                    <!--begin::Add customer-->
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="tooltip"
                        data-bs-original-title="{{ __('Add') }}" data-kt-initialized="1">
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
                        <!--end::Svg Icon-->{{ __('Add new free video') }}
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
                        class="form-control form-control-solid w-250px ps-15" placeholder="{{ __('Search free videos') }}">
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
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Image') }}</th>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Views') }}</th>
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

    <form id="crud_form" class="ajax-form w-75" action="{{ route('dashboard.free-videos.store') }}" method="post"
        enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf

        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add new free video') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-12 d-flex flex-column justify-content-center">
                                <label for="image_inp"
                                    class="form-label text-center fs-6 fw-bold mb-3">{{ __('Thumbnail Image') }}</label>
                                <x-dashboard.upload-image-inp name="image" :image="null" :directory="'FreeVideos'"
                                    placeholder="default.svg" type="editable" />
                                <div class="form-text text-center">{{ __('Optional: the YouTube thumbnail is used when there is no image') }}</div>
                            </div>
                        </div>

                        {{-- Link & grade --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="video_url_inp" class="form-label required">{{ __('YouTube video link') }}</label>
                                <input type="url" name="video_url" id="video_url_inp" class="form-control"
                                    placeholder="https://www.youtube.com/watch?v=..." dir="ltr">
                                <div class="invalid-feedback" id="video_url"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="category_id_inp" class="form-label">{{ __('Grade') }}</label>
                                <select name="category_id" id="category_id_inp" class="form-select">
                                    <option value="">{{ __('All grades') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="category_id"></div>
                            </div>
                        </div>

                        {{-- Duration, order & flags --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="duration_minutes_inp" class="form-label">{{ __('Duration (minutes)') }}</label>
                                <input type="number" name="duration_minutes" id="duration_minutes_inp" class="form-control" min="0">
                                <div class="invalid-feedback" id="duration_minutes"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="sort_order_inp" class="form-label">{{ __('Sort order') }}</label>
                                <input type="number" name="sort_order" id="sort_order_inp" class="form-control" min="0" value="0">
                                <div class="form-text">{{ __('Smaller numbers show first') }}</div>
                                <div class="invalid-feedback" id="sort_order"></div>
                            </div>
                            <div class="col-md-3 d-flex align-items-center mt-4">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="is_active" type="checkbox" value="1"
                                        id="is_active_switch" checked>
                                    <span class="form-check-label text-dark">{{ __('Active') }}</span>
                                </label>
                            </div>
                            <div class="col-md-3 d-flex align-items-center mt-4">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="is_featured" type="checkbox" value="1"
                                        id="is_featured_switch">
                                    <span class="form-check-label text-dark">{{ __('Show on home page') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Titles --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="title_ar_inp" class="form-label required">{{ __('Title (Arabic)') }}</label>
                                <input type="text" name="title_ar" id="title_ar_inp" class="form-control"
                                    placeholder="{{ __('Enter Arabic title') }}">
                                <div class="invalid-feedback" id="title_ar"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="title_en_inp" class="form-label required">{{ __('Title (English)') }}</label>
                                <input type="text" name="title_en" id="title_en_inp" class="form-control"
                                    placeholder="{{ __('Enter English title') }}">
                                <div class="invalid-feedback" id="title_en"></div>
                            </div>
                        </div>

                        {{-- Descriptions --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="description_ar_inp" class="form-label">{{ __('Description (Arabic)') }}</label>
                                <textarea name="description_ar" id="description_ar_inp" rows="5" class="form-control"></textarea>
                                <div class="invalid-feedback" id="description_ar"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="description_en_inp" class="form-label">{{ __('Description (English)') }}</label>
                                <textarea name="description_en" id="description_en_inp" rows="5" class="form-control"></textarea>
                                <div class="invalid-feedback" id="description_en"></div>
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
    <script src="{{ asset('assets/dashboard/js/datatables/free_videos.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#add_btn").click(function(e) {
                e.preventDefault();

                $("#crud_form").find('input[name="_method"]').remove();
                $("#crud_form")[0].reset();
                $('.image-input-wrapper').css('background-image', "url('/placeholder_images/default.svg')");
                $("#crud_form").find('.invalid-feedback').text('');
                $("#crud_form").find('.is-invalid').removeClass('is-invalid');

                $("#is_active_switch").prop('checked', true);
                $("#is_featured_switch").prop('checked', false);
                $("#sort_order_inp").val(0);

                $("#crud_form").attr('action', "{{ route('dashboard.free-videos.store') }}");
                $("#form_title").text("{{ __('Add new free video') }}");
                $("#crud_modal").modal('show');
            });
        });
    </script>
@endpush
