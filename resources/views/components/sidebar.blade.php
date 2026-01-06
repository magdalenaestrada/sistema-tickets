<!-- partial:partials/_sidebar.html -->
<nav class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('empresas.index') }}" class="sidebar-brand d-flex align-items-center">
            @if ($empresaGlobal && $empresaGlobal->logo)
                <img src="{{ asset('storage/' . $empresaGlobal->logo) }}" alt="Logo" style="height:40px" class="me-2">
            @else
                <span>Mi Empresa</span>
            @endif
        </a>
        <div class="sidebar-toggler">
            <span></span><span></span><span></span>
        </div>
    </div>

    @php
        $gestionOpen = request()->routeIs('empresas.*', 'reportes.*');

        $personalOpen = request()->routeIs('cargos.*', 'empleados.*', 'eventos.*', 'descuentos.*', 'clientes.*');

        $vehiculosOpen = request()->routeIs('vehiculos.*', 'horarios.index');

        $ventasOpen = request()->routeIs(
            'pasajes.*',
            'encomiendas.*',
            'asignaciones.*',
            'horarios.*',
            'tipo-encomienda.*',
        );

        $cajaOpen = request()->routeIs('caja.*');
    @endphp

    <div class="sidebar-body">
        <ul class="nav" id="sidebarNav">

            {{-- Dashboard --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="link-icon" data-lucide="box"></i>
                    <span class="link-title">Dashboard</span>
                </a>
            </li>

            <li class="nav-item nav-category">GESTIÓN</li>

            {{-- Empresa --}}
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#gestion"
                    aria-expanded="{{ $gestionOpen ? 'true' : 'false' }}">
                    <i class="link-icon" data-lucide="building"></i>
                    <span class="link-title">Empresa</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>

                <div class="collapse {{ $gestionOpen ? 'show' : '' }}" id="gestion">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('empresas.index') }}"
                                class="nav-link {{ request()->routeIs('empresas.*') ? 'active' : '' }}">
                                Mi empresa
                            </a>
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

            {{-- Personal --}}
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#personal"
                    aria-expanded="{{ $personalOpen ? 'true' : 'false' }}">
                    <i class="link-icon" data-lucide="users"></i>
                    <span class="link-title">Personal</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>

                <div class="collapse {{ $personalOpen ? 'show' : '' }}" id="personal">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('cargos.index') }}"
                                class="nav-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}">Cargos</a></li>
                        <li class="nav-item"><a href="{{ route('empleados.index') }}"
                                class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}">Empleados</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('eventos.index') }}"
                                class="nav-link {{ request()->routeIs('eventos.*') ? 'active' : '' }}">Cumpleaños</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('descuentos.index') }}"
                                class="nav-link {{ request()->routeIs('descuentos.*') ? 'active' : '' }}">Cupones</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('clientes.index') }}"
                                class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">Clientes</a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Vehículos --}}
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#vehiculos"
                    aria-expanded="{{ $vehiculosOpen ? 'true' : 'false' }}">
                    <i class="link-icon" data-lucide="car-front"></i>
                    <span class="link-title">Vehículos</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>

                <div class="collapse {{ $vehiculosOpen ? 'show' : '' }}" id="vehiculos">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('vehiculos.index') }}"
                                class="nav-link {{ request()->routeIs('vehiculos.*') ? 'active' : '' }}">Vehículos</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('horarios.index') }}"
                                class="nav-link {{ request()->routeIs('horarios.index') ? 'active' : '' }}">Horarios</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item nav-category">CAJA</li>

            {{-- Caja --}}
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#caja"
                    aria-expanded="{{ $cajaOpen ? 'true' : 'false' }}">
                    <i class="link-icon" data-lucide="wallet"></i>
                    <span class="link-title">Caja</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>

                <div class="collapse {{ $cajaOpen ? 'show' : '' }}" id="caja">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('caja.index') }}"
                                class="nav-link {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                                Caja
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item nav-category">VENTAS</li>

            {{-- Ventas --}}
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#ventas"
                    aria-expanded="{{ $ventasOpen ? 'true' : 'false' }}">
                    <i class="link-icon" data-lucide="newspaper"></i>
                    <span class="link-title">Ventas</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>

                <div class="collapse {{ $ventasOpen ? 'show' : '' }}" id="ventas">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('tipo-encomienda.index') }}"
                                class="nav-link {{ request()->routeIs('tipo-encomienda.*') ? 'active' : '' }}">Tipo
                                encomiendas</a></li>
                        <li class="nav-item"><a href="{{ route('encomiendas.index-no-asignadas') }}"
                                class="nav-link {{ request()->routeIs('encomiendas.index-no-asignadas') ? 'active' : '' }}">Crear
                                encomienda</a></li>
                        <li class="nav-item"><a href="{{ route('encomiendas.index-asignadas') }}"
                                class="nav-link {{ request()->routeIs('encomiendas.index-asignadas') ? 'active' : '' }}">Encomiendas</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('asignaciones.index') }}"
                                class="nav-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}">Salidas</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('horarios.calendario') }}"
                                class="nav-link {{ request()->routeIs('horarios.calendario') ? 'active' : '' }}">Calendario</a>
                        </li>
                        <li class="nav-item"><a href="{{ route('pasajes.index') }}"
                                class="nav-link {{ request()->routeIs('pasajes.index') ? 'active' : '' }}">Vender
                                Pasajes</a></li>
                        <li class="nav-item"><a href="{{ route('pasajes.index-busqueda') }}"
                                class="nav-link {{ request()->routeIs('pasajes.index-busqueda') ? 'active' : '' }}">Buscar
                                Pasajes</a></li>
                    </ul>
                </div>
            </li>



        </ul>
    </div>
</nav>
