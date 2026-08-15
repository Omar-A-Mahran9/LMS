@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            @can('view_dashboard')
                {{-- ==================== PAGE HEADER ==================== --}}
                <div class="d-flex flex-wrap flex-stack mb-6">
                    <div>
                        <h1 class="fs-2 fw-bold text-gray-900 mb-1">{{ __('Dashboard Overview') }}</h1>
                        <div class="fs-6 text-muted">{{ __('A summary of courses, students, books and enrollments') }}</div>
                    </div>
                </div>

                {{-- ==================== TOP STAT CARDS ==================== --}}
                <div class="row gy-5 g-xl-8 mb-8">
                    @foreach ([['icon' => 'ki-book', 'color' => 'primary', 'value' => $totalCourses, 'label' => __('Total Courses')], ['icon' => 'ki-people', 'color' => 'success', 'value' => $totalStudents, 'label' => __('Total Students')], ['icon' => 'ki-book-open', 'color' => 'info', 'value' => $totalBooks, 'label' => __('Total Books')], ['icon' => 'ki-list-check', 'color' => 'warning', 'value' => $totalBookOrders, 'label' => __('Book Orders')], ['icon' => 'ki-cart', 'color' => 'danger', 'value' => $totalBookings, 'label' => __('Course Enrollments')]] as $item)
                        <div class="col-sm-6 col-xl-3">
                            <div class="card h-100 shadow-sm border-0 stat-card">
                                <div class="card-body p-6 d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fs-6 fw-semibold text-muted mb-1">{{ $item['label'] }}</div>
                                        <div class="fs-2x fw-bold text-gray-900 lh-1">{{ $item['value'] }}</div>
                                    </div>
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label bg-light-{{ $item['color'] }}">
                                            <i class="ki-outline {{ $item['icon'] }} fs-2x text-{{ $item['color'] }}"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ==================== DETAILED BREAKDOWN SECTIONS ==================== --}}
                <div class="row gy-5 g-xl-8 mb-8">

                    {{-- Students by category --}}
                    <div class="col-xl-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title">
                                    <span class="symbol symbol-35px me-3">
                                        <span class="symbol-label bg-light-success">
                                            <i class="ki-outline ki-people fs-3 text-success"></i>
                                        </span>
                                    </span>
                                    <span class="fw-bold fs-4">{{ __('Students by Category') }}</span>
                                </h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-3">
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th>{{ __('Category') }}</th>
                                                <th class="text-end">{{ __('Students') }}</th>
                                                <th class="text-end">{{ __('Share') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($studentByCategory as $row)
                                                @php
                                                    $percentage =
                                                        $totalStudents > 0
                                                            ? round(($row->total / $totalStudents) * 100, 1)
                                                            : 0;
                                                @endphp
                                                <tr>
                                                    <td class="text-gray-800">{{ $row->category ?? __('Uncategorized') }}</td>
                                                    <td class="text-end fw-bold text-gray-900">{{ $row->total }}</td>
                                                    <td class="text-end">
                                                        <span class="badge badge-light-success">{{ $percentage }}%</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-6">
                                                        {{ __('No data available') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Course content stats --}}
                    <div class="col-xl-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title">
                                    <span class="symbol symbol-35px me-3">
                                        <span class="symbol-label bg-light-primary">
                                            <i class="ki-outline ki-book fs-3 text-primary"></i>
                                        </span>
                                    </span>
                                    <span class="fw-bold fs-4">{{ __('Course Structure') }}</span>
                                </h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-3">
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th>{{ __('Item') }}</th>
                                                <th class="text-end">{{ __('Count') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-gray-800">{{ __('Total Courses') }}</td>
                                                <td class="text-end fw-bold text-gray-900">{{ $totalCourses }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-800">{{ __('Class Count') }}</td>
                                                <td class="text-end fw-bold text-gray-900">
                                                    {{ $courseContentStats['class_count'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-800">{{ __('Section Count') }}</td>
                                                <td class="text-end fw-bold text-gray-900">
                                                    {{ $courseContentStats['section_count'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Books status --}}
                    <div class="col-xl-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title">
                                    <span class="symbol symbol-35px me-3">
                                        <span class="symbol-label bg-light-info">
                                            <i class="ki-outline ki-book-open fs-3 text-info"></i>
                                        </span>
                                    </span>
                                    <span class="fw-bold fs-4">{{ __('Books Status') }}</span>
                                </h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-3">
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th>{{ __('Status') }}</th>
                                                <th class="text-end">{{ __('Books') }}</th>
                                                <th class="text-end">{{ __('Share') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($booksStatus as $status)
                                                @php
                                                    $percentage =
                                                        $totalBooks > 0
                                                            ? round(($status->total / $totalBooks) * 100, 1)
                                                            : 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span
                                                            class="badge badge-light-{{ $status->is_active ? 'success' : 'danger' }}">
                                                            {{ $status->is_active ? __('Active') : __('Inactive') }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end fw-bold text-gray-900">{{ $status->total }}</td>
                                                    <td class="text-end">
                                                        <span class="badge badge-light-info">{{ $percentage }}%</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-6">
                                                        {{ __('No data available') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Book orders breakdown --}}
                    <div class="col-xl-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title">
                                    <span class="symbol symbol-35px me-3">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-outline ki-list-check fs-3 text-warning"></i>
                                        </span>
                                    </span>
                                    <span class="fw-bold fs-4">{{ __('Book Orders Breakdown') }}</span>
                                </h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-3">
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th>{{ __('Status') }}</th>
                                                <th class="text-end">{{ __('Orders') }}</th>
                                                <th class="text-end">{{ __('Share') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'] as $status => $color)
                                                @php
                                                    $count = $bookOrderStats[$status] ?? 0;
                                                    $percentage =
                                                        $totalBookOrders > 0
                                                            ? round(($count / $totalBookOrders) * 100, 1)
                                                            : 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span
                                                            class="badge badge-light-{{ $color }}">{{ __(ucfirst($status)) }}</span>
                                                    </td>
                                                    <td class="text-end fw-bold text-gray-900">{{ $count }}</td>
                                                    <td class="text-end">
                                                        <span
                                                            class="badge badge-light-{{ $color }}">{{ $percentage }}%</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ==================== ENROLLMENT & ORDER STATUS SUMMARY ==================== --}}
                <div class="mb-4">
                    <h3 class="fs-4 fw-bold text-gray-900 mb-4">{{ __('Enrollments & Orders by Status') }}</h3>
                </div>

                <div class="row gy-5 g-xl-8">
                    @foreach (['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'] as $status => $color)
                        @php
                            $enrollmentCount = $enrollments[$status] ?? 0;
                            $orderCount = $bookOrderStats[$status] ?? 0;
                            $enrollmentPct =
                                $totalBookings > 0 ? round(($enrollmentCount / $totalBookings) * 100, 1) : 0;
                            $orderPct = $totalBookOrders > 0 ? round(($orderCount / $totalBookOrders) * 100, 1) : 0;
                        @endphp
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body py-8 px-8">

                                    <div class="d-flex align-items-center mb-6">
                                        <div class="symbol symbol-50px me-4">
                                            <div class="symbol-label bg-light-{{ $color }}">
                                                <i class="ki-outline ki-check-circle text-{{ $color }} fs-2x"></i>
                                            </div>
                                        </div>
                                        <h5 class="text-gray-900 fw-bold m-0 text-uppercase">
                                            {{ __(ucfirst($status)) }}
                                        </h5>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-2 mb-0">
                                            <tbody>
                                                <tr>
                                                    <td class="text-muted">{{ __('Enrollments') }}</td>
                                                    <td class="text-end fw-bold text-gray-900">{{ $enrollmentCount }}</td>
                                                    <td class="text-end">
                                                        <span
                                                            class="badge badge-light-{{ $color }}">{{ $enrollmentPct }}%</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Book Orders') }}</td>
                                                    <td class="text-end fw-bold text-gray-900">{{ $orderCount }}</td>
                                                    <td class="text-end">
                                                        <span
                                                            class="badge badge-light-{{ $color }}">{{ $orderPct }}%</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- ==================== WELCOME SCREEN ==================== --}}
                <div class="d-flex flex-column align-items-center justify-content-center text-center"
                    style="min-height: 70vh;">
                    <img src="{{ getImagePathFromDirectory(setting('logo_image'), 'Settings') }}" alt="Logo"
                        class="img-fluid mb-6" style="max-width: 220px;">

                    <h2 class="text-muted fs-6 mb-1">{{ __('Hello') }}</h2>
                    <h1 class="text-dark fs-2 fw-bold">
                        {{ auth()->user()->name }}
                    </h1>
                </div>
            @endcan
        </div>
    </div>

    <style>
        .stat-card {
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .08) !important;
        }
    </style>
@endsection
