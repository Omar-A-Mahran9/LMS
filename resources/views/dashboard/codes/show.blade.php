@extends('dashboard.partials.master')

@section('content')
    <div class="container">
        <h2 class="mb-4">{{ __('Access Code Details') }}</h2>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">{{ __('Code') }}: <strong>{{ $code->code }}</strong></h5>
                <p><strong>{{ __('Class') }}:</strong> {{ $code->class->title_ar ?? '-' }}</p>
                <p><strong>{{ __('Usage Limit') }}:</strong> {{ $code->usage_limit ?? __('Unlimited') }}</p>
                <p><strong>{{ __('Used Count') }}:</strong> {{ $code->used_count }}</p>
                <p><strong>{{ __('Single Use') }}:</strong> {{ $code->single_use ? __('Yes') : __('No') }}</p>
                <p><strong>{{ __('Active') }}:</strong>
                    @if ($code->is_active)
                        <span class="badge bg-success">{{ __('Yes') }}</span>
                    @else
                        <span class="badge bg-danger">{{ __('No') }}</span>
                    @endif
                </p>
                <p><strong>{{ __('Created At') }}:</strong> {{ $code->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <h4>{{ __('Access Logs') }}</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('IP Address') }}</th>
                        <th>{{ __('Device / Browser') }}</th>
                        <th>{{ __('Used At') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($code->logs as $log)
                        <tr>
                            <td>{{ $log->student?->first_name ?? __('Guest') }}</td>
                            <td>{{ $log->device_ip ?? '-' }}</td>
                            <td>{{ $log->user_agent ? Str::limit($log->user_agent, 50) : '-' }}</td>
                            <td>{{ $log->used_at ? $log->used_at->format('Y-m-d H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('No logs found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
