@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            <div class="card card-flush shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        <h2 class="fw-bold">{{ __('Student Details') }}</h2>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="{{ route('dashboard.students.report.pdf', $student->id) }}" class="btn btn-light-primary">
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
                    <!-- Student Info -->
                    <div class="d-flex flex-wrap align-items-center gap-10 mb-10">
                        <div class="symbol symbol-150px">
                            <img src="{{ $student->full_image_path }}" class="object-fit-cover" alt="image">
                        </div>
                        <div class="flex-grow-1">
                            <div class="row row-cols-1 row-cols-md-2 g-5">
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Full Name') }}</div>
                                    <div>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Email') }}</div>
                                    <div>{{ $student->email }}</div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Phone') }}</div>
                                    <div>{{ $student->phone }}</div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Parent Phone') }}</div>
                                    <div>{{ $student->parent_phone }}</div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Parent Job') }}</div>
                                    <div>{{ $student->parent_job }}</div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Gender') }}</div>
                                    <div>{{ ucfirst($student->gender) }}</div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Government') }}</div>
                                    <div>{{ $student->government->name ?? '-' }}</div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Category') }}</div>
                                    <div>{{ $student->category->name ?? '-' }}</div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-muted">{{ __('Created At') }}</div>
                                    <div>{{ $student->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses -->
                    <h4 class="fw-bold text-gray-700 border-bottom pb-2 mb-4">{{ __('Enrolled Courses') }}</h4>
                    <div class="mb-6">
                        @forelse ($student->courses as $course)
                            <div class="d-flex align-items-center mb-2">
                                <i class="ki-outline ki-book-open fs-2 me-2 text-primary"></i>
                                <span>{{ $course->title_ar }}</span>
                                @if (method_exists($course, 'getIsCompletedForStudentAttribute') && $course->is_completed_for_student)
                                    <span class="badge badge-light-success ms-3">{{ __('Completed') }}</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No courses enrolled.') }}</p>
                        @endforelse
                    </div>

                    <!-- Classes -->
                    <h4 class="fw-bold text-gray-700 border-bottom pb-2 mb-4">{{ __('Enrolled Classes') }}</h4>
                    <div class="mb-6">
                        @forelse ($student->enrolledClasses as $class)
                            <div class="d-flex align-items-center mb-2">
                                <i class="ki-outline ki-clipboard fs-2 me-2 text-info"></i>
                                <span>{{ $class->title }} ({{ $class->course->title_ar ?? '-' }})</span>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No classes enrolled.') }}</p>
                        @endforelse
                    </div>

                    <!-- Quizzes -->
                    <h4 class="fw-bold text-gray-700 border-bottom pb-2 mb-4">{{ __('Quiz Attempts') }}</h4>
                    <div class="mb-6">
                        @forelse ($student->quizAttempts as $attempt)
                            <div class="d-flex align-items-center mb-2">
                                <i class="ki-outline ki-check-circle fs-2 me-2 text-warning"></i>
                                <span>{{ $attempt->quiz->title_ar ?? '-' }} – <strong>{{ __('Score') }}:</strong>
                                    {{ $attempt->score }}%</span>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No quiz attempts.') }}</p>
                        @endforelse
                    </div>

                    <!-- Homework -->
                    <h4 class="fw-bold text-gray-700 border-bottom pb-2 mb-4">{{ __('Homework Attempts') }}</h4>
                    <div class="mb-6">
                        @forelse ($student->homeWorkAttempts ?? [] as $homework)
                            <div class="d-flex align-items-center mb-2">
                                <i class="ki-outline ki-pencil fs-2 me-2 text-danger"></i>
                                <span>{{ $homework->homework->title_ar ?? '-' }} –
                                    {{ __('Attempted') }}
                                    {{ $student->homeWorkAttempts->where('home_work_id', $homework->home_work_id)->count() }}
                                    {{ __('time(s)') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No homework attempts.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

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
@endpush
