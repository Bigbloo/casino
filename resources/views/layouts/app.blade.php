<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Social Casino') }}</title>
    {!! PwaKit::head() !!}
</head>
<body>
    <main>
        @yield('content')
    </main>
    {!! PwaKit::scripts() !!}
</body>
</html>
