@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestión de horarios</h5>

                    <button class="btn btn-primary" onclick="modoCrearHorario()">
                        <i class="link-icon" data-lucide="plus"></i>
                        Añadir horario
                    </button>
                </div>

                <div class="card-body">
                    <table id="tablaHorarios" class="table table-hover align-middle w-100">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Ruta</th>
                                <th>Viaje</th>
                                <th>Salida</th>
                                <th>Llegada</th>
                                <th>Duración</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 id="tituloPanelHorario">Detalle</h5>
                </div>
                <div class="card-body" id="panelHorarioContenido">
                    <p class="text-muted">Selecciona un horario</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.RUTAS_HORARIO = @json($rutas);
        window.TIPOS_VIAJE_HORARIO = @json($tiposViaje);
        window.TIPOS_VEHICULO_HORARIO = @json($tiposVehiculo);
    </script>
    <script src="{{ asset('js/horarios.js') }}"></script>
@endpush
