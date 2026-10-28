@extends('dashboard.partials.master')

@section('content')
    @php
        $statusBadges = [
            'unused' => ['badge-light-success', __('Unused')],
            'used' => ['badge-light-info', __('Partially used')],
            'exhausted' => ['badge-light-secondary text-gray-700', __('Exhausted')],
            'inactive' => ['badge-light-danger', __('Inactive')],
        ];
        [$badge, $statusLabel] = $statusBadges[$code->status];
        $max = $code->max_uses;
    @endphp

    <div class="card mb-5">
        <div class="card-header border-0 pt-5">
            <div class="card-title flex-column">
                <span class="text-muted fs-7 mb-1">{{ __('Access Code Details') }}</span>
                <span class="fs-2x fw-bold" style="font-family:SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:2px;direction:ltr">{{ $code->code }}</span>
            </div>
            <div class="card-toolbar gap-3">
                <span class="badge {{ $badge }} fs-6 px-4 py-2">{{ $statusLabel }}</span>
                <a href="{{ route('dashboard.generateCode.index') }}" class="btn btn-light">{{ __('Back') }}</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-5">
                <div class="col-md-4">
                    <div class="text-muted fs-7">{{ __('Class') }}</div>
                    <div class="fw-bold fs-5">{{ $code->class?->title ?? '-' }}</div>
                    <div class="text-muted">{{ $code->class?->course?->title }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted fs-7">{{ __('Usage') }}</div>
                    <div class="fw-bold fs-5">{{ $code->used_count }} / {{ $max ?? '∞' }}</div>
                    @if ($max)
                        <div class="progress h-6px w-150px mt-2">
                            <div class="progress-bar bg-primary" style="width: {{ min(100, $code->used_count / $max * 100) }}%"></div>
                        </div>
                    @endif
                    <div class="text-muted fs-7 mt-1">
                        {{ $code->single_use ? __('Single Use') : ($max ? __('Multi use') : __('Unlimited')) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted fs-7">{{ __('Created At') }}</div>
                    <div class="fw-bold fs-5">{{ $code->created_at->format('Y-m-d') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title fw-bold">{{ __('Access Logs') }}
                <span class="badge badge-light ms-2">{{ $code->logs->count() }}</span>
            </h3>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-4">
                    <thead>
                        <tr class="text-gray-400 fw-bold fs-7 text-uppercase">
                            <th>{{ __('Student') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('IP Address') }}</th>
                            <th>{{ __('Device / Browser') }}</th>
                            <th>{{ __('Used At') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @forelse ($code->logs as $log)
                            <tr>
                                <td class="fw-bold">
                                    {{ $log->student ? trim($log->student->first_name . ' ' . $log->student->last_name) : __('Guest') }}
                                </td>
                                <td dir="ltr" class="text-start">{{ $log->student?->phone ?? '-' }}</td>
                                <td>{{ $log->device_ip ?? '-' }}</td>
                                <td class="text-muted fs-7" title="{{ $log->user_agent }}">{{ $log->user_agent ? Str::limit($log->user_agent, 50) : '-' }}</td>
                                <td>{{ $log->used_at ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-10">{{ __('No logs found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
