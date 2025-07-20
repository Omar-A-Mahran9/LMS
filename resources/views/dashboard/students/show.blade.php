@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <!-- Student Info -->
            <div class="card card-flush mb-10">
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ __('Student Details') }}</h2>
                    </div>
                    <div
                        class="form-check form-switch form-check-custom form-check-solid d-flex align-items-center justify-content-between">
                        <label class="form-check-label me-5">{{ __('Block Student') }}</label>
                        <input class="form-check-input {{ $student->block_flag ? 'bg-danger border-danger' : '' }}"
                            type="checkbox" id="statusSwitch" data-id="{{ $student->id }}"
                            {{ $student->block_flag ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="{{ $student->full_image_path }}"
                                class="img-fluid rounded w-150px h-150px object-fit-cover" />
                        </div>
                        <div class="col-md-10">
                            <table class="table table-row-bordered align-middle">
                                <tbody class="fw-semibold text-gray-600">
                                    <tr>
                                        <td class="text-muted">{{ __('Full Name') }}</td>
                                        <td class="text-end">{{ $student->full_name }}</td>
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
                                        <td class="text-muted">{{ __('Parent Phone') }}</td>
                                        <td class="text-end">{{ $student->parent_phone }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Parent Job') }}</td>
                                        <td class="text-end">{{ $student->parent_job }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('Gender') }}</td>
                                        <td class="text-end">{{ ucfirst($student->gender) }}</td>
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

            <!-- Report -->
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
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <i class="ki-outline ki-book-open fs-2hx text-primary"></i>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800">
                                                {{ $student->courses->where('is_class', '!=', 1)->count() }}</div>
                                            <div class="text-muted">{{ __('Enrolled Courses') }}</div>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled mt-3">
                                        @foreach ($student->courses->where('is_class', '!=', 1) as $course)
@php
    $pivot = $course->pivot;
    $status = $pivot->is_active ? 'active' : 'inactive';
    $color = $pivot->is_active ? 'success' : 'danger';
    $enrollStatus = $pivot->status ?? null;
    $statusColor = match ($enrollStatus) {
        'approved' => 'success',
        'pending' => 'warning',
        'rejected' => 'danger',
        default => 'secondary',
    };
@endphp
                                        <li class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                {{ $course->title }}

<span class="badge bg-{{ $statusColor }} ms-2">{{ ucfirst($enrollStatus) }}</span>

                                            </div>
                                            <div class="d-flex gap-2 align-items-center">
                                                @if ($course->is_completed_for_student ?? false)
<span class="badge bg-success">{{ __('Completed') }}</span>
@endif
                                                <a href="#" class="badge badge-light-{{ $color }} border" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">{{ ucfirst($status) }}</a>
                                                <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                                                    <div class="menu-item px-3">
                                                        <a href="javascript:;" class="menu-link px-3 change-course-status" data-id="{{ $course->id }}" data-student-id="{{ $student->id }}" data-status="active">{{ __('Active') }}</a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a href="javascript:;" class="menu-link px-3 change-course-status" data-id="{{ $course->id }}" data-student-id="{{ $student->id }}" data-status="inactive">{{ __('Inactive') }}</a>
                                                    </div>
                                                </div>

                                                <a href="#" class="badge badge-light-{{ $statusColor }} border" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">{{ ucfirst($enrollStatus) }}</a>
                                                <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                                                    <div class="menu-item px-3"><a href="javascript:;" class="menu-link px-3 change-enrollment-status" data-id="{{ $course->id }}" data-student-id="{{ $student->id }}" data-status="approved">{{ __('Approved') }}</a></div>
                                                    <div class="menu-item px-3"><a href="javascript:;" class="menu-link px-3 change-enrollment-status" data-id="{{ $course->id }}" data-student-id="{{ $student->id }}" data-status="pending">{{ __('Pending') }}</a></div>
                                                    <div class="menu-item px-3"><a href="javascript:;" class="menu-link px-3 change-enrollment-status" data-id="{{ $course->id }}" data-student-id="{{ $student->id }}" data-status="rejected">{{ __('Rejected') }}</a></div>
                                                </div>
                                            </div>
                                        </li>
@endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Enrolled Classes -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <i class="ki-outline ki-clipboard fs-2hx text-info"></i>
                                    <div>
                                        <div class="fs-3 fw-bold text-gray-800">{{ $student->courses->where('is_class', 1)->count() }}</div>
                                        <div class="text-muted">{{ __('Enrolled Classes') }}</div>
                                    </div>
                                </div>
                                <ul class="list-unstyled mt-3">
                                    @foreach ($student->courses->where('is_class', 1) as $course)
                                            @php
                                                $pivot = $course->pivot;
                                                $status = $pivot->is_active ? 'active' : 'inactive';
                                                $color = $pivot->is_active ? 'success' : 'danger';
                                                $enrollStatus = $pivot->status ?? null;
                                                $statusColor = match ($enrollStatus) {
                                                    'approved' => 'success',
                                                    'pending' => 'warning',
                                                    'rejected' => 'danger',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <li class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    {{ $course->title }}
                                                    @if ($enrollStatus)
                                                        <span
                                                            class="badge bg-{{ $statusColor }} ms-2">{{ ucfirst($enrollStatus) }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    @if ($course->is_completed_for_student ?? false)
                                                        <span class="badge bg-success">{{ __('Completed') }}</span>
                                                    @endif

                                                    <a href="#" class="badge badge-light-{{ $color }} border"
                                                        data-kt-menu-trigger="click"
                                                        data-kt-menu-placement="bottom-end">{{ ucfirst($status) }}</a>
                                                    <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                                                        <div class="menu-item px-3"><a href="javascript:;"
                                                                class="menu-link px-3 change-course-status"
                                                                data-id="{{ $course->id }}"
                                                                data-student-id="{{ $student->id }}"
                                                                data-status="active">{{ __('Active') }}</a></div>
                                                        <div class="menu-item px-3"><a href="javascript:;"
                                                                class="menu-link px-3 change-course-status"
                                                                data-id="{{ $course->id }}"
                                                                data-student-id="{{ $student->id }}"
                                                                data-status="inactive">{{ __('Inactive') }}</a></div>
                                                    </div>

                                                    <a href="#" class="badge badge-light-{{ $statusColor }} border"
                                                        data-kt-menu-trigger="click"
                                                        data-kt-menu-placement="bottom-end">{{ ucfirst($enrollStatus) }}</a>
                                                    <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                                                        <div class="menu-item px-3"><a href="javascript:;"
                                                                class="menu-link px-3 change-enrollment-status"
                                                                data-id="{{ $course->id }}"
                                                                data-student-id="{{ $student->id }}"
                                                                data-status="approved">{{ __('Approved') }}</a></div>
                                                        <div class="menu-item px-3"><a href="javascript:;"
                                                                class="menu-link px-3 change-enrollment-status"
                                                                data-id="{{ $course->id }}"
                                                                data-student-id="{{ $student->id }}"
                                                                data-status="pending">{{ __('Pending') }}</a></div>
                                                        <div class="menu-item px-3"><a href="javascript:;"
                                                                class="menu-link px-3 change-enrollment-status"
                                                                data-id="{{ $course->id }}"
                                                                data-student-id="{{ $student->id }}"
                                                                data-status="rejected">{{ __('Rejected') }}</a></div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Quiz Attempts / Homework / Videos... -->
                        <!-- Keep other cards as you had them -->

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#statusSwitch').on('change', function() {
            const id = $(this).data('id');
            $.ajax({
                url: `/dashboard/students/blocking/${id}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: () => {
                    $(this).toggleClass('bg-danger border-danger', this.checked);
                    toastr.success(this.checked ? '{{ __('Student blocked successfully') }}' :
                        '{{ __('Student unblocked successfully') }}');
                },
                error: () => {
                    $(this).prop('checked', !this.checked);
                    toastr.error('{{ __('Something went wrong') }}');
                }
            });
        });

        $(document).on('click', '.change-course-status', function() {
            const $btn = $(this);
            $.post('{{ route('dashboard.enrollments.toggleStatus') }}', {
                _token: '{{ csrf_token() }}',
                student_id: $btn.data('student-id'),
                course_id: $btn.data('id'),
                status: $btn.data('status')
            }, () => location.reload());
        });

        // $(document).on('click', '.change-enrollment-status', function() {
        //     const $btn = $(this);
        //     $.post('{{ route('dashboard.enrollments.toggleEnrollmentStatus') }}', {
        //         _token: '{{ csrf_token() }}',
        //         student_id: $btn.data('student-id'),
        //         course_id: $btn.data('id'),
        //         enrollment_status: $btn.data('status')
        //     }, () => location.reload());
        // });
    </script>
@endpush
