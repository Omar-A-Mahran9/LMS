 
@extends('dashboard.partials.master')


@section('content')

    <div
        id="kt_app_content"
        class="app-content flex-column-fluid"
    >

        <div
            id="kt_app_content_container"
            class="app-container container-xxl"
        >

            @can('view_dashboard')

                {{-- =====================================================
                     Dashboard Header
                ====================================================== --}}

                <div class="d-flex flex-wrap align-items-center justify-content-between mb-8">

                    <div>

                        <h1 class="fw-bold text-gray-900 mb-2">
                            {{ __('Dashboard') }}
                        </h1>

                        <div class="text-muted fw-semibold">
                            {{ __('Overview of your platform statistics and activities') }}
                        </div>

                    </div>

                </div>


                {{-- =====================================================
                     Statistics Cards
                ====================================================== --}}

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


                <div class="row g-5 g-xl-8 mb-8">

                    @foreach ($statCards as $card)

                        <div class="col-6 col-xl">

                            <div class="card dashboard-stat-card h-100 border-0 shadow-sm">

                                <div class="card-body p-6">

                                    <div class="d-flex align-items-center justify-content-between mb-5">

                                        <div class="symbol symbol-50px">

                                            <div class="symbol-label bg-light-{{ $card['color'] }}">

                                                <i
                                                    class="ki-outline {{ $card['icon'] }}
                                                           fs-2x text-{{ $card['color'] }}"
                                                ></i>

                                            </div>

                                        </div>

                                    </div>


                                    <div class="fs-2x fw-bold text-gray-900 mb-1">

                                        {{ number_format($card['value']) }}

                                    </div>


                                    <div class="fs-7 fw-semibold text-muted">

                                        {{ $card['label'] }}

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- =====================================================
                     Status Summary
                ====================================================== --}}

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


                <div class="row g-5 g-xl-8 mb-8">

                    @foreach ($statusCards as $status => $config)

                        <div class="col-md-4">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-body p-6">

                                    <div class="d-flex align-items-center">

                                        <div class="symbol symbol-50px me-4">

                                            <div class="symbol-label bg-light-{{ $config['color'] }}">

                                                <i
                                                    class="ki-outline {{ $config['icon'] }}
                                                           fs-2x text-{{ $config['color'] }}"
                                                ></i>

                                            </div>

                                        </div>


                                        <div>

                                            <div class="fw-bold text-gray-900 fs-5">

                                                {{ __(ucfirst($status)) }}

                                            </div>

                                            <div class="text-muted fs-7">

                                                {{ __('Current status') }}

                                            </div>

                                        </div>

                                    </div>


                                    <div class="separator separator-dashed my-5"></div>


                                    <div class="d-flex justify-content-between align-items-center mb-4">

                                        <span class="text-muted fs-6">
                                            {{ __('Enrollments') }}
                                        </span>

                                        <span class="fw-bold fs-3 text-gray-900">

                                            {{ number_format($enrollments[$status] ?? 0) }}

                                        </span>

                                    </div>


                                    <div class="d-flex justify-content-between align-items-center">

                                        <span class="text-muted fs-6">
                                            {{ __('Book Orders') }}
                                        </span>

                                        <span class="fw-bold fs-3 text-gray-900">

                                            {{ number_format($bookOrderStats[$status] ?? 0) }}

                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- =====================================================
                     Charts Row
                ====================================================== --}}

                <div class="row g-5 g-xl-8 mb-8">

                    {{-- Students By Category --}}
                    <div class="col-xl-7">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header border-0 pt-6">

                                <div class="card-title flex-column align-items-start">

                                    <h3 class="fw-bold text-gray-900 mb-1">
                                        {{ __('Students by Category') }}
                                    </h3>

                                    <span class="text-muted fs-7">
                                        {{ __('Distribution of students across categories') }}
                                    </span>

                                </div>

                            </div>


                            <div class="card-body pt-0">

                                <div
                                    id="studentsCategoryChart"
                                    class="dashboard-chart"
                                ></div>

                            </div>

                        </div>

                    </div>


                    {{-- Books Status --}}
                    <div class="col-xl-5">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header border-0 pt-6">

                                <div class="card-title flex-column align-items-start">

                                    <h3 class="fw-bold text-gray-900 mb-1">
                                        {{ __('Books Status') }}
                                    </h3>

                                    <span class="text-muted fs-7">
                                        {{ __('Active and inactive books') }}
                                    </span>

                                </div>

                            </div>


                            <div class="card-body pt-0">

                                <div
                                    id="booksStatusChart"
                                    class="dashboard-chart"
                                ></div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                     Orders & Enrollments Charts
                ====================================================== --}}

                <div class="row g-5 g-xl-8 mb-8">

                    {{-- Orders --}}
                    <div class="col-xl-6">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header border-0 pt-6">

                                <div class="card-title flex-column align-items-start">

                                    <h3 class="fw-bold text-gray-900 mb-1">
                                        {{ __('Book Orders') }}
                                    </h3>

                                    <span class="text-muted fs-7">
                                        {{ __('Orders by current status') }}
                                    </span>

                                </div>

                            </div>


                            <div class="card-body pt-0">

                                <div
                                    id="bookOrdersChart"
                                    class="dashboard-chart"
                                ></div>

                            </div>

                        </div>

                    </div>


                    {{-- Enrollments --}}
                    <div class="col-xl-6">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header border-0 pt-6">

                                <div class="card-title flex-column align-items-start">

                                    <h3 class="fw-bold text-gray-900 mb-1">
                                        {{ __('Enrollments') }}
                                    </h3>

                                    <span class="text-muted fs-7">
                                        {{ __('Enrollments by current status') }}
                                    </span>

                                </div>

                            </div>


                            <div class="card-body pt-0">

                                <div
                                    id="enrollmentsChart"
                                    class="dashboard-chart"
                                ></div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                     Course Content Overview
                ====================================================== --}}

                <div class="row g-5 g-xl-8">

                    <div class="col-12">

                        <div class="card border-0 shadow-sm">

                            <div class="card-header border-0 pt-6">

                                <div class="card-title flex-column align-items-start">

                                    <h3 class="fw-bold text-gray-900 mb-1">
                                        {{ __('Course Content Overview') }}
                                    </h3>

                                    <span class="text-muted fs-7">
                                        {{ __('Classes and sections available in the platform') }}
                                    </span>

                                </div>

                            </div>


                            <div class="card-body">

                                <div
                                    id="courseContentChart"
                                    class="dashboard-chart dashboard-chart-sm"
                                ></div>

                            </div>

                        </div>

                    </div>

                </div>


            @else

                {{-- =====================================================
                     Welcome Screen
                ====================================================== --}}

                <div
                    class="dashboard-welcome d-flex flex-column
                           align-items-center justify-content-center
                           text-center"
                >

                    <img
                        src="{{ getImagePathFromDirectory(setting('logo_image'), 'Settings') }}"
                        alt="{{ setting('website_name') }}"
                        class="img-fluid mb-6"
                    >


                    <h2 class="text-muted fs-6 mb-2">

                        {{ __('Hello') }}

                    </h2>


                    <h1 class="text-gray-900 fs-2 fw-bold">

                        {{ auth()->user()->name }}

                    </h1>


                    <p class="text-muted mt-3 mb-0">

                        {{ __('Welcome to the dashboard') }}

                    </p>

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

        .dashboard-stat-card {
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .dashboard-stat-card:hover {
            transform: translateY(-4px);

            box-shadow:
                0 0.75rem 2rem rgba(0, 0, 0, 0.08) !important;
        }


        .dashboard-chart {
            min-height: 340px;
        }


        .dashboard-chart-sm {
            min-height: 280px;
        }


        .dashboard-welcome {
            min-height: 70vh;
        }


        .dashboard-welcome img {
            max-width: 220px;
        }

    </style>

@endpush


{{-- =========================================================
     Dashboard Scripts
========================================================== --}}

@push('scripts')

    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <script>

        document.addEventListener('DOMContentLoaded', function () {

            /*
             * =========================================================
             * Dashboard Data
             * =========================================================
             */

            const isDarkMode =
                document.documentElement.getAttribute('data-bs-theme') === 'dark';


            const textColor = isDarkMode
                ? '#9CA3AF'
                : '#6B7280';


            const gridColor = isDarkMode
                ? '#374151'
                : '#E5E7EB';


            /*
             * =========================================================
             * Students By Category
             * =========================================================
             */

            const studentCategories = @json(
                collect($studentByCategory ?? [])
                    ->map(function ($row) {
                        return [
                            'category' => $row->category ?? __('Uncategorized'),
                            'total' => (int) $row->total,
                        ];
                    })
                    ->values()
            );


            const studentsCategoryElement =
                document.querySelector('#studentsCategoryChart');


            if (studentsCategoryElement) {

                new ApexCharts(
                    studentsCategoryElement,
                    {
                        chart: {
                            type: 'bar',
                            height: 340,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit'
                        },

                        series: [
                            {
                                name: @json(__('Students')),
                                data: studentCategories.map(
                                    item => item.total
                                )
                            }
                        ],

                        xaxis: {
                            categories: studentCategories.map(
                                item => item.category
                            ),

                            labels: {
                                style: {
                                    colors: textColor
                                }
                            },

                            axisBorder: {
                                show: false
                            },

                            axisTicks: {
                                show: false
                            }
                        },

                        yaxis: {
                            labels: {
                                style: {
                                    colors: textColor
                                }
                            }
                        },

                        grid: {
                            borderColor: gridColor,
                            strokeDashArray: 4
                        },

                        plotOptions: {
                            bar: {
                                borderRadius: 6,
                                columnWidth: '45%'
                            }
                        },

                        dataLabels: {
                            enabled: false
                        },

                        tooltip: {
                            y: {
                                formatter: function (value) {
                                    return value.toLocaleString();
                                }
                            }
                        },

                        noData: {
                            text: @json(__('No data available'))
                        }
                    }
                ).render();

            }


            /*
             * =========================================================
             * Books Status
             * =========================================================
             */

            const booksStatus = @json(
                collect($booksStatus ?? [])
                    ->map(function ($row) {
                        return [
                            'label' => $row->is_active
                                ? __('Active')
                                : __('Inactive'),

                            'total' => (int) $row->total,
                        ];
                    })
                    ->values()
            );


            const booksStatusElement =
                document.querySelector('#booksStatusChart');


            if (booksStatusElement) {

                new ApexCharts(
                    booksStatusElement,
                    {
                        chart: {
                            type: 'donut',
                            height: 340,
                            fontFamily: 'inherit'
                        },

                        labels: booksStatus.map(
                            item => item.label
                        ),

                        series: booksStatus.map(
                            item => item.total
                        ),

                        legend: {
                            position: 'bottom',
                            labels: {
                                colors: textColor
                            }
                        },

                        dataLabels: {
                            enabled: true
                        },

                        stroke: {
                            width: 0
                        },

                        noData: {
                            text: @json(__('No data available'))
                        }
                    }
                ).render();

            }


            /*
             * =========================================================
             * Book Orders
             * =========================================================
             */

            const bookOrders = {

                pending: @json((int) ($bookOrderStats['pending'] ?? 0)),

                approved: @json((int) ($bookOrderStats['approved'] ?? 0)),

                rejected: @json((int) ($bookOrderStats['rejected'] ?? 0))

            };


            const bookOrdersElement =
                document.querySelector('#bookOrdersChart');


            if (bookOrdersElement) {

                new ApexCharts(
                    bookOrdersElement,
                    {
                        chart: {
                            type: 'bar',
                            height: 340,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit'
                        },

                        series: [
                            {
                                name: @json(__('Orders')),

                                data: [
                                    bookOrders.pending,
                                    bookOrders.approved,
                                    bookOrders.rejected
                                ]
                            }
                        ],

                        xaxis: {
                            categories: [
                                @json(__('Pending')),
                                @json(__('Approved')),
                                @json(__('Rejected'))
                            ],

                            labels: {
                                style: {
                                    colors: textColor
                                }
                            }
                        },

                        yaxis: {
                            labels: {
                                style: {
                                    colors: textColor
                                }
                            }
                        },

                        grid: {
                            borderColor: gridColor,
                            strokeDashArray: 4
                        },

                        plotOptions: {
                            bar: {
                                borderRadius: 6,
                                columnWidth: '45%'
                            }
                        },

                        dataLabels: {
                            enabled: false
                        },

                        noData: {
                            text: @json(__('No data available'))
                        }
                    }
                ).render();

            }


            /*
             * =========================================================
             * Enrollments
             * =========================================================
             */

            const enrollments = {

                pending: @json((int) ($enrollments['pending'] ?? 0)),

                approved: @json((int) ($enrollments['approved'] ?? 0)),

                rejected: @json((int) ($enrollments['rejected'] ?? 0))

            };


            const enrollmentsElement =
                document.querySelector('#enrollmentsChart');


            if (enrollmentsElement) {

                new ApexCharts(
                    enrollmentsElement,
                    {
                        chart: {
                            type: 'donut',
                            height: 340,
                            fontFamily: 'inherit'
                        },

                        labels: [
                            @json(__('Pending')),
                            @json(__('Approved')),
                            @json(__('Rejected'))
                        ],

                        series: [
                            enrollments.pending,
                            enrollments.approved,
                            enrollments.rejected
                        ],

                        legend: {
                            position: 'bottom',

                            labels: {
                                colors: textColor
                            }
                        },

                        dataLabels: {
                            enabled: true
                        },

                        stroke: {
                            width: 0
                        },

                        noData: {
                            text: @json(__('No data available'))
                        }
                    }
                ).render();

            }


            /*
             * =========================================================
             * Course Content
             * =========================================================
             */

            const courseContent = {

                classes: @json(
                    (int) ($courseContentStats['class_count'] ?? 0)
                ),

                sections: @json(
                    (int) ($courseContentStats['section_count'] ?? 0)
                )

            };


            const courseContentElement =
                document.querySelector('#courseContentChart');


            if (courseContentElement) {

                new ApexCharts(
                    courseContentElement,
                    {
                        chart: {
                            type: 'bar',
                            height: 280,
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'inherit'
                        },

                        series: [
                            {
                                name: @json(__('Count')),

                                data: [
                                    courseContent.classes,
                                    courseContent.sections
                                ]
                            }
                        ],

                        xaxis: {
                            categories: [
                                @json(__('Classes')),
                                @json(__('Sections'))
                            ],

                            labels: {
                                style: {
                                    colors: textColor
                                }
                            }
                        },

                        yaxis: {
                            labels: {
                                style: {
                                    colors: textColor
                                }
                            }
                        },

                        grid: {
                            borderColor: gridColor,
                            strokeDashArray: 4
                        },

                        plotOptions: {
                            bar: {
                                borderRadius: 6,
                                columnWidth: '35%'
                            }
                        },

                        dataLabels: {
                            enabled: true
                        },

                        noData: {
                            text: @json(__('No data available'))
                        }
                    }
                ).render();

            }

        });

    </script>

@endpush

