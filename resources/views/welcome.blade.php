{{-- welcome.blade.php --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Talabna') }}</title>
    <link rel="icon" href="{{ asset('storage/photos/favicon.ico') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <style>
        :root {
            --primary: #5035FF;
            --primary-dark: #4026e6;
            --primary-light: #6e5aff;
            --primary-bg: #f0edff;
            --secondary: #6C757D;
            --success: #00D97E;
            --danger: #FF3D57;
            --warning: #FFAB2D;
            --info: #3EC1D3;
            --dark: #192A3E;
            --light: #F5F6FA;
            --white: #ffffff;
            --border-radius-sm: 8px;
            --border-radius: 12px;
            --border-radius-lg: 20px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            color: var(--dark);
            background-color: #f8f9fc;
            line-height: 1.6;
        }

        /* Header styles */
        .header {
            background-color: var(--white);
            box-shadow: var(--box-shadow);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
            color: var(--primary);
        }

        .main-nav .nav-link {
            color: var(--dark);
            font-weight: 500;
            padding: 8px 20px;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
        }

        .main-nav .nav-link:hover {
            color: var(--primary);
            background-color: rgba(80, 53, 255, 0.05);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: var(--border-radius-sm);
            padding: 10px 20px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            border-radius: var(--border-radius-sm);
            padding: 10px 20px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Hero section */
        .hero-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -150px;
            left: -80px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
            padding: 12px 24px;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
        }

        .hero-image {
            max-width: 100%;
            position: relative;
            z-index: 2;
        }

        /* Category section */
        .section {
            padding: 80px 0;
        }

        .section-light {
            background-color: var(--light);
        }

        .section-title {
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 4px;
            background-color: var(--primary);
            border-radius: 10px;
        }

        .section-description {
            color: var(--secondary);
            max-width: 700px;
            margin-bottom: 3rem;
        }

        /* Category cards */
        .category-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            text-decoration: none;
            color: var(--dark);
            display: block;
            height: 100%;
            text-align: center;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            color: var(--primary);
        }

        .category-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: var(--primary);
            background-color: var(--primary-bg);
            border-radius: 50%;
            transition: var(--transition);
        }

        .category-card:hover .category-icon {
            background-color: var(--primary);
            color: var(--white);
        }

        .category-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .category-count {
            color: var(--secondary);
            font-size: 14px;
        }

        /* Featured posts */
        .featured-posts {
            padding: 80px 0;
        }

        .post-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            transition: var(--transition);
            height: 100%;
        }

        .post-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .post-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .post-image img, .post-image video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .post-card:hover .post-image img,
        .post-card:hover .post-image video {
            transform: scale(1.05);
        }

        /* Video controls */
        .post-image video {
            background-color: #000;
            cursor: pointer;
        }

        .post-image .video-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background-color: rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            transition: var(--transition);
            z-index: 2;
        }

        .post-image .video-indicator i {
            color: white;
            font-size: 24px;
            margin-left: 5px; /* Slight offset for play triangle */
        }

        .post-card:hover .video-indicator {
            background-color: var(--primary);
        }

        .post-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1;
        }

        .post-badge.ماسي {
            background-color: #A239EA;
            color: white;
        }

        .post-badge.ذهبي {
            background-color: #FFD700;
            color: #333;
        }

        .post-badge.عادي {
            background-color: var(--light);
            color: var(--dark);
        }

        .post-content {
            padding: 20px;
        }

        .post-category {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }

        .post-title {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 18px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .post-description {
            color: var(--secondary);
            font-size: 14px;
            margin-bottom: 15px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            color: var(--secondary);
            font-size: 13px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .post-date i, .post-views i, .post-likes i {
            margin-right: 5px;
            font-size: 14px;
        }

        .post-price {
            font-weight: 700;
            color: var(--success);
            margin-bottom: 15px;
        }

        /* Stats section */
        .stats-section {
            background-color: var(--primary-bg);
            padding: 60px 0;
        }

        .stats-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            flex: 1;
            min-width: 200px;
            margin: 10px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--dark);
            font-weight: 500;
        }

        /* Subcategory section */
        .subcategory-section {
            background-color: var(--white);
            padding: 80px 0;
        }

        .subcategory-tabs {
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 15px;
            display: flex;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
        }

        .subcategory-tabs::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Edge */
        }

        .subcategory-tab {
            padding: 10px 20px;
            margin-right: 10px;
            background-color: var(--light);
            color: var(--dark);
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .subcategory-tab.active {
            background-color: var(--primary);
            color: var(--white);
        }

        .subcategory-tab:hover:not(.active) {
            background-color: var(--primary-bg);
        }

        .subcategory-content {
            display: none;
        }

        .subcategory-content.active {
            display: block;
        }

        /* Latest posts */
        .latest-posts {
            padding: 80px 0;
            background-color: var(--light);
        }

        .swiper {
            width: 100%;
            padding-bottom: 40px;
        }

        .swiper-pagination-bullet-active {
            background-color: var(--primary);
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: var(--primary);
        }

        /* App banner */
        .app-banner {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            padding: 80px 0;
            color: var(--white);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .app-banner::before {
            content: '';
            position: absolute;
            top: -200px;
            right: -200px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .app-banner::after {
            content: '';
            position: absolute;
            bottom: -200px;
            left: -200px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .app-banner-content {
            position: relative;
            z-index: 1;
            max-width: 700px;
            margin: 0 auto;
        }

        .app-banner-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .app-banner-description {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .app-btn {
            background-color: var(--white);
            color: var(--primary);
            border-radius: var(--border-radius-sm);
            padding: 15px 30px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: var(--transition);
            text-decoration: none;
        }

        .app-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            color: var(--primary);
        }

        .app-btn i {
            font-size: 24px;
            margin-right: 10px;
        }

        /* Footer */
        .footer {
            background-color: var(--dark);
            color: var(--light);
            padding: 80px 0 30px;
        }

        .footer-logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 20px;
            display: block;
        }

        .footer-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 25px;
        }

        .footer-heading {
            font-size: 18px;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-heading::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 10px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--white);
            padding-left: 5px;
        }

        .footer-social {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        .footer-social li {
            margin-right: 15px;
        }

        .footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white);
            transition: var(--transition);
        }

        .footer-social a:hover {
            background-color: var(--primary);
            transform: translateY(-5px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            margin-top: 50px;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }

        .no-image-placeholder {
            background-color: var(--primary-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--primary);
            font-size: 40px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-section {
                padding: 60px 0;
            }

            .hero-image {
                margin-top: 40px;
            }

            .section {
                padding: 60px 0;
            }

            .app-banner-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-section {
                padding: 50px 0;
            }

            .stat-item {
                min-width: 150px;
            }

            .section {
                padding: 50px 0;
            }

            .app-banner-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .hero-buttons .btn {
                display: block;
                width: 100%;
                margin-right: 0;
                margin-bottom: 15px;
            }

            .stats-container {
                flex-direction: column;
            }

            .stat-item {
                width: 100%;
                margin: 10px 0;
            }

            .app-btn {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container">
        <nav class="navbar navbar-expand-lg">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Talabna') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto main-nav">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="btn btn-outline-primary me-2" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif
                    @else
                        @if(Auth::user()->hasRole(['admin']))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->user_name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>
    </div>
</header>

@auth
    @if(!Auth::user()->hasRole(['admin']))
        <div class="app-action-banner py-3" style="background-color: var(--primary); color: white;">
            <div class="container">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div class="mb-3 mb-md-0">
                        <h5 class="mb-1">Use the Talabna App for Full Features!</h5>
                        <p class="mb-0">Post services, manage your listings, and connect with others directly from the app.</p>
                    </div>
                    <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" class="btn btn-light" style="color: var(--primary);" target="_blank">
                        <i class="fab fa-google-play me-2"></i> Download Now
                    </a>
                </div>
            </div>
        </div>
    @endif
@endauth

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">Find & Offer Services with Talabna</h1>
                    <p class="hero-subtitle">Connect with local service providers or offer your services to thousands of users. Your one-stop platform for all service needs.</p>
                    <div class="hero-buttons">
                        <a href="#categories" class="btn btn-light">Explore Services</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-light">Join Now</a>
                    </div>
                </div>
            </div>
                <div class="col-lg-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" class="hero-image img-fluid">
                        <!-- Background gradient -->
                        <defs>
                            <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#6e5aff" />
                                <stop offset="100%" stop-color="#5035FF" />
                            </linearGradient>
                            <linearGradient id="cardGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffffff" />
                                <stop offset="100%" stop-color="#f5f6fa" />
                            </linearGradient>
                            <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feGaussianBlur in="SourceAlpha" stdDeviation="10" />
                                <feOffset dx="0" dy="10" result="offsetblur" />
                                <feComponentTransfer>
                                    <feFuncA type="linear" slope="0.3" />
                                </feComponentTransfer>
                                <feMerge>
                                    <feMergeNode />
                                    <feMergeNode in="SourceGraphic" />
                                </feMerge>
                            </filter>
                        </defs>

                        <!-- Background with abstract shapes -->
                        <rect width="800" height="500" fill="url(#bgGradient)" />

                        <!-- Abstract circles for design -->
                        <circle cx="700" cy="100" r="150" fill="#8a79ff" opacity="0.2" />
                        <circle cx="100" cy="400" r="200" fill="#8a79ff" opacity="0.2" />

                        <!-- Service cards -->
                        <!-- Card 1: Mobile & Devices -->
                        <g transform="translate(150, 150)" filter="url(#shadow)">
                            <rect width="180" height="220" rx="15" fill="url(#cardGradient)" />
                            <circle cx="90" cy="70" r="40" fill="#f0edff" />
                            <path d="M78 50 L78 90 L102 90 L102 50 L78 50 Z M90 82 C88.3 82 87 83.3 87 85 C87 86.7 88.3 88 90 88 C91.7 88 93 86.7 93 85 C93 83.3 91.7 82 90 82 Z" fill="#5035FF" />
                            <text x="90" y="130" font-family="Arial, sans-serif" font-size="16" font-weight="bold" fill="#192A3E" text-anchor="middle">Mobile</text>
                            <text x="90" y="155" font-family="Arial, sans-serif" font-size="14" fill="#6C757D" text-anchor="middle">350+ Services</text>
                        </g>

                        <!-- Card 2: Real Estate -->
                        <g transform="translate(360, 120)" filter="url(#shadow)">
                            <rect width="180" height="220" rx="15" fill="url(#cardGradient)" />
                            <circle cx="90" cy="70" r="40" fill="#f0edff" />
                            <path d="M65 90 L90 55 L115 90 L65 90 Z M75 90 L75 105 L105 105 L105 90 L75 90 Z" fill="#5035FF" />
                            <text x="90" y="130" font-family="Arial, sans-serif" font-size="16" font-weight="bold" fill="#192A3E" text-anchor="middle">Real Estate</text>
                            <text x="90" y="155" font-family="Arial, sans-serif" font-size="14" fill="#6C757D" text-anchor="middle">420+ Listings</text>
                        </g>

                        <!-- Card 3: Jobs -->
                        <g transform="translate(570, 180)" filter="url(#shadow)">
                            <rect width="180" height="220" rx="15" fill="url(#cardGradient)" />
                            <circle cx="90" cy="70" r="40" fill="#f0edff" />
                            <path d="M75 50 L105 50 L105 60 L115 60 L115 100 L65 100 L65 60 L75 60 L75 50 Z M90 60 L90.001 60 Z" fill="#5035FF" />
                            <text x="90" y="130" font-family="Arial, sans-serif" font-size="16" font-weight="bold" fill="#192A3E" text-anchor="middle">Jobs</text>
                            <text x="90" y="155" font-family="Arial, sans-serif" font-size="14" fill="#6C757D" text-anchor="middle">280+ Positions</text>
                        </g>

                        <!-- Search bar -->
                        <g transform="translate(250, 370)" filter="url(#shadow)">
                            <rect width="300" height="60" rx="30" fill="white" />
                            <circle cx="40" cy="30" r="15" fill="none" stroke="#5035FF" stroke-width="3" />
                            <line x1="50" y1="40" x2="60" y2="50" stroke="#5035FF" stroke-width="3" stroke-linecap="round" />
                            <text x="115" y="35" font-family="Arial, sans-serif" font-size="16" fill="#6C757D" text-anchor="middle">Find services...</text>
                            <rect x="200" y="10" width="80" height="40" rx="20" fill="#5035FF" />
                            <text x="240" y="35" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">Search</text>
                        </g>

                        <!-- Logo text -->
                        <text x="120" y="80" font-family="Arial, sans-serif" font-size="36" font-weight="bold" fill="white">TALABNA</text>
                        <text x="120" y="110" font-family="Arial, sans-serif" font-size="18" fill="rgba(255,255,255,0.8)">Find & Offer Services</text>
                    </svg>
                </div>.
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number">{{ number_format($allService) }}</div>
                <div class="stat-label">Active Services</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ number_format($users) }}</div>
                <div class="stat-label">Registered Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ number_format($allDiamond + $allGolden) }}</div>
                <div class="stat-label">Premium Services</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ number_format($purchaseRequests) }}</div>
                <div class="stat-label">Transactions</div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section" id="categories">
    <div class="container">
        <h2 class="section-title">Browse Service Categories</h2>
        <p class="section-description">Explore our wide range of service categories and find exactly what you're looking for.</p>

        <div class="row">
            @php
                // Define category icons mapping
                $categoryIcons = [
                    1 => 'fas fa-mobile-alt',       // Mobile & Devices
                    2 => 'fas fa-car',              // Vehicles
                    3 => 'fas fa-briefcase',        // Jobs
                    4 => 'fas fa-building',         // Real Estate
                    5 => 'fas fa-tools',            // General Services
                ];
            @endphp

            @foreach($categories as $category)
                <div class="col-lg-4 col-md-6">
                    <a href="#" class="category-card">
                        <div class="category-icon">
                            <i class="{{ $categoryIcons[$category->id] ?? 'fas fa-list' }}"></i>
                        </div>
                        <h3 class="category-name">{{ is_array($category->name) ? $category->name[app()->getLocale()] : $category->name }}</h3>
                        <div class="category-count">{{ number_format($category->service_posts_count) }} Services</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Posts -->
