@php
    $dateValue = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '';
    $placementHints = [
        'top_bar'      => __('A thin strip above the navbar on every page (text + link, no image)'),
        'popup'        => __('A window that opens once a day when the visitor enters the website'),
        'home_top'     => __('A wide banner right under the hero of the home page'),
        'home_middle'  => __('A wide banner in the middle of the home page'),
        'home_bottom'  => __('A wide banner before the FAQs of the home page'),
        'courses_page' => __('A wide banner at the top of the courses page'),
    ];
@endphp

@csrf

<div class="row g-7">
    {{-- ==================== Main ==================== --}}
    <div class="col-xl-8">

        {{-- Placement --}}
        <div class="card mb-7">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">{{ __('Where should it appear?') }}</h3>
            </div>
            <div class="card-body pt-2">
                <div class="row g-4" data-kt-buttons="true">
                    @foreach ($placements as $key => $label)
                        <div class="col-md-6">
                            <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-5 h-100 {{ $banner->placement === $key ? 'active' : '' }}">
                                <input class="form-check-input me-4 mt-1" type="radio" name="placement" value="{{ $key }}"
                                    {{ $banner->placement === $key ? 'checked' : '' }}>
                                <span>
                                    <span class="fw-bold fs-6 d-block text-gray-800">{{ __($label) }}</span>
                                    <span class="text-muted fs-7">{{ $placementHints[$key] }}</span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="fv-plugins-message-container invalid-feedback" id="placement"></div>
            </div>
        </div>

        {{-- Content --}}
        <div class="card mb-7">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">{{ __('Content') }}</h3>
            </div>
            <div class="card-body pt-2">
                <div class="row g-5" data-banner-images>
                    <div class="col-md-6">
                        <label class="form-label fw-bold required" data-image-label>{{ __('Image (desktop)') }}</label>
                        <x-dashboard.upload-image-inp name="image" :image="$banner->full_image_path" :directory="null"
                            placeholder="default.svg" type="editable"></x-dashboard.upload-image-inp>
                        <div class="text-muted fs-7 text-center">{{ __('Recommended: 1600×500 for banners, 800×800 for the popup') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Image (mobile)') }}</label>
                        <x-dashboard.upload-image-inp name="mobile_image" :image="$banner->full_mobile_image_path" :directory="null"
                            placeholder="default.svg" type="editable"></x-dashboard.upload-image-inp>
                        <div class="text-muted fs-7 text-center">{{ __('Optional, 800×600. The desktop image is used when empty') }}</div>
                        @if ($banner->mobile_image)
                            <label class="form-check form-check-sm form-check-custom form-check-solid justify-content-center mt-3">
                                <input class="form-check-input" type="checkbox" name="remove_mobile_image" value="1">
                                <span class="form-check-label">{{ __('Remove the mobile image') }}</span>
                            </label>
                        @endif
                    </div>
                </div>

                <div class="separator my-7" data-banner-images></div>

                <div class="row g-5">
                    <div class="col-md-6 fv-row">
                        <label class="form-label fw-bold required">{{ __('Title in Arabic') }}</label>
                        <input type="text" name="title_ar" id="title_ar_inp" class="form-control form-control-solid"
                            value="{{ $banner->title_ar }}" data-preview="title">
                        <div class="fv-plugins-message-container invalid-feedback" id="title_ar"></div>
                    </div>
                    <div class="col-md-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Title in English') }}</label>
                        <input type="text" name="title_en" id="title_en_inp" class="form-control form-control-solid"
                            value="{{ $banner->title_en }}">
                        <div class="fv-plugins-message-container invalid-feedback" id="title_en"></div>
                    </div>

                    <div class="col-md-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Subtitle in Arabic') }}</label>
                        <textarea name="subtitle_ar" id="subtitle_ar_inp" class="form-control form-control-solid" rows="2"
                            data-preview="subtitle">{{ $banner->subtitle_ar }}</textarea>
                        <div class="fv-plugins-message-container invalid-feedback" id="subtitle_ar"></div>
                    </div>
                    <div class="col-md-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Subtitle in English') }}</label>
                        <textarea name="subtitle_en" id="subtitle_en_inp" class="form-control form-control-solid" rows="2">{{ $banner->subtitle_en }}</textarea>
                        <div class="fv-plugins-message-container invalid-feedback" id="subtitle_en"></div>
                    </div>

                    <div class="col-md-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Badge in Arabic') }}</label>
                        <input type="text" name="badge_ar" id="badge_ar_inp" class="form-control form-control-solid"
                            value="{{ $banner->badge_ar }}" placeholder="{{ __('e.g. 30% off') }}" data-preview="badge">
                        <div class="fv-plugins-message-container invalid-feedback" id="badge_ar"></div>
                    </div>
                    <div class="col-md-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Badge in English') }}</label>
                        <input type="text" name="badge_en" id="badge_en_inp" class="form-control form-control-solid"
                            value="{{ $banner->badge_en }}">
                        <div class="fv-plugins-message-container invalid-feedback" id="badge_en"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Button & link --}}
        <div class="card mb-7">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">{{ __('Button & link') }}</h3>
            </div>
            <div class="card-body pt-2">
                <div class="row g-5">
                    <div class="col-12 fv-row">
                        <label class="form-label fw-bold">{{ __('Link') }}</label>
                        <input type="text" name="link" id="link_inp" class="form-control form-control-solid" dir="ltr"
                            value="{{ $banner->link }}" placeholder="/courses/12  {{ __('or') }}  https://...">
                        <div class="text-muted fs-7 mt-1">{{ __('Start with / for a page of the website. Leave empty if the banner should not open anything') }}</div>
                        <div class="fv-plugins-message-container invalid-feedback" id="link"></div>
                    </div>
                    <div class="col-md-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Button name in Arabic') }}</label>
                        <input type="text" name="btn_title_ar" id="btn_title_ar_inp" class="form-control form-control-solid"
                            value="{{ $banner->btn_title_ar }}" placeholder="{{ __('e.g. Subscribe now') }}" data-preview="button">
                        <div class="fv-plugins-message-container invalid-feedback" id="btn_title_ar"></div>
                    </div>
                    <div class="col-md-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Button name in English') }}</label>
                        <input type="text" name="btn_title_en" id="btn_title_en_inp" class="form-control form-control-solid"
                            value="{{ $banner->btn_title_en }}">
                        <div class="fv-plugins-message-container invalid-feedback" id="btn_title_en"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="open_in_new_tab" value="1"
                                {{ $banner->open_in_new_tab ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">{{ __('Open the link in a new tab') }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== Side ==================== --}}
    <div class="col-xl-4">

        {{-- Preview --}}
        <div class="card mb-7">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">{{ __('Preview') }}</h3>
            </div>
            <div class="card-body pt-2">
                <div id="banner_preview" class="rounded-3 overflow-hidden position-relative"
                    style="min-height: 150px; background: #1f4e4a center / cover no-repeat; background-image: url('{{ $banner->full_image_path }}');">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(90deg, rgba(0,0,0,.55), rgba(0,0,0,.1));"></div>
                    <div class="position-relative p-5 d-flex flex-column gap-2 text-white" dir="rtl" id="banner_preview_content">
                        <span class="badge badge-warning align-self-start" data-preview-out="badge">{{ $banner->badge_ar }}</span>
                        <span class="fw-bolder fs-4" data-preview-out="title">{{ $banner->title_ar ?: __('Banner title') }}</span>
                        <span class="fs-7 opacity-75" data-preview-out="subtitle">{{ $banner->subtitle_ar }}</span>
                        <span class="btn btn-sm btn-light align-self-start mt-2" data-preview-out="button">{{ $banner->btn_title_ar }}</span>
                    </div>
                </div>
                <div class="text-muted fs-8 mt-2">{{ __('A rough preview, the website styles it per placement') }}</div>
            </div>
        </div>

        {{-- Publishing --}}
        <div class="card mb-7">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">{{ __('Publishing') }}</h3>
            </div>
            <div class="card-body pt-2 d-flex flex-column gap-5">
                <label class="form-check form-switch form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                        {{ $banner->is_active ? 'checked' : '' }}>
                    <span class="form-check-label fw-semibold">{{ __('Active') }}</span>
                </label>

                <div class="fv-row">
                    <label class="form-label fw-bold">{{ __('Starts at') }}</label>
                    <input type="datetime-local" name="starts_at" id="starts_at_inp" class="form-control form-control-solid"
                        value="{{ $dateValue($banner->starts_at) }}">
                    <div class="fv-plugins-message-container invalid-feedback" id="starts_at"></div>
                </div>

                <div class="fv-row">
                    <label class="form-label fw-bold">{{ __('Ends at') }}</label>
                    <input type="datetime-local" name="ends_at" id="ends_at_inp" class="form-control form-control-solid"
                        value="{{ $dateValue($banner->ends_at) }}">
                    <div class="fv-plugins-message-container invalid-feedback" id="ends_at"></div>
                    <div class="text-muted fs-7 mt-1">{{ __('Egypt time. Leave empty to show it until you turn it off') }}</div>
                </div>

                <label class="form-check form-switch form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" name="show_countdown" value="1"
                        {{ $banner->show_countdown ? 'checked' : '' }}>
                    <span class="form-check-label fw-semibold">{{ __('Show a countdown until the end date') }}</span>
                </label>

                <div class="fv-row">
                    <label class="form-label fw-bold">{{ __('Order') }}</label>
                    <input type="number" min="0" name="sort_order" id="sort_order_inp" class="form-control form-control-solid"
                        value="{{ $banner->sort_order ?? 0 }}">
                    <div class="text-muted fs-7 mt-1">{{ __('Smaller shows first when a place has more than one banner') }}</div>
                    <div class="fv-plugins-message-container invalid-feedback" id="sort_order"></div>
                </div>
            </div>
        </div>

        {{-- Audience --}}
        <div class="card mb-7">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">{{ __('Who sees it?') }}</h3>
            </div>
            <div class="card-body pt-2 d-flex flex-column gap-5">
                <div class="fv-row">
                    <select name="audience" id="audience_inp" class="form-select form-select-solid">
                        @foreach ($audiences as $key => $label)
                            <option value="{{ $key }}" {{ $banner->audience === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                    <div class="fv-plugins-message-container invalid-feedback" id="audience"></div>
                </div>

                <div class="fv-row" id="category_group">
                    <label class="form-label fw-bold">{{ __('Grade') }}</label>
                    <select name="category_id" id="category_id_inp" class="form-select form-select-solid">
                        <option value="">{{ __('All grades') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $banner->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="text-muted fs-7 mt-1">{{ __('Picking a grade shows it only to logged in students of that grade') }}</div>
                    <div class="fv-plugins-message-container invalid-feedback" id="category_id"></div>
                </div>
            </div>
        </div>

        {{-- Colors --}}
        <div class="card mb-7">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold">{{ __('Colors') }}</h3>
            </div>
            <div class="card-body pt-2">
                <div class="row g-5">
                    <div class="col-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Background') }}</label>
                        <input type="color" name="bg_color" id="bg_color_inp" class="form-control form-control-solid h-45px p-1"
                            value="{{ $banner->bg_color ?? '#1f4e4a' }}">
                        <div class="fv-plugins-message-container invalid-feedback" id="bg_color"></div>
                    </div>
                    <div class="col-6 fv-row">
                        <label class="form-label fw-bold">{{ __('Text') }}</label>
                        <input type="color" name="text_color" id="text_color_inp" class="form-control form-control-solid h-45px p-1"
                            value="{{ $banner->text_color ?? '#ffffff' }}">
                        <div class="fv-plugins-message-container invalid-feedback" id="text_color"></div>
                    </div>
                </div>
                <div class="text-muted fs-7 mt-2">{{ __('Used by the top bar, and behind banners without an image') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-3 mb-10">
    <a href="{{ route('dashboard.banners.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
    <button type="submit" class="btn btn-primary">
        <span class="indicator-label">{{ __('Save Changes') }}</span>
        <span class="indicator-progress">{{ __('Please wait...') }}
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    </button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.banner-form');
            const preview = document.getElementById('banner_preview');
            let imageUrl = @json($banner->full_image_path);

            const placement = () => form.querySelector('[name=placement]:checked')?.value;
            const isTopBar = () => placement() === 'top_bar';

            // Top bar is text only: hide the image inputs and use the colors in the preview
            const syncPlacement = () => {
                form.querySelectorAll('[data-banner-images]').forEach(el => el.classList.toggle('d-none', isTopBar()));
                form.querySelectorAll('[name=placement]').forEach(r => r.closest('label').classList.toggle('active', r.checked));
                syncPreview();
            };

            const syncAudience = () => {
                document.getElementById('category_group').classList.toggle('d-none', form.audience.value === 'guests');
            };

            const syncPreview = () => {
                form.querySelectorAll('[data-preview]').forEach(input => {
                    const out = preview.querySelector(`[data-preview-out="${input.dataset.preview}"]`);
                    const value = input.value.trim();
                    out.textContent = value || (input.dataset.preview === 'title' ? @json(__('Banner title')) : '');
                    out.classList.toggle('d-none', !value && input.dataset.preview !== 'title');
                });
                preview.style.backgroundColor = form.bg_color.value;
                document.getElementById('banner_preview_content').style.color = form.text_color.value;
                preview.style.minHeight = isTopBar() ? '60px' : '150px';
                preview.style.backgroundImage = !isTopBar() && imageUrl ? `url('${imageUrl}')` : 'none';
            };

            form.querySelectorAll('[name=placement]').forEach(r => r.addEventListener('change', syncPlacement));
            form.audience.addEventListener('change', syncAudience);
            form.querySelectorAll('[data-preview], [type=color]').forEach(i => i.addEventListener('input', syncPreview));

            // Show a newly picked desktop image in the preview right away
            form.querySelector('[name=image]').addEventListener('change', e => {
                const file = e.target.files[0];
                if (file) {
                    imageUrl = URL.createObjectURL(file);
                    syncPreview();
                }
            });

            syncPlacement();
            syncAudience();
        });
    </script>
@endpush
