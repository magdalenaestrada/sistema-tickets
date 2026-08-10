<nav class="sidebar">
    @php
        $empresaOpen = request()->routeIs('empresas.*', 'empleados.*', 'usuarios.*', 'series-sucursal.*', 'paradas.*');
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
    <div class="sidebar-header">
        <a href="{{ route($route) }}" class="sidebar-brand d-flex align-items-center">
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
                                <li class="nav-item">
                                    <a href="{{ route('series-sucursal.index') }}"
                                        class="nav-link {{ request()->routeIs('series-sucursal.*') ? 'active' : '' }}">
                                        Mis series
                                    </a>
                                </li>

                                <li class="nav-item"><a href="{{ route('empleados.index') }}"
                                        class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}">Empleados</a>
                                </li>
                                <li class="nav-item"><a href="{{ route('paradas.index') }}"
                                        class="nav-link {{ request()->routeIs('paradas.*') ? 'active' : '' }}">Ubi.
                                        Sucursales</a>
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
                                        class="nav-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}">Tipo de
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
