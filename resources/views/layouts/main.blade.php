<!DOCTYPE html>
<html lang="es">

<head>


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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    @routes

    <style>
        /* Dimensiones Base */
        :root {
            --sb-width: 250px;
            --sb-collapsed-width: 70px;
            --sb-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Layout principal */
        .main-wrapper {
            display: flex !important;
            width: 100% !important;
        }

        .sidebar {
            width: var(--sb-width) !important;
            min-width: var(--sb-width) !important;
            transition: var(--sb-transition) !important;
            z-index: 1001;
        }

        .page-wrapper {
            width: calc(100% - var(--sb-width)) !important;
            margin-left: var(--sb-width) !important;
            transition: var(--sb-transition) !important;
        }

        body.sidebar-collapsed .sidebar {
            width: var(--sb-collapsed-width) !important;
            min-width: var(--sb-collapsed-width) !important;
        }

        body.sidebar-collapsed .page-wrapper {
            width: calc(100% - var(--sb-collapsed-width)) !important;
            margin-left: var(--sb-collapsed-width) !important;
        }

        /* Ocultar elementos de texto al colapsar */
        body.sidebar-collapsed .brand-text,
        body.sidebar-collapsed .link-title,
        body.sidebar-collapsed .link-arrow,
        body.sidebar-collapsed .nav-category,
        body.sidebar-collapsed .sidebar .collapse {
            display: none !important;
        }

        /* Centrar iconos y logo */
        body.sidebar-collapsed .sidebar-brand {
            justify-content: center !important;
            padding: 0 !important;
        }

        body.sidebar-collapsed .brand-icon {
            width: 38px !important;
            height: 38px !important;
            margin: 0 auto !important;
        }

        body.sidebar-collapsed .nav-link {
            justify-content: center !important;
            padding: 10px 0 !important;
        }

        body.sidebar-collapsed .link-icon {
            margin-right: 0 !important;
        }

        /* ========================================================
       RESPONSIVE (MÓVIL)
       ======================================================== */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                transform: translateX(-100%);
            }

            body.sidebar-open .sidebar {
                transform: translateX(0) !important;
            }

            .page-wrapper {
                width: 100% !important;
                margin-left: 0 !important;
            }

            #sidebarOverlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                display: none;
            }

            body.sidebar-open #sidebarOverlay {
                display: block !important;
            }
        }
    </style>
</head>

