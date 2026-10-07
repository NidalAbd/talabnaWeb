<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $preview['title'] }}</title>
    <meta name="description" content="{{ $preview['description'] }}">

    {{-- Link preview card for WhatsApp, Facebook, Telegram, X, iMessage --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Talabna">
    <meta property="og:title" content="{{ $preview['title'] }}">
    <meta property="og:description" content="{{ $preview['description'] }}">
    <meta property="og:image" content="{{ $preview['image'] }}">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $preview['title'] }}">
    <meta name="twitter:description" content="{{ $preview['description'] }}">
    <meta name="twitter:image" content="{{ $preview['image'] }}">
    {{-- Safari shows an "Open in Talabna" banner on iPhone --}}
    <meta name="apple-itunes-app" content="app-id=6814376173, app-argument={{ $deepLink }}">

    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; text-align: center; padding: 24px;
               margin: 0; min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
               background: #f7f5f0; color: #1d1d1f; }
        img.cover { max-width: 320px; width: 100%; border-radius: 16px; object-fit: cover; max-height: 320px; }
        h2 { font-size: 20px; margin: 16px 0 6px; }
        p { color: #666; margin: 4px 0 18px; }
        .button { display: inline-block; background: #e8a33d; color: #1d1d1f; padding: 12px 22px; text-decoration: none;
                  font-size: 16px; font-weight: 600; margin: 4px; border-radius: 12px; }
    </style>
    <script>
        function storeUrl() {
            var ua = navigator.userAgent || '';
            if (/iPhone|iPad|iPod/.test(ua) || (/Macintosh/.test(ua) && 'ontouchend' in document)) return "{{ $appStoreUrl }}";
            return "{{ $playStoreUrl }}";
        }

        // When the app is installed, iOS Universal Links / Android App Links open it before this page loads.
        // Otherwise: try the app scheme, then send the reader to the store for their phone.
        window.onload = function () {
            document.getElementById('store').href = storeUrl();
            var isPhone = /Android|iPhone|iPad|iPod/.test(navigator.userAgent || '');
            if (!isPhone) return; // a computer stays on this page
            window.location.href = "{{ $deepLink }}";
            setTimeout(function () {
                if (!document.hidden) window.location = storeUrl();
            }, 1800);
        };
    </script>
</head>
<body>
@if (!empty($preview['image']))
    <img class="cover" src="{{ $preview['image'] }}" alt="">
@endif
<h2>{{ $preview['title'] }}</h2>
<p>Opening this {{ $routeName }} in the Talabna app…</p>
<div>
    <a class="button" href="{{ $deepLink }}">Open in Talabna</a>
    <a id="store" class="button" style="background:#1d1d1f;color:#fff" href="{{ $playStoreUrl }}">Get the app</a>
</div>
</body>
</html>
