@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">

            <div class="row gy-5 g-xl-10">
                <!-- Student Overview Card -->
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
                            <!-- Student Info -->
                            <div class="d-flex align-items-center gap-10 mb-7">
                                <div class="symbol symbol-100px">
                                    <img src="{{ $student->full_image_path }}" class="object-fit-cover" alt="image">
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1">
                                        {{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}
                                    </h4>
                                    <div class="text-muted">{{ $student->email }} - {{ $student->phone }}</div>
                                    <div class="text-muted">
                                        {{ __('Parent:') }} {{ $student->parent_phone }} ({{ $student->parent_job }})
                                    </div>
                                    <div class="text-muted">
                                        {{ __('Gov:') }} {{ $student->government->name ?? '-' }} |
                                        {{ __('Category:') }} {{ $student->category->name ?? '-' }}
                                    </div>
                                    <div class="text-muted">{{ __('Joined:') }}
                                        {{ $student->created_at->format('Y-m-d') }}</div>
                                </div>
                            </div>

                            <!-- Stats Cards -->
                            <div class="row g-5">
                                <!-- Enrolled Courses -->
                                <x-dashboard.student-stat icon="ki-book-open" title="{{ __('Enrolled Courses') }}"
                                    count="{{ $student->courses->count() }}" color="primary" :items="$student->courses"
                                    itemLabel="title_ar" completedAttr="is_completed_for_student" />

                                <!-- Enrolled Classes -->
                                <x-dashboard.student-stat icon="ki-clipboard" title="{{ __('Enrolled Classes') }}"
                                    count="{{ $student->enrolledClasses->count() }}" color="info" :items="$student->enrolledClasses"
                                    :itemSlot="true">
                                    @foreach ($student->enrolledClasses as $class)
                                        <li>{{ $class->title }} <small
                                                class="text-muted">({{ $class->course->title_ar ?? '-' }})</small></li>
                                    @endforeach
                                </x-dashboard.student-stat>

                                <!-- Quiz Attempts -->
                                <x-dashboard.student-stat icon="ki-check-circle" title="{{ __('Quiz Attempts') }}"
                                    count="{{ $student->quizAttempts->count() }}" color="warning" :items="$student->quizAttempts"
                                    :itemSlot="true">
                                    @foreach ($student->quizAttempts as $attempt)
                                        <li>{{ $attempt->quiz->title_ar ?? '-' }}: <strong>{{ $attempt->score }}%</strong>
                                        </li>
                                    @endforeach
                                </x-dashboard.student-stat>

                                <!-- Homework Attempts -->
                                <x-dashboard.student-stat icon="ki-pencil" title="{{ __('Homework Attempts') }}"
                                    count="{{ $student->homeWorkAttempts->count() }}" color="danger" :items="$student->homeWorkAttempts"
                                    :itemSlot="true">
                                    @foreach ($student->homeWorkAttempts->groupBy('home_work_id') as $group)
                                        <li>
                                            {{ $group->first()->homework->title_ar ?? '-' }}:
                                            {{ __('Attempted') }} {{ $group->count() }} {{ __('time(s)') }}
                                        </li>
                                    @endforeach
                                </x-dashboard.student-stat>

                                <!-- Watched Videos -->
                                <x-dashboard.student-stat icon="ki-video" title="{{ __('Watched Videos') }}"
                                    count="{{ $student->watchedVideos->count() }}" color="success" :items="$student->watchedVideos->take(5)"
                                    :itemSlot="true">
                                    @foreach ($student->watchedVideos->take(5) as $video)
                                        <li>
                                            {{ $video->title_ar ?? '-' }} -
                                            {{ $video->pivot->is_completed ? __('Completed') : __('In Progress') }}
                                        </li>
                                    @endforeach
                                </x-dashboard.student-stat>
                            </div>
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
