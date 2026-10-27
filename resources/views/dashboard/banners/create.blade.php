@extends('dashboard.partials.master')
@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form class="ajax-form banner-form" method="POST" enctype="multipart/form-data"
                action="{{ route('dashboard.banners.store') }}"
                data-redirection-url="{{ route('dashboard.banners.index') }}">
                <h2 class="fw-bold mb-7">{{ __('Add banner / offer') }}</h2>
                @include('dashboard.banners._form')
            </form>
        </div>
    </div>
@endsection
