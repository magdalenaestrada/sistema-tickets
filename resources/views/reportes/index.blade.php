@extends('layouts.app')

@section('content')
    <div class="container">

        {{-- Navegación de reportes --}}
        <div class="card mb-3">
            <div class="card-body p-2">

                <ul class="nav nav-pills" id="menuReportes">

                    <li class="nav-item">
                        <a href="#" class="nav-link active reporte-tab" data-reporte="ventas">
                            <i class="fas fa-dollar-sign me-1"></i>
                            Ventas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link reporte-tab" data-reporte="pasajeros">
                            <i class="fas fa-users me-1"></i>
                            Pasajeros
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link reporte-tab" data-reporte="viajes">
                            <i class="fas fa-route me-1"></i>
                            Viajes
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link reporte-tab" data-reporte="encomiendas">
                            <i class="fas fa-box me-1"></i>
                            Encomiendas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link reporte-tab" data-reporte="cupones">
                            <i class="fas fa-ticket-alt me-1"></i>
                            Cupones
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link reporte-tab" data-reporte="vehiculos">
                            <i class="fas fa-bus me-1"></i>
                            Vehículos
                        </a>
                    </li>

                </ul>

            </div>
        </div>

        @include('reportes.partials.filtros')

        @include('reportes.partials.dashboard-ventas')

        @include('reportes.partials.dashboard-pasajeros')

        @include('reportes.partials.dashboard-viajes')

        @include('reportes.partials.dashboard-encomiendas')

        @include('reportes.partials.dashboard-cupones')

        @include('reportes.partials.dashboard-vehiculos')

    </div>
@endsection

@push('js')
    <script src="{{ asset('js/reportes.js') }}"></script>
@endpush
