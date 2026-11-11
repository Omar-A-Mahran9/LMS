@props(['slug', 'route', 'title'])
@php($isActive = getClassIfUrlContains('here', $slug) !== '')
<div class="menu-item {{ $isActive ? 'here' : '' }}">
    <!--begin:Menu link-->
    <a class="menu-link {{ $isActive ? 'active' : '' }}" href="{{ $route }}" title="{{ $title }}"
        @if ($isActive) aria-current="page" @endif>
        <span class="menu-icon">
            {!! $slot !!}
        </span>
        <span class="menu-title">{{ $title }}</span>
    </a>
    <!--end:Menu link-->
</div>
