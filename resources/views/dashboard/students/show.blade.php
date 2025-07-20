@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="row gy-5 g-xl-10">
                <div class="col-12">
                    <div class="card card-flush shadow-sm">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                <h2 class="fw-bold">{{ __('Student Report') }}</h2>
                            </div>
                            <div class="d-flex gap-3">
                                <a href="{{ route('dashboard.students.report.pdf', $student->id) }}"
                                    class="btn btn-light-primary">
                                    <i class="ki-outline ki-download fs-2"></i> {{ __('Download PDF Report') }}
                                </a>
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <label class="form-check-label me-3">{{ __('Block Student') }}</label>
                                    <input
                                        class="form-check-input {{ $student->block_flag ? 'bg-danger border-danger' : '' }}"
                                        type="checkbox" id="statusSwitch" data-id="{{ $student->id }}"
                                        {{ $student->block_flag ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Basic Info -->
                            <div class="d-flex align-items-center gap-10 mb-7">
                                <div class="symbol symbol-100px">
                                    <img src="{{ $student->full_image_path }}" class="object-fit-cover" alt="Student Image">
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1">{{ $student->first_name }} {{ $student->middle_name }}
                                        {{ $student->last_name }}</h4>
                                    <div class="text-muted">{{ $student->email }} - {{ $student->phone }}</div>
                                    <div class="text-muted">{{ __('Parent:') }} {{ $student->parent_phone }}
                                        ({{ $student->parent_job }})</div>
                                    <div class="text-muted">
                                        {{ __('Gov:') }} {{ $student->government->name ?? '-' }} |
                                        {{ __('Category:') }} {{ $student->category->name ?? '-' }}
                                    </div>
                                    <div class="text-muted">{{ __('Joined:') }}
                                        {{ $student->created_at->format('Y-m-d') }}</div>
                                </div>
                            </div>

                            <div class="row g-5">
                                <!-- Enrolled Courses -->
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center gap-3">
                                                <i class="ki-outline ki-book-open fs-2hx text-primary"></i>
                                                <div>
                                                    <div class="fs-3 fw-bold text-gray-800">
                                                        {{ $student->courses->count() }}</div>
                                                    <div class="text-muted">{{ __('Enrolled Courses') }}</div>
                                                </div>
                                            </div>
                                            <hr>
                                            <ul class="list-unstyled mt-3">
                                                @foreach ($student->courses as $course)
                                                    <li class="d-flex justify-content-between">
                                                        <span>{{ $course->title_ar }}</span>
                                                        @if ($course->is_completed_for_student ?? false)
                                                            <span class="badge bg-success">{{ __('Completed') }}</span>
                                                        @endif
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
                                            <div class="d-flex align-items-center gap-3">
                                                <i class="ki-outline ki-clipboard fs-2hx text-info"></i>
                                                <div>
                                                    <div class="fs-3 fw-bold text-gray-800">
                                                        {{ $student->enrolledClasses->count() }}</div>
                                                    <div class="text-muted">{{ __('Enrolled Classes') }}</div>
                                                </div>
                                            </div>
                                            <hr>
                                            <ul class="list-unstyled mt-3">
                                                @foreach ($student->enrolledClasses as $class)
                                                    <li>{{ $class->title }} <small
                                                            class="text-muted">({{ $class->course->title_ar ?? '-' }})</small>
                                                    </li>
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
                                                    <div class="fs-3 fw-bold text-gray-800">
                                                        {{ $student->quizAttempts->count() }}</div>
                                                    <div class="text-muted">{{ __('Quiz Attempts') }}</div>
                                                </div>
                                            </div>
                                            <hr>
                                            <ul class="list-unstyled mt-3">
                                                @foreach ($student->quizAttempts as $attempt)
                                                    <li>
                                                        {{ $attempt->quiz->title_ar ?? '-' }}:
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
                                                        {{ $attempt->homework->title_ar ?? '-' }}:
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
                                                    <div class="fs-3 fw-bold text-gray-800">
                                                        {{ $student->watchedVideos->count() }}</div>
                                                    <div class="text-muted">{{ __('Watched Videos') }}</div>
                                                </div>
                                            </div>
                                            <hr>
                                            <ul class="list-unstyled mt-3">
                                                @foreach ($student->watchedVideos->take(5) as $video)
                                                    <li>
                                                        {{ $video->title_ar ?? '-' }} -
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
