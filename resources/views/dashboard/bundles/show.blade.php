@extends('dashboard.partials.master')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">{{ __('Bundle Details') }}</h2>
            <a href="{{ route('dashboard.bundles.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right"></i> {{ __('Back to Bundles') }}
            </a>
        </div>

        {{-- ======================= Bundle Info Card ======================= --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-2 text-center">
                        <img src="{{ $bundle->full_image_path }}" alt="{{ $bundle->title_ar }}" class="img-fluid rounded"
                            style="max-height: 120px; object-fit: cover;">
                    </div>

                    <div class="col-md-10">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <h4 class="mb-2">
                                {{ $bundle->title_ar }}
                                <small class="text-muted d-block fs-6">{{ $bundle->title_en }}</small>
                            </h4>
                            <span class="badge {{ $bundle->is_active ? 'bg-success' : 'bg-danger' }} fs-6">
                                {{ $bundle->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </div>

                        <div class="row mt-3 g-3">
                            <div class="col-md-3">
                                <strong>{{ __('Price') }}:</strong>
                                @if ($bundle->discount_price)
                                    <span class="text-decoration-line-through text-muted">{{ $bundle->price }}</span>
                                    <span class="text-success fw-bold">{{ $bundle->discount_price }}</span>
                                @else
                                    {{ $bundle->price }}
                                @endif
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('Valid From') }}:</strong>
                                {{ $bundle->starts_at ?? '-' }}
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('Expires At') }}:</strong>
                                {{ $bundle->expires_at ?? '-' }}
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('Created At') }}:</strong>
                                {{ $bundle->created_at }}
                            </div>
                        </div>

                        @if ($bundle->description_ar)
                            <div class="mt-3">
                                <strong>{{ __('Description') }}:</strong>
                                <p class="text-muted mb-0">{{ Str::limit(strip_tags($bundle->description_ar), 200) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================= Quick Stats ======================= --}}
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-6">
                <div class="card text-center shadow-sm border-primary">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-primary">{{ $bundle->classes_count }}</h3>
                        <small class="text-muted">{{ __('Classes') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card text-center shadow-sm">
                    <div class="card-body py-3">
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        <small class="text-muted">{{ __('Total Codes') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card text-center shadow-sm border-success">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-success">{{ $stats['available'] }}</h3>
                        <small class="text-muted">{{ __('Available') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card text-center shadow-sm border-danger">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-danger">{{ $stats['used_up'] }}</h3>
                        <small class="text-muted">{{ __('Fully Used') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card text-center shadow-sm">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-info">{{ $stats['active'] }}</h3>
                        <small class="text-muted">{{ __('Active Codes') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card text-center shadow-sm">
                    <div class="card-body py-3">
                        <h3 class="mb-0 text-secondary">{{ $stats['inactive'] }}</h3>
                        <small class="text-muted">{{ __('Inactive Codes') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================= Classes in Bundle ======================= --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-gray ">
                <h5 class="mt-5">{{ __('Classes in this Bundle') }} ({{ $bundle->classes_count }})</h5>
            </div>
            <div class="card-body">
                @forelse ($bundle->classes as $class)
                    <span class="badge bg-light text-dark border me-2 mb-2 p-2 fs-7">
                        <i class="fas fa-book me-1"></i>
                        {{ $class->title_ar ?? ($class->title ?? '-') }}
                    </span>
                @empty
                    <p class="text-muted mb-0">{{ __('No classes added to this bundle yet') }}</p>
                @endforelse
            </div>
        </div>

        {{-- ======================= Codes Table ======================= --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0">{{ __('Access Codes') }}</h5>

                <div class="d-flex align-items-center position-relative">
                    <input type="text" id="code_search" class="form-control form-control-sm ps-3" style="width: 220px;"
                        placeholder="{{ __('Search code...') }}">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0" id="codes_table">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Single Use') }}</th>
                            <th>{{ __('Usage') }}</th>
                            <th>{{ __('Logs Count') }}</th>
                            <th>{{ __('Can Be Used') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bundle->codes as $code)
                            <tr>
                                <td>
                                    <code class="fw-bold">{{ $code->code }}</code>
                                    <button class="btn btn-sm btn-light border ms-1" onclick="copyCode(this)"
                                        data-code="{{ $code->code }}" title="{{ __('Copy') }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                                <td>
                                    <span class="badge {{ $code->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $code->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td>
                                    @if ($code->single_use)
                                        <span class="badge bg-warning text-dark">{{ __('Yes') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('No') }}</span>
                                    @endif
                                </td>
                                <td style="min-width: 140px;">
                                    {{ $code->used_count }} / {{ $code->usage_limit ?? __('∞') }}
                                    @if ($code->usage_limit)
                                        @php
                                            $percentage = min(100, ($code->used_count / $code->usage_limit) * 100);
                                        @endphp
                                        <div class="progress mt-1" style="height: 6px;">
                                            <div class="progress-bar {{ $percentage >= 100 ? 'bg-danger' : 'bg-info' }}"
                                                style="width: {{ $percentage }}%"></div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $code->logs_count }}
                                    </span>
                                </td>
                                <td>
                                    @if ($code->canBeUsed())
                                        <span class="badge bg-success">{{ __('Yes') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('No') }}</span>
                                    @endif
                                </td>
                                <td>{{ $code->created_at }}</td>
                                <td>
                                    <a href="{{ route('dashboard.codes.show', $code->id) }}"
                                        class="btn btn-sm btn-icon btn-light-primary" title="{{ __('View Logs') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    {{ __('No codes generated for this bundle yet') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function copyCode(btn) {
            const code = btn.dataset.code;
            navigator.clipboard.writeText(code).then(() => {
                const icon = btn.querySelector('i');
                icon.classList.remove('fa-copy');
                icon.classList.add('fa-check', 'text-success');
                setTimeout(() => {
                    icon.classList.remove('fa-check', 'text-success');
                    icon.classList.add('fa-copy');
                }, 1200);
            });
        }

        // فلترة بسيطة على جدول الأكواد بدون DataTables
        document.getElementById('code_search').addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            document.querySelectorAll('#codes_table tbody tr').forEach(function(row) {
                row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
            });
        });
    </script>
@endsection
