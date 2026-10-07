<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talabna</title>
    <meta property="og:title" content="Talabna">
    <meta property="og:description" content="{{ $message }}">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; text-align: center; padding: 24px;
               margin: 0; min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
               background: #f7f5f0; color: #1d1d1f; }
        p { color: #666; }
        .button { display: inline-block; background: #e8a33d; color: #1d1d1f; padding: 12px 22px; text-decoration: none;
                  font-size: 16px; font-weight: 600; border-radius: 12px; }
    </style>
</head>
<body>
<h2>{{ $message }}</h2>
<p>{{ $suggestion }}</p>
<a class="button" href="{{ $alternativeLink }}">{{ $alternativeLinkText }}</a>
</body>
</html>
