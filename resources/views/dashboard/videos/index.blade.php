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
                <h3 class="fw-bold m-0">{{ __('Videos list') }}</h3>
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
                        <!--end::Svg Icon-->{{ __('Add new video') }}
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
                        class="form-control form-control-solid w-250px ps-15" placeholder="{{ __('Courses') }}">
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
                        <th>{{ __('Course') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created at') }}</th>
                        <th>{{ __('Is Preview') }}</th>
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

    <form id="crud_form" class="ajax-form w-75" action="{{ route('dashboard.videos.store') }}" method="post"
        enctype="multipart/form-data" data-success-callback="onAjaxSuccess" data-error-callback="onAjaxError">
        @csrf

        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Add New video') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-12 d-flex flex-column justify-content-center">
                                <label for="image_inp"
                                    class="form-label  text-center fs-6 fw-bold mb-3">{{ __('Thumbnail Image') }}</label>
                                <x-dashboard.upload-image-inp name="image" :image="null" :directory="'courses'"
                                    placeholder="default.svg" type="editable" />
                            </div>
                        </div>
                        {{-- Course & Section --}}
                        <div class="row mb-4">
                            <div class="col-4">
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
            
                            <div class="col-4">
                                <label for="duration_seconds_inp"
                                    class="form-label">{{ __('Duration (Seconds)') }}</label>
                                <input type="number" name="duration_seconds" id="duration_seconds_inp"
                                    class="form-control" min="0">
                                <div class="fv-plugins-message-container invalid-feedback" id="duration_seconds"></div>
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

                        {{-- Video Info --}}
                        <div class="row mb-4">
                            <div class="col-4">
                                <label for="video_url_inp" class="form-label">{{ __('Video URL') }}</label>
                                <input type="url" name="video_url" id="video_url_inp" class="form-control"
                                    placeholder="{{ __('Enter video URL') }}">
                                <div class="fv-plugins-message-container invalid-feedback" id="video_url"></div>
                            </div>

                            <div class="col-3" id="quiz_select_wrapper">
                                <div class="d-flex justify-content-between">
                                    <label for="quiz_id_inp" class="form-label">{{ __('Quiz') }}</label>
                                    <label class="form-check form-switch form-check-custom form-check-solid  ">
                                        <input class="form-check-input " name="quiz_required" type="checkbox"
                                            value="1" id="quiz_required_switch">
                                        <span class="form-check-label text-dark"
                                            for="quiz_required_switch">{{ __('required') }}</span>
                                    </label>
                                </div>

                                <select name="quiz_id" id="quiz_id_inp" class="form-select" data-control="select2"
                                    data-placeholder="{{ __('Select Quiz') }}"
                                    data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                    <option value="" selected></option>
                                    @foreach ($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}">{{ $quiz->title_en }}</option>
                                    @endforeach
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback" id="quiz_id"></div>
                            </div>

                            <div class="col-2 d-flex align-items-center mt-4">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" name="is_preview" type="checkbox" value="1"
                                        id="is_preview_switch">
                                    <span class="form-check-label text-dark"
                                        for="is_preview_switch">{{ __('Free Preview?') }}</span>
                                </label>
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
    <!-- Hidden div for YouTube Player -->
    <div id="player-container" style="display: none;">
        <div id="yt-player"></div>
    </div>
    {{-- begin::Add Country Modal --}}
@endsection
@push('scripts')
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/videos.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>

    <script src="{{ asset('assets/dashboard/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#add_btn").click(function(e) {
                e.preventDefault();

                // Remove method override inputs (_method) used for PUT/PATCH on edit
                $("[title='_method']").remove();

                // Reset the form fields
                $("#crud_form")[0].reset();


                // Reset image previews to placeholder (assuming your image wrapper has this class)
                $('.image-input-wrapper').css('background-image', "url('/placeholder_images/default.svg')");

                // Clear validation errors and invalid classes
                $("#crud_form").find('.invalid-feedback').text('');
                $("#crud_form").find('.is-invalid').removeClass('is-invalid');

                // Reset TinyMCE editors content if present
                if (typeof tinymce !== 'undefined') {
                    tinymce.editors.forEach(editor => editor.setContent(''));
                }

                // Reset checkboxes by title attribute if they have it (otherwise use IDs)
                $("#is_active_switch", "#is_preview_switch", "#quiz_required_switch")
                    .prop('checked', false);

                $("#crud_form").attr('action', "{{ route('dashboard.videos.store') }}");

                // Reset modal title
                $("#form_title").text("{{ __('Add new video') }}");

                // Optionally, reset date inputs
                $("#start_date_inp, #end_date_inp").val('');

                // Open modal if you want to show it on "Add"
                $("#crud_modal").modal('show');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quizRequiredSwitch = document.getElementById('quiz_required_switch');
            const quizSelect = document.getElementById('quiz_id_inp');

            function toggleSelects() {
                if (quizRequiredSwitch.checked) {
                    quizSelect.disabled = false;
                } else {
                    quizSelect.disabled = true;
                    quizSelect.value = ''; // clear value when disabled
                }
            }

            // Initial toggle on page load
            toggleSelects();

            // Listen for checkbox change
            quizRequiredSwitch.addEventListener('change', toggleSelects);
        });
    </script>


    <script>
        let ytPlayer;

        function extractYouTubeVideoId(url) {
            const regex =
                /(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
            const match = url.match(regex);
            return match ? match[1] : null;
        }

        // Load the YouTube IFrame API script
        let tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        document.head.appendChild(tag);

        // YouTube API will call this function when ready
        function onYouTubeIframeAPIReady() {
            // Nothing yet – we create player when needed
        }

        document.getElementById('video_url_inp').addEventListener('change', function() {
            const url = this.value;
            const videoId = extractYouTubeVideoId(url);

            if (!videoId) return;

            // If a player already exists, destroy it first
            if (ytPlayer && ytPlayer.destroy) {
                ytPlayer.destroy();
            }

            // Create a hidden YouTube player
            ytPlayer = new YT.Player('yt-player', {
                height: '0',
                width: '0',
                videoId: videoId,
                events: {
                    'onReady': function(event) {
                        const duration = ytPlayer.getDuration();
                        document.getElementById('duration_seconds_inp').value = Math.round(duration);
                    }
                }
            });
        });
    </script>
@endpush
@push("styles")
<style>
  input#quiz_required_switch.form-check-input {
    width: 16px;
    height: 16px;
    margin-top: 0.2rem;
  }
</style>

@endpush
