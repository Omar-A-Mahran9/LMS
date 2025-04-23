@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">

            <!-- General Stats -->
            <div class="row gy-5 g-xl-10">
                <div class="col-sm-6 col-xl-4 mb-5 mb-xl-10">
                    <div class="card h-lg-40">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="ki-outline ki-book fs-2hx text-gray-600 mb-2"></i>
                            <span class="fs-3x fw-bold text-gray-800">{{ $orderPlaced }}</span>
                            <span class="text-muted">{{ __('Total Bookings') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4 mb-5 mb-xl-10">
                    <div class="card h-lg-40">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="ki-outline ki-dollar fs-2hx text-gray-600 mb-2"></i>
                            <span class="fs-3x fw-bold text-gray-800">{{ number_format($earnings, 2) }} SAR</span>
                            <span class="text-muted">{{ __('Total Earnings') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4 mb-5 mb-xl-10">
                    <div class="card h-lg-40">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="ki-outline ki-people fs-2hx text-gray-600 mb-2"></i>
                            <span class="fs-3x fw-bold text-gray-800">{{ $passengersCount }}</span>
                            <span class="text-muted">{{ __('Total Passengers') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status -->
            <div class="row gy-5 g-xl-10">
                @foreach ($orderStats as $status => $count)
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                                <i class="ki-outline ki-book fs-2hx text-gray-600 mb-2"></i>
                                <span class="fs-2x fw-bold text-gray-800">{{ $count }}</span>
                                <span class="text-muted text-uppercase">{{ ucfirst($status) }} Orders</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Top Add-on Services -->
            <div class="card mt-10">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Top Add-on Services') }}</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @forelse($topServices as $service)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $service['name_en'] }}
                                <span class="badge bg-primary rounded-pill">{{ $service['total_usage'] }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">{{ __('No data available') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Monthly Earnings -->
            <div class="card mt-10">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Monthly Earnings (Last 6 Months)') }}</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach ($monthlyEarnings as $item)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $item->month }}</span>
                                <strong>{{ number_format($item->total, 2) }} SAR</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection
