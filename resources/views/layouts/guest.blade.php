<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>SIM LANTAS KWAN</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('img/ntb.png') }}">

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        @vite(['resources/css/app.css'])
        <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
        @livewireStyles
        @stack('styles')

    </head>
    <body style="font-family: 'Nunito', sans-serif;">
        <div class="text-gray-900 antialiased">
            {{ $slot }}
        </div>

        <!-- Scripts -->
        @vite(['resources/js/app.js'])
        @livewireScripts
        <wireui:scripts />
        @stack('scripts')
    </body>
</html>

