@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <!-- Student Card -->
            <div class="card card-flush mb-10">
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ __('Student Details') }}</h2>
                    </div>
                    <div
                        class="form-check form-switch form-check-custom form-check-solid d-flex align-items-center justify-content-between">
                        <label class="form-check-label me-5">
                            {{ __('Block Student') }}
                        </label>
                        <input class="form-check-input {{ $student->block_flag ? 'bg-danger border-danger' : '' }}"
                            type="checkbox" id="statusSwitch" data-id="{{ $student->id }}"
                            {{ $student->block_flag ? 'checked' : '' }}>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="{{ $student->full_image_path }}" alt="Student Image"
                                class="img-fluid rounded w-150px h-150px object-fit-cover" />
                        </div>
                        <div class="col-md-10">
                            <table class="table table-row-bordered align-middle">
                                <tbody class="fw-semibold text-gray-600">
                                    <tr>
                                        <td class="text-muted">{{ __('Full Name') }}</td>
                                        <td class="text-end">{{ $student->first_name }} {{ $student->middle_name }}
                                            {{ $student->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Email') }}</td>
                                        <td class="text-end">{{ $student->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Phone') }}</td>
                                        <td class="text-end">{{ $student->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Parent phone') }}</td>
                                        <td class="text-end">{{ $student->parent_phone }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Parent job') }}</td>
                                        <td class="text-end">{{ $student->parent_job }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Gender') }}</td>
                                        <td class="text-end">{{ __(ucfirst($student->gender)) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Government') }}</td>
                                        <td class="text-end">{{ $student->government->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Category') }}</td>
                                        <td class="text-end">{{ $student->category->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Created At') }}</td>
                                        <td class="text-end">{{ $student->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Student Card -->

            <!-- Student Report Cards -->
            <div class="card card-flush">
                <div class="card-header">
                    <h3 class="fw-bold">{{ __('Student Report') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row g-5">

                        <!-- Enrolled Courses -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="ki-outline ki-book-open fs-2hx text-primary"></i>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800">
                                                {{ $student->courses->where('is_class', '!=', 1)->count() }}
                                            </div>
                                            <div class="text-muted">{{ __('Enrolled Courses') }}</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <ul class="list-unstyled mt-3">
                                        @php $i = 1; @endphp
                                        @foreach ($student->courses as $course)
                                            @if (empty($course->is_class) || $course->is_class == 0)
                                                <li class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>{{ $i++ }} - {{ $course->title }}</span>

                                                    <div class="d-flex align-items-center gap-2">
                                                        @if ($course->is_completed_for_student ?? false)
                                                            <span class="badge bg-success">{{ __('Completed') }}</span>
                                                        @endif

                                                        @php
                                                            $status = $course->is_active ? 'active' : 'inactive';
                                                            $color = match ($status) {
                                                                'active' => 'success',
                                                                'inactive' => 'secondary',
                                                                default => 'light',
                                                            };
                                                        @endphp

                                                        <!-- Status dropdown -->
                                                        <div>
                                                            <a href="#"
                                                                class="badge badge-light-{{ $color }} fw-bold border rounded"
                                                                data-kt-menu-trigger="click"
                                                                data-kt-menu-placement="bottom-end">
                                                                {{ __(ucfirst($status)) }}
                                                            </a>
                                                            <div class="menu menu-sub menu-sub-dropdown"
                                                                data-kt-menu="true">
                                                                <div class="menu-item px-3">
                                                                    <a href="javascript:;"
                                                                        class="menu-link px-3 change-course-status"
                                                                        data-id="{{ $course->id }}" data-status="active">
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
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>

                            </div>
                        </div>

                        <!-- Enrolled Classes -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="ki-outline ki-clipboard fs-2hx text-info"></i>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800">
                                                {{ $student->courses->where('is_class', '=', 1)->count() }}
                                            </div>
                                            <div class="text-muted">{{ __('Enrolled Classes') }}</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <ul class="list-unstyled mt-3">
                                        @foreach ($student->courses as $course)
                                            @if ($course->is_class)
                                                <li class="d-flex justify-content-between">
                                                    <span>{{ $course->title }}</span>
                                                    @if ($course->is_completed_for_student ?? false)
                                                        <span class="badge bg-success">{{ __('Completed') }}</span>
                                                    @endif
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Quiz Attempts -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="ki-outline ki-check-circle fs-2hx text-warning"></i>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800">{{ $student->quizAttempts->count() }}
                                            </div>
                                            <div class="text-muted">{{ __('Quiz Attempts') }}</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <ul class="list-unstyled mt-3">
                                        @foreach ($student->quizAttempts as $attempt)
                                            <li>{{ $attempt->quiz->title ?? '-' }}:
                                                <strong>{{ $attempt->score }}%</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Homework Attempts -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="ki-outline ki-pencil fs-2hx text-danger"></i>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800">
                                                {{ $student->homeWorkAttempts->count() }}</div>
                                            <div class="text-muted">{{ __('Homework Attempts') }}</div>
                                        </div>
                                    </div>
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

                        <!-- Watched Videos -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="ki-outline ki-video fs-2hx text-success"></i>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800">{{ $student->watchedVideos->count() }}
                                            </div>
                                            <div class="text-muted">{{ __('Watched Videos') }}</div>
                                        </div>
                                    </div>
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

                    </div> <!-- /.row -->
                </div>
            </div>
            <!-- End Student Report Cards -->
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#statusSwitch').on('change', function() {
            const $checkbox = $(this);
            const studentId = $checkbox.data('id');
            $.ajax({
                url: `/dashboard/students/blocking/${studentId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function() {
                    if ($checkbox.is(':checked')) {
                        $checkbox.addClass('bg-danger border-danger');
                        toastr.success(`{{ __('Student blocked successfully') }}`);
                    } else {
                        $checkbox.removeClass('bg-danger border-danger');
                        toastr.success(`{{ __('Student unblocked successfully') }}`);
                    }
                },
                error: function() {
                    $checkbox.prop('checked', !$checkbox.is(':checked'));
                    toastr.error('{{ __('Something went wrong') }}');
                }
            });
        });
    </script>

    <script>
        const langActive = "{{ __('Active') }}";
        const langInactive = "{{ __('Inactive') }}";

        $(document).on('click', '.change-course-status', function() {
            const $link = $(this);
            const courseId = $link.data('id');
            const studentId = $link.data('sudent_id');

            const newStatus = $link.data('status');

            $.ajax({
                url: '{{ route('dashboard.enrollments.toggleStatus') }}',
                type: 'POST',
                data: {
                    student_id: studentId,
                    course_id: courseId,
                    status: 'active', // or 'inactive'
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success('Status updated');
                },
                error: function() {
                    toastr.error('Error updating status');
                }
            });

        });
    </script>
@endpush
