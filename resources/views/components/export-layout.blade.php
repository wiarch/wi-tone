<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet">
        @stack('head')
        @vite(['resources/css/app.css'])
    </head>
    <body class="song-export-body font-sans antialiased">
        {{ $slot }}
    </body>
</html>
