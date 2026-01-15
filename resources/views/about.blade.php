<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ __('welcome.title') }} - About Us</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="./assets/images/favicon.ico" />

    <!-- Library / Plugin Css Build -->
    <link rel="stylesheet" href="./assets/css/core/libs.min.css" />


    <!-- Hope Ui Design System Css -->
    <link rel="stylesheet" href="./assets/css/hope-ui.min.css?v=2.0.0" />

    <!-- Custom Css -->
    <link rel="stylesheet" href="./assets/css/custom.min.css?v=2.0.0" />

    <!-- Dark Css -->
    <link rel="stylesheet" href="./assets/css/dark.min.css" />

    <!-- Customizer Css -->
    <link rel="stylesheet" href="./assets/css/customizer.min.css" />

    <!-- RTL Css -->
    <link rel="stylesheet" href="./assets/css/rtl.min.css" />

    <!-- Flatpickr css -->
    <link rel="stylesheet" href="./assets/vendor/flatpickr/dist/flatpickr.min.css" />

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-WNGH9RL');
        window.tag_manager_event = 'about-page-preview';
        window.tag_manager_product = 'HopeUI';
    </script>
    <!-- End Google Tag Manager -->
</head>

<body class="uikit " data-bs-spy="scroll" data-bs-target="#elements-section" data-bs-offset="0" tabindex="0">
    <!-- loader Start -->
    <div id="loading">
        <div class="loader simple-loader">
            <div class="loader-body"></div>
        </div>
    </div>
    <!-- loader END -->

    <div class="wrapper">
        <span class="uisheet screen-darken"></span>
        <!-- Header Section -->
        <div class="header"
            style="background: url('./assets/images/dashboard/land.png'); background-size: cover; background-repeat: no-repeat; height: 60vh;position: relative;">
            <div class="container">
                <nav class="rounded nav navbar navbar-expand-lg navbar-light top-1">
                    <div class="container-fluid">
                        <a class="mx-2 navbar-brand" href="{{ url('/') }}">
                            <h5 class="logo-title">Stockflowkp</h5>
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbar-2" aria-controls="navbar-2" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbar-2">
                            <ul class="mb-2 navbar-nav ms-auto mb-lg-0 d-flex align-items-start">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ strtoupper(app()->getLocale()) }}
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">English</a></li>
                                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'fr') }}">Français</a></li>
                                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'sw') }}">Kiswahili</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" aria-current="page" href="{{ url('/') }}#features">{{ __('welcome.features') }}</a>
                                </li>
                                <li class="nav-item me-3">
                                    <a class="nav-link" aria-current="page" href="{{ url('/') }}#pricing">{{ __('welcome.pricing') }}</a>
                                </li>
                                <li class="nav-item me-3">
                                    <a class="nav-link" href="{{ route('policies') }}">{{ __('welcome.legal') }}</a>
                                </li>
                                <li class="nav-item me-3">
                                    <a class="nav-link" href="{{ url('/') }}#contact">Contact</a>
                                </li>
                                <li class="nav-item me-3">
                                    <a class="nav-link active" href="{{ route('about') }}">{{ __('welcome.about_us') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="btn btn-success" href="{{ route('register') }}">
                                        <svg class="icon-22" width="22" height="22" viewBox="0 0 24 24"
                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.4" fill-rule="evenodd" clip-rule="evenodd"
                                                d="M5.91064 20.5886C5.91064 19.7486 6.59064 19.0686 7.43064 19.0686C8.26064 19.0686 8.94064 19.7486 8.94064 20.5886C8.94064 21.4186 8.26064 22.0986 7.43064 22.0986C6.59064 22.0986 5.91064 21.4186 5.91064 20.5886ZM17.1606 20.5886C17.1606 19.7486 17.8406 19.0686 18.6806 19.0686C19.5106 19.0686 20.1906 19.7486 20.1906 20.5886C20.1906 21.4186 19.5106 22.0986 18.6806 22.0986C17.8406 22.0986 17.1606 21.4186 17.1606 20.5886Z"
                                                fill="currentColor"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M20.1907 6.34909C20.8007 6.34909 21.2007 6.55909 21.6007 7.01909C22.0007 7.47909 22.0707 8.13909 21.9807 8.73809L21.0307 15.2981C20.8507 16.5591 19.7707 17.4881 18.5007 17.4881H7.59074C6.26074 17.4881 5.16074 16.4681 5.05074 15.1491L4.13074 4.24809L2.62074 3.98809C2.22074 3.91809 1.94074 3.52809 2.01074 3.12809C2.08074 2.71809 2.47074 2.44809 2.88074 2.50809L5.26574 2.86809C5.60574 2.92909 5.85574 3.20809 5.88574 3.54809L6.07574 5.78809C6.10574 6.10909 6.36574 6.34909 6.68574 6.34909H20.1907ZM14.1307 11.5481H16.9007C17.3207 11.5481 17.6507 11.2081 17.6507 10.7981C17.6507 10.3781 17.3207 10.0481 16.9007 10.0481H14.1307C13.7107 10.0481 13.3807 10.3781 13.3807 10.7981C13.3807 11.2081 13.7107 11.5481 14.1307 11.5481Z"
                                                fill="currentColor"></path>
                                        </svg>
                                        {{ __('welcome.get_started') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="container d-flex align-items-center justify-content-center" style="height: 70%;">
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bold mb-4">About stockflowkp</h1>
                    <p class="lead mb-4">Empowering businesses with intelligent inventory management solutions</p>
                    <a href="{{ url('/') }}#contact" class="btn btn-light btn-lg">Get In Touch</a>
                </div>
            </div>
        </div>

        <!-- Our Story Section -->
        <section style="padding: 80px 20px; background: white;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h2 style="color: #4f46e5; margin-bottom: 30px; font-size: 2.5rem; font-weight: 700;">Our Story</h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #666; margin-bottom: 20px;">
                            stockflowkp was born from the vision to revolutionize how small and medium enterprises manage their inventory and operations.
                            We understand the challenges faced by businesses in tracking stock, managing sales, and maintaining accurate records.
                        </p>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #666; margin-bottom: 20px;">
                            Our platform combines cutting-edge technology with user-friendly design to provide comprehensive solutions
                            that grow with your business. From multi-tenant management to advanced reporting, we empower entrepreneurs
                            to focus on what matters most - growing their business.
                        </p>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h3 style="color: #4f46e5; font-size: 2rem; font-weight: 700;">500+</h3>
                                    <p style="color: #666;">Active Businesses</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h3 style="color: #4f46e5; font-size: 2rem; font-weight: 700;">10K+</h3>
                                    <p style="color: #666;">Products Managed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div style="position: relative;">
                            <img src="./assets/images/dashboard/land.png" alt="Our Story" class="img-fluid rounded shadow"
                                 style="width: 100%; max-width: 500px;">
                            <div style="position: absolute; bottom: -20px; right: -20px; background: #4f46e5; color: white; padding: 20px; border-radius: 10px; box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);">
                                <h5 style="margin: 0;">Trusted by</h5>
                                <p style="margin: 0; opacity: 0.9;">Businesses Worldwide</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Vision Section -->
        <section id="vision" style="padding: 80px 20px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 style="color: #4f46e5; margin-bottom: 20px; font-size: 2.5rem; font-weight: 700;">Our Vision</h2>
                    <p style="font-size: 1.1rem; color: #666; max-width: 600px; margin: 0 auto;">
                        To be the leading platform that empowers every business, regardless of size, to achieve operational excellence
                        through intelligent inventory management and seamless business operations.
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="text-center h-100">
                            <div style="width: 80px; height: 80px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                                <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f46e5; margin-bottom: 15px;">Innovation</h4>
                            <p>Continuously evolving our platform with the latest technology to meet changing business needs.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="text-center h-100">
                            <div style="width: 80px; height: 80px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                                <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                                    <path d="M17 20c0 1.1-.9 2-2 2H9c-1.1 0-2-.9-2-2V7c0-1.1.9-2 2-2h6c1.1 0 2 .9 2 2v13zm3-16H4c-.55 0-1 .45-1 1s.45 1 1 1h1v15c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4V7h1c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f46e5; margin-bottom: 15px;">Accessibility</h4>
                            <p>Making powerful business tools accessible to entrepreneurs of all sizes and technical backgrounds.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="text-center h-100">
                            <div style="width: 80px; height: 80px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                                <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                                    <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zm4 18v-6h2.5l-2.54-7.63C19.68 7.55 18.92 7 18.06 7h-.12c-.86 0-1.63.55-1.9 1.37L13.5 16H16v6c0 1.1-.9 2-2 2s-2-.9-2-2v-6c0-2.21 1.79-4 4-4h.5l2.09 6.26c.14.44.49.74.94.74H22c.55 0 1-.45 1-1s-.45-1-1-1h-1.5zM10 12c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0-6c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4z"/>
                                </svg>
                            </div>
                            <h4 style="color: #4f46e5; margin-bottom: 15px;">Community</h4>
                            <p>Building a supportive community of businesses that grow and succeed together.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Mission Section -->
        <section style="padding: 80px 20px; background: white;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 order-lg-2">
                        <h2 style="color: #4f46e5; margin-bottom: 30px; font-size: 2.5rem; font-weight: 700;">Our Mission</h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #666; margin-bottom: 20px;">
                            To provide businesses with intelligent, scalable inventory management solutions that eliminate operational
                            inefficiencies and drive sustainable growth. We believe that every business deserves access to enterprise-grade
                            tools that are simple to use and powerful in capability.
                        </p>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 15px; font-size: 1.1rem; color: #666;">
                                <i class="text-success me-2">✓</i> Streamline inventory tracking and management
                            </li>
                            <li style="margin-bottom: 15px; font-size: 1.1rem; color: #666;">
                                <i class="text-success me-2">✓</i> Enable data-driven business decisions
                            </li>
                            <li style="margin-bottom: 15px; font-size: 1.1rem; color: #666;">
                                <i class="text-success me-2">✓</i> Foster business growth through technology
                            </li>
                            <li style="margin-bottom: 15px; font-size: 1.1rem; color: #666;">
                                <i class="text-success me-2">✓</i> Provide exceptional customer support
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6 order-lg-1">
                        <div style="position: relative;">
                            <div style="background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 20px; padding: 40px; color: white; box-shadow: 0 20px 40px rgba(79, 70, 229, 0.2);">
                                <h3 style="margin-bottom: 20px;">Why Choose stockflowkp?</h3>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-center mb-3">
                                            <div style="font-size: 2rem; font-weight: 700;">99.9%</div>
                                            <small>Uptime</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center mb-3">
                                            <div style="font-size: 2rem; font-weight: 700;">24/7</div>
                                            <small>Support</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div style="font-size: 2rem; font-weight: 700;">100%</div>
                                            <small>Secure</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div style="font-size: 2rem; font-weight: 700;">∞</div>
                                            <small>Scalable</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section style="padding: 80px 20px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 style="color: #4f46e5; margin-bottom: 20px; font-size: 2.5rem; font-weight: 700;">Meet Our Team</h2>
                    <p style="font-size: 1.1rem; color: #666; max-width: 600px; margin: 0 auto;">
                        Passionate professionals dedicated to transforming how businesses operate.
                    </p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="text-center">
                            <div style="width: 120px; height: 120px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                                <svg width="60" height="60" fill="white" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                            <h5 style="color: #4f46e5; margin-bottom: 5px;">Development Team</h5>
                            <p style="color: #666; font-size: 0.9rem;">Building the future of business management</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="text-center">
                            <div style="width: 120px; height: 120px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                                <svg width="60" height="60" fill="white" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H8V9h4v8zm6 0h-4V7h4v10z"/>
                                </svg>
                            </div>
                            <h5 style="color: #4f46e5; margin-bottom: 5px;">Business Analysts</h5>
                            <p style="color: #666; font-size: 0.9rem;">Understanding and optimizing business processes</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="text-center">
                            <div style="width: 120px; height: 120px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                                <svg width="60" height="60" fill="white" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <h5 style="color: #4f46e5; margin-bottom: 5px;">Quality Assurance</h5>
                            <p style="color: #666; font-size: 0.9rem;">Ensuring excellence in every feature</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="text-center">
                            <div style="width: 120px; height: 120px; background: linear-gradient(45deg, #4f46e5, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);">
                                <svg width="60" height="60" fill="white" viewBox="0 0 24 24">
                                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                </svg>
                            </div>
                            <h5 style="color: #4f46e5; margin-bottom: 5px;">Customer Support</h5>
                            <p style="color: #666; font-size: 0.9rem;">Always here to help our users succeed</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section style="padding: 80px 20px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); text-align: center;">
            <div class="container">
                <h2 style="color: white; margin-bottom: 20px; font-size: 2.5rem; font-weight: 700;">Ready to Transform Your Business?</h2>
                <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Join thousands of businesses already using stockflowkp to streamline their operations and drive growth.
                </p>
                <div>
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg me-3">Start Free Trial</a>
                    <a href="{{ url('/') }}#contact" class="btn btn-outline-light btn-lg">Contact Us</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer style="background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%); color: white; padding: 60px 20px 20px;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <h5 style="color: white; margin-bottom: 20px;">stockflowkp</h5>
                        <p style="opacity: 0.8; line-height: 1.6;">{{ __('welcome.hero_subtitle') }}</p>
                        <div style="margin-top: 20px;">
                            <a href="#" style="color: white; margin-right: 15px; font-size: 24px;"><i class="fab fa-facebook"></i></a>
                            <a href="#" style="color: white; margin-right: 15px; font-size: 24px;"><i class="fab fa-twitter"></i></a>
                            <a href="#" style="color: white; margin-right: 15px; font-size: 24px;"><i class="fab fa-linkedin"></i></a>
                            <a href="#" style="color: white; font-size: 24px;"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h6 style="color: white; margin-bottom: 20px;">{{ __('welcome.product') }}</h6>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 10px;"><a href="{{ url('/') }}#features" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.features') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="{{ url('/') }}#pricing" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.pricing') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.api') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.integrations') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h6 style="color: white; margin-bottom: 20px;">{{ __('welcome.company') }}</h6>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 10px;"><a href="{{ route('about') }}" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.about_us') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.careers') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.blog') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="{{ url('/') }}#contact" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.contact') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h6 style="color: white; margin-bottom: 20px;">{{ __('welcome.support') }}</h6>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.help_center') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.documentation') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.community') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.status') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4">
                        <h6 style="color: white; margin-bottom: 20px;">{{ __('welcome.legal') }}</h6>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.privacy_policy') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.terms_of_service') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.cookie_policy') }}</a></li>
                            <li style="margin-bottom: 10px;"><a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none;">{{ __('welcome.gdpr') }}</a></li>
                        </ul>
                    </div>
                </div>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 40px 0;">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p style="margin: 0; opacity: 0.8;">{{ __('welcome.copyright') }}
                            <script>document.write(new Date().getFullYear())</script>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p style="margin: 0; opacity: 0.8;">{{ __('welcome.footer_tagline') }}</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Library Bundle Script -->
    <script src="./assets/js/core/libs.min.js"></script>

    <!-- External Library Bundle Script -->
    <script src="./assets/js/core/external.min.js"></script>

    <!-- Widgetchart Script -->
    <script src="./assets/js/charts/widgetcharts.js"></script>

    <!-- mapchart Script -->
    <script src="./assets/js/charts/vectore-chart.js"></script>
    <script src="./assets/js/charts/dashboard.js"></script>

    <!-- fslightbox Script -->
    <script src="./assets/js/plugins/fslightbox.js"></script>

    <!-- Settings Script -->
    <script src="./assets/js/plugins/setting.js"></script>

    <!-- Slider-tab Script -->
    <script src="./assets/js/plugins/slider-tabs.js"></script>

    <!-- Form Wizard Script -->
    <script src="./assets/js/plugins/form-wizard.js"></script>

    <!-- AOS Animation Plugin-->
    <script src="./assets/js/plugins/aos.js"></script>
    <script>
        AOS.init();
    </script>

    <!-- App Script -->
    <script src="./assets/js/hope-ui.js" defer></script>

    <!-- Flatpickr Script -->
    <script src="./assets/vendor/flatpickr/dist/flatpickr.min.js"></script>
    <script src="./assets/js/plugins/flatpickr.js" defer></script>

    <script src="./assets/js/plugins/prism.mini.js"></script>
</body>

</html>
