<!--begin::Header-->
<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}"
    data-kt-sticky-name="app-header-minimize" data-kt-sticky-animation="false"
    data-kt-sticky-offset="{default: '0px', lg: '0px'}">
    <!--begin::Header container-->
    <div class="app-container container-fluid d-flex align-items-stretch flex-stack mt-lg-8" id="kt_app_header_container">
        <!--begin::Sidebar toggle-->
        <div class="d-flex align-items-center d-block d-lg-none ms-n3" title="Show sidebar menu">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px me-1" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-outline ki-abstract-14 fs-2"></i>
            </div>
            <!--begin::Logo image-->
            <a href="{{ route('dashboard.index') }}">
                @if (isArabic())
                    <img alt="Logo" src="{{ asset('assets/dashboard/media/svg/products-categories/Logo.svg') }}"
                        class="h-50px d-none d-sm-inline app-sidebar-logo-default theme-light-show" />
                @else
                    <img alt="Logo" src="{{ asset('assets/dashboard/media/svg/products-categories/Logo.svg') }}"
                        class="h-50px d-none d-sm-inline app-sidebar-logo-default theme-light-show" />
                @endif
                @if (isArabic())
                    <img alt="Logo" src="{{ asset('assets/dashboard/media/svg/products-categories/Logo.svg') }}"
                        class="h-50px theme-dark-show" />
                @else
                    <img alt="Logo" src="{{ asset('assets/dashboard/media/svg/products-categories/Logo.svg') }}"
                        class="h-50px theme-dark-show" />
                @endif
            </a>
            <!--end::Logo image-->
        </div>
        <!--end::Sidebar toggle-->
        <!--begin::Navbar-->
        <div class="app-navbar flex-lg-grow-1" id="kt_app_header_navbar">
            <div class="app-navbar-item d-flex align-items-stretch flex-lg-grow-1 me-1 me-lg-0">
                <!--begin::Search-->
                <div id="kt_header_search" class="header-search d-flex align-items-center w-lg-275px"
                    data-kt-search-keypress="true" data-kt-search-min-length="2" data-kt-search-enter="enter"
                    data-kt-search-layout="menu" data-kt-search-responsive="true" data-kt-menu-trigger="auto"
                    data-kt-menu-permanent="true" data-kt-menu-placement="bottom-start">
                    <!--begin::Tablet and mobile search toggle-->
                    <div data-kt-search-element="toggle"
                        class="search-toggle-mobile d-flex d-lg-none align-items-center">
                        <div class="d-flex">
                            <i class="ki-outline ki-magnifier fs-1"></i>
                        </div>
                    </div>
                    <!--end::Tablet and mobile search toggle-->
                    <!--begin::Form(use d-none d-lg-block classes for responsive search)-->
                    {{--  <form data-kt-search-element="form" class="d-none d-lg-block w-100 position-relative mb-5 mb-lg-0" autocomplete="off">
                        <!--begin::Hidden input(Added to disable form autocomplete)-->
                        <input type="hidden" />
                        <!--end::Hidden input-->
                        <!--begin::Icon-->
                        <i class="ki-outline ki-magnifier search-icon fs-2 text-gray-500 position-absolute top-50 translate-middle-y ms-5"></i>
                        <!--end::Icon-->
                        <!--begin::Input-->
                        <input type="text" class="search-input form-control form-control-solid ps-13" name="search" value="" placeholder="Search..." data-kt-search-element="input" />
                        <!--end::Input-->
                        <!--begin::Spinner-->
                        <span class="search-spinner position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-5" data-kt-search-element="spinner">
                            <span class="spinner-border h-15px w-15px align-middle text-gray-400"></span>
                        </span>
                        <!--end::Spinner-->
                        <!--begin::Reset-->
                        <span class="search-reset btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4" data-kt-search-element="clear">
                            <i class="ki-outline ki-cross fs-2 fs-lg-1 me-0"></i>
                        </span>
                        <!--end::Reset-->
                    </form>  --}}
                    <!--end::Form-->
                    <!--begin::Menu-->
                    <div data-kt-search-element="content"
                        class="menu menu-sub menu-sub-dropdown py-7 px-7 overflow-hidden w-300px w-md-350px">
                        <!--begin::Wrapper-->
                        <div data-kt-search-element="wrapper">
                            <!--begin::Recently viewed-->
                            <div data-kt-search-element="results" class="d-none">
                                <!--begin::Items-->
                                <div class="scroll-y mh-200px mh-lg-350px">
                                    <!--begin::Category title-->
                                    <h3 class="fs-5 text-muted m-0 pb-5" data-kt-search-element="category-title">Users
                                    </h3>
                                    <!--end::Category title-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <img src="{{ asset('assets/dashboard/media/avatars/300-6.jpg') }}"
                                                alt="" />
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Karina Clark</span>
                                            <span class="fs-7 fw-semibold text-muted">Marketing Manager</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <img src="{{ asset('assets/dashboard/media/avatars/300-2.jpg') }}"
                                                alt="" />
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Olivia Bold</span>
                                            <span class="fs-7 fw-semibold text-muted">Software Engineer</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <img src="{{ asset('assets/dashboard/media/avatars/300-9.jpg') }}"
                                                alt="" />
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Ana Clark</span>
                                            <span class="fs-7 fw-semibold text-muted">UI/UX Designer</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <img src="{{ asset('assets/dashboard/media/avatars/300-14.jpg') }}"
                                                alt="" />
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Nick Pitola</span>
                                            <span class="fs-7 fw-semibold text-muted">Art Director</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <img src="{{ asset('assets/dashboard/media/avatars/300-11.jpg') }}"
                                                alt="" />
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Edward Kulnic</span>
                                            <span class="fs-7 fw-semibold text-muted">System Administrator</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Category title-->
                                    <h3 class="fs-5 text-muted m-0 pt-5 pb-5" data-kt-search-element="category-title">
                                        students</h3>
                                    <!--end::Category title-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <img class="w-20px h-20px"
                                                    src="{{ asset('assets/dashboard/media/svg/brand-logos/volicity-9.svg') }}"
                                                    alt="" />
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Company Rbranding</span>
                                            <span class="fs-7 fw-semibold text-muted">UI Design</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <img class="w-20px h-20px"
                                                    src="{{ asset('assets/dashboard/media/svg/brand-logos/tvit.svg') }}"
                                                    alt="" />
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Company Re-branding</span>
                                            <span class="fs-7 fw-semibold text-muted">Web Development</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <img class="w-20px h-20px"
                                                    src="{{ asset('assets/dashboard/media/svg/misc/infography.svg') }}"
                                                    alt="" />
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Business Analytics App</span>
                                            <span class="fs-7 fw-semibold text-muted">Administration</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <img class="w-20px h-20px"
                                                    src="{{ asset('assets/dashboard/media/svg/brand-logos/leaf.svg') }}"
                                                    alt="" />
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">EcoLeaf App Launch</span>
                                            <span class="fs-7 fw-semibold text-muted">Marketing</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <img class="w-20px h-20px"
                                                    src="{{ asset('assets/dashboard/media/svg/brand-logos/tower.svg') }}"
                                                    alt="" />
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column justify-content-start fw-semibold">
                                            <span class="fs-6 fw-semibold">Tower Group Website</span>
                                            <span class="fs-7 fw-semibold text-muted">Google Adwords</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Category title-->
                                    <h3 class="fs-5 text-muted m-0 pt-5 pb-5" data-kt-search-element="category-title">
                                        Projects</h3>
                                    <!--end::Category title-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-notepad fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <span class="fs-6 fw-semibold">Si-Fi Project by AU Themes</span>
                                            <span class="fs-7 fw-semibold text-muted">#45670</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-frame fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <span class="fs-6 fw-semibold">Shopix Mobile App Planning</span>
                                            <span class="fs-7 fw-semibold text-muted">#45690</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-message-text-2 fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <span class="fs-6 fw-semibold">Finance Monitoring SAAS Discussion</span>
                                            <span class="fs-7 fw-semibold text-muted">#21090</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <a href="#"
                                        class="d-flex text-dark text-hover-primary align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-profile-circle fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <span class="fs-6 fw-semibold">Dashboard Analitics Launch</span>
                                            <span class="fs-7 fw-semibold text-muted">#34560</span>
                                        </div>
                                        <!--end::Title-->
                                    </a>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->
                            </div>
                            <!--end::Recently viewed-->
                            <!--begin::Recently viewed-->
                            <div class="" data-kt-search-element="main">
                                <!--begin::Heading-->
                                <div class="d-flex flex-stack fw-semibold mb-4">
                                    <!--begin::Label-->
                                    <span class="text-muted fs-6 me-2">Recently Searched:</span>
                                    <!--end::Label-->
                                    <!--begin::Toolbar-->
                                    <div class="d-flex" data-kt-search-element="toolbar">
                                        <!--begin::Preferences toggle-->
                                        <div data-kt-search-element="preferences-show"
                                            class="btn btn-icon w-20px btn-sm btn-active-color-primary me-2 data-bs-toggle="
                                            title="Show search preferences">
                                            <i class="ki-outline ki-setting-2 fs-2"></i>
                                        </div>
                                        <!--end::Preferences toggle-->
                                        <!--begin::Advanced search toggle-->
                                        <div data-kt-search-element="advanced-options-form-show"
                                            class="btn btn-icon w-20px btn-sm btn-active-color-primary me-n1"
                                            data-bs-toggle="tooltip" title="Show more search options">
                                            <i class="ki-outline ki-down fs-2"></i>
                                        </div>
                                        <!--end::Advanced search toggle-->
                                    </div>
                                    <!--end::Toolbar-->
                                </div>
                                <!--end::Heading-->
                                <!--begin::Items-->
                                <div class="scroll-y mh-200px mh-lg-325px">
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-laptop fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <a href="#"
                                                class="fs-6 text-gray-800 text-hover-primary fw-semibold">BoomApp by
                                                Keenthemes</a>
                                            <span class="fs-7 text-muted fw-semibold">#45789</span>
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-chart-simple fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <a href="#"
                                                class="fs-6 text-gray-800 text-hover-primary fw-semibold">"Kept API
                                                Project Meeting</a>
                                            <span class="fs-7 text-muted fw-semibold">#84050</span>
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-chart fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <a href="#"
                                                class="fs-6 text-gray-800 text-hover-primary fw-semibold">"KPI
                                                Monitoring App Launch</a>
                                            <span class="fs-7 text-muted fw-semibold">#84250</span>
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-chart-line-down fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <a href="#"
                                                class="fs-6 text-gray-800 text-hover-primary fw-semibold">Project
                                                Reference FAQ</a>
                                            <span class="fs-7 text-muted fw-semibold">#67945</span>
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-sms fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <a href="#"
                                                class="fs-6 text-gray-800 text-hover-primary fw-semibold">"FitPro App
                                                Development</a>
                                            <span class="fs-7 text-muted fw-semibold">#84250</span>
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-bank fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <a href="#"
                                                class="fs-6 text-gray-800 text-hover-primary fw-semibold">Shopix Mobile
                                                App</a>
                                            <span class="fs-7 text-muted fw-semibold">#45690</span>
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-40px me-4">
                                            <span class="symbol-label bg-light">
                                                <i class="ki-outline ki-chart-line-down fs-2 text-primary"></i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Title-->
                                        <div class="d-flex flex-column">
                                            <a href="#"
                                                class="fs-6 text-gray-800 text-hover-primary fw-semibold">"Landing UI
                                                Design" Launch</a>
                                            <span class="fs-7 text-muted fw-semibold">#24005</span>
                                        </div>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->
                            </div>
                            <!--end::Recently viewed-->
                            <!--begin::Empty-->
                            <div data-kt-search-element="empty" class="text-center d-none">
                                <!--begin::Icon-->
                                <div class="pt-10 pb-10">
                                    <i class="ki-outline ki-search-list fs-4x opacity-50"></i>
                                </div>
                                <!--end::Icon-->
                                <!--begin::Message-->
                                <div class="pb-15 fw-semibold">
                                    <h3 class="text-gray-600 fs-5 mb-2">No result found</h3>
                                    <div class="text-muted fs-7">Please try again with a different query</div>
                                </div>
                                <!--end::Message-->
                            </div>
                            <!--end::Empty-->
                        </div>
                        <!--end::Wrapper-->
                        <!--begin::Preferences-->
                        <form data-kt-search-element="advanced-options-form" class="pt-1 d-none">
                            <!--begin::Heading-->
                            <h3 class="fw-semibold text-dark mb-7">Advanced Search</h3>
                            <!--end::Heading-->
                            <!--begin::Input group-->
                            <div class="mb-5">
                                <input type="text" class="form-control form-control-sm form-control-solid"
                                    placeholder="Contains the word" name="query" />
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-5">
                                <!--begin::Radio group-->
                                <div class="nav-group nav-group-fluid">
                                    <!--begin::Option-->
                                    <label>
                                        <input type="radio" class="btn-check" name="type" value="has"
                                            checked="checked" />
                                        <span
                                            class="btn btn-sm btn-color-muted btn-active btn-active-primary">All</span>
                                    </label>
                                    <!--end::Option-->
                                    <!--begin::Option-->
                                    <label>
                                        <input type="radio" class="btn-check" name="type" value="users" />
                                        <span
                                            class="btn btn-sm btn-color-muted btn-active btn-active-primary px-4">Users</span>
                                    </label>
                                    <!--end::Option-->
                                    <!--begin::Option-->
                                    <label>
                                        <input type="radio" class="btn-check" name="type" value="orders" />
                                        <span
                                            class="btn btn-sm btn-color-muted btn-active btn-active-primary px-4">Orders</span>
                                    </label>
                                    <!--end::Option-->
                                    <!--begin::Option-->
                                    <label>
                                        <input type="radio" class="btn-check" name="type" value="projects" />
                                        <span
                                            class="btn btn-sm btn-color-muted btn-active btn-active-primary px-4">Projects</span>
                                    </label>
                                    <!--end::Option-->
                                </div>
                                <!--end::Radio group-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-5">
                                <input type="text" name="assignedto"
                                    class="form-control form-control-sm form-control-solid" placeholder="Assigned to"
                                    value="" />
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-5">
                                <input type="text" name="collaborators"
                                    class="form-control form-control-sm form-control-solid"
                                    placeholder="Collaborators" value="" />
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-5">
                                <!--begin::Radio group-->
                                <div class="nav-group nav-group-fluid">
                                    <!--begin::Option-->
                                    <label>
                                        <input type="radio" class="btn-check" name="attachment" value="has"
                                            checked="checked" />
                                        <span class="btn btn-sm btn-color-muted btn-active btn-active-primary">Has
                                            attachment</span>
                                    </label>
                                    <!--end::Option-->
                                    <!--begin::Option-->
                                    <label>
                                        <input type="radio" class="btn-check" name="attachment" value="any" />
                                        <span
                                            class="btn btn-sm btn-color-muted btn-active btn-active-primary px-4">Any</span>
                                    </label>
                                    <!--end::Option-->
                                </div>
                                <!--end::Radio group-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-5">
                                <select name="timezone" aria-label="Select a Timezone" data-control="select2"
                                    data-dropdown-parent="#kt_header_search" data-placeholder="date_period"
                                    class="form-select form-select-sm form-select-solid">
                                    <option value="next">Within the next</option>
                                    <option value="last">Within the last</option>
                                    <option value="between">Between</option>
                                    <option value="on">On</option>
                                </select>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-8">
                                <!--begin::Col-->
                                <div class="col-6">
                                    <input type="number" name="date_number"
                                        class="form-control form-control-sm form-control-solid" placeholder="Lenght"
                                        value="" />
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-6">
                                    <select name="date_typer" aria-label="Select a Timezone" data-control="select2"
                                        data-dropdown-parent="#kt_header_search" data-placeholder="Period"
                                        class="form-select form-select-sm form-select-solid">
                                        <option value="days">Days</option>
                                        <option value="weeks">Weeks</option>
                                        <option value="months">Months</option>
                                        <option value="years">Years</option>
                                    </select>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end">
                                <button type="reset"
                                    class="btn btn-sm btn-light fw-bold btn-active-light-primary me-2"
                                    data-kt-search-element="advanced-options-form-cancel">Cancel</button>
                                <a href="../../demo55/dist/pages/search/horizontal.html"
                                    class="btn btn-sm fw-bold btn-primary"
                                    data-kt-search-element="advanced-options-form-search">Search</a>
                            </div>
                            <!--end::Actions-->
                        </form>
                        <!--end::Preferences-->
                        <!--begin::Preferences-->
                        <form data-kt-search-element="preferences" class="pt-1 d-none">
                            <!--begin::Heading-->
                            <h3 class="fw-semibold text-dark mb-7">Search Preferences</h3>
                            <!--end::Heading-->
                            <!--begin::Input group-->
                            <div class="pb-4 border-bottom">
                                <label
                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                    <span
                                        class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">Projects</span>
                                    <input class="form-check-input" type="checkbox" value="1"
                                        checked="checked" />
                                </label>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="py-4 border-bottom">
                                <label
                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                    <span
                                        class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">Targets</span>
                                    <input class="form-check-input" type="checkbox" value="1"
                                        checked="checked" />
                                </label>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="py-4 border-bottom">
                                <label
                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                    <span class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">Affiliate
                                        Programs</span>
                                    <input class="form-check-input" type="checkbox" value="1" />
                                </label>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="py-4 border-bottom">
                                <label
                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                    <span
                                        class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">Referrals</span>
                                    <input class="form-check-input" type="checkbox" value="1"
                                        checked="checked" />
                                </label>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="py-4 border-bottom">
                                <label
                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                    <span
                                        class="form-check-label text-gray-700 fs-6 fw-semibold ms-0 me-2">Users</span>
                                    <input class="form-check-input" type="checkbox" value="1" />
                                </label>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end pt-7">
                                <button type="reset"
                                    class="btn btn-sm btn-light fw-bold btn-active-light-primary me-2"
                                    data-kt-search-element="preferences-dismiss">Cancel</button>
                                <button type="submit" class="btn btn-sm fw-bold btn-primary">Save Changes</button>
                            </div>
                            <!--end::Actions-->
                        </form>
                        <!--end::Preferences-->
                    </div>
                    <!--end::Menu-->
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Activities-->
            <div class="app-navbar-item ms-1 ms-md-3">
                <!--begin::Menu- wrapper-->
                {{--  <div class="btn btn-icon btn-custom btn-color-gray-500 btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end" id="kt_activities_toggle">
                    <i class="ki-outline ki-notification-on fs-2"></i>
                </div>  --}}
                <!--begin::Notifications-->
                <div class="d-flex align-items-center ms-3 ms-lg-4">
                    <!--begin::Drawer wrapper-->
                    <div class="btn btn-icon btn-custom btn-color-gray-500 btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px position-relative"
                        id="kt_drawer_chat_toggle">
                        <a href="https://lms.sa" target="_blank">
                            {{--  <i class="fa-duotone fa-solid fa-globe fs-2"></i>  --}}
                            <i class="fa-solid fa-globe fs-2"></i>
                            {{--  <i class="ki-outline ki-abstract-20 fs-2"></i>  --}}
                        </a>
                    </div>
                    {{-- <div class="btn btn-icon btn-custom btn-color-gray-500 btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px position-relative"
                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end" id="kt_activities_toggle"> --}}
                    <!--begin::Svg Icon | path: icons/duotune/communication/com003.svg-->
                    {{--  <span class="svg-icon svg-icon-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3"
                                    d="M12 22C13.6569 22 15 20.6569 15 19C15 17.3431 13.6569 16 12 16C10.3431 16 9 17.3431 9 19C9 20.6569 10.3431 22 12 22Z"
                                    fill="currentColor" />
                                <path
                                    d="M19 15V18C19 18.6 18.6 19 18 19H6C5.4 19 5 18.6 5 18V15C6.1 15 7 14.1 7 13V10C7 7.6 8.7 5.6 11 5.1V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V5.1C15.3 5.6 17 7.6 17 10V13C17 14.1 17.9 15 19 15ZM11 10C11 9.4 11.4 9 12 9C12.6 9 13 8.6 13 8C13 7.4 12.6 7 12 7C10.3 7 9 8.3 9 10C9 10.6 9.4 11 10 11C10.6 11 11 10.6 11 10Z"
                                    fill="currentColor" />
                            </svg>
                        </span>  --}}
                    {{-- <i class="ki-outline ki-notification-on fs-2"></i> --}}

                    <!--end::Svg Icon-->

                    <!--begin::Bullet-->
                    {{-- <span
                            class="bullet bullet-dot bg-danger h-6px w-6px position-absolute translate-middle top-0 start-50 animation-blink  {{ $unreadNotifications->count() == 0 ? 'd-none' : '' }}"></span> --}}
                    <!--end::Bullet-->
                    {{-- </div> --}}
                    <!--end::Drawer wrapper-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" id="kt_menu"
                        data-kt-menu="true" style="">

                        <!--begin::Tab content-->
                        <div class="tab-content">

                            <div class="tab-pane fade show active" id="unread_notifications" role="tabpanel">

                            </div>
                        </div>
                        <!--end::Tab content-->
                    </div>
                </div>
                <!--end::Notifications-->
                <!--end::Menu wrapper-->
            </div>
            <!--end::Activities-->
        </div>
        <!--end::Navbar-->
    </div>
    <!--end::Header container-->
</div>
<!--end::Header-->
