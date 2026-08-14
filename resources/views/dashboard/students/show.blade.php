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
                        <label class="form-check-label me-5">{{ __('Block Student') }}</label>
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

            <!-- Report Cards -->
            <div class="card card-flush">
                <div class="card-header">
                    <h3 class="fw-bold">{{ __('Student Report') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row g-5">

                        <!-- Enrolled Courses / Subscriptions -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 report-card border rounded-3">
                                <div class="card-body d-flex flex-column">
                                    <div class="report-card-head d-flex align-items-center gap-3 mb-4">
                                        <div class="symbol symbol-45px">
                                            <span class="symbol-label bg-light-primary">
                                                <i class="ki-outline ki-wallet fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800 lh-1">
                                                {{ $student->courses->where('is_class', '!=', 1)->count() }}
                                            </div>
                                            <div class="text-muted fs-7">{{ __('Enrolled Courses') }}</div>
                                        </div>
                                    </div>

                                    <div class="report-search position-relative mb-4">
                                        <i
                                            class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                        <input type="text" class="form-control form-control-sm ps-10 report-search-input"
                                            placeholder="{{ __('Search courses...') }}" autocomplete="off">
                                    </div>

                                    <ul class="list-unstyled report-scroll flex-grow-1 m-0"
                                        style="max-height: 560px; overflow-y: auto;">
                                        @forelse ($student->courses->where('is_class', '!=', 1) as $i => $course)
@php
    $pivot = $course->pivot;
    $status = $pivot->is_active ? 'active' : 'inactive';
    $color = $pivot->is_active ? 'success' : 'danger';

    $subStatusColor = match ($pivot->status) {
        'approved' => 'success',
        'pending' => 'warning',
        'rejected' => 'danger',
        default => 'light',
    };
    $subStatusLabel = match ($pivot->status) {
        'approved' => __('مقبول'),
        'pending' => __('قيد الانتظار'),
        'rejected' => __('مرفوض'),
        default => $pivot->status,
    };
    $paymentLabel = match ($pivot->payment_type) {
        'wallet_transfer' => __('تحويل من المحفظة'),
        'pay_in_center' => __('الدفع في المركز'),
        'contact_with_support' => __('التواصل مع الدعم'),
        default => $pivot->payment_type,
    };
@endphp
                                            <li class="report-item d-flex justify-content-between align-items-start py-3 border-bottom border-dashed">
                                                <div>
                                                    <div class="fw-semibold text-gray-800">{{ $i + 1 }} - {{ $course->title }}
                                                        @if ($course->is_completed_for_student ?? false)
<span class="badge bg-success ms-1">{{ __('Completed') }}</span>
@endif
                                                    </div>
                                                    <div class="text-muted fs-8 mt-1">
                                                        <span class="badge badge-light-{{ $subStatusColor }}">{{ $subStatusLabel }}</span>
                                                        {{ $paymentLabel }}
                                                        • {{ \Carbon\Carbon::parse($pivot->created_at)->format('Y-m-d') }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="#" class="badge badge-light-{{ $color }} fw-bold border rounded"
                                                       data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
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
                                            </li>
                                        @empty
                                            <li class="report-empty-initial text-muted text-center py-5">{{ __('No enrolled courses') }}</li>
@endforelse
                                    </ul>
                                    <div class="report-empty text-muted text-center py-5 d-none">{{ __('No matches') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Enrolled Classes -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 report-card border rounded-3">
                                <div class="card-body d-flex flex-column">
                                    <div class="report-card-head d-flex align-items-center gap-3 mb-4">
                                        <div class="symbol symbol-45px">
                                            <span class="symbol-label bg-light-info">
                                                <i class="ki-outline ki-clipboard fs-2 text-info"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800 lh-1">
                                                {{ $student->courses->where('is_class', 1)->count() }}
                                            </div>
                                            <div class="text-muted fs-7">{{ __('Enrolled Classes') }}</div>
                                        </div>
                                    </div>

                                    <div class="report-search position-relative mb-4">
                                        <i class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                        <input type="text" class="form-control form-control-sm ps-10 report-search-input"
                                            placeholder="{{ __('Search classes...') }}" autocomplete="off">
                                    </div>

                                    <ul class="list-unstyled report-scroll flex-grow-1 m-0" style="max-height: 400px; overflow-y: auto;">
                                        @forelse ($student->courses->where('is_class', 1) as $course)
                                            @php
                                                $pivot = $course->pivot;
                                                $status = $pivot->is_active ? 'active' : 'inactive';
                                                $color = $pivot->is_active ? 'success' : 'danger';

                                                // Optional: status from pivot (e.g., pending/approved/rejected)
                                                $enrollStatus = $pivot->status ?? null;
                                                $statusColor = match ($enrollStatus) {
                                                    'approved' => 'success',
                                                    'pending' => 'warning',
                                                    'rejected' => 'danger',
                                                    default => 'light',
                                                };
                                            @endphp
                                            <li
                                                class="report-item d-flex justify-content-between align-items-center py-3 border-bottom border-dashed">
                                                <div class="text-gray-800">
                                                    {{ $course->title }}
                                                    @if ($enrollStatus)
                                                        <span class="badge bg-{{ $statusColor }} ms-2">
                                                            {{ __(ucfirst($enrollStatus)) }}
                                                        </span>
                                                    @endif
                                                </div>

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
                                        @empty
                                            <li class="report-empty-initial text-muted text-center py-5">
                                                {{ __('No enrolled classes') }}</li>
                                        @endforelse
                                    </ul>
                                    <div class="report-empty text-muted text-center py-5 d-none">{{ __('No matches') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quiz / Exam Attempts -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 report-card border rounded-3">
                                <div class="card-body d-flex flex-column">
                                    <div class="report-card-head d-flex align-items-center gap-3 mb-4">
                                        <div class="symbol symbol-45px">
                                            <span class="symbol-label bg-light-warning">
                                                <i class="ki-outline ki-check-circle fs-2 text-warning"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800 lh-1">
                                                {{ $student->quizAttempts->count() }}
                                            </div>
                                            <div class="text-muted fs-7">{{ __('Quiz Attempts') }}</div>
                                        </div>
                                    </div>

                                    <div class="report-search position-relative mb-4">
                                        <i
                                            class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                        <input type="text"
                                            class="form-control form-control-sm ps-10 report-search-input"
                                            placeholder="{{ __('Search quizzes...') }}" autocomplete="off">
                                    </div>

                                    <ul class="list-unstyled report-scroll flex-grow-1 m-0"
                                        style="max-height: 380px; overflow-y: auto;">
                                        @forelse ($student->quizAttempts as $attempt)
                                            @php
                                                $full = $attempt->quiz->full_score ?? 0;
                                                $percent =
                                                    $full > 0 && !is_null($attempt->score)
                                                        ? round(($attempt->score / $full) * 100)
                                                        : null;
                                                $quizTitle = $attempt->quiz->title ?? '-';
                                            @endphp
                                            <li
                                                class="report-item d-flex justify-content-between align-items-center py-3 border-bottom border-dashed">
                                                <span class="text-gray-800">{{ $quizTitle }}</span>
                                                <strong>
                                                    @if (is_null($attempt->score))
                                                        <span
                                                            class="badge bg-light-secondary text-secondary">{{ __('Not Attempted') }}</span>
                                                    @else
                                                        {{ $attempt->score }} / {{ $full ?: '-' }}
                                                        @if (!is_null($percent))
                                                            <span
                                                                class="badge bg-{{ $percent >= 50 ? 'success' : 'danger' }} ms-1">
                                                                {{ $percent }}%
                                                            </span>
                                                        @endif
                                                    @endif
                                                </strong>
                                            </li>
                                        @empty
                                            <li class="report-empty-initial text-muted text-center py-5">
                                                {{ __('No quiz attempts') }}</li>
                                        @endforelse
                                    </ul>
                                    <div class="report-empty text-muted text-center py-5 d-none">{{ __('No matches') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Homework Attempts -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 report-card border rounded-3">
                                <div class="card-body d-flex flex-column">
                                    <div class="report-card-head d-flex align-items-center gap-3 mb-4">
                                        <div class="symbol symbol-45px">
                                            <span class="symbol-label bg-light-danger">
                                                <i class="ki-outline ki-pencil fs-2 text-danger"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800 lh-1">
                                                {{ $student->homeWorkAttempts->count() }}</div>
                                            <div class="text-muted fs-7">{{ __('Homework Attempts') }}</div>
                                        </div>
                                    </div>

                                    <div class="report-search position-relative mb-4">
                                        <i
                                            class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                        <input type="text"
                                            class="form-control form-control-sm ps-10 report-search-input"
                                            placeholder="{{ __('Search homework...') }}" autocomplete="off">
                                    </div>

                                    <ul class="list-unstyled report-scroll flex-grow-1 m-0"
                                        style="max-height: 320px; overflow-y: auto;">
                                        @forelse ($student->homeWorkAttempts as $attempt)
                                            @php
                                                $hwTitle = $attempt->homework->title ?? '-';
                                            @endphp
                                            <li class="report-item py-2 border-bottom border-dashed text-gray-800">
                                                {{ $hwTitle }}: {{ __('Attempted') }}
                                                {{ $student->homeWorkAttempts->where('home_work_id', $attempt->home_work_id)->count() }}
                                                {{ __('time(s)') }}
                                            </li>
                                        @empty
                                            <li class="report-empty-initial text-muted text-center py-5">
                                                {{ __('No homework attempts') }}</li>
                                        @endforelse
                                    </ul>
                                    <div class="report-empty text-muted text-center py-5 d-none">{{ __('No matches') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Watched Videos with progress -->
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 report-card border rounded-3">
                                <div class="card-body d-flex flex-column">
                                    <div class="report-card-head d-flex align-items-center gap-3 mb-4">
                                        <div class="symbol symbol-45px">
                                            <span class="symbol-label bg-light-success">
                                                <i class="ki-outline ki-video fs-2 text-success"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fs-3 fw-bold text-gray-800 lh-1">
                                                {{ $student->watchedVideos->count() }}</div>
                                            <div class="text-muted fs-7">{{ __('Watched Videos') }}</div>
                                        </div>
                                    </div>

                                    <div class="report-search position-relative mb-4">
                                        <i
                                            class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                        <input type="text"
                                            class="form-control form-control-sm ps-10 report-search-input"
                                            placeholder="{{ __('Search videos...') }}" autocomplete="off">
                                    </div>

                                    <ul class="list-unstyled report-scroll flex-grow-1 m-0"
                                        style="max-height: 720px; overflow-y: auto;">
                                        @forelse ($student->watchedVideos as $video)
                                            @php
                                                $duration = $video->duration_seconds ?: 1;
                                                $watchSeconds = (int) ($video->pivot->watch_seconds ?? 0);
                                                $percent = min(100, round(($watchSeconds / $duration) * 100));
                                                $lastAt = $video->pivot->last_watched_at
                                                    ? \Carbon\Carbon::parse(
                                                        $video->pivot->last_watched_at,
                                                    )->diffForHumans()
                                                    : '-';
                                            @endphp
                                            <li class="report-item py-3 border-bottom border-dashed">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-gray-800 fw-semibold">{{ $video->title }}</span>
                                                    @if ($video->pivot->is_completed)
                                                        <span class="badge bg-success">{{ __('Completed') }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-light-warning text-warning">{{ $percent }}%</span>
                                                    @endif
                                                </div>
                                                <div class="progress h-6px">
                                                    <div class="progress-bar bg-primary"
                                                        style="width: {{ $percent }}%"></div>
                                                </div>
                                                <div class="text-muted fs-8 mt-1">
                                                    {{ __('Stopped at') }} {{ gmdate('i:s', $watchSeconds) }}
                                                    • {{ __('Last watched') }} {{ $lastAt }}
                                                    • {{ __('Views') }}: {{ $video->pivot->views }}
                                                </div>
                                            </li>
                                        @empty
                                            <li class="report-empty-initial text-muted text-center py-5">
                                                {{ __('No watched videos') }}</li>
                                        @endforelse
                                    </ul>
                                    <div class="report-empty text-muted text-center py-5 d-none">{{ __('No matches') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .report-card {
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        }

        .report-card .symbol-label {
            border-radius: 10px;
        }

        .report-search-input {
            border-radius: 6px;
        }

        .report-item {
            transition: background-color .15s ease;
        }

        .report-item:hover {
            background-color: #f9f9f9;
        }

        .report-item:last-child {
            border-bottom: 0 !important;
        }

        .report-scroll {
            padding-inline-end: 6px;
        }

        .report-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .report-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .report-scroll::-webkit-scrollbar-thumb {
            background-color: #d9d9e3;
            border-radius: 10px;
        }

        .report-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #b5b5c3;
        }
    </style>
@endpush

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
                    $checkbox.toggleClass('bg-danger border-danger', $checkbox.is(':checked'));
                    toastr.success($checkbox.is(':checked') ?
                        '{{ __('Student blocked successfully') }}' :
                        '{{ __('Student unblocked successfully') }}');
                },
                error: function() {
                    $checkbox.prop('checked', !$checkbox.is(':checked'));
                    toastr.error('{{ __('Something went wrong') }}');
                }
            });
        });

        $(document).on('click', '.change-course-status', function() {
            const $link = $(this);
            const courseId = $link.data('id');
            const studentId = $link.data('student-id');
            const newStatus = $link.data('status');

            $.ajax({
                url: '{{ route('dashboard.enrollments.toggleStatus') }}',
                type: 'POST',
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
                        .removeClass('badge-light-success badge-light-danger')
                        .addClass('badge-light-' + (newStatus === 'active' ? 'success' : 'danger'))
                        .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                },
                error: function() {
                    toastr.error('{{ __('Error updating status') }}');
                }
            });
        });

        // Per-card search. Matches against the item's own visible text (no separate
        // data attribute to keep in sync), so it always reflects exactly what's on screen.
        (function() {
            document.querySelectorAll('.report-search-input').forEach(function(input) {
                input.addEventListener('keyup', function() {
                    const term = input.value.trim().toLowerCase();
                    const card = input.closest('.report-card');
                    if (!card) return;

                    const list = card.querySelector('.report-scroll');
                    const emptyState = card.querySelector('.report-empty');
                    const items = list ? list.querySelectorAll('.report-item') : [];

                    let visibleCount = 0;
                    items.forEach(function(item) {
                        const text = (item.textContent || '').toLowerCase();
                        const matches = text.indexOf(term) !== -1;
                        item.style.display = matches ? '' : 'none';
                        if (matches) visibleCount++;
                    });

                    if (emptyState) {
                        const show = term !== '' && visibleCount === 0 && items.length > 0;
                        emptyState.classList.toggle('d-none', !show);
                    }
                });
            });
        })();
    </script>
@endpush
