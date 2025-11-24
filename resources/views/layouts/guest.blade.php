<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Buses - Login</title>

    <script src="{{ asset('assets/js/color-modes.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fuentes --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- CSS y JS de NobleUI --}}
    <link rel="stylesheet" href="{{ asset('assets/theme/css/nobleui-style.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="w-100" style="max-width: 600px;">
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('assets/theme/vendors/core/core.js') }}"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>

</html>
