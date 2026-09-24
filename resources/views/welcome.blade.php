 
@extends('dashboard.partials.master')

@section('content')

    <div id="kt_app_content" class="flex-column-fluid">

        <div id="kt_app_content_container" class="app-container container-xxl">

            @can('view_dashboard')

                {{-- =========================================================
                     Dashboard Statistics
                ========================================================== --}}

                @php
                    $statCards = [
                        [
                            'key' => 'courses',
                            'icon' => 'ki-book',
                            'color' => 'primary',
                            'value' => $totalCourses,
                            'label' => __('Total Courses'),
                        ],
                        [
                            'key' => 'students',
                            'icon' => 'ki-people',
                            'color' => 'success',
                            'value' => $totalStudents,
                            'label' => __('Total Students'),
                        ],
                        [
                            'key' => 'books',
                            'icon' => 'ki-book-open',
                            'color' => 'info',
                            'value' => $totalBooks,
                            'label' => __('Total Books'),
                        ],
                        [
                            'key' => 'book_orders',
                            'icon' => 'ki-list-check',
                            'color' => 'warning',
                            'value' => $totalBookOrders,
                            'label' => __('Book Orders'),
                        ],
                        [
                            'key' => 'enrollments',
                            'icon' => 'ki-cart',
                            'color' => 'danger',
                            'value' => $totalBookings,
                            'label' => __('Course Enrollments'),
                        ],
                    ];
                @endphp


                <div class="row gy-5 g-xl-8 mb-8">

                    @foreach ($statCards as $card)

                        <div class="col-sm-6 col-xl-3">

                            <div class="card h-100 shadow-sm border-0 stat-card">

                                <div class="card-body p-6">

                                    {{-- Card Header --}}
                                    <div class="d-flex align-items-center justify-content-between mb-4">

                                        <div class="symbol symbol-45px">

                                            <div class="symbol-label bg-light-{{ $card['color'] }}">

                                                <i
                                                    class="ki-outline {{ $card['icon'] }} fs-2x text-{{ $card['color'] }}">
                                                </i>

                                            </div>

                                        </div>


                                        <div class="text-end">

                                            <div class="fs-2x fw-bold text-gray-900 lh-1">
                                                {{ $card['value'] }}
                                            </div>

                                        </div>

                                    </div>


                                    {{-- Card Title --}}
                                    <div class="fs-6 fw-semibold text-muted mb-4">
                                        {{ $card['label'] }}
                                    </div>


                                    {{-- =================================================
                                         Students Breakdown
                                    ================================================== --}}
                                    @if ($card['key'] === 'students')

                                        <div class="separator separator-dashed mb-3"></div>

                                        <div class="d-flex flex-column gap-2">

                                            @forelse ($studentByCategory as $row)

                                                <div class="d-flex justify-content-between align-items-center">

                                                    <span class="fs-7 text-muted">
                                                        {{ $row->category ?? __('Uncategorized') }}
                                                    </span>

                                                    <span class="fs-7 fw-bold text-gray-800">
                                                        {{ $row->total }}
                                                    </span>

                                                </div>

                                            @empty

                                                <span class="fs-7 text-muted">
                                                    {{ __('No data') }}
                                                </span>

                                            @endforelse

                                        </div>

                                    @endif


                                    {{-- =================================================
                                         Courses Breakdown
                                    ================================================== --}}
                                    @if ($card['key'] === 'courses')

                                        <div class="separator separator-dashed mb-3"></div>

                                        <div class="d-flex flex-column gap-2">

                                            <div class="d-flex justify-content-between align-items-center">

                                                <span class="fs-7 text-muted">
                                                    {{ __('Class Count') }}
                                                </span>

                                                <span class="fs-7 fw-bold text-gray-800">
                                                    {{ $courseContentStats['class_count'] }}
                                                </span>

                                            </div>


                                            <div class="d-flex justify-content-between align-items-center">

                                                <span class="fs-7 text-muted">
                                                    {{ __('Section Count') }}
                                                </span>

                                                <span class="fs-7 fw-bold text-gray-800">
                                                    {{ $courseContentStats['section_count'] }}
                                                </span>

                                            </div>

                                        </div>

                                    @endif


                                    {{-- =================================================
                                         Books Breakdown
                                    ================================================== --}}
                                    @if ($card['key'] === 'books')

                                        <div class="separator separator-dashed mb-3"></div>

                                        <div class="d-flex flex-column gap-2">

                                            @forelse ($booksStatus as $status)

                                                <div class="d-flex justify-content-between align-items-center">

                                                    <span
                                                        class="badge badge-light-{{ $status->is_active ? 'success' : 'danger' }}"
                                                    >
                                                        {{ $status->is_active ? __('Active') : __('Inactive') }}
                                                    </span>

                                                    <span class="fs-7 fw-bold text-gray-800">
                                                        {{ $status->total }}
                                                    </span>

                                                </div>

                                            @empty

                                                <span class="fs-7 text-muted">
                                                    {{ __('No data') }}
                                                </span>

                                            @endforelse

                                        </div>

                                    @endif


                                    {{-- =================================================
                                         Book Orders Breakdown
                                    ================================================== --}}
                                    @if ($card['key'] === 'book_orders')

                                        <div class="separator separator-dashed mb-3"></div>

                                        <div class="d-flex flex-column gap-2">

                                            @foreach (['pending', 'approved', 'rejected'] as $status)

                                                @php
                                                    $statusColor = match ($status) {
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                    };
                                                @endphp

                                                <div class="d-flex justify-content-between align-items-center">

                                                    <span class="badge badge-light-{{ $statusColor }}">
                                                        {{ __(ucfirst($status)) }}
                                                    </span>

                                                    <span class="fs-7 fw-bold text-gray-800">
                                                        {{ $bookOrderStats[$status] ?? 0 }}
                                                    </span>

                                                </div>

                                            @endforeach

                                        </div>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- =========================================================
                     Status Summary
                ========================================================== --}}

                @php
                    $statusCards = [
                        'pending' => [
                            'color' => 'warning',
                            'icon' => 'ki-time',
                        ],
                        'approved' => [
                            'color' => 'success',
                            'icon' => 'ki-check-circle',
                        ],
                        'rejected' => [
                            'color' => 'danger',
                            'icon' => 'ki-cross-circle',
                        ],
                    ];
                @endphp


                <div class="row gy-5 g-xl-8">

                    @foreach ($statusCards as $status => $config)

                        <div class="col-md-4">

                            <div class="card shadow-sm border-0 h-100">

                                <div class="card-body d-flex flex-column align-items-center text-center py-10">

                                    {{-- Status Icon --}}
                                    <div class="symbol symbol-60px mb-5">

                                        <div class="symbol-label bg-light-{{ $config['color'] }}">

                                            <i
                                                class="ki-outline {{ $config['icon'] }}
                                                text-{{ $config['color'] }} fs-2qx">
                                            </i>

                                        </div>

                                    </div>


                                    {{-- Status Title --}}
                                    <h5 class="text-gray-900 fw-bold mb-6 text-uppercase">

                                        {{ __(ucfirst($status)) }}

                                    </h5>


                                    {{-- Status Statistics --}}
                                    <div class="w-100 px-6">

                                        {{-- Enrollments --}}
                                        <div class="d-flex justify-content-between align-items-center py-2">

                                            <span class="fs-6 text-muted">
                                                {{ __('Enrollments') }}
                                            </span>

                                            <span class="fs-3 fw-bold text-gray-900">
                                                {{ $enrollments[$status] ?? 0 }}
                                            </span>

                                        </div>


                                        <div class="separator separator-dashed my-3"></div>


                                        {{-- Book Orders --}}
                                        <div class="d-flex justify-content-between align-items-center py-2">

                                            <span class="fs-6 text-muted">
                                                {{ __('Book Orders') }}
                                            </span>

                                            <span class="fs-3 fw-bold text-gray-900">
                                                {{ $bookOrderStats[$status] ?? 0 }}
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>


            @else

                {{-- =========================================================
                     Welcome Screen
                ========================================================== --}}

                <div
                    class="d-flex flex-column align-items-center justify-content-center text-center"
                    style="min-height: 70vh;"
                >

                    <img
                        src="{{ getImagePathFromDirectory(setting('logo_image'), 'Settings') }}"
                        alt="{{ setting('website_name') }}"
                        class="img-fluid mb-6"
                        style="max-width: 220px;"
                    >


                    <h2 class="text-muted fs-6 mb-1">
                        {{ __('Hello') }}
                    </h2>


                    <h1 class="text-dark fs-2 fw-bold">

                        {{ auth()->user()->name }}

                    </h1>

                </div>

            @endcan

        </div>

    </div>

@endsection


{{-- =========================================================
     Dashboard Styles
========================================================== --}}

@push('styles')

    <style>
        .stat-card {
            transition:
                transform 0.15s ease,
                box-shadow 0.15s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
        }
    </style>

@endpush

