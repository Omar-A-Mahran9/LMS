@extends('dashboard.partials.master')

@push('styles')
    <link href="{{ asset('assets/dashboard/css/datatables' . (isDarkMode() ? '.dark' : '') . '.bundle.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ asset('assets/dashboard/plugins/custom/datatables/datatables.bundle' . (isArabic() ? '.rtl' : '') . '.css') }}"
        rel="stylesheet" type="text/css" />
    <style>
        .code-text {
            font-family: 'DejaVu Sans Mono', SFMono-Regular, Menlo, Consolas, monospace;
            letter-spacing: 1px;
            direction: ltr;
        }

        .status-card {
            cursor: pointer;
            transition: box-shadow .15s, border-color .15s;
            border: 1px dashed var(--bs-gray-300);
        }

        .status-card:hover {
            box-shadow: 0 0 0 1px var(--bs-gray-400);
        }

        .status-card.active {
            border: 1px solid var(--bs-primary);
            box-shadow: 0 0 0 1px var(--bs-primary);
        }
    </style>
@endpush

@section('content')
    @php
        $classLabel = fn($class) => $class->title . ($class->course ? ' — ' . $class->course->title : '');
        $statuses = [
            'all' => ['label' => __('All codes'), 'color' => 'primary'],
            'unused' => ['label' => __('Unused'), 'color' => 'success'],
            'used' => ['label' => __('Partially used'), 'color' => 'info'],
            'exhausted' => ['label' => __('Exhausted'), 'color' => 'secondary'],
            'inactive' => ['label' => __('Inactive'), 'color' => 'danger'],
        ];
    @endphp

    {{-- Status cards: counts follow the filters below, a click filters the table by that status --}}
    <div class="row g-3 mb-5">
        @foreach ($statuses as $key => $status)
            <div class="col">
                <div class="card status-card h-100 {{ $key === 'all' ? 'active' : '' }}" data-status="{{ $key === 'all' ? '' : $key }}">
                    <div class="card-body py-4 px-5">
                        <div class="fs-2hx fw-bold text-{{ $status['color'] }}" data-stat="{{ $key }}">—</div>
                        <div class="fw-semibold text-gray-600">{{ $status['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-5 mb-x-10">
        <div class="card-header border-0 pt-5">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ __('Access Codes List') }}</h3>
            </div>

            <div class="card-toolbar gap-3">
                <div class="btn-group">
                    <button type="button" class="btn btn-light-primary dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fas fa-file-pdf me-2"></i>{{ __('Export PDF') }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item btn-export-pdf" data-layout="table" href="#"
                                target="_blank">{{ __('Report table') }}</a></li>
                        <li><a class="dropdown-item btn-export-pdf" data-layout="cards" href="#"
                                target="_blank">{{ __('Cards to print and cut') }}</a></li>
                    </ul>
                </div>

                <button type="button" class="btn btn-primary" id="add_btn">
                    <i class="fas fa-plus me-2"></i>{{ __('Generate codes') }}
                </button>
            </div>
        </div>

        <div class="card-body pt-4">
            {{-- Filters: the table, the cards and the PDF export all follow them --}}
            <div class="row g-3 mb-5" id="codes_filters">
                <div class="col-md-3">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                        <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid ps-12"
                            placeholder="{{ __('Search by code or class') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="filter_class" class="form-select form-select-solid" data-filter="class_id"
                        data-placeholder="{{ __('All classes') }}" data-allow-clear="true">
                        <option></option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $classLabel($class) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filter_status" class="form-select form-select-solid" data-filter="status">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (array_slice($statuses, 1, null, true) as $key => $status)
                            <option value="{{ $key }}">{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-solid">
                        <input type="date" class="form-control form-control-solid" data-filter="date_from"
                            title="{{ __('Created from') }}">
                        <span class="input-group-text">→</span>
                        <input type="date" class="form-control form-control-solid" data-filter="date_to"
                            title="{{ __('Created to') }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-light w-100" id="reset_filters" title="{{ __('Reset') }}">
                        <i class="fas fa-undo p-0"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-end align-items-center d-none mb-4" data-kt-docs-table-toolbar="selected">
                <div class="fw-bold me-5">
                    <span class="me-2" data-kt-docs-table-select="selected_count"></span>{{ __('Selected') }}
                </div>
                <button type="button" class="btn btn-danger"
                    data-kt-docs-table-select="delete_selected">{{ __('Delete') }}</button>
            </div>

            <table id="kt_datatable" class="table align-middle text-center table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                    data-kt-check-target="#kt_datatable .form-check-input" value="1" />
                            </div>
                        </th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Usage') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th class="min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
        </div>
    </div>

    <form id="crud_form" class="ajax-form" action="{{ route('dashboard.generateCode.store') }}" method="post"
        data-success-callback="onCodesSaved" data-error-callback="onAjaxError">
        @csrf
        <div class="modal fade" tabindex="-1" id="crud_modal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="form_title">{{ __('Generate codes') }}</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                            aria-label="Close"><i class="ki-outline ki-cross fs-1"></i></div>
                    </div>

                    <div class="modal-body">
                        <div class="mb-5">
                            <label for="class_id_inp" class="form-label required">{{ __('Class') }}</label>
                            <select name="class_id" id="class_id_inp" class="form-select"
                                data-placeholder="{{ __('Select class') }}">
                                <option value=""></option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $classLabel($class) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="class_id"></div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6" id="code_count_wrapper">
                                <label for="code_count_inp" class="form-label">{{ __('Number of Codes to Generate') }}</label>
                                <input type="number" name="code_count" id="code_count_inp" class="form-control"
                                    min="1" max="1000" value="1">
                                <div class="form-text">{{ __('Up to 1000 codes at once') }}</div>
                                <div class="invalid-feedback" id="code_count"></div>
                            </div>
                            <div class="col-md-6" id="code_wrapper">
                                <label for="code_inp" class="form-label">{{ __('Access Code') }}</label>
                                <input type="text" name="code" id="code_inp" class="form-control code-text"
                                    placeholder="{{ __('Leave blank to auto-generate') }}">
                                <div class="invalid-feedback" id="code"></div>
                            </div>
                        </div>

                        <div class="row mb-5 align-items-end">
                            <div class="col-md-6">
                                <label for="usage_limit_inp" class="form-label">{{ __('Usage Limit') }}</label>
                                <input type="number" name="usage_limit" id="usage_limit_inp" class="form-control"
                                    min="1" placeholder="{{ __('Leave blank for unlimited') }}">
                                <div class="invalid-feedback" id="usage_limit"></div>
                            </div>
                            <div class="col-md-6 d-flex gap-8 pb-3">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    {{-- hidden 0 so that switching it off is actually saved --}}
                                    <input type="hidden" name="single_use" value="0">
                                    <input class="form-check-input" name="single_use" type="checkbox" value="1"
                                        id="single_use_switch" checked>
                                    <span class="form-check-label text-dark">{{ __('Single Use') }}</span>
                                </label>
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" name="is_active" type="checkbox" value="1"
                                        id="is_active_switch" checked>
                                    <span class="form-check-label text-dark">{{ __('Active') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('Save') }}</span>
                            <span class="indicator-progress">
                                {{ __('Please wait...') }} <span class="spinner-border spinner-border-sm ms-2"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const codesExportUrl = "{{ route('dashboard.generateCode.exportPDF') }}";
        const codesStoreUrl = "{{ route('dashboard.generateCode.store') }}";
    </script>
    <script src="{{ asset('assets/dashboard/js/global/datatable-config.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datatables/access_codes.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/global/crud-operations.js') }}"></script>
@endpush
