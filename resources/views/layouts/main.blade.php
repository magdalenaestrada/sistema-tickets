<!DOCTYPE html>

<html lang="es">

<head>
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            z-index: 1000;
            transition: width 0.3s ease;
            overflow-x: hidden;
        }

        .page-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        body.sidebar-collapsed .sidebar {
            width: 70px;
        }

        body.sidebar-collapsed .page-wrapper {
            margin-left: 70px;
        }

        body.sidebar-collapsed .link-title,
        body.sidebar-collapsed .link-arrow,
        body.sidebar-collapsed .sidebar-brand span {
            display: none;
        }

        body.sidebar-collapsed .nav-link {
            justify-content: center;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }

            .page-wrapper {
                margin-left: 0;
            }

            body.sidebar-open .sidebar {
                width: 260px;
            }
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Buses</title>

    <script src="{{ asset('assets/js/color-modes.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png"
        href="{{ $empresaGlobal->icon ? asset('storage/' . $empresaGlobal->icon) : asset('default/favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    @routes

</head>

<body>
    <div class="main-wrapper">
        <x-sidebar />

        <div class="page-wrapper">
            <x-navbar />
            <div id="loaderOverlay"
                style="
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: var(--bs-body-bg);
        z-index: 9999; display: flex;
        align-items: center; justify-content: center;
     ">
                <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <div id="contenidoApp" class="page-content container-fluid" style="display:none;">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/theme/vendors/core/core.js') }}"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <script>
        lucide.createIcons();
    </script>

    @include('layouts.partials.scripts')

    @stack('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const toggler = document.querySelector(".sidebar-toggler");

            if (toggler) {
                toggler.addEventListener("click", function() {
                    document.body.classList.toggle("sidebar-collapsed");
                });
            }

            // móvil
            const sidebarToggler = document.querySelector(".sidebar-toggler");

            if (sidebarToggler && window.innerWidth < 768) {
                sidebarToggler.addEventListener("click", function() {
                    document.body.classList.toggle("sidebar-open");
                });
            }

        });
    </script>
</body>

</html>
