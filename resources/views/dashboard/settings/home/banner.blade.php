@extends('dashboard.partials.master')
@section('content')
    @include('dashboard.partials.settings-nav')

    <!--begin::Form-->
    <form class="form d-flex flex-column flex-lg-row ajax-form" action="{{ route('dashboard.settings.home.banner') }}"
        method="post" data-success-callback="onAjaxSuccess" data-hide-alert="true">
        @csrf

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">


            @include('dashboard.partials.settings-home-nav')
            <!--begin::Tab content-->
            <div class="tab-content">
                <!--begin::Tab pane-->
                <div class="tab-pane fade show active" role="tab-panel">
                    <div class="d-flex flex-column gap-7 gap-lg-10 mb-5">
                        <!--begin::Inventory-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>{{ __('About us') }}</h2>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0">

                                <!--begin::Card body-->
                                <div class="card-body text-center pt-0">
                                    <!--begin::Image input-->
                                    <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                                        data-kt-image-input="true">
                                        <!--begin::Preview existing avatar-->
                                        <div class="image-input-wrapper w-150px h-150px"
                                            style="background-image: url({{ asset(getImagePathFromDirectory(setting('common_question_banner'), 'Settings')) }})">
                                        </div>
                                        <!--end::Preview existing avatar-->
                                        <!--begin::Label-->
                                        <label
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                            title="{{ __('تغير الصورة') }}">
                                            <i class="bi bi-pencil-fill fs-7"></i>
                                            <!--begin::Inputs-->
                                            <input type="file" name="common_question_banner"
                                                accept=".png, .jpg, .jpeg" />
                                            <input type="hidden" name="avatar_remove" />
                                            <!--end::Inputs-->
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Cancel-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                            title="{{ __('الغاء') }}">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <!--end::Cancel-->
                                        <!--begin::Remove-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                            title="{{ __('حذف') }}">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <!--end::Remove-->
                                    </div>
                                    <!--end::Image input-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7">
                                        {{ __('صيغة الصورة يجب ان تكون من نوع *.jpg, *.jpeg, *.gif, *.svg') }}
                                    </div>
                                    <!--end::Description-->
                                    <div class="invalid-feedback" id="common_question_banner"></div>
                                </div>
                                <!--end::Card body-->
                                <!--begin::Input group-->
                                <div class="mb-10 row">
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Label in arabic') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <input class="form-control" value="{{ setting('label_common_question_ar') }}"
                                            name="label_common_question_ar" id="label_common_question_ar_inp"
                                            placeholder="{{ __('Label in arabic') }}" />
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="label_common_question_ar">
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Label in english') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <input class="form-control" value="{{ setting('label_common_question_en') }}"
                                            name="label_common_question_en" id="label_common_question_en_inp"
                                            placeholder="{{ __('Label in english') }}" />
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="label_common_question_en">
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="mb-10 row">
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('About us in arabic') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <textarea name="description_common_question_ar" id="description_common_question_ar_inp" data-kt-autosize="true"
                                            placeholder="{{ __('About us in arabic') }}" class="tox-target">
                                            {{ setting('description_common_question_ar') }}
                                            </textarea>
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="description_common_question_ar"></div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('About us in english') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <textarea name="description_common_question_en" id="description_common_question_en_inp" data-kt-autosize="true"
                                            placeholder="{{ __('About us in english') }}" class="tox-target">
                                            {{ setting('description_common_question_en') }}
                                            </textarea>
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="description_common_question_en"></div>
                                        <!--end::Description-->
                                    </div>
                                </div>
                                <!--end::Input group-->
                            </div>
                            <!--end::Card header-->
                        </div>
                        <!--end::Inventory-->
                    </div>
                    <div class="d-flex flex-column gap-7 gap-lg-10 mb-5">
                        <!--begin::Inventory-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>{{ __('How to use data website') }}</h2>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <!--begin::Input group-->
                                <!--begin::Card body-->
                                <div class="card-body text-center pt-0">
                                    <!--begin::Image input-->
                                    <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                                        data-kt-image-input="true">
                                        <!--begin::Preview existing avatar-->
                                        <div class="image-input-wrapper w-150px h-150px"
                                            style="background-image: url({{ asset(getImagePathFromDirectory(setting('how_to_use_banner'), 'Settings')) }})">
                                        </div>
                                        <!--end::Preview existing avatar-->
                                        <!--begin::Label-->
                                        <label
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                            title="{{ __('تغير الصورة') }}">
                                            <i class="bi bi-pencil-fill fs-7"></i>
                                            <!--begin::Inputs-->
                                            <input type="file" name="how_to_use_banner" accept=".png, .jpg, .jpeg" />
                                            <input type="hidden" name="avatar_remove" />
                                            <!--end::Inputs-->
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Cancel-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                            title="{{ __('الغاء') }}">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <!--end::Cancel-->
                                        <!--begin::Remove-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                            title="{{ __('حذف') }}">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <!--end::Remove-->
                                    </div>
                                    <!--end::Image input-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7">
                                        {{ __('صيغة الصورة يجب ان تكون من نوع *.jpg, *.jpeg, *.gif, *.svg') }}
                                    </div>
                                    <!--end::Description-->
                                    <div class="invalid-feedback" id="how_to_use_banner"></div>
                                </div>
                                <!--end::Card body-->
                                <div class="mb-10 row">
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Label in arabic') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <input class="form-control" value="{{ setting('label_how_to_use_ar') }}"
                                            name="label_how_to_use_ar" id="label_how_to_use_ar_inp"
                                            placeholder="{{ __('Label in arabic') }}" />
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="label_how_to_use_ar">
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Label in english') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <input class="form-control" value="{{ setting('label_how_to_use_en') }}"
                                            name="label_how_to_use_en" id="label_how_to_use_en_inp"
                                            placeholder="{{ __('Label in english') }}" />
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="label_how_to_use_en">
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                </div>
                                <div class="row mb-6" id="video_how_to_use_url_group">
                                    <label for="video_how_to_use_url_inp"
                                        class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Video URL') }}</label>
                                    <div class="col-lg-10 fv-row">
                                        <input type="text" name="video_how_to_use_url"
                                            value="{{ setting('video_how_to_use_url') }}" id="video_how_to_use_url_inp"
                                            class="form-control form-control-lg form-control-solid"
                                            placeholder="{{ __('Enter video URL') }}" value="" />
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="video_how_to_use_url"></div>
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="mb-10 row">
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('contact in arabic') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <textarea name="description_how_to_use_ar" id="description_how_to_use_ar_inp" data-kt-autosize="true"
                                            placeholder="{{ __('contact in arabic') }}" class="tox-target">
                                            {{ setting('description_how_to_use_ar') }}
                                            </textarea>
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="description_how_to_use_ar"></div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('contact in english') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <textarea name="description_how_to_use_en" id="description_how_to_use_en_inp" data-kt-autosize="true"
                                            placeholder="{{ __('contact in english') }}" class="tox-target">
                                            {{ setting('description_how_to_use_en') }}
                                            </textarea>
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="description_how_to_use_en"></div>
                                        <!--end::Description-->
                                    </div>
                                </div>
                                <!--end::Input group-->
                            </div>
                            <!--end::Card header-->
                        </div>
                        <!--end::Inventory-->
                    </div>

                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        <!--begin::Inventory-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>{{ __('Courses') }}</h2>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0">


                                <!--begin::Card body-->
                                <div class="card-body text-center pt-0">
                                    <!--begin::Image input-->
                                    <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                                        data-kt-image-input="true">
                                        <!--begin::Preview existing avatar-->
                                        <div class="image-input-wrapper w-150px h-150px"
                                            style="background-image: url({{ asset(getImagePathFromDirectory(setting('contact_banner'), 'Settings')) }})">
                                        </div>
                                        <!--end::Preview existing avatar-->
                                        <!--begin::Label-->
                                        <label
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                            title="{{ __('تغير الصورة') }}">
                                            <i class="bi bi-pencil-fill fs-7"></i>
                                            <!--begin::Inputs-->
                                            <input type="file" name="contact_banner" accept=".png, .jpg, .jpeg" />
                                            <input type="hidden" name="avatar_remove" />
                                            <!--end::Inputs-->
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Cancel-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                            title="{{ __('الغاء') }}">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <!--end::Cancel-->
                                        <!--begin::Remove-->
                                        <span
                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                            title="{{ __('حذف') }}">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <!--end::Remove-->
                                    </div>
                                    <!--end::Image input-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7">
                                        {{ __('صيغة الصورة يجب ان تكون من نوع *.jpg, *.jpeg, *.gif, *.svg') }}
                                    </div>
                                    <!--end::Description-->
                                    <div class="invalid-feedback" id="contact_banner"></div>
                                </div>
                                <!--end::Card body-->
                                <div class="mb-10 row">
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Label in arabic') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <input class="form-control" value="{{ setting('label_contact_ar') }}"
                                            name="label_contact_ar" id="label_contact_ar_inp"
                                            placeholder="{{ __('Label in arabic') }}" />
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback" id="label_contact_ar">
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Label in english') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <input class="form-control" value="{{ setting('label_contact_en') }}"
                                            name="label_contact_en" id="label_contact_en_inp"
                                            placeholder="{{ __('Label in english') }}" />
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback" id="label_contact_en">
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="mb-10 row">
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Service in arabic') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <textarea name="description_contact_ar" id="description_contact_ar_inp" data-kt-autosize="true"
                                            placeholder="{{ __('Service in arabic') }}" class="tox-target">
                                            {{ setting('description_contact_ar') }}
                                            </textarea>
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="description_contact_ar"></div>
                                        <!--end::Description-->
                                    </div>
                                    <div class="col-lg-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('Service in english') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Editor-->
                                        <textarea name="description_contact_en" id="description_contact_en_inp" data-kt-autosize="true"
                                            placeholder="{{ __('Service in english') }}" class="tox-target">
                                            {{ setting('description_contact_en') }}
                                            </textarea>
                                        <!--end::Editor-->
                                        <!--begin::Description-->
                                        <div class="fv-plugins-message-container invalid-feedback"
                                            id="description_contact_en"></div>
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

        const tinymceOptions = {
            height: "480",
            menubar: false,
            toolbar: [
                "styleselect",
                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                "bullist numlist | outdent indent | ltr rtl | blockquote subscript superscript | advlist | autolink | lists charmap | print preview | code"
            ],
            directionality: language,
            plugins: "advlist autolink link image lists charmap print preview code directionality"
        };

        // Initialize editors for all targeted selectors
        ["#description_common_question_ar_inp", "#description_common_question_en_inp", "#description_how_to_use_ar_inp",
            "#description_how_to_use_en_inp", "#description_contact_en_inp", "#description_contact_ar_inp"
        ]
        .forEach(selector => {
            tinymce.init({
                ...tinymceOptions,
                selector
            });
        });
    </script>
@endpush
