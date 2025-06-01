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



                            @can('view_videos')
                                <a href="{{ route('dashboard.videos.index') }}"
                                    class="btn btn-primary d-flex align-items-center">
                                    <i class="ki-outline ki-plus fs-2 me-2"></i> {{ __('Add New video') }}
                                </a>
                            @endcan

                            @can('view_quizzes')
                                <a href="{{ route('dashboard.quizzes.index') }}"
                                    class="btn btn-primary d-flex align-items-center">
                                    <i class="ki-outline ki-plus fs-2 me-2"></i> {{ __('Add New quiz') }}
                                </a>
                            @endcan

                            @can('view_homework')
                                <a href="{{ route('dashboard.homeworks.index') }}"
                                    class="btn btn-primary d-flex align-items-center">
                                    <i class="ki-outline ki-plus fs-2 me-2"></i> {{ __('Add New homework') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>

                <div class="card card-flush">

                    <div class="card-body">
                        <!--begin::Row-->
                        <div class="row">
                            <!--begin::Image-->
                            <div class="col-md-2 text-start">
                                <img src="{{ $class->full_image_path }}" alt="Course Image"
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
                                                    <td class="text-end text-dark">{{ $class->title }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Active') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $class->is_active ? __('Yes') : __('No') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Free Preview?') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $class->is_preview ? __('Yes') : __('No') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Created At') }}</td>
                                                    <td class="text-end text-dark">
                                                        {{ $class->created_at->format('Y-m-d') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('Description') }}</td>
                                                    <td class="text-end text-dark">{!! $class->description !!}</td>
                                                </tr>


                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Right Column -->
                                    <div class="col-md-5">

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
