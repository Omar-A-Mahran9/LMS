@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="d-flex flex-column gap-5 gap-lg-10">
                <!--begin::Course Card-->
                <div class="card card-flush">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            <h2>{{ __('Course Details') }}</h2>
                        </div>
                        <div class="d-flex gap-2">


                            @if ($course->is_class == 1)
                                @can('view_classes')
                                    <a href="{{ route('dashboard.classes.index', ['course_id' => $course->id]) }}"
                                        class="btn btn-primary d-flex align-items-center">
                                        <i class="ki-outline ki-book fs-2"></i> {{ __('Classes') }}
                                    </a>
                                @endcan
                            @else
                                @can('view_sections')
                                    <a href="{{ route('dashboard.sections.index', ['course_id' => $course->id]) }}"
                                        class="btn btn-primary d-flex align-items-center">
                                        <i class="ki-outline ki-note fs-2"></i> {{ __('sections') }}
                                    </a>
                                @endcan
                            @endif

                        </div>
                    </div>
                </div>
                <div class="card card-flush">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="text-gray-600 fw-semibold">{{ __('Enrolled Students') }}</div>
                                <div class="fs-2 fw-bold text-dark">{{ $course->enrollments_count }}</div>
                            </div>

                            @if ($course->is_class)
                                <div class="col-md-4">
                                    <div class="text-gray-600 fw-semibold">{{ __('Total Classes') }}</div>
                                    <div class="fs-2 fw-bold text-dark">{{ $course->classes_count }}</div>
                                </div>
                            @else
                                <div class="col-md-4">
                                    <div class="text-gray-600 fw-semibold">{{ __('Total Sections') }}</div>
                                    <div class="fs-2 fw-bold text-dark">{{ $course->sections_count }}</div>
                                </div>
                            @endif

                            <div class="col-md-4">
                                <div class="text-gray-600 fw-semibold">{{ __('Course Views') }}</div>
                                <div class="fs-2 fw-bold text-dark">{{ $course->views }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-flush">

                    <div class="card-body">
                        <!--begin::Row-->
                        <div class="row">
                            <!--begin::Image-->
                            <div class="col-md-2 text-start">
                                <img src="{{ $course->full_image_path }}" alt="Course Image"
                                    class="img-fluid rounded w-150px h-150px object-fit-cover" />
                            </div>
                            <!--end::Image-->

                            <!--begin::Details-->
                            <div class="col-md-10">
                                <div class="row gap-5 align-items-start justify-content-center">
                                    <!-- Left Column -->
                                    <div class="col-md-5">
                                        <table class="table table-row-bordered align-middle">
                                            <tbody class="fw-semibold text-gray-600">
                                                <tr>
                                                    <td class="text-muted">{{ __('Title') }}</td>
                                                    <td class="text-end text-dark">{{ $course->title }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Instructor') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ optional($course->instructor)->name ?? '-' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Category') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ optional($course->category)->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Subcategories') }}</td>
                                                    <td class="text-end text-dark">
                                                        @forelse ($course->subCategories as $subcategory)
                                                            <span
                                                                class="badge badge-light-primary fw-bold me-1">{{ $subcategory->name }}</span>
                                                        @empty
                                                            {{ __('No subcategories') }}
                                                        @endforelse
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td class="text-muted">{{ __('Show in Home') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->show_in_home ? __('Yes') : __('No') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Featured') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->featured ? __('Yes') : __('No') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Certificate Available?') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->certificate_available ? __('Yes') : __('No') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Enrollment Open') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->is_enrollment_open ? __('Yes') : __('No') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Enrolled Students') }}</td>
                                                    <td class="text-end text-dark">{{ $course->enrolled_students }}</td>
                                                </tr>

                                                <tr>
                                                    <td class="text-muted">{{ __('Created At') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->created_at->format('Y-m-d') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('views') }}</td>
                                                    <td class="text-end text-dark">{{ $course->views }}</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Right Column -->
                                    <div class="col-md-5">
                                        <table class="table table-row-bordered align-middle">
                                            <tbody class="fw-semibold text-gray-600">
                                                <tr>
                                                    <td class="text-muted">{{ __('Description') }}</td>
                                                    <td class="text-end text-dark">{!! $course->description !!}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Note') }}</td>
                                                    <td class="text-end text-dark">{!! $course->note !!}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Price') }}</td>
                                                    <td class="text-end text-dark">{{ number_format($course->price, 2) }}
                                                        EGP</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Has Discount') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->have_discount ? __('Yes') : __('No') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Discount %') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->discount_percentage ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Is Free Course?') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->is_free ? __('Yes') : __('No') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Start Date') }}</td>
                                                    <td class="text-end text-dark">{{ $course->start_date ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('End Date') }}</td>
                                                    <td class="text-end text-dark">{{ $course->end_date ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Max Students') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $course->max_students ?? __('Unlimited') }}
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            <!--end::Details-->
                        </div>
                        <!--end::Row-->
                    </div>
                </div>
                <!--end::Course Card-->


            </div>
        </div>
    </div>
@endsection
