<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Whisperly — {{ $title ?? 'Chat' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 h-screen flex flex-col" x-data>
    @include('partials.navbar')
    <main class="flex-1 overflow-hidden">
        {{ $slot }}
    </main>
</body>
</html>