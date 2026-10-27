@extends('dashboard.partials.master')
@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form class="ajax-form banner-form" method="POST" enctype="multipart/form-data"
                action="{{ route('dashboard.banners.update', $banner) }}"
                data-redirection-url="{{ route('dashboard.banners.index') }}">
                @method('PUT')
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-7">
                    <h2 class="fw-bold m-0">{{ __('Edit banner / offer') }}</h2>
                    <span class="badge badge-light-primary fs-7 py-3 px-4">
                        <i class="ki-outline ki-mouse-circle fs-5 me-2 text-primary"></i>
                        {{ __('Clicks') }}: {{ number_format($banner->clicks) }}
                    </span>
                </div>
                @include('dashboard.banners._form')
            </form>
        </div>
    </div>
@endsection
