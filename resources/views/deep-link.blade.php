<!DOCTYPE html>
<html>
<head>
    <title>Opening {{ $routeName }} in Talbna App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 80vh;
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        .fallback {
            margin-top: 20px;
            color: #666;
        }
    </style>
    <script>
        // Function to detect if user is on Android
        function isAndroid() {
            return /Android/.test(navigator.userAgent);
        }

        // Attempt to open the app
        function openApp() {
            var deepLink = "{{ $deepLink }}";
            var playStoreUrl = "{{ $playStoreUrl }}";
            var webFallbackUrl = "{{ $webFallbackUrl }}";

            // Log for debugging
            console.log("Attempting to open deep link: " + deepLink);

            // Try to open the app with the deep link
            window.location.href = deepLink;

            // Set a timeout to redirect to Play Store if app doesn't open
            setTimeout(function() {
                // Check if we're still on this page
                if (document.hidden) {
                    // The app opened successfully
                    console.log("App opened successfully");
                    return;
                }

                console.log("App did not open, redirecting to store or web");
                if (isAndroid()) {
                    window.location = playStoreUrl;
                } else {
                    // For desktop or iOS, redirect to web version
                    window.location = webFallbackUrl;
                }
            }, 2000); // Wait 2 seconds before redirecting
        }

        // Run when page loads
        window.onload = openApp;
    </script>
</head>
<body>
<h2>Opening {{ $routeName }} in Talbna App</h2>
<div class="loader"></div>
<p>{{('deep-link.redirecting_you_to_the_app_') }}</p>

<div class="fallback">
    <p>{{('deep-link.if_nothing_happens_') }}</p>
    <a id="app-store-link" class="button" href="{{ $playStoreUrl }}">{{('deep-link.download_the_app') }}</a>
    <a id="web-fallback" class="button" style="background-color: #3498db;" href="{{ $webFallbackUrl }}">{{('deep-link.continue_to_website') }}</a>
</div>
</body>
</html>







