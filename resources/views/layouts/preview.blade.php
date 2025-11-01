<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') • Preview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: light dark; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #111827;
        }
        .prose {
            line-height: 1.7;
        }
        .content-body h2 { font-size: 1.75rem; margin-top: 2rem; }
        .content-body p { margin-bottom: 1rem; }
        .content-body img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <main>
        @yield('content')
    </main>
</body>
</html>
