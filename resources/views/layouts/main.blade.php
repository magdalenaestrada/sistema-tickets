<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Buses</title>

    <script src="{{ asset('assets/js/color-modes.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    @routes

</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <x-sidebar />
            <x-navbar />
            <div id="loaderOverlay"
                style="
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: white; z-index: 9999; display: flex;
        align-items: center; justify-content: center;
     ">
                <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <div id="contenidoApp" class="page-content container-xxl" style="display:none;">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/theme/vendors/core/core.js') }}"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    @include('layouts.partials.scripts')

    @stack('scripts')
</body>

</html>
@routes
