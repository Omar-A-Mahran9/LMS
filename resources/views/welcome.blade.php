@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">

            <!-- LMS General Statistics -->
            <div class="row gy-5 g-xl-10 mb-5">
                @foreach ([['icon' => 'ki-book', 'value' => $totalCourses, 'label' => __('Total Courses')], ['icon' => 'ki-people', 'value' => $totalStudents, 'label' => __('Total Students')], ['icon' => 'ki-book-open', 'value' => $totalBooks, 'label' => __('Total Books')], ['icon' => 'ki-list-check', 'value' => $totalBookOrders, 'label' => __('Book Orders')], ['icon' => 'ki-cart', 'value' => $totalBookings, 'label' => __('Course Enrollments')]] as $item)
                    <div class="col-sm-4 col-xl-3">
                        <div class="card h-lg-40">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <!-- Main Stat -->
                                    <div class="text-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="ki-outline {{ $item['icon'] }} fs-2hx text-gray-600"></i>
                                            <div class="fs-3x fw-bold text-gray-800">{{ $item['value'] }}</div>
                                        </div>
                                        <div class="text-muted">{{ $item['label'] }}</div>
                                    </div>

                                    <!-- Conditional Table: example for 'Total Students' -->
                                    @if ($item['label'] == __('Total Students'))
                                        <table class="table table-sm table-borderless ms-5 w-auto">

                                            <tbody>
                                                @foreach ($studentByCategory as $row)
                                                    <tr>
                                                        <td class="fw-bold text-muted">
                                                            {{ $row->category ?? __('Uncategorized') }}</td>
                                                        <td class="text-end text-gray-800">{{ $row->total }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif

                                    <!-- Table for Courses: classes & sections -->
                                    @if ($item['label'] == __('Total Courses'))
                                        <table class="table table-sm table-borderless ms-5 w-auto">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold text-muted">{{ __('Class Count') }}</td>
                                                    <td class="text-end text-gray-800">
                                                        {{ $courseContentStats['class_count'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold text-muted">{{ __('Section Count') }}</td>
                                                    <td class="text-end text-gray-800">
                                                        {{ $courseContentStats['section_count'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif


                                    <!-- Table for Books: active/inactive -->
                                    @if ($item['label'] == __('Total Books'))
                                        <table class="table table-sm table-borderless ms-5 w-auto">
                                            <tbody>
                                                @foreach ($booksStatus as $status)
                                                    <tr>
                                                        <td>
                                                            <span
                                                                class="badge {{ $status->is_active ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $status->is_active ? __('Active') : __('Inactive') }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $status->total }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif

                                    @if ($item['label'] == __('Book Orders'))
                                        <table class="table table-sm table-borderless ms-5 w-auto">

                                            <tbody>
                                                @foreach (['pending', 'approved', 'rejected'] as $status)
                                                    <tr>
                                                        <td>
                                                            <span
                                                                class="badge 
                            {{ $status == 'pending' ? 'bg-warning' : ($status == 'approved' ? 'bg-success' : 'bg-danger') }}">
                                                                {{ __(ucfirst($status)) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end text-gray-800">
                                                            {{ $bookOrderStats[$status] ?? 0 }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif

                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row gy-5 g-xl-10 mt-4 ">
                @foreach (['pending', 'approved', 'rejected'] as $status)
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center py-10">

                                <div
                                    class="symbol symbol-50px mb-4 {{ $status == 'pending' ? 'bg-warning' : ($status == 'approved' ? 'bg-success' : 'bg-danger') }}">
                                    <i class="ki-outline ki-check-circle text-white fs-2"></i>
                                </div>

                                <h5 class="text-gray-800 fw-bold mb-2">
                                    {{ __(ucfirst($status)) }}
                                </h5>

                                <div class="d-flex flex-column align-items-center">
                                    <div class="fs-4 text-gray-700 mb-1">{{ __('Enrollments') }}:</div>
                                    <div class="fs-2 fw-bold text-gray-800">{{ $enrollments[$status] ?? 0 }}</div>
                                </div>

                                <div class="separator my-4 w-50"></div>

                                <div class="d-flex flex-column align-items-center">
                                    <div class="fs-4 text-gray-700 mb-1">{{ __('Book Orders') }}:</div>
                                    <div class="fs-2 fw-bold text-gray-800">{{ $bookOrderStats[$status] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>


            <!-- Monthly Earnings -->
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Monthly Course Earnings (Last 6 Months)') }}</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach ($monthlyEarnings as $month)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $month->month }}</span>
                                <strong>{{ number_format($month->total, 2) }} SAR</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Top Courses by Student Count -->
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Top Courses by Enrolled Students') }}</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach ($topCourses as $course)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $course->title_en }} ({{ $course->students_count }} {{ __('Students') }})</span>
                                <strong>{{ number_format($course->earnings, 2) }} SAR</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection
