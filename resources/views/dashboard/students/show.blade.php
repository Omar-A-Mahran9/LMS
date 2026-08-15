@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            {{-- ==================== STUDENT CARD ==================== --}}
            <div class="card card-flush mb-10 student-card">
                <div class="card-body p-0">
                    <div class="student-hero d-flex flex-wrap flex-md-nowrap align-items-center gap-6 p-8">

                        <div class="symbol symbol-100px symbol-circle flex-shrink-0 border border-3 border-white shadow-sm">
                            <img src="{{ $student->full_image_path }}" alt="{{ __('Student Image') }}"
                                class="object-fit-cover" />
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                <h2 class="fw-bold text-gray-900 m-0">
                                    {{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}
                                </h2>
                                <span class="badge badge-light-{{ $student->block_flag ? 'danger' : 'success' }}">
                                    {{ $student->block_flag ? __('Blocked') : __('Active') }}
                                </span>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @if ($student->category->name ?? false)
                                    <span class="badge badge-light-primary">
                                        <i class="ki-outline ki-category fs-6 me-1"></i>{{ $student->category->name }}
                                    </span>
                                @endif
                                @if ($student->government->name ?? false)
                                    <span class="badge badge-light-info">
                                        <i class="ki-outline ki-geolocation fs-6 me-1"></i>{{ $student->government->name }}
                                    </span>
                                @endif
                                <span class="badge badge-light-secondary">
                                    <i
                                        class="ki-outline ki-profile-circle fs-6 me-1"></i>{{ __(ucfirst($student->gender)) }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="block-toggle d-flex flex-column align-items-center align-items-md-end gap-2 flex-shrink-0">
                            <label class="fw-semibold text-muted fs-7" for="statusSwitch">{{ __('Block Student') }}</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input {{ $student->block_flag ? 'bg-danger border-danger' : '' }}"
                                    type="checkbox" id="statusSwitch" data-id="{{ $student->id }}"
                                    {{ $student->block_flag ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <div class="separator"></div>

                    <div class="p-8 pt-6">
                        <div class="row g-6">
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="info-item">
                                    <div class="info-label"><i class="ki-outline ki-sms fs-5 me-1"></i>{{ __('Email') }}
                                    </div>
                                    <div class="info-value">{{ $student->email ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="info-item">
                                    <div class="info-label"><i
                                            class="ki-outline ki-phone fs-5 me-1"></i>{{ __('Phone') }}</div>
                                    <div class="info-value">{{ $student->phone ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="info-item">
                                    <div class="info-label"><i
                                            class="ki-outline ki-phone fs-5 me-1"></i>{{ __('Parent phone') }}</div>
                                    <div class="info-value">{{ $student->parent_phone ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="info-item">
                                    <div class="info-label"><i
                                            class="ki-outline ki-briefcase fs-5 me-1"></i>{{ __('Parent job') }}</div>
                                    <div class="info-value">{{ $student->parent_job ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="info-item">
                                    <div class="info-label"><i
                                            class="ki-outline ki-geolocation fs-5 me-1"></i>{{ __('Government') }}</div>
                                    <div class="info-value">{{ $student->government->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="info-item">
                                    <div class="info-label"><i
                                            class="ki-outline ki-category fs-5 me-1"></i>{{ __('Category') }}</div>
                                    <div class="info-value">{{ $student->category->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="info-item">
                                    <div class="info-label"><i
                                            class="ki-outline ki-profile-circle fs-5 me-1"></i>{{ __('Gender') }}</div>
                                    <div class="info-value">{{ __(ucfirst($student->gender)) }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="info-item">
                                    <div class="info-label"><i
                                            class="ki-outline ki-calendar fs-5 me-1"></i>{{ __('Created At') }}</div>
                                    <div class="info-value">{{ $student->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ==================== END STUDENT CARD ==================== --}}

            <!--
                            Perf note: the "Watched Videos" card below reads $video->course and $video->class
                            per video. If the student has many watched videos, eager-load these in the
                            controller to avoid N+1 queries:
                            $student->load(['watchedVideos.course', 'watchedVideos.class']);
                        -->

            {{-- ==================== REPORT SECTION ==================== --}}
            <div class="d-flex flex-wrap flex-stack mb-6">
                <div>
                    <h3 class="fw-bold text-gray-900 mb-1">{{ __('Student Report') }}</h3>
                    <div class="fs-7 text-muted">{{ __('Activity, progress and results across the platform') }}</div>
                </div>
            </div>

            <div class="row g-5">

                {{-- Enrolled Courses / Subscriptions --}}
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 report-card border-0 shadow-sm rounded-3">
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
                                <input type="text" class="form-control form-control-sm ps-10 pe-10 report-search-input"
                                    placeholder="بحث في الكورسات..." autocomplete="off" aria-label="بحث في الكورسات">
                                <button type="button"
                                    class="report-search-clear btn btn-icon btn-sm position-absolute top-50 end-0 translate-middle-y me-1 d-none">
                                    <i class="ki-outline ki-cross fs-6 text-gray-500"></i>
                                </button>
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
                                    <li class="report-item d-flex justify-content-between align-items-start py-3 border-bottom">
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
                                    <li class="report-empty-initial text-muted text-center py-5">لا يوجد كورسات مشترك بها</li>
@endforelse
                            </ul>
                            <div class="report-empty text-muted text-center py-5 d-none">لا توجد نتائج مطابقة</div>
                        </div>
                    </div>
                </div>

                {{-- Enrolled Classes --}}
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 report-card border-0 shadow-sm rounded-3">
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
                                <input type="text" class="form-control form-control-sm ps-10 pe-10 report-search-input"
                                    placeholder="بحث في الحصص..." autocomplete="off" aria-label="بحث في الحصص">
                                <button type="button" class="report-search-clear btn btn-icon btn-sm position-absolute top-50 end-0 translate-middle-y me-1 d-none">
                                    <i class="ki-outline ki-cross fs-6 text-gray-500"></i>
                                </button>
                            </div>

                            <ul class="list-unstyled report-scroll flex-grow-1 m-0" style="max-height: 400px; overflow-y: auto;">
                                @forelse ($student->courses->where('is_class', 1) as $course)
                                    @php
                                        $pivot = $course->pivot;
                                        $status = $pivot->is_active ? 'active' : 'inactive';
                                        $color = $pivot->is_active ? 'success' : 'danger';

                                        $enrollStatus = $pivot->status ?? null;
                                        $statusColor = match ($enrollStatus) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'light',
                                        };
                                    @endphp
                                    <li
                                        class="report-item d-flex justify-content-between align-items-center py-3 border-bottom">
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
                                                    data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                    {{ ucfirst($status) }}
                                                </a>
                                                <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                                                    <div class="menu-item px-3">
                                                        <a href="javascript:;" class="menu-link px-3 change-course-status"
                                                            data-id="{{ $course->id }}"
                                                            data-student-id="{{ $student->id }}" data-status="active">
                                                            {{ __('Active') }}
                                                        </a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a href="javascript:;" class="menu-link px-3 change-course-status"
                                                            data-id="{{ $course->id }}"
                                                            data-student-id="{{ $student->id }}" data-status="inactive">
                                                            {{ __('Inactive') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="report-empty-initial text-muted text-center py-5">لا يوجد حصص مشترك بها</li>
                                @endforelse
                            </ul>
                            <div class="report-empty text-muted text-center py-5 d-none">لا توجد نتائج مطابقة</div>
                        </div>
                    </div>
                </div>

                {{-- Quiz / Exam Attempts --}}
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 report-card border-0 shadow-sm rounded-3">
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
                                <input type="text" class="form-control form-control-sm ps-10 pe-10 report-search-input"
                                    placeholder="بحث في الامتحانات..." autocomplete="off" aria-label="بحث في الامتحانات">
                                <button type="button"
                                    class="report-search-clear btn btn-icon btn-sm position-absolute top-50 end-0 translate-middle-y me-1 d-none">
                                    <i class="ki-outline ki-cross fs-6 text-gray-500"></i>
                                </button>
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
                                        class="report-item d-flex justify-content-between align-items-center py-3 border-bottom">
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
                                    <li class="report-empty-initial text-muted text-center py-5">لا توجد محاولات امتحانات
                                    </li>
                                @endforelse
                            </ul>
                            <div class="report-empty text-muted text-center py-5 d-none">لا توجد نتائج مطابقة</div>
                        </div>
                    </div>
                </div>

                {{-- Homework Attempts --}}
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 report-card border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex flex-column">
                            <div class="report-card-head d-flex align-items-center gap-3 mb-4">
                                <div class="symbol symbol-45px">
                                    <span class="symbol-label bg-light-danger">
                                        <i class="ki-outline ki-pencil fs-2 text-danger"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-3 fw-bold text-gray-800 lh-1">{{ $student->homeWorkAttempts->count() }}
                                    </div>
                                    <div class="text-muted fs-7">{{ __('Homework Attempts') }}</div>
                                </div>
                            </div>

                            <div class="report-search position-relative mb-4">
                                <i
                                    class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                <input type="text" class="form-control form-control-sm ps-10 pe-10 report-search-input"
                                    placeholder="بحث في الواجبات..." autocomplete="off" aria-label="بحث في الواجبات">
                                <button type="button"
                                    class="report-search-clear btn btn-icon btn-sm position-absolute top-50 end-0 translate-middle-y me-1 d-none">
                                    <i class="ki-outline ki-cross fs-6 text-gray-500"></i>
                                </button>
                            </div>

                            <ul class="list-unstyled report-scroll flex-grow-1 m-0"
                                style="max-height: 320px; overflow-y: auto;">
                                @forelse ($student->homeWorkAttempts as $attempt)
                                    @php
                                        $hwTitle = $attempt->homework->title ?? '-';
                                    @endphp
                                    <li class="report-item py-2 border-bottom text-gray-800">
                                        {{ $hwTitle }}: {{ __('Attempted') }}
                                        {{ $student->homeWorkAttempts->where('home_work_id', $attempt->home_work_id)->count() }}
                                        {{ __('time(s)') }}
                                    </li>
                                @empty
                                    <li class="report-empty-initial text-muted text-center py-5">لا توجد محاولات واجبات
                                    </li>
                                @endforelse
                            </ul>
                            <div class="report-empty text-muted text-center py-5 d-none">لا توجد نتائج مطابقة</div>
                        </div>
                    </div>
                </div>

                {{--
                    Watched Videos, split by parent type.
                    A video's course_video is either tied to a Course (course_id set) or a
                    CourseClass (class_id set) - never both - so we split the already
                    eager-loaded $student->watchedVideos collection in PHP rather than
                    issuing two separate queries.
                --}}
                @php
                    $courseWatchedVideos = $student->watchedVideos->whereNull('class_id')->values();
                    $classWatchedVideos = $student->watchedVideos->whereNotNull('class_id')->values();
                @endphp

                {{-- Watched Videos - Courses --}}
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 report-card border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex flex-column">
                            <div class="report-card-head d-flex align-items-center gap-3 mb-4">
                                <div class="symbol symbol-45px">
                                    <span class="symbol-label bg-light-success">
                                        <i class="ki-outline ki-video fs-2 text-success"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-3 fw-bold text-gray-800 lh-1">{{ $courseWatchedVideos->count() }}</div>
                                    <div class="text-muted fs-7">{{ __('Watched Videos - Courses') }}</div>
                                </div>
                            </div>

                            <div class="report-search position-relative mb-4">
                                <i
                                    class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                <input type="text" class="form-control form-control-sm ps-10 pe-10 report-search-input"
                                    placeholder="بحث في فيديوهات الكورسات..." autocomplete="off"
                                    aria-label="بحث في فيديوهات الكورسات">
                                <button type="button"
                                    class="report-search-clear btn btn-icon btn-sm position-absolute top-50 end-0 translate-middle-y me-1 d-none">
                                    <i class="ki-outline ki-cross fs-6 text-gray-500"></i>
                                </button>
                            </div>

                            <ul class="list-unstyled report-scroll flex-grow-1 m-0"
                                style="max-height: 560px; overflow-y: auto;">
                                @forelse ($courseWatchedVideos as $video)
                                    @php
                                        $duration = $video->duration_seconds ?: 1;
                                        $watchSeconds = (int) ($video->pivot->watch_seconds ?? 0);
                                        $percent = min(100, round(($watchSeconds / $duration) * 100));
                                        $lastAt = $video->pivot->last_watched_at
                                            ? \Carbon\Carbon::parse($video->pivot->last_watched_at)->diffForHumans()
                                            : '-';
                                        $parentLabel = $video->course->title ?? null;
                                    @endphp
                                    <li class="report-item py-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-gray-800 fw-semibold">{{ $video->title }}</span>
                                            @if ($video->pivot->is_completed)
                                                <span class="badge bg-success">{{ __('Completed') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-light-warning text-warning">{{ $percent }}%</span>
                                            @endif
                                        </div>
                                        @if ($parentLabel)
                                            <div class="text-muted fs-8 mb-1">
                                                <i class="ki-outline ki-wallet fs-7 me-1"></i>{{ $parentLabel }}
                                            </div>
                                        @endif
                                        <div class="progress h-6px">
                                            <div class="progress-bar bg-primary" style="width: {{ $percent }}%">
                                            </div>
                                        </div>
                                        <div class="text-muted fs-8 mt-1">
                                            توقف عند {{ gmdate('i:s', $watchSeconds) }}
                                            • آخر مشاهدة {{ $lastAt }}
                                            • المشاهدات: {{ $video->pivot->views }}
                                        </div>
                                    </li>
                                @empty
                                    <li class="report-empty-initial text-muted text-center py-5">لا توجد فيديوهات كورسات
                                        تمت مشاهدتها</li>
                                @endforelse
                            </ul>
                            <div class="report-empty text-muted text-center py-5 d-none">لا توجد نتائج مطابقة</div>
                        </div>
                    </div>
                </div>

                {{-- Watched Videos - Classes --}}
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 report-card border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex flex-column">
                            <div class="report-card-head d-flex align-items-center gap-3 mb-4">
                                <div class="symbol symbol-45px">
                                    <span class="symbol-label bg-light-info">
                                        <i class="ki-outline ki-video fs-2 text-info"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fs-3 fw-bold text-gray-800 lh-1">{{ $classWatchedVideos->count() }}</div>
                                    <div class="text-muted fs-7">{{ __('Watched Videos - Classes') }}</div>
                                </div>
                            </div>

                            <div class="report-search position-relative mb-4">
                                <i
                                    class="ki-outline ki-magnifier fs-4 position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                                <input type="text" class="form-control form-control-sm ps-10 pe-10 report-search-input"
                                    placeholder="بحث في فيديوهات الحصص..." autocomplete="off"
                                    aria-label="بحث في فيديوهات الحصص">
                                <button type="button"
                                    class="report-search-clear btn btn-icon btn-sm position-absolute top-50 end-0 translate-middle-y me-1 d-none">
                                    <i class="ki-outline ki-cross fs-6 text-gray-500"></i>
                                </button>
                            </div>

                            <ul class="list-unstyled report-scroll flex-grow-1 m-0"
                                style="max-height: 560px; overflow-y: auto;">
                                @forelse ($classWatchedVideos as $video)
                                    @php
                                        $duration = $video->duration_seconds ?: 1;
                                        $watchSeconds = (int) ($video->pivot->watch_seconds ?? 0);
                                        $percent = min(100, round(($watchSeconds / $duration) * 100));
                                        $lastAt = $video->pivot->last_watched_at
                                            ? \Carbon\Carbon::parse($video->pivot->last_watched_at)->diffForHumans()
                                            : '-';
                                        $parentLabel = $video->class->title ?? ($video->class->name ?? null);
                                    @endphp
                                    <li class="report-item py-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-gray-800 fw-semibold">{{ $video->title }}</span>
                                            @if ($video->pivot->is_completed)
                                                <span class="badge bg-success">{{ __('Completed') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-light-warning text-warning">{{ $percent }}%</span>
                                            @endif
                                        </div>
                                        @if ($parentLabel)
                                            <div class="text-muted fs-8 mb-1">
                                                <i class="ki-outline ki-clipboard fs-7 me-1"></i>{{ $parentLabel }}
                                            </div>
                                        @endif
                                        <div class="progress h-6px">
                                            <div class="progress-bar bg-primary" style="width: {{ $percent }}%">
                                            </div>
                                        </div>
                                        <div class="text-muted fs-8 mt-1">
                                            توقف عند {{ gmdate('i:s', $watchSeconds) }}
                                            • آخر مشاهدة {{ $lastAt }}
                                            • المشاهدات: {{ $video->pivot->views }}
                                        </div>
                                    </li>
                                @empty
                                    <li class="report-empty-initial text-muted text-center py-5">لا توجد فيديوهات حصص تمت
                                        مشاهدتها</li>
                                @endforelse
                            </ul>
                            <div class="report-empty text-muted text-center py-5 d-none">لا توجد نتائج مطابقة</div>
                        </div>
                    </div>
                </div>

            </div>
            {{-- ==================== END REPORT SECTION ==================== --}}

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .student-card .student-hero {
            background: linear-gradient(135deg, rgba(0, 0, 0, .015), rgba(0, 0, 0, 0));
        }

        .info-item .info-label {
            font-size: .8rem;
            color: #99a1b7;
            font-weight: 600;
            margin-bottom: .2rem;
            display: flex;
            align-items: center;
        }

        .info-item .info-value {
            font-size: .95rem;
            font-weight: 600;
            color: #1e2129;
        }

        .report-card {
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1.25rem rgba(0, 0, 0, .07) !important;
        }

        .report-card .symbol-label {
            border-radius: 10px;
        }

        .report-search-input {
            border-radius: 6px;
        }

        .report-search-clear {
            border: none;
            background: transparent;
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
            function filterCard(input) {
                const term = input.value.trim().toLowerCase();
                const card = input.closest('.report-card');
                if (!card) return;

                const list = card.querySelector('.report-scroll');
                const emptyState = card.querySelector('.report-empty');
                const clearBtn = card.querySelector('.report-search-clear');
                const items = list ? list.querySelectorAll('.report-item') : [];

                if (clearBtn) clearBtn.classList.toggle('d-none', term === '');

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
            }

            document.querySelectorAll('.report-search-input').forEach(function(input) {
                input.addEventListener('input', function() {
                    filterCard(input);
                }, true);

                // Prevent Metronic's KTMenu keyboard navigation (bound at document level
                // for the Active/Inactive dropdowns) from swallowing keystrokes typed here.
                ['keydown', 'keyup', 'keypress'].forEach(function(evt) {
                    input.addEventListener(evt, function(e) {
                        e.stopPropagation();
                    });
                });
            });

            document.querySelectorAll('.report-search-clear').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const wrapper = btn.closest('.report-search');
                    const input = wrapper ? wrapper.querySelector('.report-search-input') : null;
                    if (!input) return;
                    input.value = '';
                    input.focus();
                    filterCard(input);
                });
            });
        })();
    </script>
@endpush
