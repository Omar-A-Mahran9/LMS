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
                <h3 class="fw-bold m-0">{{ __('Banners & offers') }}</h3>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-stack flex-wrap gap-3 mb-5">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center position-relative">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-docs-table-filter="search"
                            class="form-control form-control-solid w-250px ps-13" placeholder="{{ __('Search') }}">
                    </div>
                    <select class="form-select form-select-solid w-225px" id="placement_filter">
                        <option value="all">{{ __('All places') }}</option>
                        @foreach (\App\Models\Banner::PLACEMENTS as $key => $label)
                            <option value="{{ $key }}">{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>

                <a class="btn btn-primary" href="{{ route('dashboard.banners.create') }}" data-kt-docs-table-toolbar="base">
                    <i class="ki-outline ki-plus fs-2"></i>{{ __('Add banner / offer') }}
                </a>

                <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-docs-table-select="selected_count"></span>{{ __('Selected item') }}
                    </div>
                    <button type="button" class="btn btn-danger" data-kt-docs-table-select="delete_selected">{{ __('delete') }}</button>
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
                        <th class="text-center min-w-250px">{{ __('Banner') }}</th>
                        <th class="text-center">{{ __('Place') }}</th>
                        <th class="text-center">{{ __('Audience') }}</th>
                        <th class="text-center min-w-150px">{{ __('Period') }}</th>
                        <th class="text-center">{{ __('Clicks') }}</th>
                        <th class="text-center">{{ __('Order') }}</th>
                        <th class="text-center">{{ __('Status') }}</th>
                        <th class="text-center min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        window.bannerLabels = {
            placements: @json(collect(\App\Models\Banner::PLACEMENTS)->map(fn ($l) => __($l))),
            audiences: @json(collect(\App\Models\Banner::AUDIENCES)->map(fn ($l) => __($l))),
        };
    </script>
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/banners.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>
@endpush
