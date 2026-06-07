<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title') - {{ settings('app_name') }}</title>
    <link rel="stylesheet" href="/minimal/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- PWA Meta Tags (Phase 3) --}}
    <meta name="theme-color" content="#FF5733">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ settings('app_name') }}">
    <meta name="msapplication-TileColor" content="#FF5733">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/minimal/img/icons/icon-192x192.png">

    {{-- PWA Kit Head (injected when package is available) --}}
    @if(class_exists('\Devrabiul\PwaKit\Facades\PwaKit'))
        {!! \Devrabiul\PwaKit\Facades\PwaKit::head() !!}
    @endif

    @yield('styles')
</head>

<body>
    <div class="app-container">
        @include('frontend.Minimal.partials.navbar')

        <main class="main-content">
            @yield('content')
        </main>

        <footer class="main-footer">
            <p>&copy; {{ date('Y') }} {{ settings('app_name') }}. All rights reserved.</p>
        </footer>
    </div>

    @include('frontend.Minimal.partials.modals')

    <script src="/frontend/Default/js/jquery-3.4.1.min.js"></script>
    <script src="/minimal/js/app.js"></script>

    {{-- PWA Kit Scripts (injected when package is available) --}}
    @if(class_exists('\Devrabiul\PwaKit\Facades\PwaKit'))
        {!! \Devrabiul\PwaKit\Facades\PwaKit::scripts() !!}
    @else
        {{-- Fallback inline Service Worker registration --}}
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function (reg) {
                            console.log('Service Worker registered:', reg.scope);
                        })
                        .catch(function (err) {
                            console.warn('Service Worker registration failed:', err);
                        });
                });
            }
        </script>
    @endif

    @yield('scripts')
</body>

</html>
