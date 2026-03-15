@php
    // Server-side SEO data generation for search engine crawlers
    $seoController = app(\App\Http\Controllers\SeoController::class);
    $seoData = $seoController->getServerSideSeo(request()->path(), 'ar');
@endphp
<!DOCTYPE html>
<html lang="{{ $seoData['locale'] ?? 'ar' }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Search Console Verification -->
    <meta name="google-site-verification" content="BSAla0i5_oynIREw-fChMYZ9zCMyA95qqYxILO8xBpE" />

    <!-- Google Analytics (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-E68NQJJES2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-E68NQJJES2');
    </script>

    <!-- Dynamic SEO Meta Tags -->
    <title>{{ $seoData['title'] ?? 'طلبنا - Talabna | أكبر سوق للإعلانات المبوبة' }}</title>
    <meta name="description" content="{{ $seoData['description'] ?? 'طلبنا - أكبر منصة للإعلانات المبوبة في الوطن العربي. بيع واشتري السيارات، العقارات، الهواتف، الوظائف والمزيد بسهولة وأمان.' }}">
    <meta name="keywords" content="{{ $seoData['keywords'] ?? 'إعلانات مبوبة, سيارات للبيع, عقارات, وظائف, هواتف, بيع وشراء, classified ads' }}">
    <meta name="author" content="Talabna">
    <meta name="robots" content="index, follow">

    <!-- OpenGraph Meta -->
    <meta property="og:type" content="{{ $seoData['og']['type'] ?? 'website' }}">
    <meta property="og:site_name" content="Talabna - طلبنا">
    <meta property="og:title" content="{{ $seoData['title'] ?? 'طلبنا - Talabna | أكبر سوق للإعلانات المبوبة' }}">
    <meta property="og:description" content="{{ $seoData['description'] ?? 'أكبر منصة للإعلانات المبوبة في الوطن العربي' }}">
    <meta property="og:image" content="{{ $seoData['og']['image'] ?? asset('storage/photos/og-image.jpg') }}">
    <meta property="og:image:width" content="{{ $seoData['og']['image_width'] ?? '1200' }}">
    <meta property="og:image:height" content="{{ $seoData['og']['image_height'] ?? '630' }}">
    <meta property="og:url" content="{{ $seoData['canonical'] ?? url()->current() }}">
    <meta property="og:locale" content="{{ $seoData['og']['locale'] ?? 'ar_SA' }}">
    <meta property="og:locale:alternate" content="en_US">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@talabna">
    <meta name="twitter:title" content="{{ $seoData['title'] ?? 'طلبنا - Talabna' }}">
    <meta name="twitter:description" content="{{ $seoData['description'] ?? 'أكبر منصة للإعلانات المبوبة' }}">
    <meta name="twitter:image" content="{{ $seoData['og']['image'] ?? asset('storage/photos/og-image.jpg') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $seoData['canonical'] ?? url()->current() }}">

    <!-- Hreflang for multilingual SEO -->
    <link rel="alternate" hreflang="ar" href="{{ url()->current() }}?locale=ar">
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}?locale=en">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <!-- JSON-LD Structured Data -->
    @if(!empty($seoData['jsonLd']))
        @foreach($seoData['jsonLd'] as $schema)
            <script type="application/ld+json">
                {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endforeach
    @else
        <!-- Default Organization Schema -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Talabna - طلبنا",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('storage/photos/logo.png') }}",
            "sameAs": [
                "https://facebook.com/talabna",
                "https://twitter.com/talabna",
                "https://instagram.com/talabna"
            ],
            "contactPoint": {
                "@type": "ContactPoint",
                "contactType": "customer service",
                "availableLanguage": ["Arabic", "English"]
            }
        }
        </script>
        <!-- Default WebSite Schema with SearchAction -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "Talabna - طلبنا",
            "url": "{{ url('/') }}",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "{{ url('/browse') }}?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
        </script>
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/photos/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/photos/apple-touch-icon.png') }}">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/js/app.js'])

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
