@php
    use Illuminate\Support\Str;
    $currentRoute = Route::currentRouteName();
    $productRoutes = ['manageproduct', 'officer.product.create', 'officer.sales'];
    $loanRoutes = ['officer.loanmanagement', 'officer.loan.aging.analysis'];
@endphp
<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>STOCKFLOWKP - Officer Panel</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/aos/dist/aos.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.min.css?v=2.0.0') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/dark.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/rtl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/mckenziearts/laravel-notify/dist/notify.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    @stack('styles')

    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body class="  ">
    <!-- Session Keep-Alive -->
    <img src="/ping" style="display: none;" alt="keep-alive">

    <!-- loader Start -->
    <div id="loading">
        <div class="loader simple-loader">
            <div class="loader-body"></div>
        </div>
    </div>
    <!-- loader END -->

    <aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all ">
        <div class="sidebar-header d-flex align-items-center justify-content-start">
            <a href="{{ route('officer.dashboard') }}" class="navbar-brand">
                <div class="logo-main">
                    <div class="logo-normal">
                        <svg class=" icon-30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2"
                                transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                            <rect x="7.72803" y="27.728" width="28" height="4" rx="2"
                                transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                            <rect x="10.5366" y="16.3945" width="16" height="4" rx="2"
                                transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                            <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2"
                                transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                        </svg>
                    </div>
                    <div class="logo-mini">
                        <svg class=" icon-30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2"
                                transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                            <rect x="7.72803" y="27.728" width="28" height="4" rx="2"
                                transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                            <rect x="10.5366" y="16.3945" width="16" height="4" rx="2"
                                transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                            <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2"
                                transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                        </svg>
                    </div>
                </div>
                <!--logo End-->
                <h4 class="logo-title">STOCKFLOWKP</h4>
                <span class="badge bg-info">Officer</span>
            </a>
            <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                <i class="icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </i>
            </div>
        </div>

        @include('notify::components.notify')

        <div class="sidebar-body pt-0 data-scrollbar">
            <div class="sidebar-list">
                <!-- Sidebar Menu Start -->
                <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                    <li class="nav-item static-item">
                        <a class="nav-link static-item disabled" href="#" tabindex="-1">
                            <span class="default-icon">Officer Panel</span>
                            <span class="mini-icon">-</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentRoute === 'officer.dashboard' ? 'active' : '' }}"
                            href="{{ route('officer.dashboard') }}">
                            <i class="icon">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                    <path opacity="0.4"
                                        d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z"
                                        fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z"
                                        fill="currentColor"></path>
                                </svg>
                            </i>
                            <span class="item-name">Dashboard</span>
                        </a>
                    </li>





                    <!-- Product Management -->
                    @if (auth()->user()->hasPermission('adding_product'))
                        <li class="nav-item">
                            <a class="nav-link {{ in_array($currentRoute, $productRoutes) ? 'active' : '' }}"
                                href="#product-menu" data-bs-toggle="collapse" aria-expanded="false">
                                <i class="icon">
                                    <svg width="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                        <path opacity="0.4"
                                            d="M9.07861 16.1355H14.8936C15.4766 16.1355 15.9406 15.6645 15.9406 15.0815V12.3105C16.6176 12.0135 17.0656 11.3525 17.0656 10.5885V8.57251C17.0656 7.28551 15.8786 6.21251 14.5586 6.29551C14.2956 4.82251 13.0586 3.63651 11.5756 3.63651C10.0926 3.63651 8.85561 4.82251 8.59261 6.29551C7.27261 6.21251 6.08561 7.28551 6.08561 8.57251V10.5885C6.08561 11.3525 6.53361 12.0135 7.21061 12.3105V15.0815C7.21061 15.6645 7.67461 16.1355 8.25761 16.1355H9.07861Z"
                                            fill="currentColor"></path>
                                        <path
                                            d="M20.0691 18.2185H3.89691C2.96591 18.2185 2.21094 17.4635 2.21094 16.5325V14.6155C2.21094 13.6845 2.96591 12.9295 3.89691 12.9295H20.0691C21.0001 12.9295 21.7551 13.6845 21.7551 14.6155V16.5325C21.7551 17.4635 21.0001 18.2185 20.0691 18.2185Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </i>
                                <span class="item-name">Products</span>
                                <i class="right-icon">
                                    <svg class="icon-18" width="18" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M12 4.5C7.30558 4.5 3.5 8.30558 3.5 13C3.5 17.6944 7.30558 21.5 12 21.5C16.6944 21.5 20.5 17.6944 20.5 13C20.5 8.30558 16.6944 4.5 12 4.5ZM12 6.5C15.5899 6.5 18.5 9.41015 18.5 13C18.5 16.5899 15.5899 19.5 12 19.5C8.41015 19.5 5.5 16.5899 5.5 13C5.5 9.41015 8.41015 6.5 12 6.5Z"
                                            fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M12 8.5C10.6193 8.5 9.5 9.61929 9.5 11V13.5H11V11C11 10.7239 11.2239 10.5 11.5 10.5H12.5C12.7761 10.5 13 10.7239 13 11C13 11.2761 12.7761 11.5 12.5 11.5H11.5V13.5C11.5 13.7761 11.7239 14 12 14C12.2761 14 12.5 13.7761 12.5 13.5V11C12.5 9.61929 13.6193 8.5 15 8.5C15.2761 8.5 15.5 8.72386 15.5 9C15.5 9.27614 15.2761 9.5 15 9.5C14.7239 9.5 14.5 9.72386 14.5 10V13.5C14.5 14.8807 13.3807 16 12 16C10.6193 16 9.5 14.8807 9.5 13.5V11C9.5 9.61929 10.6193 8.5 12 8.5Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </i>
                            </a>
                            <ul class="sub-nav collapse" id="product-menu" data-bs-parent="#sidebar-menu">
                                <li class="nav-item">
                                    <a class="nav-link {{ $currentRoute === 'manageproduct' ? 'active' : '' }}"
                                        href="{{ route('manageproduct') }}">
                                        <i class="icon">
                                            <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="12" cy="12" r="2" fill="currentColor">
                                                </circle>
                                            </svg>
                                        </i>
                                        <span class="item-name">Manage Products</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $currentRoute === 'officer.product.create' ? 'active' : '' }}"
                                        href="{{ route('officer.product.create') }}">
                                        <i class="icon">
                                            <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </i>
                                        <span class="item-name">Create Product</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif



                    <li class="nav-item">
                        <a class="nav-link {{ $currentRoute === 'officer.sales' ? 'active' : '' }}"
                            href="{{ route('officer.sales') }}">
                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3 3H5L5.4 5M7 13H17L21 5H5.4M7 13L5.4 5M7 13L4.707 15.293C4.077 15.923 4.523 17 5.414 17H17M17 17C15.895 17 15 17.895 15 19C15 20.105 15.895 21 17 21C18.105 21 19 20.105 19 19C19 17.895 18.105 17 17 17ZM9 19C9 20.105 8.105 21 7 21C5.895 21 5 20.105 5 19C5 17.895 5.895 17 7 17C8.105 17 9 17.895 9 19Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </i>
                            <span class="item-name">Sales</span>
                        </a>
                    </li>

                    <!-- Customer Management -->
                    @if (auth()->user()->hasPermission('manage_customer'))
                        <li class="nav-item">
                            <a class="nav-link {{ $currentRoute === 'officer.customers.manage' ? 'active' : '' }}"
                                href="{{ route('officer.customers.manage') }}">
                                <i class="icon">
                                    <svg width="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                        <path
                                            d="M11.9488 14.54C8.49884 14.54 5.58789 15.1038 5.58789 17.2795C5.58789 19.4562 8.51765 20.0001 11.9488 20.0001C15.3988 20.0001 18.3098 19.4364 18.3098 17.2606C18.3098 15.084 15.38 14.54 11.9488 14.54Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.4"
                                            d="M11.949 12.467C14.2851 12.467 16.1583 10.5831 16.1583 8.23351C16.1583 5.88306 14.2851 4 11.949 4C9.61293 4 7.73975 5.88306 7.73975 8.23351C7.73975 10.5831 9.61293 12.467 11.949 12.467Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </i>
                                <span class="item-name">Customers</span>
                            </a>
                        </li>
                    @endif

                    <!-- Category Management -->
                    @if (auth()->user()->hasPermission('manage_category'))
                        <li class="nav-item">
                            <a class="nav-link {{ $currentRoute === 'officer.categories.manage' ? 'active' : '' }}"
                                href="{{ route('officer.categories.manage') }}">
                                <i class="icon">
                                    <svg width="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                        <path opacity="0.4"
                                            d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z"
                                            fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </i>
                                <span class="item-name">Categories</span>
                            </a>
                        </li>
                    @endif




                    @if (auth()->user()->hasPermission('sale_report'))
                        <li class="nav-item">
                            <a class="nav-link {{ in_array($currentRoute, $loanRoutes) ? 'active' : '' }}"
                                href="#loan-menu" data-bs-toggle="collapse" aria-expanded="false">
                                <i class="icon">
                                    <svg width="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                        <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </i>
                                <span class="item-name">Loan Management</span>
                                <i class="right-icon">
                                    <svg class="icon-18" width="18" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M12 4.5C7.30558 4.5 3.5 8.30558 3.5 13C3.5 17.6944 7.30558 21.5 12 21.5C16.6944 21.5 20.5 17.6944 20.5 13C20.5 8.30558 16.6944 4.5 12 4.5ZM12 6.5C15.5899 6.5 18.5 9.41015 18.5 13C18.5 16.5899 15.5899 19.5 12 19.5C8.41015 19.5 5.5 16.5899 5.5 13C5.5 9.41015 8.41015 6.5 12 6.5Z"
                                            fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M12 8.5C10.6193 8.5 9.5 9.61929 9.5 11V13.5H11V11C11 10.7239 11.2239 10.5 11.5 10.5H12.5C12.7761 10.5 13 10.7239 13 11C13 11.2761 12.7761 11.5 12.5 11.5H11.5V13.5C11.5 13.7761 11.7239 14 12 14C12.2761 14 12.5 13.7761 12.5 13.5V11C12.5 9.61929 13.6193 8.5 15 8.5C15.2761 8.5 15.5 8.72386 15.5 9C15.5 9.27614 15.2761 9.5 15 9.5C14.7239 9.5 14.5 9.72386 14.5 10V13.5C14.5 14.8807 13.3807 16 12 16C10.6193 16 9.5 14.8807 9.5 13.5V11C9.5 9.61929 10.6193 8.5 12 8.5Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </i>
                            </a>
                            <ul class="sub-nav collapse" id="loan-menu" data-bs-parent="#sidebar-menu">
                                <li class="nav-item">
                                    <a class="nav-link {{ $currentRoute === 'officer.loanmanagement' ? 'active' : '' }}"
                                        href="{{ route('officer.loanmanagement') }}">
                                        <i class="icon">
                                            <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="12" cy="12" r="2" fill="currentColor">
                                                </circle>
                                            </svg>
                                        </i>
                                        <span class="item-name">Loan Overview</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $currentRoute === 'officer.loan.aging.analysis' ? 'active' : '' }}"
                                        href="{{ route('officer.loan.aging.analysis') }}">
                                        <i class="icon">
                                            <svg class="icon-10" width="10" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </i>
                                        <span class="item-name">Aging Analysis</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <!-- Profile -->
                    <li class="nav-item">
                        <a class="nav-link {{ $currentRoute === 'officer.profile' ? 'active' : '' }}"
                            href="{{ route('officer.profile') }}">
                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M11.9488 14.54C8.49884 14.54 5.58789 15.1038 5.58789 17.2795C5.58789 19.4562 8.51765 20.0001 11.9488 20.0001C15.3988 20.0001 18.3098 19.4364 18.3098 17.2606C18.3098 15.084 15.38 14.54 11.9488 14.54Z"
                                        fill="currentColor"></path>
                                    <path opacity="0.4"
                                        d="M11.949 12.467C14.2851 12.467 16.1583 10.5831 16.1583 8.23351C16.1583 5.88306 14.2851 4 11.949 4C9.61293 4 7.73975 5.88306 7.73975 8.23351C7.73975 10.5831 9.61293 12.467 11.949 12.467Z"
                                        fill="currentColor"></path>
                                </svg>
                            </i>
                            <span class="item-name">Profile</span>
                        </a>
                    </li>
                </ul>
                <!-- Sidebar Menu End -->
            </div>
        </div>
        <div class="sidebar-footer"></div>
    </aside>

    <main class="main-content">
        <div class="position-relative iq-banner">
            <!--Nav Start-->
            <nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
                <div class="container-fluid navbar-inner">
                    <a href="{{ route('officer.dashboard') }}" class="navbar-brand">
                        <!--Logo start-->
                        <!--logo End-->

                        <!--Logo start-->
                        <div class="logo-main">
                            <div class="logo-normal">
                                <svg class="text-primary icon-30" viewBox="0 0 30 30" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2"
                                        transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                                    <rect x="7.72803" y="27.728" width="28" height="4" rx="2"
                                        transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                                    <rect x="10.5366" y="16.3945" width="16" height="4" rx="2"
                                        transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                                    <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2"
                                        transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                                </svg>
                            </div>
                            <div class="logo-mini">
                                <svg class="text-primary icon-30" viewBox="0 0 30 30" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2"
                                        transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                                    <rect x="7.72803" y="27.728" width="28" height="4" rx="2"
                                        transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                                    <rect x="10.5366" y="16.3945" width="16" height="4" rx="2"
                                        transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                                    <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2"
                                        transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                                </svg>
                            </div>
                        </div>
                        <!--logo End-->
                        <h4 class="logo-title">STOCKFLOWKP</h4>
                    </a>
                    <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                        <i class="icon">
                            <svg width="20px" class="icon-20" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M3 7V5C3 3.89543 3.89543 3 5 3H7M17 3H19C20.1046 3 21 3.89543 21 5V7M21 17V19C21 20.1046 20.1046 21 19 21H17M7 21H5C3.89543 21 3 20.1046 3 19V17"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </svg>
                        </i>
                    </div>
                    @livewire('officer-search')
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon">
                            <span class="mt-2 navbar-toggler-bar bar1"></span>
                            <span class="navbar-toggler-bar bar2"></span>
                            <span class="navbar-toggler-bar bar3"></span>
                        </span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0">
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link" id="notification-drop" data-bs-toggle="dropdown">
                                    <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M19.7695 11.6453C19.039 10.7923 18.7071 10.0531 18.7071 8.79716V8.37013C18.7071 6.73354 18.3304 5.67907 17.5115 4.62459C16.2493 2.98699 14.1244 2 12.0442 2H11.9558C9.91935 2 7.86106 2.94167 6.577 4.5128C5.71333 5.58842 5.29293 6.68822 5.29293 8.37013V8.79716C5.29293 10.0531 4.98284 10.7923 4.23049 11.6453C3.67691 12.2738 3.5 13.0815 3.5 13.9557C3.5 14.8309 3.78723 15.6598 4.36367 16.3336C5.11602 17.1413 6.17846 17.6569 7.26375 17.7466C8.83505 17.9258 10.4063 17.9933 12.0005 17.9933C13.5937 17.9933 15.165 17.8805 16.7372 17.7466C17.8215 17.6569 18.884 17.1413 19.6363 16.3336C20.2118 15.6598 20.5 14.8309 20.5 13.9557C20.5 13.0815 20.3231 12.2738 19.7695 11.6453Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.4"
                                            d="M14.0088 19.2283C13.5088 19.1215 10.4627 19.1215 9.96275 19.2283C9.53539 19.327 9.07324 19.5566 9.07324 20.0602C9.09809 20.5406 9.37935 20.9646 9.76895 21.2335L9.76795 21.2345C10.2718 21.6273 10.8632 21.877 11.4824 21.9667C11.8123 22.012 12.1482 22.01 12.4901 21.9667C13.1083 21.877 13.6997 21.6273 14.2036 21.2345L14.2026 21.2335C14.5922 20.9646 14.8734 20.5406 14.8983 20.0602C14.8983 19.5566 14.4361 19.327 14.0088 19.2283Z"
                                            fill="currentColor"></path>
                                    </svg>
                                    @php
                                        $unreadMessages = \App\Models\Message::forTenant(
                                            auth()->user()->tenant?->id ?? null,
                                        )
                                            ->whereNull('read_at')
                                            ->count();
                                    @endphp
                                    @if ($unreadMessages > 0)
                                        <span class="badge bg-danger">{{ $unreadMessages }}</span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notification-drop"
                                    style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                                    <li class="dropdown-header">
                                        <h6>Notifications</h6>
                                    </li>
                                    @php
                                        $recentMessages = \App\Models\Message::forTenant(
                                            auth()->user()->tenant?->id ?? null,
                                        )
                                            ->with('sender')
                                            ->latest()
                                            ->limit(5)
                                            ->get();
                                    @endphp
                                    @forelse($recentMessages as $message)
                                        <li>
                                            <a class="dropdown-item {{ is_null($message->read_at) ? 'bg-light' : '' }}"
                                                href="{{ route('messages.show', $message) }}">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0">
                                                        @if ($message->sender->profile_picture)
                                                            <img src="{{ asset('storage/profiles/' . $message->sender->profile_picture) }}"
                                                                alt="User" class="rounded-circle" width="32"
                                                                height="32">
                                                        @else
                                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                                style="width: 32px; height: 32px;">
                                                                <span
                                                                    class="text-white fw-bold">{{ substr($message->sender->name, 0, 1) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <div class="fw-bold">{{ $message->subject }}</div>
                                                        <div class="small text-muted">
                                                            {{ Str::limit($message->body, 50) }}</div>
                                                        <div class="small text-muted">
                                                            {{ $message->created_at->diffForHumans() }}</div>
                                                    </div>
                                                    @if (is_null($message->read_at))
                                                        <div class="flex-shrink-0">
                                                            <span class="badge bg-primary">New</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </a>
                                        </li>
                                    @empty
                                        <li>
                                            <div class="dropdown-item text-center text-muted">
                                                No notifications
                                            </div>
                                        </li>
                                    @endforelse
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-center" href="{{ route('messages.index') }}">
                                            View All Notifications
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#" class="py-0 nav-link d-flex align-items-center" href="#"
                                    id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    @if (auth()->user()->profile_picture)
                                        <img src="{{ asset('storage/profiles/' . auth()->user()->profile_picture) }}"
                                            alt="User-Profile" class="img-fluid avatar avatar-50 avatar-rounded">
                                    @else
                                        <img src="{{ asset('assets/images/avatars/01.png') }}" alt="User-Profile"
                                            class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded">
                                        <img src="{{ asset('assets/images/avatars/avtar_1.png') }}"
                                            alt="User-Profile"
                                            class="theme-color-purple-img img-fluid avatar avatar-50 avatar-rounded">
                                        <img src="{{ asset('assets/images/avatars/avtar_2.png') }}"
                                            alt="User-Profile"
                                            class="theme-color-blue-img img-fluid avatar avatar-50 avatar-rounded">
                                        <img src="{{ asset('assets/images/avatars/avtar_4.png') }}"
                                            alt="User-Profile"
                                            class="theme-color-green-img img-fluid avatar avatar-50 avatar-rounded">
                                        <img src="{{ asset('assets/images/avatars/avtar_5.png') }}"
                                            alt="User-Profile"
                                            class="theme-color-yellow-img img-fluid avatar avatar-50 avatar-rounded">
                                        <img src="{{ asset('assets/images/avatars/avtar_3.png') }}"
                                            alt="User-Profile"
                                            class="theme-color-pink-img img-fluid avatar avatar-50 avatar-rounded">
                                    @endif
                                    <div class="caption ms-3 d-none d-md-block ">
                                        <h6 class="mb-0 caption-title">{{ auth()->user()->name ?? 'Officer' }}</h6>
                                        <p class="mb-0 caption-sub-title">
                                            {{ auth()->user()->roles->first()?->name ?? 'officer' }}</p>
                                        <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="{{ route('profile') }}">Profile</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="#"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                                    </li>
                                </ul>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="iq-navbar-header" style="height: 215px;">
                <div class="container-fluid iq-container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="flex-wrap d-flex justify-content-between align-items-center">
                                <div>
                                    <h1>Hello {{ auth()->user()->name ?? 'Officer' }}!</h1>
                                    <p>Welcome to STOCKFLOWKP Officer Panel. Manage your assigned tasks and permissions.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="iq-header-img">
                    <img src="{{ asset('assets/images/dashboard/top-header.png') }}" alt="header"
                        class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX">
                    <img src="{{ asset('assets/images/dashboard/top-header1.png') }}" alt="header"
                        class="theme-color-purple-img img-fluid w-100 h-100 animated-scaleX">
                    <img src="{{ asset('assets/images/dashboard/top-header2.png') }}" alt="header"
                        class="theme-color-blue-img img-fluid w-100 h-100 animated-scaleX">
                    <img src="{{ asset('assets/images/dashboard/top-header3.png') }}" alt="header"
                        class="theme-color-green-img img-fluid w-100 h-100 animated-scaleX">
                    <img src="{{ asset('assets/images/dashboard/top-header4.png') }}" alt="header"
                        class="theme-color-yellow-img img-fluid w-100 h-100 animated-scaleX">
                    <img src="{{ asset('assets/images/dashboard/top-header5.png') }}" alt="header"
                        class="theme-color-pink-img img-fluid w-100 h-100 animated-scaleX">
                </div>
            </div> <!-- Nav Header Component End -->
            <!--Nav End-->
        </div>

        <div class="container-fluid  mt-n5 py-0">
            @yield('content')
        </div>

        <footer class="footer">
            <div class="footer-body">
                <ul class="left-panel list-inline mb-0 p-0">
                    <li class="list-inline-item"><a href="#">Officer Panel</a>
                    </li>
                </ul>
                <div class="right-panel">
                    ©
                    <script>
                        document.write(new Date().getFullYear())
                    </script> STOCKFLOWKP
                    <span class="">
                        <svg class="icon-15" width="15" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M15.85 2.50065C16.481 2.50065 17.111 2.58965 17.71 2.79065C21.401 3.99065 22.731 8.04065 21.62 11.5806C20.99 13.3896 19.96 15.0406 18.611 16.3896C16.68 18.2596 14.561 19.9196 12.28 21.3496L12.03 21.5006L11.77 21.3396C9.48102 19.9196 7.35002 18.2596 5.40102 16.3796C4.06102 15.0306 3.03002 13.3896 2.39002 11.5806C1.26002 8.04065 2.59002 3.99065 6.32102 2.76965C6.61102 2.66965 6.91002 2.59965 7.21002 2.56065H7.33002C7.61102 2.51965 7.89002 2.50065 8.17002 2.50065H8.28002C8.91002 2.51965 9.52002 2.62965 10.111 2.83065H10.17C10.21 2.84965 10.24 2.87065 10.26 2.88965C10.481 2.96065 10.69 3.04065 10.89 3.15065L11.27 3.32065C11.3618 3.36962 11.4649 3.44445 11.554 3.50912C11.6104 3.55009 11.6612 3.58699 11.7 3.61065C11.7163 3.62028 11.7329 3.62996 11.7496 3.63972C11.8354 3.68977 11.9247 3.74191 12 3.79965C13.111 2.95065 14.46 2.49065 15.85 2.50065ZM18.51 9.70065C18.92 9.68965 19.27 9.36065 19.3 8.93965V8.82065C19.33 7.41965 18.481 6.15065 17.19 5.66065C16.78 5.51965 16.33 5.74065 16.18 6.16065C16.04 6.58065 16.26 7.04065 16.68 7.18965C17.321 7.42965 17.75 8.06065 17.75 8.75965V8.79065C17.731 9.01965 17.8 9.24065 17.94 9.41065C18.08 9.58065 18.29 9.67965 18.51 9.70065Z"
                                fill="currentColor"></path>
                        </svg>
                    </span> by <a href="">STOCKFLOWKP</a>.
                </div>
            </div>
        </footer>

    </main>
    <a class="btn btn-fixed-end btn-warning btn-icon btn-setting" data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasExample" role="button" aria-controls="offcanvasExample">
        <svg width="24" viewBox="0 0 24 24" class="animated-rotate icon-24" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M20.8064 7.62361L20.184 6.54352C19.6574 5.6296 18.4905 5.31432 17.5753 5.83872V5.83872C17.1397 6.09534 16.6198 6.16815 16.1305 6.04109C15.6411 5.91402 15.2224 5.59752 14.9666 5.16137C14.8021 4.88415 14.7137 4.56839 14.7103 4.24604V4.24604C14.7251 3.72922 14.5302 3.2284 14.1698 2.85767C13.8094 2.48694 13.3143 2.27786 12.7973 2.27808H11.5433C11.0367 2.27807 10.5511 2.47991 10.1938 2.83895C9.83644 3.19798 9.63693 3.68459 9.63937 4.19112V4.19112C9.62435 5.23693 8.77224 6.07681 7.72632 6.0767C7.40397 6.07336 7.08821 5.98494 6.81099 5.82041V5.82041C5.89582 5.29601 4.72887 5.61129 4.20229 6.52522L3.5341 7.62361C3.00817 8.53639 3.31916 9.70261 4.22975 10.2323V10.2323C4.82166 10.574 5.18629 11.2056 5.18629 11.8891C5.18629 12.5725 4.82166 13.2041 4.22975 13.5458V13.5458C3.32031 14.0719 3.00898 15.2353 3.5341 16.1454V16.1454L4.16568 17.2346C4.4124 17.6798 4.82636 18.0083 5.31595 18.1474C5.80554 18.2866 6.3304 18.2249 6.77438 17.976V17.976C7.21084 17.7213 7.73094 17.6516 8.2191 17.7822C8.70725 17.9128 9.12299 18.233 9.37392 18.6717C9.53845 18.9489 9.62686 19.2646 9.63021 19.587V19.587C9.63021 20.6435 10.4867 21.5 11.5433 21.5H12.7973C13.8502 21.5001 14.7053 20.6491 14.7103 19.5962V19.5962C14.7079 19.088 14.9086 18.6 15.2679 18.2407C15.6272 17.8814 16.1152 17.6807 16.6233 17.6831C16.9449 17.6917 17.2594 17.7798 17.5387 17.9394V17.9394C18.4515 18.4653 19.6177 18.1544 20.1474 17.2438V17.2438L20.8064 16.1454C21.0615 15.7075 21.1315 15.186 21.001 14.6964C20.8704 14.2067 20.55 13.7894 20.1108 13.5367V13.5367C19.6715 13.284 19.3511 12.8666 19.2206 12.3769C19.09 11.8873 19.16 11.3658 19.4151 10.928C19.581 10.6383 19.8211 10.3982 20.1108 10.2323V10.2323C21.0159 9.70289 21.3262 8.54349 20.8064 7.63277V7.63277V7.62361Z"
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <circle cx="12.1747" cy="11.8891" r="2.63616" stroke="currentColor" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round"></circle>
        </svg>
    </a>
    </div>
    </div>
    </div>

    <!-- Library Bundle Script -->
    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>

    <!-- External Library Bundle Script -->
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>

    <!-- Widgetchart Script -->
    <script src="{{ asset('assets/js/charts/widgetcharts.js') }}"></script>
    <script src="{{ asset('assets/js/charts/vectore-chart.js') }}"></script>
    <script src="{{ asset('assets/js/charts/dashboard.js') }}"></script>

    <!-- fslightbox Script -->
    <script src="{{ asset('assets/js/plugins/fslightbox.js') }}"></script>

    <!-- Settings Script -->
    <script src="{{ asset('assets/js/plugins/setting.js') }}"></script>

    <!-- Slider-tab Script -->
    <script src="{{ asset('assets/js/plugins/slider-tabs.js') }}"></script>

    <!-- Form Wizard Script -->
    <script src="{{ asset('assets/js/plugins/form-wizard.js') }}"></script>

    <!-- AOS Animation Plugin -->
    <script src="{{ asset('assets/vendor/aos/dist/aos.js') }}"></script>

    <!-- App Script -->
    <script src="{{ asset('assets/js/hope-ui.js') }}" defer></script>
    <script src="{{ asset('vendor/mckenziearts/laravel-notify/dist/notify.js') }}"></script>


    @livewireScripts

    @stack('scripts')

</body>

</html>