<body>
    <div class="main-wrapper">

        @php
            $empresaOpen = request()->routeIs(
                'empresas.*',
                'empleados.*',
                'usuarios.*',
                'series-sucursal.*',
                'paradas.*',
            );
            $maestrosOpen = request()->routeIs('cargos.*', 'tipo-encomienda.*', 'tipo-cupones.*');

            $personalOpen = request()->routeIs('eventos.*');

            $transportesOpen =
                request()->routeIs('vehiculos.*') ||
                request()->routeIs('horarios.*', 'asignaciones.*', 'rutas.*') ||
                (request()->routeIs('salidas.*') && !request()->routeIs('salidas.index-vendedor'));

            $gestionOpen = request()->routeIs('clientes.*', 'descuentos.*', 'reportes.*');
            $encomiendasOpen = request()->routeIs('encomiendas.*');
            $cajaOpen = request()->routeIs('caja.*');
            $facturacion = request()->routeIs('facturacion.*');
            $route = Auth::user()->hasRole('Administrador') ? 'dashboard.admin' : 'dashboard.vendedor';
            $isContador = Auth::user()->hasRole('Contador');
            $isAdmin = Auth::user()->hasRole('Administrador');
        @endphp

        <nav class="sidebar">
            <!-- Header del Sidebar -->
            <div class="sidebar-header">
                <a class="sidebar-brand">
                    <!-- Icono/Logo ajustado -->
                    <img src="{{ $empresaGlobal->logo ? asset('storage/' . $empresaGlobal->logo) : asset('default/favicon.ico') }}"
                        alt="Logo" class="brand-icon"
                        style="max-width: 80px; max-height: 80px; object-fit: contain;">
                </a>
                <!-- BotÃ³n Hamburguesa dentro del Sidebar -->
                <button type="button" class="btn sidebar-toggler">
                    <i data-lucide="menu"></i>
                </button>
            </div>

            <div class="sidebar-body">
                <ul class="nav" id="sidebarNav">
                    @if ($isContador)
                        <li class="nav-item {{ request()->routeIs($route) ? 'active' : '' }}">
                            <a href="{{ route($route) }}" class="nav-link">
                                <i class="link-icon" data-lucide="home"></i>
                                <span class="link-title">INICIO</span>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->is('facturacion*') ? 'active' : '' }}">
                            <a href="{{ route('facturacion.index') }}" class="nav-link">
                                <i class="link-icon" data-lucide="receipt"></i>
                                <span class="link-title">Comprobantes</span>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                            <a href="{{ route('reportes.index') }}" class="nav-link">
                                <i class="link-icon" data-lucide="bar-chart-3"></i>
                                <span class="link-title">Reportes</span>
                            </a>
                        </li>
                    @else
                        <li class="nav-item {{ request()->routeIs($route) ? 'active' : '' }}">
                            <a href="{{ route($route) }}" class="nav-link">
                                <i class="link-icon" data-lucide="home"></i>
                                <span class="link-title">INICIO</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('cajas*') ? 'active' : '' }}">
                            <a href="{{ route('caja.index') }}" class="nav-link">
                                <i class="link-icon" data-lucide="piggy-bank"></i>
                                <span class="link-title">Caja</span>
                            </a>
                        </li>


                        <li class="nav-item {{ request()->routeIs('pasajes.index') ? 'active' : '' }}"> <a
                                href="{{ route('pasajes.index') }}" class="nav-link">
                                <i class="link-icon" data-lucide="ticket"></i>
                                <span class="link-title">Vender pasajes</span>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('pasajes.listar') ? 'active' : '' }}">
                            <a href="{{ route('pasajes.listar') }}" class="nav-link">
                                <i class="link-icon" data-lucide="receipt-text"></i>
                                <span class="link-title">Buscar pasajes</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#encomiendas"
                                aria-expanded="{{ $encomiendasOpen ? 'true' : 'false' }}">
                                <i class="link-icon" data-lucide="package"></i>
                                <span class="link-title">Acciones encomiendas</span>
                                <i class="link-arrow" data-lucide="chevron-down"></i>
                            </a>

                            <div class="collapse {{ $encomiendasOpen ? 'show' : '' }}" id="encomiendas"
                                data-bs-parent="#sidebarNav">
                                <ul class="nav sub-menu">
                                    <li class="nav-item">
                                        <a href="{{ route('encomiendas.consulta.index') }}"
                                            class="nav-link {{ request()->routeIs('encomiendas.consulta.index') ? 'active' : '' }}">
                                            Buscar encomienda
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('encomiendas.crear-encomienda') }}"
                                            class="nav-link {{ request()->routeIs('encomiendas.crear-encomienda') ? 'active' : '' }}">
                                            Crear encomienda
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('encomiendas.index-no-asignadas') }}"
                                            class="nav-link {{ request()->routeIs('encomiendas.index-no-asignadas') ? 'active' : '' }}">
                                            Asignar encomienda
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('encomiendas.index-asignadas') }}"
                                            class="nav-link {{ request()->routeIs('encomiendas.index-asignadas') ? 'active' : '' }}">
                                            Encomiendas
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item {{ request()->is('facturacion*') ? 'active' : '' }}">
                            <a href="{{ route('facturacion.index') }}" class="nav-link">
                                <i class="link-icon" data-lucide="ticket-check"></i>
                                <span class="link-title">Comprobantes</span>
                            </a>
                        </li>

                        <li class="nav-item {{ request()->routeIs('salidas.index-vendedor') ? 'active' : '' }}">
                            <a href="{{ route('salidas.index-vendedor') }}" class="nav-link">
                                <i class="link-icon" data-lucide="file-clock"></i>
                                <span class="link-title">Manifiestos</span>
                            </a>
                        </li>
                        @if ($isAdmin)
                            <li class="nav-item nav-category">ADMINISTRACIÓN</li>

                            {{-- Empresa --}}
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="collapse" href="#empresa"
                                    aria-expanded="{{ $empresaOpen ? 'true' : 'false' }}">
                                    <i class="link-icon" data-lucide="building"></i>
                                    <span class="link-title">Empresa</span>
                                    <i class="link-arrow" data-lucide="chevron-down"></i>
                                </a>

                                <div class="collapse {{ $empresaOpen ? 'show' : '' }}" id="empresa"
                                    data-bs-parent="#sidebarNav">
                                    <ul class="nav sub-menu">
                                        <li class="nav-item">
                                            <a href="{{ route('empresas.index') }}"
                                                class="nav-link {{ request()->routeIs('empresas.*') ? 'active' : '' }}">
                                                Mi empresa
                                            </a>
                                        </li>
                                          <li class="nav-item"><a href="{{ route('paradas.index') }}"
                                                class="nav-link {{ request()->routeIs('paradas.*') ? 'active' : '' }}">Paradas</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('series-sucursal.index') }}"
                                                class="nav-link {{ request()->routeIs('series-sucursal.*') ? 'active' : '' }}">
                                                Mis series
                                            </a>
                                        </li>

                                        <li class="nav-item"><a href="{{ route('empleados.index') }}"
                                                class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}">Empleados</a>
                                        </li>
                                      
                                        <li class="nav-item">
                                            <a href="{{ route('usuarios.index') }}"
                                                class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">Usuarios
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="collapse" href="#maestros"
                                    aria-expanded="{{ $maestrosOpen ? 'true' : 'false' }}">
                                    <i class="link-icon" data-lucide="puzzle"></i>
                                    <span class="link-title">Maestros</span>
                                    <i class="link-arrow" data-lucide="chevron-down"></i>
                                </a>

                                <div class="collapse {{ $maestrosOpen ? 'show' : '' }}" id="maestros"
                                    data-bs-parent="#sidebarNav">
                                    <ul class="nav sub-menu">
                                        <li class="nav-item"><a href="{{ route('cargos.index') }}"
                                                class="nav-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}">Tipo
                                                de
                                                cargo
                                            </a>
                                        </li>
                                        <li class="nav-item"><a href="{{ route('tipo-cupones.index') }}"
                                                class="nav-link {{ request()->routeIs('tipo-cupones.*') ? 'active' : '' }}">Tipo
                                                de
                                                cupones
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('tipo-encomienda.index') }}"
                                                class="nav-link {{ request()->routeIs('tipo-encomienda.*') ? 'active' : '' }}">
                                                Tipo encomiendas
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="collapse" href="#transportes"
                                    aria-expanded="{{ $transportesOpen ? 'true' : 'false' }}">
                                    <i class="link-icon" data-lucide="tram-front"></i>
                                    <span class="link-title">Transportes y operación</span>
                                    <i class="link-arrow" data-lucide="chevron-down"></i>
                                </a>

                                <div class="collapse {{ $transportesOpen ? 'show' : '' }}" id="transportes"
                                    data-bs-parent="#sidebarNav">
                                    <ul class="nav sub-menu">
                                        <li class="nav-item"><a href="{{ route('vehiculos.index') }}"
                                                class="nav-link {{ request()->routeIs('vehiculos.*') ? 'active' : '' }}">Transportes</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('rutas.index') }}"
                                                class="nav-link {{ request()->routeIs('rutas.*') ? 'active' : '' }}">
                                                Rutas
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('horarios.index') }}"
                                                class="nav-link {{ request()->routeIs('horarios.index') ? 'active' : '' }}">
                                                Horarios
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('salidas.index') }}"
                                                class="nav-link {{ request()->routeIs('salidas.*') && !request()->routeIs('salidas.index-vendedor') ? 'active' : '' }}">
                                                Salidas programadas
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="collapse" href="#gestion"
                                    aria-expanded="{{ $gestionOpen ? 'true' : 'false' }}">
                                    <i class="link-icon" data-lucide="newspaper"></i>
                                    <span class="link-title">Gestión</span>
                                    <i class="link-arrow" data-lucide="chevron-down"></i>
                                </a>

                                <div class="collapse {{ $gestionOpen ? 'show' : '' }}" id="gestion"
                                    data-bs-parent="#sidebarNav">
                                    <ul class="nav sub-menu">
                                        <li class="nav-item">
                                            <a href="{{ route('clientes.index') }}"
                                                class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">Clientes
                                            </a>
                                        </li>
                                        <li class="nav-item"><a href="{{ route('descuentos.index') }}"
                                                class="nav-link {{ request()->routeIs('descuentos.*') ? 'active' : '' }}">Cupones</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reportes.index') }}"
                                                class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                                                Reportes
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            </li>
                        @endif
                    @endif
                </ul>
            </div>
        </nav>

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

    <div id="sidebarOverlay"></div>

    <script src="{{ asset('assets/theme/vendors/core/core.js') }}"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <script>
        lucide.createIcons();
    </script>

    @include('layouts.partials.scripts')

    @stack('scripts')

    @if (session('abrir_caja'))
        <script>
            document.getElementById("crear-encomienda")?.addEventListener("click", function(e) {
                if (!window.VENTA_CONFIG.esAdmin && !window.VENTA_CONFIG.cajaAbierta) {
                    e.preventDefault();

                    Swal.fire({
                        icon: "warning",
                        title: "Caja cerrada",
                        text: "Debe abrir una caja antes de crear una encomienda.",
                        confirmButtonText: "Ir a abrir caja",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = window.VENTA_CONFIG.rutaCaja;
                        }
                    });
                }
            });
        </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const body = document.body;
            const MOBILE_BREAKPOINT = 768;

            // 1. Aplicar estado guardado en desktop
            if (window.innerWidth > MOBILE_BREAKPOINT) {
                if (localStorage.getItem("sidebar_state") === "collapsed") {
                    body.classList.add("sidebar-collapsed");
                }
            }

            // 2. Delegación de evento de Clic Global para el botón hamburguesa
            document.addEventListener("click", function(e) {
                // Busca si hicieron clic en el botón o en el icono dentro del botón
                const toggler = e.target.closest(".sidebar-toggler");
                if (!toggler) return;

                e.preventDefault();

                if (window.innerWidth <= MOBILE_BREAKPOINT) {
                    // Modo Móvil
                    body.classList.toggle("sidebar-open");
                } else {
                    // Modo Desktop (Colapsar / Expandir)
                    body.classList.toggle("sidebar-collapsed");

                    // Guardar preferencia
                    const isCollapsed = body.classList.contains("sidebar-collapsed");
                    localStorage.setItem("sidebar_state", isCollapsed ? "collapsed" : "open");
                }
            });

            // 3. Cerrar overlay en móvil
            const overlay = document.getElementById("sidebarOverlay");
            if (overlay) {
                overlay.addEventListener("click", function() {
                    body.classList.remove("sidebar-open");
                });
            }
        });
    </script>
</body>

</html>
