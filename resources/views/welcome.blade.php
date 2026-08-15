@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            @can('view_dashboard')
                {{-- ==================== STAT CARDS ==================== --}}
                <div class="row gy-5 g-xl-8 mb-8">
                    @foreach ([['icon' => 'ki-book', 'color' => 'primary', 'value' => $totalCourses, 'label' => __('Total Courses')], ['icon' => 'ki-people', 'color' => 'success', 'value' => $totalStudents, 'label' => __('Total Students')], ['icon' => 'ki-book-open', 'color' => 'info', 'value' => $totalBooks, 'label' => __('Total Books')], ['icon' => 'ki-list-check', 'color' => 'warning', 'value' => $totalBookOrders, 'label' => __('Book Orders')], ['icon' => 'ki-cart', 'color' => 'danger', 'value' => $totalBookings, 'label' => __('Course Enrollments')]] as $item)
                        <div class="col-sm-6 col-xl-3">
                            <div class="card h-100 shadow-sm border-0 stat-card">
                                <div class="card-body p-6">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="symbol symbol-45px">
                                            <div class="symbol-label bg-light-{{ $item['color'] }}">
                                                <i class="ki-outline {{ $item['icon'] }} fs-2x text-{{ $item['color'] }}"></i>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fs-2x fw-bold text-gray-900 lh-1">{{ $item['value'] }}</div>
                                        </div>
                                    </div>

                                    <div class="fs-6 fw-semibold text-muted mb-4">{{ $item['label'] }}</div>

                                    {{-- Breakdown table per card --}}
                                    @if ($item['label'] == __('Total Students'))
                                        <div class="separator separator-dashed mb-3"></div>
                                        <div class="d-flex flex-column gap-2">
                                            @forelse ($studentByCategory as $row)
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span
                                                        class="fs-7 text-muted">{{ $row->category ?? __('Uncategorized') }}</span>
                                                    <span class="fs-7 fw-bold text-gray-800">{{ $row->total }}</span>
                                                </div>
                                            @empty
                                                <span class="fs-7 text-muted">{{ __('No data') }}</span>
                                            @endforelse
                                        </div>
                                    @endif

                                    @if ($item['label'] == __('Total Courses'))
                                        <div class="separator separator-dashed mb-3"></div>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fs-7 text-muted">{{ __('Class Count') }}</span>
                                                <span
                                                    class="fs-7 fw-bold text-gray-800">{{ $courseContentStats['class_count'] }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fs-7 text-muted">{{ __('Section Count') }}</span>
                                                <span
                                                    class="fs-7 fw-bold text-gray-800">{{ $courseContentStats['section_count'] }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($item['label'] == __('Total Books'))
                                        <div class="separator separator-dashed mb-3"></div>
                                        <div class="d-flex flex-column gap-2">
                                            @forelse ($booksStatus as $status)
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span
                                                        class="badge badge-light-{{ $status->is_active ? 'success' : 'danger' }}">
                                                        {{ $status->is_active ? __('Active') : __('Inactive') }}
                                                    </span>
                                                    <span class="fs-7 fw-bold text-gray-800">{{ $status->total }}</span>
                                                </div>
                                            @empty
                                                <span class="fs-7 text-muted">{{ __('No data') }}</span>
                                            @endforelse
                                        </div>
                                    @endif

                                    @if ($item['label'] == __('Book Orders'))
                                        <div class="separator separator-dashed mb-3"></div>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach (['pending', 'approved', 'rejected'] as $status)
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span
                                                        class="badge badge-light-{{ $status == 'pending' ? 'warning' : ($status == 'approved' ? 'success' : 'danger') }}">
                                                        {{ __(ucfirst($status)) }}
                                                    </span>
                                                    <span
                                                        class="fs-7 fw-bold text-gray-800">{{ $bookOrderStats[$status] ?? 0 }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ==================== STATUS SUMMARY CARDS ==================== --}}
                <div class="row gy-5 g-xl-8">
                    @foreach (['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'] as $status => $color)
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex flex-column align-items-center text-center py-10">

                                    <div class="symbol symbol-60px mb-5">
                                        <div class="symbol-label bg-light-{{ $color }}">
                                            <i class="ki-outline ki-check-circle text-{{ $color }} fs-2qx"></i>
                                        </div>
                                    </div>

                                    <h5 class="text-gray-900 fw-bold mb-6 text-uppercase">
                                        {{ __(ucfirst($status)) }}
                                    </h5>

                                    <div class="w-100 px-6">
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <span class="fs-6 text-muted">{{ __('Enrollments') }}</span>
                                            <span class="fs-3 fw-bold text-gray-900">{{ $enrollments[$status] ?? 0 }}</span>
                                        </div>

                                        <div class="separator separator-dashed my-3"></div>

                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <span class="fs-6 text-muted">{{ __('Book Orders') }}</span>
                                            <span class="fs-3 fw-bold text-gray-900">{{ $bookOrderStats[$status] ?? 0 }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- ==================== WELCOME SCREEN ==================== --}}
                <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 70vh;">
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
