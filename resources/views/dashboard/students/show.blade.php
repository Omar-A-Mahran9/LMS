@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="d-flex flex-column gap-5 gap-lg-10">
                <!--begin::Student Card-->
                <div class="card card-flush ">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{ __('Student Details') }}</h2>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('dashboard.students.report.pdf', $student->id) }}"
                                class="btn btn-light-primary">
                                <i class="ki-outline ki-download fs-2"></i> {{ __('Download PDF Report') }}
                            </a>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <label class="form-check-label me-3">{{ __('Block Student') }}</label>
                                <input class="form-check-input {{ $student->block_flag ? 'bg-danger border-danger' : '' }}"
                                    type="checkbox" id="statusSwitch" data-id="{{ $student->id }}"
                                    {{ $student->block_flag ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-10">
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

                        <!-- Courses -->
                        <h4 class="mt-5">{{ __('Enrolled Courses') }}</h4>
                        <ul class="ps-4">
                            @forelse ($student->courses as $course)
                                <li>
                                    {{ $course->title_ar }}
                                    @if (method_exists($course, 'getIsCompletedForStudentAttribute') && $course->is_completed_for_student)
                                        <span class="badge bg-success">{{ __('Completed') }}</span>
                                    @endif
                                </li>
                            @empty
                                <li>{{ __('No courses enrolled.') }}</li>
                            @endforelse
                        </ul>

                        <!-- Classes -->
                        <h4 class="mt-5">{{ __('Enrolled Classes') }}</h4>
                        <ul class="ps-4">
                            @forelse ($student->enrolledClasses as $class)
                                <li>{{ $class->title }} ({{ $class->course->title_ar ?? '-' }})</li>
                            @empty
                                <li>{{ __('No classes enrolled.') }}</li>
                            @endforelse
                        </ul>

                        <!-- Quizzes -->
                        <h4 class="mt-5">{{ __('Quiz Attempts') }}</h4>
                        <ul class="ps-4">
                            @forelse ($student->quizAttempts as $attempt)
                                <li>
                                    {{ $attempt->quiz->title_ar ?? '-' }} —
                                    {{ __('Score') }}: <strong>{{ $attempt->score }}%</strong>
                                </li>
                            @empty
                                <li>{{ __('No quiz attempts.') }}</li>
                            @endforelse
                        </ul>

                        <!-- Homeworks -->
                        <h4 class="mt-5">{{ __('Homework Attempts') }}</h4>
                        <ul class="ps-4">
                            @forelse ($student->homeworks as $homework)
                                <li>
                                    {{ $homework->homework->title_ar ?? '-' }} —
                                    {{ __('Attempted') }}
                                    {{ $student->homeworks->where('home_work_id', $homework->home_work_id)->count() }}
                                    {{ __('time(s)') }}
                                </li>
                            @empty
                                <li>{{ __('No homework attempts.') }}</li>
                            @endforelse
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#statusSwitch').on('change', function() {
            var $checkbox = $(this);
            var studentId = $checkbox.data('id');

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
@endpush