<section class="featured-posts section-light">
    <div class="container">
        <h2 class="section-title">Featured Services</h2>
        <p class="section-description">Discover our top-rated premium services with excellent reviews from our users.</p>

        <div class="row">
            @forelse($featuredPosts as $post)
                <div class="col-lg-4 col-md-6">
                    <div class="post-card">
                        <div class="post-image">
                            @if($post->photos->count() > 0)
                                @php $firstMedia = $post->photos->first(); @endphp
                                @if($firstMedia->isVideo)
                                    <div class="video-indicator">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    <video class="w-100 h-100 object-fit-cover" controls>
                                        <source src="{{ asset($firstMedia->src) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                @else
                                    <img src="{{ asset($firstMedia->src) }}" alt="{{ $post->title }}">
                                @endif
                            @else
                                <div class="no-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                            <div class="post-badge {{ $post->have_badge }}">
                                @if($post->have_badge == 'ماسي')
                                    <i class="fas fa-gem"></i> Diamond
                                @elseif($post->have_badge == 'ذهبي')
                                    <i class="fas fa-medal"></i> Gold
                                @else
                                    <i class="fas fa-check"></i> Standard
                                @endif
                            </div>
                        </div>
                        <div class="post-content">
                            <span class="post-category">
                                {{ is_array($post->category->name) ? $post->category->name[app()->getLocale()] : $post->category->name }}
                                -
                                {{ is_array($post->subCategory->name) ? $post->subCategory->name[app()->getLocale()] : $post->subCategory->name }}
                            </span>
                            <h3 class="post-title">{{ $post->title }}</h3>
                            <p class="post-description">{{ Str::limit($post->description, 100) }}</p>
                            <div class="post-price">{{ number_format($post->price) }} {{ $post->price_currency_code }}</div>
                            <div class="post-meta">
                                <span class="post-date"><i class="far fa-clock"></i> {{ $post->created_at->diffForHumans() }}</span>
                                <span class="post-views"><i class="far fa-eye"></i> {{ $post->view_count }}</span>
                                <span class="post-likes"><i class="far fa-heart"></i> {{ $post->favorites_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p>No featured posts available at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Subcategory Section -->
<section class="subcategory-section">
    <div class="container">
        <h2 class="section-title">Explore Subcategories</h2>
        <p class="section-description">Find specific services by browsing through our detailed subcategories.</p>

        <!-- Tabs for different main categories -->
        <div class="subcategory-tabs">
            @foreach($categories as $index => $category)
                <div class="subcategory-tab {{ $index === 0 ? 'active' : '' }}" data-category="{{ $category->id }}">
                    {{ is_array($category->name) ? $category->name[app()->getLocale()] : $category->name }}
                </div>
            @endforeach
        </div>

        <!-- Subcategory content -->
        @foreach($categories as $index => $category)
            <div class="subcategory-content {{ $index === 0 ? 'active' : '' }}" id="subcategory-{{ $category->id }}">
                <div class="row">
                    @forelse($subcategories[$category->id] as $subcategory)
                        <div class="col-lg-3 col-md-4 col-6">
                            <a href="#" class="category-card">
                                <div class="category-icon">
                                    <i class="{{ $categoryIcons[$category->id] ?? 'fas fa-list' }}"></i>
                                </div>
                                <h3 class="category-name">{{ is_array($subcategory->name) ? $subcategory->name[app()->getLocale()] : $subcategory->name }}</h3>
                                <div class="category-count">{{ number_format($subcategory->service_posts_count) }} Items</div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12 text-center">
                            <p>No subcategories available for this category.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Latest posts for this category -->
                <div class="mt-5">
                    <h3 class="mb-4">Latest in {{ is_array($category->name) ? $category->name[app()->getLocale()] : $category->name }}</h3>
                    <div class="row">
                        @forelse($latestByCategory[$category->id] as $post)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="post-card">
                                    <div class="post-image">
                                        @if($post->photos->count() > 0)
                                            @php $firstMedia = $post->photos->first(); @endphp
                                            @if($firstMedia->isVideo)
                                                <div class="video-indicator">
                                                    <i class="fas fa-play"></i>
                                                </div>
                                                <video class="w-100 h-100 object-fit-cover" controls>
                                                    <source src="{{ asset($firstMedia->src) }}" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            @else
                                                <img src="{{ asset($firstMedia->src) }}" alt="{{ $post->title }}">
                                            @endif
                                        @else
                                            <div class="no-image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                        <div class="post-badge {{ $post->have_badge }}">
                                            @if($post->have_badge == 'ماسي')
                                                <i class="fas fa-gem"></i> Diamond
                                            @elseif($post->have_badge == 'ذهبي')
                                                <i class="fas fa-medal"></i> Gold
                                            @else
                                                <i class="fas fa-check"></i> Standard
                                            @endif
                                        </div>
                                    </div>
                                    <div class="post-content">
                                    <span class="post-category">
                                        {{ is_array($post->subCategory->name) ? $post->subCategory->name[app()->getLocale()] : $post->subCategory->name }}
                                    </span>
                                        <h3 class="post-title">{{ $post->title }}</h3>
                                        <p class="post-description">{{ Str::limit($post->description, 80) }}</p>
                                        <div class="post-price">{{ number_format($post->price) }} {{ $post->price_currency_code }}</div>
                                        <div class="post-meta">
                                            <span class="post-date"><i class="far fa-clock"></i> {{ $post->created_at->diffForHumans() }}</span>
                                            <span class="post-views"><i class="far fa-eye"></i> {{ $post->view_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center">
                                <p>No posts available in this category yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Latest Posts Section -->
<section class="latest-posts">
    <div class="container">
        <h2 class="section-title">Latest Services</h2>
        <p class="section-description">Check out the newest services added to our platform.</p>

        <!-- Swiper slider for latest posts -->
        <div class="swiper">
            <div class="swiper-wrapper">
                @forelse($latestPosts as $post)
                    <div class="swiper-slide">
                        <div class="post-card">
                            <div class="post-image">
                                @if($post->photos->count() > 0)
                                    @php $firstMedia = $post->photos->first(); @endphp
                                    @if($firstMedia->isVideo)
                                        <div class="video-indicator">
                                            <i class="fas fa-play"></i>
                                        </div>
                                        <video class="w-100 h-100 object-fit-cover" controls>
                                            <source src="{{ asset($firstMedia->src) }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @else
                                        <img src="{{ asset($firstMedia->src) }}" alt="{{ $post->title }}">
                                    @endif
                                @else
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                                <div class="post-badge {{ $post->have_badge }}">
                                    @if($post->have_badge == 'ماسي')
                                        <i class="fas fa-gem"></i> Diamond
                                    @elseif($post->have_badge == 'ذهبي')
                                        <i class="fas fa-medal"></i> Gold
                                    @else
                                        <i class="fas fa-check"></i> Standard
                                    @endif
                                </div>
                            </div>
                            <div class="post-content">
                                <span class="post-category">
                                    {{ is_array($post->category->name) ? $post->category->name[app()->getLocale()] : $post->category->name }}
                                </span>
                                <h3 class="post-title">{{ $post->title }}</h3>
                                <p class="post-description">{{ Str::limit($post->description, 80) }}</p>
                                <div class="post-price">{{ number_format($post->price) }} {{ $post->price_currency_code }}</div>
                                <div class="post-meta">
                                    <span class="post-date"><i class="far fa-clock"></i> {{ $post->created_at->diffForHumans() }}</span>
                                    <span class="post-views"><i class="far fa-eye"></i> {{ $post->view_count }}</span>
                                    <span class="post-likes"><i class="far fa-heart"></i> {{ $post->favorites_count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="swiper-slide">
                        <div class="text-center">
                            <p>No posts available yet.</p>
                        </div>
                    </div>
                @endforelse
            </div>
            <!-- Add pagination -->
            <div class="swiper-pagination"></div>
            <!-- Add navigation buttons -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
</section>

<!-- Popular Posts Section (Most Viewed) -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Popular Services</h2>
        <p class="section-description">Discover the most viewed services on our platform.</p>

        <div class="row">
            @forelse($popularPosts as $post)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="post-card">
                        <div class="post-image">
                            @if($post->photos->count() > 0)
                                @php $firstMedia = $post->photos->first(); @endphp
                                @if($firstMedia->isVideo)
                                    <div class="video-indicator">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    <video class="w-100 h-100 object-fit-cover" controls>
                                        <source src="{{ asset($firstMedia->src) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                @else
                                    <img src="{{ asset($firstMedia->src) }}" alt="{{ $post->title }}">
                                @endif
                            @else
                                <div class="no-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                            <div class="post-badge {{ $post->have_badge }}">
                                @if($post->have_badge == 'ماسي')
                                    <i class="fas fa-gem"></i> Diamond
                                @elseif($post->have_badge == 'ذهبي')
                                    <i class="fas fa-medal"></i> Gold
                                @else
                                    <i class="fas fa-check"></i> Standard
                                @endif
                            </div>
                        </div>
                        <div class="post-content">
                            <span class="post-category">
                                {{ is_array($post->category->name) ? $post->category->name[app()->getLocale()] : $post->category->name }}
                            </span>
                            <h3 class="post-title">{{ $post->title }}</h3>
                            <p class="post-description">{{ Str::limit($post->description, 80) }}</p>
                            <div class="post-price">{{ number_format($post->price) }} {{ $post->price_currency_code }}</div>
                            <div class="post-meta">
                                <span class="post-date"><i class="far fa-clock"></i> {{ $post->created_at->diffForHumans() }}</span>
                                <span class="post-views"><i class="far fa-eye"></i> {{ $post->view_count }}</span>
                                <span class="post-likes"><i class="far fa-heart"></i> {{ $post->favorites_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p>No popular posts available yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@auth
    @if(!Auth::user()->hasRole(['admin', 'admin']))
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2>Experience Talabna on Your Mobile</h2>
                        <p class="lead">Get the full experience with our mobile app. Post services, manage your listings, receive notifications, and connect with service providers on the go.</p>
                        <div class="mt-4">
                            <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" class="btn btn-primary btn-lg" target="_blank">
                                <i class="fab fa-google-play me-2"></i> Download on Google Play
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center mt-4 mt-lg-0">
                        <i class="fas fa-mobile-alt" style="font-size: 150px; color: var(--primary);"></i>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endauth

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-5 mb-lg-0">
                <a href="{{ url('/') }}" class="footer-logo">{{ config('app.name', 'Talabna') }}</a>
                <p class="footer-description">Talabna is your one-stop platform for finding and offering services. Connect with providers or offer your expertise to thousands of users.</p>
                <ul class="footer-social">
                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                    <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-2 mb-5 mb-md-0">
                <h4 class="footer-heading">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Services</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="{{ route('login') }}">Login</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-2 mb-5 mb-md-0">
                <h4 class="footer-heading">Categories</h4>
                <ul class="footer-links">
                    @foreach($categories as $category)
                        <li>
                            <a href="#">{{ is_array($category->name) ? $category->name[app()->getLocale()] : $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-lg-3 col-md-2">
                <h4 class="footer-heading">Contact Info</h4>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt me-2"></i> 123 Business Street, City, Country</li>
                    <li><i class="fas fa-phone-alt me-2"></i> +123 456 7890</li>
                    <li><i class="fas fa-envelope me-2"></i> info@talabna.com</li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-2">
                <h4 class="footer-heading">Policy and Privacy</h4>

                <ul class="footer-links">
                    <li><a href="{{ route('policy.index') }}"><i class="fas fa-shield-alt me-2"></i> Privacy Policy</a></li>
                    <li><a href="{{ route('policy.index') }}?terms=1"><i class="fas fa-gavel me-2"></i> Terms of Service</a></li>
                    <li><a href="{{ route('policy.index') }}?child_safety=1"><i class="fas fa-child me-2"></i> Child Safety Policy</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    // Initialize Swiper
    const swiper = new Swiper('.swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 1,
            },
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        }
    });

    // Subcategory tabs
    document.querySelectorAll('.subcategory-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            document.querySelectorAll('.subcategory-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Show corresponding content
            const categoryId = this.getAttribute('data-category');
            document.querySelectorAll('.subcategory-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById('subcategory-' + categoryId).classList.add('active');
        });
    });

    // Video interaction
    document.addEventListener('DOMContentLoaded', function() {
        // Make clicking on video container play/pause the video
        document.querySelectorAll('.post-image').forEach(container => {
            const video = container.querySelector('video');
            if (video) {
                container.addEventListener('click', function(e) {
                    // Prevent click when controls are clicked
                    if (e.target === video) {
                        if (video.paused) {
                            video.play();
                        } else {
                            video.pause();
                        }
                    }
                });

                // Hide the play indicator when video starts playing
                video.addEventListener('play', function() {
                    const indicator = container.querySelector('.video-indicator');
                    if (indicator) {
                        indicator.style.opacity = '0';
                    }
                });

                // Show the play indicator when video is paused
                video.addEventListener('pause', function() {
                    const indicator = container.querySelector('.video-indicator');
                    if (indicator) {
                        indicator.style.opacity = '1';
                    }
                });
            }
        });
    });
</script>
</body>
</html>
