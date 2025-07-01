@extends('dashboard.partials.master')
@section('content')
    @include('dashboard.partials.settings-nav')

    <!--begin::Form-->
    <form class="form d-flex flex-column flex-lg-row ajax-form" action="{{ route('dashboard.settings.home.general') }}"
        method="post" data-success-callback="onAjaxSuccess" data-hide-alert="true">
        @csrf
        <!--begin::Main column-->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!--begin::Logo settings-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{ __('logos') }}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!-- Logo Image -->
                <div class="mb-5 text-center">
                    <label class="form-label fw-bold">{{ __('الشعار الأساسي (Logo)') }}</label>
                    <div class="image-input image-input-outline" data-kt-image-input="true">
                        <div class="image-input-wrapper w-150px h-150px"
                            style="background-image: url('{{ asset(getImagePathFromDirectory(setting('logo_image'), 'Settings')) }}')">
                        </div>

                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ __('تغيير الصورة') }}">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="logo_image" accept=".png,.jpg,.jpeg,.ico" />
                            <input type="hidden" name="logo_remove" />
                        </label>

                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ __('إلغاء') }}">
                            <i class="bi bi-x fs-2"></i>
                        </span>

                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ __('حذف') }}">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                    </div>
                    <div class="text-muted fs-7 mt-2">
                        {{ __('الامتدادات المسموحة: jpg, jpeg, png, ico') }}
                    </div>
                    <div class="invalid-feedback" id="logo_image"></div>
                </div>

                <!--begin::Card body-->
                <!-- Light Logo -->
                <div class="mb-5 text-center">
                    <label class="form-label fw-bold">{{ __('الشعار الفاتح (Light Logo)') }}</label>
                    <div class="image-input image-input-outline" data-kt-image-input="true">
                        <div class="image-input-wrapper w-150px h-150px"
                            style="background-image: url('{{ asset(getImagePathFromDirectory(setting('light_logo_image'), 'Settings')) }}')">
                        </div>

                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ __('تغيير الصورة') }}">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="light_logo_image" accept=".png,.jpg,.jpeg,.ico" />
                            <input type="hidden" name="light_logo_remove" />
                        </label>

                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ __('إلغاء') }}">
                            <i class="bi bi-x fs-2"></i>
                        </span>

                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ __('حذف') }}">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                    </div>
                    <div class="text-muted fs-7 mt-2">
                        {{ __('الامتدادات المسموحة: jpg, jpeg, png, ico') }}
                    </div>
                    <div class="invalid-feedback" id="light_logo_image"></div>
                </div>

                <!--end::Card body-->
                <!-- Favicon -->
                <div class="mb-5 text-center">
                    <label class="form-label fw-bold">{{ __('أيقونة المتصفح (Favicon)') }}</label>
                    <div class="image-input image-input-outline" data-kt-image-input="true">
                        <div class="image-input-wrapper w-50px h-50px"
                            style="background-image: url('{{ asset(getImagePathFromDirectory(setting('favicon_icon'), 'Settings')) }}')">
                        </div>

                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ __('تغيير الصورة') }}">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="favicon_icon" accept=".ico,.png,.jpg" />
                            <input type="hidden" name="favicon_remove" />
                        </label>

                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ __('إلغاء') }}">
                            <i class="bi bi-x fs-2"></i>
                        </span>

                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ __('حذف') }}">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                    </div>
                    <div class="text-muted fs-7 mt-2">
                        {{ __('الامتدادات المسموحة: ico, png, jpg') }}
                    </div>
                    <div class="invalid-feedback" id="favicon_icon"></div>
                </div>

            </div>
        </div>
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            @include('dashboard.partials.settings-home-nav')
            <!--begin::Tab content-->
            <div class="tab-content">
                <!--begin::Tab pane-->
                <div class="tab-pane fade show active" id="settings_general" role="tab-panel">
                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        <!--begin::Inventory-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>{{ __('General') }}</h2>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <!--begin::Input group-->
                                <div class="mb-10 row">
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Label in arabic') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <input class="form-control" value="{{ setting('label_general_ar') }}"
                                            name="label_general_ar" id="label_general_ar_inp"
                                            placeholder="{{ __('Label in arabic') }}" />
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback" id="label_general_ar">
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Label in english') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <input class="form-control" value="{{ setting('label_general_en') }}"
                                            name="label_general_en" id="label_general_en_inp"
                                            placeholder="{{ __('Label in english') }}" />
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback" id="label_general_en">
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="mb-10 row">
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('title in arabic') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <textarea name="general_ar" id="general_ar_inp" data-kt-autosize="true" placeholder="{{ __('title in arabic') }}"
                                            class="tox-target">
                                            {{ setting('general_ar') }}
                                            </textarea>
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback" id="general_ar"></div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('title in english') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <textarea name="general_en" id="general_en_inp" data-kt-autosize="true" placeholder="{{ __('titlein english') }}"
                                            class="tox-target">
                                            {{ setting('general_en') }}
                                            </textarea>
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback" id="general_en"></div>
                                        <!--end::Description-->
                                    </div>
                                </div>
                                <!--end::Input group-->
                            </div>
                            <!--end::Card header-->
                        </div>
                        <!--end::Inventory-->
                    </div>
                </div>
                <!--end::Tab pane-->
            </div>
            <!--end::Tab content-->
            <div class="d-flex justify-content-end">
                <!--begin::Button-->
                <button type="submit" id="submit" class="btn btn-primary">
                    <span class="indicator-label"> {{ __('Save') }}</span>
                    <span class="indicator-progress"> {{ __('Please wait...') }}
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Main column-->
    </form>
    <!--end::Form-->
@endsection

@push('scripts')
    <script src="{{ asset('assets/dashboard/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        window['onAjaxSuccess'] = () => {
            soundStatus = $("[name='sound_status']:checked").val();
            showToast();
        }
    </script>
    <script>
        let language = locale == 'en' ? 'ltr' : 'rtl';
        tinymce.init({
            selector: "#general_ar_inp",
            height: "480",
            menubar: false,
            toolbar: ["styleselect",
                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                "bullist numlist | outdent indent | ltr rtl | blockquote subscript superscript | advlist | autolink | lists charmap | print preview |  code"
            ],
            directionality: language, // Set the initial direction to RTL if needed
            plugins: "advlist autolink link image lists charmap print preview code directionality"
        });
        tinymce.init({
            selector: "#general_en_inp",
            height: "480",
            menubar: false,
            toolbar: ["styleselect",
                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                "bullist numlist | outdent indent | ltr rtl | blockquote subscript superscript | advlist | autolink | lists charmap | print preview |  code"
            ],
            directionality: language, // Set the initial direction to RTL if needed
            plugins: "advlist autolink link image lists charmap print preview code directionality"
        });
    </script>
@endpush
