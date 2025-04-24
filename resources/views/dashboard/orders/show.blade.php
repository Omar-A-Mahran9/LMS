@extends('dashboard.partials.master')

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="d-flex flex-column gap-7 gap-lg-10">
                <div class="d-flex flex-wrap flex-stack gap-5 gap-lg-10">
                    <ul
                        class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-lg-n2 me-auto">
                        <li class="nav-item">
                            <a class="nav-link text-active-primar pb-4 active" data-bs-toggle="tab"
                                href="#kt_ecommerce_sales_order_summary">{{ __('Order Summary') }}</a>
                        </li>
                    </ul>
                </div>

                <div class="d-flex flex-column flex-xl-row gap-7 gap-lg-2">
                    <!-- Order details -->
                    <div class="card card-flush py-4 flex-row-fluid">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>{{ __('Order Details') }} ({{ $order->id }})</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-bordered mb-0 fs-6 gy-6 min-w-300px">
                                    <tbody class="fw-semibold text-gray-600">
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-regular fa-calendar fs-4 me-2"></i>{{ __('Created at') }}
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">{{ $order->created_at->format('d-m-Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-solid fa-wallet fs-4 me-2"></i>{{ __('Service') }}
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">
                                                @foreach ($addonServices as $addon)
                                                    <p>{{ $addon['service_name'] }} ({{ $addon['count'] }})</p>
                                                @endforeach
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-solid fa-dollar-sign fs-4 me-2"></i>{{ __('Total Price') }}
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">{{ $totalPrice }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Customer details -->
                    <div class="card card-flush py-4 flex-row-fluid">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>{{ __('Customer Details') }}</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                    <tbody class="fw-semibold text-gray-600">
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-regular fa-user fs-4 me-2"></i>{{ __('Customer') }}
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">{{ $order->customer->full_name }}</td>
                                        </tr>

                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-solid fa-phone fs-4 me-2"></i>{{ __('Phone') }}
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">{{ $order->customer->phone }}</td>
                                        </tr>

                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-regular fa-calendar fs-4 me-2"></i>{{ __('Visiting Date') }}
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">{{ $order->date  }}</td>
                                        </tr>


                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-regular fa-calendar fs-4 me-2"></i>{{ __('Visiting Time') }}
                                                </div>
                                            </td>
                                            <td class="fw-bold text-end">{{ $order->time }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Map iframe -->
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>{{ __('Location') }}</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <!-- Google Maps Embed -->
                        <div class="ratio ratio-16x9">
                            <iframe
                                src="https://www.google.com/maps?q={{ $order->lat }},{{ $order->lng }}&output=embed"
                                width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
