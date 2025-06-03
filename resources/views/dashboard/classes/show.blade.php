@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="d-flex flex-column gap-5 gap-lg-10">
                <!--begin::Course Card-->
                <div class="card card-flush">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            <h2>{{ __('Course Details') }}</h2>
                        </div>
                        <div class="d-flex gap-2">



                            @can('view_videos')
                                <a href="{{ route('dashboard.videos.index') }}"
                                    class="btn btn-primary d-flex align-items-center">
                                    <i class="ki-outline ki-plus fs-2 me-2"></i> {{ __('Add New video') }}
                                </a>
                            @endcan

                            @can('view_quizzes')
                                {{-- <a href="{{ route('dashboard.quizzes.index') }}"
                                    class="btn btn-primary d-flex align-items-center">
                                    <i class="ki-outline ki-plus fs-2 me-2"></i> {{ __('Add New quiz') }}
                                </a> --}}
                                <!--begin::Toolbar-->
                                <div class="" id="add_btn" data-bs-toggle="modal" data-bs-target="#crud_modal"
                                    data-kt-docs-table-toolbar="base">
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
                                        <!--end::Svg Icon-->{{ __('Add new quiz') }}
                                    </button>
                                    <!--end::Add customer-->
                                </div>
                                <!--end::Toolbar-->
                            @endcan

                            @can('view_homework')
                                <a href="{{ route('dashboard.homeworks.index') }}"
                                    class="btn btn-primary d-flex align-items-center">
                                    <i class="ki-outline ki-plus fs-2 me-2"></i> {{ __('Add New homework') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>

                <div class="card card-flush">

                    <div class="card-body">
                        <!--begin::Row-->
                        <div class="row">
                            <!--begin::Image-->
                            <div class="col-md-2 text-start">
                                <img src="{{ $class->full_image_path }}" alt="Course Image"
                                    class="img-fluid rounded w-150px h-150px object-fit-cover" />
                            </div>
                            <!--end::Image-->

                            <!--begin::Details-->
                            <div class="col-md-10">
                                <div class="row gap-5 align-items-start justify-content-center">
                                    <!-- Left Column -->
                                    <div class="col-md-5">
                                        <table class="table table-row-bordered align-middle">
                                            <tbody class="fw-semibold text-gray-600">
                                                <tr>
                                                    <td class="text-muted">{{ __('Title') }}</td>
                                                    <td class="text-end text-dark">{{ $class->title }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Active') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $class->is_active ? __('Yes') : __('No') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Free Preview?') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $class->is_preview ? __('Yes') : __('No') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Created At') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $class->created_at->format('Y-m-d') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Description') }}</td>
                                                    <td class="text-end text-dark">{!! $class->description !!}</td>
                                                </tr>


                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Right Column -->
                                    <div class="col-md-5">

                                    </div>
                                </div>

                            </div>
                            <!--end::Details-->
                        </div>
                        <!--end::Row-->
                    </div>
                </div>
                <!--end::Course Card-->
                <form id="crud_form" class="ajax-form w-75" action="{{ route('dashboard.quizzes.store') }}" method="post"
                    enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
                    @csrf
                    <div class="modal fade" tabindex="-1" id="crud_modal">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="form_title">{{ __('Add New quiz') }}</h5>
                                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                                        aria-label="Close">
                                        <i class="ki-outline ki-cross fs-1"></i>
                                    </div>
                                </div>

                                <div class="modal-body">

                                    {{-- Course & Section --}}
                                    <div class="row mb-4">
                                        <input type="hidden" name="class_id" value="{{ $class->id }}">
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
                                            <label for="title_en_inp"
                                                class="form-label">{{ __('Title (English)') }}</label>
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
                                            <div class="fv-plugins-message-container invalid-feedback"
                                                id="description_ar"></div>
                                        </div>
                                        <div class="col-6">
                                            <label for="description_en_inp"
                                                class="form-label">{{ __('Description (English)') }}</label>
                                            <textarea name="description_en" id="description_en_inp" data-kt-autosize="true" class="tinymce"></textarea>
                                            <div class="fv-plugins-message-container invalid-feedback"
                                                id="description_en"></div>
                                        </div>
                                    </div>

                                    {{-- quiz Info --}}
                                    <div class="row mb-4">
                                        <div class="col-3">
                                            <label for="duration_minutes_inp"
                                                class="form-label">{{ __('Duration (Minutes)') }}</label>
                                            <input type="number" name="duration_minutes" id="duration_minutes_inp"
                                                class="form-control" min="0">
                                            <div class="fv-plugins-message-container invalid-feedback"
                                                id="duration_minutes">
                                            </div>
                                        </div>
                                        <div class="col-2 d-flex align-items-center mt-4">
                                            <label class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" name="is_active" type="checkbox"
                                                    value="1" id="is_active_switch" checked>
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


            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>

    <script src="{{ asset('assets/dashboard/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>

    <script>
        $(document).ready(() => {

            initTinyMc();

        });
    </script>
@endpush
