 
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
                            'icon' => 'ki-book',
                            'color' => 'primary',
                            'value' => $stats['kpis']['courses'],
                            'label' => __('Total Courses'),
                            'hint' => __(':count active', ['count' => number_format($stats['courses']['active'])]),
                        ],
                        [
                            'icon' => 'ki-people',
                            'color' => 'success',
                            'value' => $stats['kpis']['students'],
                            'label' => __('Total Students'),
                            'hint' => __(':count grades', ['count' => number_format(collect($stats['students_by_category'])->whereNotNull('category_id')->count())]),
                        ],
                        [
                            'icon' => 'ki-book-open',
                            'color' => 'info',
                            'value' => $stats['kpis']['books'],
                            'label' => __('Total Books'),
                            'hint' => __(':count active', ['count' => number_format($stats['books']['active'])]),
                        ],
                        [
                            'icon' => 'ki-basket',
                            'color' => 'warning',
                            'value' => $stats['kpis']['book_orders'],
                            'label' => __('Book Orders'),
                            'hint' => __(':count pending', ['count' => number_format($stats['book_orders']['counts']['pending'])]),
                        ],
                        [
                            'icon' => 'ki-teacher',
                            'color' => 'danger',
                            'value' => $stats['kpis']['enrollments'],
                            'label' => __('Course Enrollments'),
                            'hint' => __(':count pending', ['count' => number_format($stats['enrollments']['counts']['pending'])]),
                        ],
                    ];
                @endphp

                <div class="row g-5 g-xl-8 mb-8">
                    @foreach ($statCards as $card)
                        <div class="col-6 col-md-4 col-xl">
                            <div class="card dashboard-stat-card h-100 border-0 shadow-sm">
                                <div class="card-body p-6">
                                    <div class="symbol symbol-50px mb-5">
                                        <div class="symbol-label bg-light-{{ $card['color'] }}">
                                            <i class="ki-outline {{ $card['icon'] }} fs-2x text-{{ $card['color'] }}"></i>
                                        </div>
                                    </div>
                                    <div class="fs-2x fw-bold text-gray-900 mb-1">{{ number_format($card['value']) }}</div>
                                    <div class="fs-7 fw-semibold text-muted">{{ $card['label'] }}</div>
                                    <div class="fs-8 text-gray-500 mt-1">{{ $card['hint'] }}</div>
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

                                            {{ number_format($stats['enrollments']['counts'][$status]) }}
                                            <span class="fs-7 fw-semibold text-muted ms-1">({{ $stats['enrollments']['percentages'][$status] }}%)</span>

                                        </span>

                                    </div>


                                    <div class="d-flex justify-content-between align-items-center">

                                        <span class="text-muted fs-6">
                                            {{ __('Book Orders') }}
                                        </span>

                                        <span class="fw-bold fs-3 text-gray-900">

                                            {{ number_format($stats['book_orders']['counts'][$status]) }}
                                            <span class="fs-7 fw-semibold text-muted ms-1">({{ $stats['book_orders']['percentages'][$status] }}%)</span>

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

        .dashboard-chart-empty {
            min-height: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            color: var(--bs-gray-500);
        }

        /* Readable tooltips in both themes */
        .apexcharts-tooltip.apexcharts-theme-light {
            background: var(--bs-body-bg) !important;
            color: var(--bs-gray-800);
            border-color: var(--bs-border-color) !important;
        }

        .apexcharts-tooltip.apexcharts-theme-light .apexcharts-tooltip-title {
            background: var(--bs-gray-100) !important;
            border-color: var(--bs-border-color) !important;
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
@can('view_dashboard')
    @php
        $chartLabels = [
                'students' => __('Students'),
                'orders' => __('Orders'),
                'enrollments' => __('Enrollments'),
                'books' => __('Books'),
                'total' => __('Total'),
                'active' => __('Active'),
                'inactive' => __('Inactive'),
                'pending' => __('Pending'),
                'approved' => __('Approved'),
                'rejected' => __('Rejected'),
                'classes' => __('Classes'),
                'sections' => __('Sections'),
                'noData' => __('No data available'),
            ];
    @endphp
    {{-- ApexCharts comes with plugins.bundle.js (loaded once by the layout) --}}
    <script>
        (function () {
            const stats = @json($stats);
            const t = @json($chartLabels);
            const isRtl = document.documentElement.getAttribute('dir') === 'rtl';
            const statuses = ['pending', 'approved', 'rejected'];

            // Same digits as the cards (number_format): Western digits, thousands separator.
            // Wrapped in a left-to-right isolate so "4 (66.7%)" doesn't flip inside Arabic text.
            const ltr = (text) => '\u2066' + text + '\u2069';
            const fmt = (n) => ltr(Number(n || 0).toLocaleString('en-US'));
            const pct = (part, total) => ltr((total > 0 ? Math.round(part / total * 1000) / 10 : 0) + '%');
            // Long grade names on two lines instead of being cut
            const wrap = (label, max = 16) => {
                const lines = [''];
                String(label).split(' ').forEach((word) => {
                    const last = lines.length - 1;
                    if (lines[last] && (lines[last] + ' ' + word).length > max) lines.push(word);
                    else lines[last] = lines[last] ? lines[last] + ' ' + word : word;
                });
                return lines.length > 1 ? lines : lines[0];
            };
            // RTL: first item on the right
            const ordered = (list) => (isRtl ? [...list].reverse() : list);

            const css = (name, fallback) =>
                getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
            const palette = () => ({
                text: css('--bs-gray-600', '#6B7280'),
                grid: css('--bs-border-color', '#E5E7EB'),
                primary: css('--bs-primary', '#1B84FF'),
                success: css('--bs-success', '#17C653'),
                warning: css('--bs-warning', '#F6C000'),
                danger: css('--bs-danger', '#F8285A'),
                info: css('--bs-info', '#7239EA'),
                muted: css('--bs-gray-400', '#B5B5C3'),
                card: css('--bs-body-bg', '#FFFFFF'),
                heading: css('--bs-gray-900', '#071437'),
            });

            const charts = {};

            const empty = (el) => {
                el.innerHTML = '<div class="dashboard-chart-empty"><i class="ki-outline ki-chart-simple fs-3x"></i>'
                    + '<span class="fs-6 fw-semibold"></span></div>';
                el.querySelector('span').textContent = t.noData;
            };

            const base = (c, height) => ({
                chart: {
                    height,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    background: 'transparent',
                    animations: { enabled: !window.matchMedia('(prefers-reduced-motion: reduce)').matches },
                },
                theme: { mode: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light' },
                grid: { borderColor: c.grid, strokeDashArray: 4 },
                legend: { position: 'bottom', labels: { colors: c.text }, fontSize: '13px' },
                noData: { text: t.noData, style: { color: c.text } },
                responsive: [{ breakpoint: 576, options: { chart: { height: Math.min(height, 280) } } }],
            });

            const axisLabels = (c) => ({ style: { colors: c.text, fontSize: '12px' } });

            // Bar of counts with "count (x%)" tooltips; distributed = one colour per bar
            const bar = (c, { height, labels, values, colors, name, horizontal = false }) => {
                const total = values.reduce((a, b) => a + b, 0);
                // Vertical bars follow the reading direction; horizontal ones stay biggest on top
                const order = (list) => (horizontal ? list : ordered(list));
                return {
                    ...base(c, height),
                    chart: { ...base(c, height).chart, type: 'bar' },
                    series: [{ name, data: order(values) }],
                    colors: colors ? order(colors) : [c.primary],
                    plotOptions: {
                        bar: { borderRadius: 6, columnWidth: '45%', barHeight: '60%', distributed: !!colors, horizontal },
                    },
                    legend: { show: false },
                    dataLabels: {
                        enabled: true,
                        formatter: (v) => fmt(v),
                        style: { fontSize: '12px', colors: ['#fff'] },
                    },
                    xaxis: {
                        categories: order(labels),
                        labels: { ...axisLabels(c), trim: false, hideOverlappingLabels: false, rotate: 0,
                            formatter: horizontal ? (v) => fmt(v) : undefined },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                    },
                    yaxis: {
                        opposite: isRtl && !horizontal,
                        labels: { ...axisLabels(c), formatter: horizontal ? undefined : (v) => fmt(Math.round(v)), maxWidth: 160 },
                    },
                    tooltip: { y: { formatter: (v) => `${fmt(v)} (${pct(v, total)})` } },
                };
            };

            const donut = (c, { labels, values, colors, totalLabel }) => {
                const total = values.reduce((a, b) => a + b, 0);
                return {
                    ...base(c, 340),
                    chart: { ...base(c, 340).chart, type: 'donut' },
                    labels,
                    series: values,
                    colors,
                    stroke: { width: 2, colors: [c.card] },
                    dataLabels: { enabled: true, formatter: (v) => ltr((Math.round(v * 10) / 10) + '%'), dropShadow: { enabled: false } },
                    legend: {
                        ...base(c, 340).legend,
                        formatter: (label, opts) => `${label}: ${fmt(values[opts.seriesIndex])} (${pct(values[opts.seriesIndex], total)})`,
                    },
                    tooltip: { y: { formatter: (v) => `${fmt(v)} (${pct(v, total)})` } },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '68%',
                                labels: {
                                    show: true,
                                    value: { color: c.heading, fontSize: '24px', fontWeight: 700, formatter: (v) => fmt(v) },
                                    name: { color: c.text },
                                    total: { show: true, label: totalLabel, color: c.text, formatter: () => fmt(total) },
                                },
                            },
                        },
                    },
                };
            };

            const builders = {
                studentsCategoryChart: (c) => {
                    const rows = stats.students_by_category;
                    if (!rows.length || stats.kpis.students === 0) return null;
                    // Many grades read better as horizontal bars
                    const horizontal = rows.length > 6;
                    return bar(c, {
                        height: horizontal ? Math.max(340, rows.length * 38) : 340,
                        labels: rows.map((r) => (horizontal ? r.label : wrap(r.label))),
                        values: rows.map((r) => r.total),
                        name: t.students,
                        horizontal,
                    });
                },
                booksStatusChart: (c) => {
                    if (stats.books.total === 0) return null;
                    return donut(c, {
                        labels: [t.active, t.inactive],
                        values: [stats.books.active, stats.books.inactive],
                        colors: [c.success, c.muted],
                        totalLabel: t.books,
                    });
                },
                bookOrdersChart: (c) => {
                    if (stats.book_orders.total === 0) return null;
                    return bar(c, {
                        height: 340,
                        labels: statuses.map((s) => t[s]),
                        values: statuses.map((s) => stats.book_orders.counts[s]),
                        colors: [c.warning, c.success, c.danger],
                        name: t.orders,
                    });
                },
                enrollmentsChart: (c) => {
                    if (stats.enrollments.total === 0) return null;
                    return donut(c, {
                        labels: statuses.map((s) => t[s]),
                        values: statuses.map((s) => stats.enrollments.counts[s]),
                        colors: [c.warning, c.success, c.danger],
                        totalLabel: t.enrollments,
                    });
                },
                courseContentChart: (c) => {
                    const { classes, sections } = stats.content;
                    if (classes.total + sections.total === 0) return null;
                    const labels = [t.classes, t.sections];
                    return {
                        ...base(c, 280),
                        chart: { ...base(c, 280).chart, type: 'bar', stacked: true },
                        series: [
                            { name: t.active, data: [classes.active, sections.active] },
                            { name: t.inactive, data: [classes.inactive, sections.inactive] },
                        ],
                        colors: [c.primary, c.muted],
                        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '45%' } },
                        dataLabels: { enabled: true, formatter: (v) => (v ? fmt(v) : ''), style: { colors: ['#fff'] } },
                        xaxis: {
                            categories: labels,
                            labels: { ...axisLabels(c), formatter: (v) => fmt(v) },
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                        },
                        yaxis: { opposite: isRtl, labels: axisLabels(c) },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: (v, { dataPointIndex }) => {
                                    const totals = [classes.total, sections.total];
                                    return `${fmt(v)} (${pct(v, totals[dataPointIndex])})`;
                                },
                            },
                        },
                        legend: { ...base(c, 280).legend, show: true },
                    };
                },
            };

            const render = () => {
                if (typeof ApexCharts === 'undefined') {
                    console.error('ApexCharts is not loaded');
                    return;
                }
                const c = palette();
                Object.entries(builders).forEach(([id, build]) => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    charts[id]?.destroy();
                    delete charts[id];
                    el.innerHTML = '';
                    try {
                        const options = build(c);
                        if (!options) return empty(el);
                        charts[id] = new ApexCharts(el, options);
                        charts[id].render();
                    } catch (e) {
                        console.error(`Dashboard chart ${id} failed`, e);
                        empty(el);
                    }
                });
            };

            const start = () => {
                render();
                // Metronic theme switch (light / dark / system) without a reload
                if (typeof KTThemeMode !== 'undefined' && KTThemeMode.on) {
                    KTThemeMode.on('kt.thememode.change', () => setTimeout(render, 50));
                }
            };

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
            else start();
        })();
    </script>
@endcan
@endpush
