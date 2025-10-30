<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Buses</title>

    {{-- Configuración de color --}}
    <script src="{{ asset('assets/js/color-modes.js') }}"></script>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fuentes --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Estilos compilados con Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Stack opcional para estilos adicionales por vista --}}
    @stack('styles')
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <x-sidebar />
            <x-navbar />

            <div class="page-content container-xxl">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/theme/vendors/core/core.js') }}"></script>
    <script src="{{ asset('assets/theme/vendors/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>

    @include('layouts.partials.scripts')

    @stack('scripts')
</body>
</html>
