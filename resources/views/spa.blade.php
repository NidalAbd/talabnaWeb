<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>طلبنا - Talabna | أكبر سوق للإعلانات المبوبة</title>
    <meta name="description" content="طلبنا - أكبر منصة للإعلانات المبوبة في الوطن العربي. بيع واشتري السيارات، العقارات، الهواتف، الوظائف والمزيد بسهولة وأمان.">
    <meta name="keywords" content="إعلانات مبوبة, سيارات للبيع, عقارات, وظائف, هواتف, بيع وشراء, classified ads">
    <meta name="author" content="Talabna">
    <meta name="robots" content="index, follow">

    <!-- OpenGraph Meta -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Talabna - طلبنا">
    <meta property="og:title" content="طلبنا - Talabna | أكبر سوق للإعلانات المبوبة">
    <meta property="og:description" content="أكبر منصة للإعلانات المبوبة في الوطن العربي">
    <meta property="og:image" content="{{ asset('storage/photos/og-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="ar_SA">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="طلبنا - Talabna">
    <meta name="twitter:description" content="أكبر منصة للإعلانات المبوبة">
    <meta name="twitter:image" content="{{ asset('storage/photos/og-image.jpg') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/photos/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/photos/apple-touch-icon.png') }}">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        /* Initial loading state */
        #app-loader {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fc;
            z-index: 9999;
        }
        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e0e0e0;
            border-top-color: #5035FF;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Initial Loader -->
    <div id="app-loader">
        <div class="loader-spinner"></div>
    </div>

    <!-- Vue App Mount Point -->
    <div id="app"></div>

    <script>
        // Remove loader when Vue app is ready
        window.addEventListener('load', function() {
            setTimeout(function() {
                var loader = document.getElementById('app-loader');
                if (loader) loader.style.display = 'none';
            }, 100);
        });
    </script>
</body>
</html>
