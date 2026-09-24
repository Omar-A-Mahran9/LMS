<!DOCTYPE html>

<html
    lang="{{ isArabic() ? 'ar' : 'en' }}"
    dir="{{ isArabic() ? 'rtl' : 'ltr' }}"
    style="direction: {{ isArabic() ? 'rtl' : 'ltr' }}"
    data-theme-mode="{{ setting('theme_mode') ?? 'light' }}"
    data-theme="{{ setting('theme_mode') ?? 'light' }}"
>

<head>

    {{-- =========================================================
         Head
    ========================================================== --}}
    @include('dashboard.partials.head')

    @stack('styles')

</head>


<body
    id="kt_app_body"
    class="app-default"

    {{-- Header --}}
    data-kt-app-header-fixed="true"
    data-kt-app-header-fixed-mobile="true"

    {{-- Sidebar --}}
    data-kt-app-sidebar-enabled="true"
    data-kt-app-sidebar-fixed="true"
    data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true"
    data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true"

    {{-- Toolbar --}}
    data-kt-app-toolbar-enabled="true"
>


    {{-- =========================================================
         Theme Mode
         Must run before the page renders to avoid theme flashing.
    ========================================================== --}}
    <script>
        (function () {
            const defaultThemeMode = 'light';
            let themeMode = defaultThemeMode;

            const html = document.documentElement;

            if (!html) {
                return;
            }

            /*
             * Check explicit theme mode from the HTML element.
             */
            if (html.hasAttribute('data-bs-theme-mode')) {

                themeMode = html.getAttribute('data-bs-theme-mode');

            /*
             * Otherwise check localStorage.
             */
            } else if (localStorage.getItem('data-bs-theme') !== null) {

                themeMode = localStorage.getItem('data-bs-theme');

            /*
             * Otherwise use the default theme.
             */
            } else {

                themeMode = defaultThemeMode;
            }


            /*
             * Resolve system theme.
             */
            if (themeMode === 'system') {

                themeMode = window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches
                    ? 'dark'
                    : 'light';
            }


            /*
             * Apply theme.
             */
            html.setAttribute('data-bs-theme', themeMode);

        })();
    </script>


    {{-- =========================================================
         Application Root
    ========================================================== --}}
    <div
        id="kt_app_root"
        class="d-flex flex-column flex-root app-root"
    >

        {{-- =====================================================
             Application Page
        ====================================================== --}}
        <div
            id="kt_app_page"
            class="app-page flex-column flex-column-fluid"
        >


            {{-- =================================================
                 Header
            ================================================== --}}
            @include('dashboard.partials.header')


            {{-- =================================================
                 Application Wrapper
            ================================================== --}}
            <div
                id="kt_app_wrapper"
                class="app-wrapper flex-column flex-row-fluid"
            >


                {{-- =================================================
                     Sidebar
                ================================================== --}}
                @include('dashboard.partials.aside')


                {{-- =================================================
                     Main
                ================================================== --}}
                <div
                    id="kt_app_main"
                    class="app-main flex-column flex-row-fluid"
                >

                    {{-- =================================================
                         Content Wrapper
                    ================================================== --}}
                    <div class="d-flex flex-column flex-column-fluid">


                        {{-- =================================================
                             Toolbar
                        ================================================== --}}
                        <div
                            id="kt_app_toolbar"
                            class="app-toolbar pt-7 pt-lg-10"
                        >

                            <div
                                id="kt_app_toolbar_container"
                                class="app-container container-fluid d-flex align-items-stretch"
                            >

                                <div
                                    class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100"
                                >

                                    {{-- Page Title / Breadcrumbs --}}
                                    <div
                                        class="page-title d-flex flex-column justify-content-center gap-1 me-3"
                                    >
                                        @yield('breadcrumbs')
                                    </div>

                                </div>

                            </div>

                        </div>
                        {{-- End Toolbar --}}


                        {{-- =================================================
                             Content
                        ================================================== --}}
                        <div
                            id="kt_app_content"
                            class="app-content flex-column-fluid"
                        >

                            <div
                                id="kt_app_content_container"
                                class="app-container container-fluid"
                            >

                                @yield('content')

                            </div>

                        </div>
                        {{-- End Content --}}


                    </div>
                    {{-- End Content Wrapper --}}


                    {{-- =================================================
                         Footer
                    ================================================== --}}
                    @include('dashboard.partials.footer')

                </div>
                {{-- End Main --}}


            </div>
            {{-- End Application Wrapper --}}


        </div>
        {{-- End Application Page --}}


    </div>
    {{-- End Application Root --}}


    {{-- =========================================================
         Scroll To Top
    ========================================================== --}}
    <div
        id="kt_scrolltop"
        class="scrolltop"
        data-kt-scrolltop="true"
    >
        <i class="ki-outline ki-arrow-up"></i>
    </div>


    {{-- =========================================================
         Global Scripts
    ========================================================== --}}
    @include('dashboard.partials.foot')


    {{-- =========================================================
         Toast
    ========================================================== --}}
    <div
        class="position-fixed bottom-0 start-0 p-3"
        style="z-index: 1090"
    >

        <div
            id="kt_docs_toast_toggle"
            class="toast"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
        >

            {{-- Toast Header --}}
            <div class="toast-header">

                <img
                    src="{{ asset('placeholder_images/favicon.svg') }}"
                    class="me-2 theme-light-show"
                    width="20"
                    alt="{{ setting('website_name') }}"
                >

                <img
                    src="{{ asset('placeholder_images/favicon.svg') }}"
                    class="me-2 theme-dark-show"
                    width="20"
                    alt="{{ setting('website_name') }}"
                >

                <strong class="me-auto">
                    {{ setting('website_name') }}
                </strong>

                <small>
                    {{ __('Now') }}
                </small>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="toast"
                    aria-label="{{ __('Close') }}"
                ></button>

            </div>


            {{-- Toast Body --}}
            <div class="toast-body">

                {{ __('Done successfully') }}.

            </div>

        </div>

    </div>
    {{-- End Toast --}}


    {{-- =========================================================
         Dashboard Initialization
    ========================================================== --}}
    <script>
        $(document).ready(function () {

            /*
             * Initialize favicon notifications.
             */
            window.favicon = new Favico({
                animation: 'popFade'
            });


            /*
             * Initialize global search.
             */
            if (typeof KTLayoutSearch !== 'undefined') {
                KTLayoutSearch.init();
            }

        });
    </script>


    {{-- Page scripts (@stack('scripts')) are printed once, inside dashboard.partials.foot above.
         Printing the stack here too loaded every page script twice ("Cannot reinitialise DataTable"). --}}

</body>

</html>

