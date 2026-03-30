@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Gestión de salidas</h5>

                    <div class="d-flex gap-2">
                        <button class="btn btn-success" onclick="modoGenerarSalidas()">
                            <i class="link-icon" data-lucide="calendar-plus"></i>
                            Generar salidas
                        </button>

                        <button class="btn btn-primary" onclick="modoCrearSalida()">
                            <i class="link-icon" data-lucide="plus"></i>
                            Añadir salida
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <table id="tablaSalidas" class="table table-hover align-middle w-100">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Ruta</th>
                                <th>Fecha</th>
                                <th>Salida</th>
                                <th>Llegada</th>
                                <th>Estado</th>
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
                    <h5 id="tituloPanelSalida">Detalle</h5>
                </div>
                <div class="card-body" id="panelSalidaContenido">
                    <p class="text-muted">Selecciona una salida</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.HORARIOS_SALIDA = @json(
            \App\Models\Horario::with('ruta')->get()->map(function ($h) {
                    return [
                        'id' => $h->id,
                        'nombre' => ($h->ruta?->nombre ?? 'Sin ruta') . ' - ' . ($h->hora_formateada ?? ''),
                    ];
                }));
    </script>
    <script src="{{ asset('js/salidas.js') }}"></script>
@endpush
