<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ __('policies.title') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />

    <!-- Library / Plugin Css Build -->
    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}" />

    <!-- Hope Ui Design System Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}" />

    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.min.css?v=2.0.0') }}" />

    <!-- Dark Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/dark.min.css') }}" />

    <!-- Customizer Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.min.css') }}" />

    <!-- RTL Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/rtl.min.css') }}" />

    <!-- Flatpickr css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/dist/flatpickr.min.css') }}" />

    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', sans-serif;
        }

        .policy-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .policy-content {
            background: white;
            padding: 60px 0;
        }

        .policy-section {
            margin-bottom: 40px;
        }

        .policy-section h2 {
            color: #4f46e5;
            border-bottom: 3px solid #7c3aed;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .policy-section h3 {
            color: #4f46e5;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .back-button {
            background: rgba(255,255,255,0.2) !important;
            color: #fff !important;
            border-color: rgba(255,255,255,0.5) !important;
            border-radius: 12px;
            backdrop-filter: blur(2px);
        }

        .language-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>

<body class="uikit">
    <!-- Language Switcher -->
    <div class="language-switcher">
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                {{ strtoupper(app()->getLocale()) }}
            </button>
            <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                <li><a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">English</a></li>
                <li><a class="dropdown-item" href="{{ route('lang.switch', 'fr') }}">Français</a></li>
                <li><a class="dropdown-item" href="{{ route('lang.switch', 'sw') }}">Kiswahili</a></li>
            </ul>
        </div>
    </div>

    <section class="policy-header">
        <div class="container">
            <h1 class="display-4 fw-bold">{{ __('policies.title') }}</h1>
            <p class="lead">{{ __('policies.subtitle') }}</p>
            <button onclick="history.back()" class="btn back-button mt-3">
                <i class="ri-arrow-left-line me-2"></i>{{ __('policies.back_to_site') }}
            </button>
        </div>
    </section>

    <section class="policy-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">

                    <!-- Terms of Service -->
                    <div class="policy-section">
                        <h2>{{ __('policies.terms_title') }}</h2>
                        <p class="text-muted mb-4">{{ __('policies.terms_intro') }}</p>

                        <h3>{{ __('policies.acceptance_title') }}</h3>
                        <p>{{ __('policies.acceptance_content') }}</p>

                        <h3>{{ __('policies.services_title') }}</h3>
                        <p>{{ __('policies.services_content') }}</p>

                        <h3>{{ __('policies.user_responsibilities_title') }}</h3>
                        <p>{{ __('policies.user_responsibilities_content') }}</p>

                        <h3>{{ __('policies.prohibited_activities_title') }}</h3>
                        <p>{{ __('policies.prohibited_activities_content') }}</p>

                        <h3>{{ __('policies.intellectual_property_title') }}</h3>
                        <p>{{ __('policies.intellectual_property_content') }}</p>

                        <h3>{{ __('policies.limitation_liability_title') }}</h3>
                        <p>{{ __('policies.limitation_liability_content') }}</p>

                        <h3>{{ __('policies.termination_title') }}</h3>
                        <p>{{ __('policies.termination_content') }}</p>

                        <h3>{{ __('policies.changes_title') }}</h3>
                        <p>{{ __('policies.changes_content') }}</p>
                    </div>

                    <!-- Privacy Policy -->
                    <div class="policy-section">
                        <h2>{{ __('policies.privacy_title') }}</h2>
                        <p class="text-muted mb-4">{{ __('policies.privacy_intro') }}</p>

                        <h3>{{ __('policies.information_collect_title') }}</h3>
                        <p>{{ __('policies.information_collect_content') }}</p>

                        <h3>{{ __('policies.use_information_title') }}</h3>
                        <p>{{ __('policies.use_information_content') }}</p>

                        <h3>{{ __('policies.share_information_title') }}</h3>
                        <p>{{ __('policies.share_information_content') }}</p>

                        <h3>{{ __('policies.data_security_title') }}</h3>
                        <p>{{ __('policies.data_security_content') }}</p>

                        <h3>{{ __('policies.user_rights_title') }}</h3>
                        <p>{{ __('policies.user_rights_content') }}</p>

                        <h3>{{ __('policies.cookies_title') }}</h3>
                        <p>{{ __('policies.cookies_content') }}</p>

                        <h3>{{ __('policies.contact_title') }}</h3>
                        <p>{{ __('policies.contact_content') }}</p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Library Bundle Script -->
    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>

    <!-- External Library Bundle Script -->
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>

    <!-- Widgetchart Script -->
    <script src="{{ asset('assets/js/charts/widgetcharts.js') }}"></script>

    <!-- mapchart Script -->
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

    <!-- App Script -->
    <script src="{{ asset('assets/js/hope-ui.js') }}" defer></script>

    <!-- Flatpickr Script -->
    <script src="{{ asset('assets/vendor/flatpickr/dist/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/flatpickr.js') }}" defer></script>

    <script src="{{ asset('assets/js/plugins/prism.mini.js') }}"></script>
</body>

</html>
