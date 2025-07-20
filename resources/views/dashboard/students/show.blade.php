@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            {{-- Student Info Card --}}
            <x-students.student-card :student="$student" />

            {{-- Report Cards --}}
            <div class="card card-flush">
                <div class="card-header">
                    <h3 class="fw-bold">{{ __('Student Report') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row g-5">

                        {{-- Enrolled Courses --}}
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <x-ki-icon icon="book-open" class="text-primary" />
                                    <div class="fs-3 fw-bold text-gray-800">
                                        {{ $student->courses->where('is_class', '!=', 1)->count() }}
                                    </div>
                                    <div class="text-muted">{{ __('Enrolled Courses') }}</div>
                                    <hr>
                                    <ul class="list-unstyled mt-3">
                                        @foreach ($student->courses->where('is_class', '!=', 1) as $i => $course)
@php
    $pivot = $course->pivot;
    $status = $pivot->is_active ? 'active' : 'inactive';
    $color = $pivot->is_active ? 'success' : 'secondary';
@endphp
                                        <li class="d-flex justify-content-between align-items-center mb-2">
                                            <span>{{ $i + 1 }} - {{ $course->title }}</span>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($course->is_completed_for_student ?? false)
<span class="badge bg-success">{{ __('Completed') }}</span>
@endif
                                                <div>
                                                    <a href="#"
                                                       class="badge badge-light-{{ $color }} fw-bold border rounded"
                                                       data-kt-menu-trigger="click"
                                                       data-kt-menu-placement="bottom-end">
                                                       {{ ucfirst($status) }}
                                                    </a>
                                                    <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                                                        <div class="menu-item px-3">
                                                            <a href="javascript:;" class="menu-link px-3 change-course-status"
                                                               data-id="{{ $course->id }}"
                                                               data-student-id="{{ $student->id }}"
                                                               data-status="active">
                                                               {{ __('Active') }}
                                                            </a>
                                                        </div>
                                                        <div class="menu-item px-3">
                                                            <a href="javascript:;" class="menu-link px-3 change-course-status"
                                                               data-id="{{ $course->id }}"
                                                               data-student-id="{{ $student->id }}"
                                                               data-status="inactive">
                                                               {{ __('Inactive') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
@endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Enrolled Classes --}}
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <x-ki-icon icon="clipboard" class="text-info" />
                                <div class="fs-3 fw-bold text-gray-800">
                                    {{ $student->courses->where('is_class', '=', 1)->count() }}
                                </div>
                                <div class="text-muted">{{ __('Enrolled Classes') }}</div>
                                <hr>
                                <ul class="list-unstyled mt-3">
                                    @foreach ($student->courses->where('is_class', '=', 1) as $course)
                                            @php
                                                $pivot = $course->pivot;
                                                $status = $pivot->is_active ? 'active' : 'inactive';
                                                $color = $pivot->is_active ? 'success' : 'secondary';
                                            @endphp
                                            <li class="d-flex justify-content-between align-items-center mb-2">
                                                <span>{{ $course->title }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($course->is_completed_for_student ?? false)
                                                        <span class="badge bg-success">{{ __('Completed') }}</span>
                                                    @endif
                                                    <div>
                                                        <a href="#"
                                                            class="badge badge-light-{{ $color }} fw-bold border rounded"
                                                            data-kt-menu-trigger="click"
                                                            data-kt-menu-placement="bottom-end">
                                                            {{ ucfirst($status) }}
                                                        </a>
                                                        <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                                                            <div class="menu-item px-3">
                                                                <a href="javascript:;"
                                                                    class="menu-link px-3 change-course-status"
                                                                    data-id="{{ $course->id }}"
                                                                    data-student-id="{{ $student->id }}"
                                                                    data-status="active">
                                                                    {{ __('Active') }}
                                                                </a>
                                                            </div>
                                                            <div class="menu-item px-3">
                                                                <a href="javascript:;"
                                                                    class="menu-link px-3 change-course-status"
                                                                    data-id="{{ $course->id }}"
                                                                    data-student-id="{{ $student->id }}"
                                                                    data-status="inactive">
                                                                    {{ __('Inactive') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Quiz Attempts --}}
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <x-ki-icon icon="check-circle" class="text-warning" />
                                    <div class="fs-3 fw-bold text-gray-800">{{ $student->quizAttempts->count() }}</div>
                                    <div class="text-muted">{{ __('Quiz Attempts') }}</div>
                                    <hr>
                                    <ul class="list-unstyled mt-3">
                                        @foreach ($student->quizAttempts as $attempt)
                                            <li>{{ $attempt->quiz->title ?? '-' }}:
                                                <strong>{{ $attempt->score }}%</strong></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Homework Attempts --}}
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <x-ki-icon icon="pencil" class="text-danger" />
                                    <div class="fs-3 fw-bold text-gray-800">{{ $student->homeWorkAttempts->count() }}</div>
                                    <div class="text-muted">{{ __('Homework Attempts') }}</div>
                                    <hr>
                                    <ul class="list-unstyled mt-3">
                                        @foreach ($student->homeWorkAttempts->take(5) as $attempt)
                                            <li>
                                                {{ $attempt->homework->title ?? '-' }}:
                                                {{ __('Attempted') }}
                                                {{ $student->homeWorkAttempts->where('home_work_id', $attempt->home_work_id)->count() }}
                                                {{ __('time(s)') }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Watched Videos --}}
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <x-ki-icon icon="video" class="text-success" />
                                    <div class="fs-3 fw-bold text-gray-800">{{ $student->watchedVideos->count() }}</div>
                                    <div class="text-muted">{{ __('Watched Videos') }}</div>
                                    <hr>
                                    <ul class="list-unstyled mt-3">
                                        @foreach ($student->watchedVideos->take(5) as $video)
                                            <li>
                                                {{ $video->title ?? '-' }} -
                                                {{ $video->pivot->is_completed ? __('Completed') : __('In Progress') }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div> {{-- /.row --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.change-course-status', function() {
            const $link = $(this);
            const courseId = $link.data('id');
            const studentId = $link.data('student-id');
            const newStatus = $link.data('status');

            $.ajax({
                url: '{{ route('dashboard.enrollments.toggleStatus') }}',
                method: 'POST',
                data: {
                    student_id: studentId,
                    course_id: courseId,
                    status: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    toastr.success('{{ __('Status updated successfully') }}');
                    const badge = $link.closest('.menu-sub-dropdown').siblings('a');
                    badge
                        .removeClass('badge-light-success badge-light-secondary')
                        .addClass('badge-light-' + (newStatus === 'active' ? 'success' : 'secondary'))
                        .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                },
                error: function() {
                    toastr.error('{{ __('Error updating status') }}');
                }
            });
        });
    </script>
@endpush
