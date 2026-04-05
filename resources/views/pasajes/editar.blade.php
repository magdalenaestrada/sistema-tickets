@extends('layouts.app')

<style>
    .seat.libre .seat-body,
    .seat.libre .seat-base {
        fill: #cbd5e1 !important;
    }

    .seat.reservado .seat-body,
    .seat.reservado .seat-base {
        fill: #f9db16 !important;
    }

    .seat.ocupado .seat-body,
    .seat.ocupado .seat-base {
        fill: #51dc26 !important;
    }

    .seat.selected-seat .seat-body,
    .seat.selected-seat .seat-base {
        fill: #2563eb !important;
    }

    .seat.seat-actual .seat-body,
    .seat.seat-actual .seat-base {
        fill: #a31616 !important;
    }

    #svgContainerCambio {
        text-align: center;
    }

    #svgContainerCambio svg {
        width: 100%;
        max-width: 450px;
        height: auto;
    }
</style>

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Editar pasaje #{{ $pasaje->id }}</h4>
                <small class="text-muted">
                    Estado actual:
                    @php
                        $estadoTexto = match ($pasaje->estado) {
                            'R' => 'Reservado',
                            'V' => 'Vendido',
                            'F' => 'Abordó',
                            'X' => 'Cancelado / No abordó',
                            default => $pasaje->estado,
                        };
                    @endphp
                    <strong>{{ $estadoTexto }}</strong>
                </small>
            </div>

            <a href="{{ route('pasajes.index') }}" class="btn btn-secondary">
                Volver
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">

                {{-- DATOS DEL PASAJERO --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Datos del pasajero</strong>
                    </div>
                    <div class="card-body">
                        <form id="formEditarPasaje" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="pasaje_id" value="{{ $pasaje->id }}">

                            <div class="row">
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Tipo documento <span class="text-danger">*</span></label>
                                    <select class="form-select" name="tipo_documento_id" required>
                                        @foreach ($tipos_documentos as $tipo)
                                            <option value="{{ $tipo->id }}"
                                                {{ $pasaje->persona?->tipo_documento_id == $tipo->id ? 'selected' : '' }}>
                                                {{ $tipo->codigo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Documento <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="documento"
                                        value="{{ $pasaje->persona?->documento }}" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nombres"
                                        value="{{ $pasaje->persona?->nombres }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="apellidos"
                                        value="{{ $pasaje->persona?->apellidos }}" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Celular</label>
                                    <input type="text" class="form-control" name="celular"
                                        value="{{ $pasaje->persona?->celular }}" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono"
                                        value="{{ $pasaje->persona?->telefono }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Correo</label>
                                    <input type="email" class="form-control" name="correo"
                                        value="{{ $pasaje->persona?->correo }}">
                                </div>
                            </div>

                            <div class="row align-items-end">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" id="pasajero_menor"
                                            name="pasajero_menor" value="1"
                                            {{ $pasaje->pasajero_menor ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pasajero_menor">
                                            ¿Pasajero menor de edad?
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-8 mb-3" id="contenedorAutorizacion"
                                    style="{{ $pasaje->pasajero_menor ? '' : 'display:none;' }}">
                                    <label class="form-label">Autorización PDF <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="autorizacion_pdf" accept=".pdf">

                                    @if ($pasaje->autorizacion_pdf)
                                        <small class="text-muted d-block mt-1">
                                            Ya existe una autorización cargada.
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-primary" id="btnGuardarDatosPasaje">
                                    Guardar datos del pasajero
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Cambiar viaje / asiento</strong>
                    </div>
                    <div class="card-body">
                        <form id="formCambiarViaje">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="nuevo_asiento_numero" id="nuevo_asiento_numero" value="">
                            <input type="hidden" name="descuento_id" id="descuento_id_cambio" value="">
                            <input type="hidden" name="descuento_monto" id="descuento_monto_cambio" value="0">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nueva salida</label>
                                    <select class="form-select" name="nueva_salida_id" id="nueva_salida_id">
                                        <option value="">Seleccionar...</option>
                                        @foreach ($salidas ?? [] as $salida)
                                            <option value="{{ $salida->id }}">
                                                {{ $salida->fecha_salida?->format('Y-m-d') }}
                                                - {{ $salida->horario?->hora_formateada }}
                                                - {{ $salida->horario?->tipo_vehiculo?->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Origen</label>
                                    <select class="form-select" name="origen_id" id="origen_id">
                                        @foreach ($sucursales as $sucursal)
                                            <option value="{{ $sucursal->id }}"
                                                {{ $pasaje->origen_sucursal_id == $sucursal->id ? 'selected' : '' }}>
                                                {{ $sucursal->nombre_comercial }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Destino</label>
                                    <select class="form-select" name="destino_id" id="destino_id">
                                        @foreach ($sucursales as $sucursal)
                                            <option value="{{ $sucursal->id }}"
                                                {{ $pasaje->destino_sucursal_id == $sucursal->id ? 'selected' : '' }}>
                                                {{ $sucursal->nombre_comercial }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cupón / descuento</label>
                                    <input type="text" class="form-control" id="descuento_codigo_cambio"
                                        placeholder="Código">
                                    <small class="text-muted" id="descuento_msg_cambio"></small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Asiento actual</label>
                                    <input type="text" class="form-control" value="{{ $pasaje->asiento_numero }}"
                                        readonly>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nuevo asiento seleccionado</label>
                                    <input type="text" class="form-control" id="nuevo_asiento_texto" readonly
                                        placeholder="Seleccione en el mapa">
                                </div>
                            </div>

                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Mapa de asientos</strong>
                                    <small class="text-muted">Haz click en una silla libre o reservada/vendida para
                                        editar</small>
                                </div>

                                <div id="svgContainerCambio" style="min-height: 280px;">
                                    <div class="text-muted">Selecciona una salida para cargar asientos</div>
                                </div>

                                <div class="mt-3 d-flex gap-3 flex-wrap">
                                    <div><span class="badge" style="background:#cbd5e1;">&nbsp;</span> Libre</div>
                                    <div><span class="badge" style="background:#f9db16;">&nbsp;</span> Reservado</div>
                                    <div><span class="badge" style="background:#51dc26;">&nbsp;</span> Vendido</div>
                                    <div><span class="badge" style="background:#2563eb;">&nbsp;</span> Seleccionado</div>
                                    <div><span class="badge" style="background:#16a34a;">&nbsp;</span> Asiento actual
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-warning" id="btnCambiarViaje">
                                    Guardar cambio de viaje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- FACTURACIÓN --}}
                @if ($pasaje->venta)
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>Facturación / venta</strong>
                        </div>
                        <div class="card-body">
                            <form id="formEditarVenta">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Tipo documento factura</label>
                                        <select class="form-select" name="tipo_documento_factura_id">
                                            @foreach ($tipos_documentos_facturas as $tipo)
                                                <option value="{{ $tipo->id }}"
                                                    {{ $pasaje->venta?->tipo_documento_factura_id == $tipo->id ? 'selected' : '' }}>
                                                    {{ $tipo->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Número documento</label>
                                        <input type="text" class="form-control" name="numero_documento_id"
                                            value="{{ $pasaje->venta?->persona?->documento }}">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Razón social</label>
                                        <input type="text" class="form-control" name="razon_social"
                                            value="{{ $pasaje->venta?->persona?->nombres }}">
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="button" class="btn btn-info" id="btnGuardarVenta">
                                        Guardar datos de venta
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                {{-- RESUMEN --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Resumen del pasaje</strong>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Ruta:</strong> {{ $pasaje->salida?->horario?->ruta?->nombre }}</p>
                        <p class="mb-1"><strong>Fecha:</strong> {{ $pasaje->salida?->fecha_salida?->format('Y-m-d') }}
                        </p>
                        <p class="mb-1"><strong>Hora:</strong> {{ $pasaje->salida?->horario?->hora_formateada }}</p>
                        <p class="mb-1"><strong>Asiento:</strong> {{ $pasaje->asiento_numero }}</p>
                        <p class="mb-1"><strong>Origen:</strong> {{ $pasaje->origen?->nombre_comercial }}</p>
                        <p class="mb-1"><strong>Destino:</strong> {{ $pasaje->destino?->nombre_comercial }}</p>
                        <p class="mb-1"><strong>Precio:</strong> {{ number_format((float) $pasaje->precio_cobrado, 2) }}
                        </p>
                        <p class="mb-0"><strong>Promoción:</strong> {{ $pasaje->es_promocion ? 'Sí' : 'No' }}</p>
                    </div>
                </div>

                {{-- ACCIONES --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Acciones</strong>
                    </div>
                    <div class="card-body">
                        @if (in_array($pasaje->estado, ['V']))
                            <button type="button" class="btn btn-success w-100 mb-2" id="btnAbordo">
                                Marcar abordó
                            </button>

                            <button type="button" class="btn btn-danger w-100 mb-2" id="btnNoAbordo">
                                Marcar no abordó
                            </button>
                        @endif

                        @if (in_array($pasaje->estado, ['R', 'V']))
                            <button type="button" class="btn btn-outline-danger w-100" id="btnCancelarPasaje">
                                Cancelar pasaje
                            </button>
                        @endif
                    </div>
                </div>

                @if ($pasaje->venta)
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>Pagos registrados</strong>
                        </div>
                        <div class="card-body">
                            @forelse ($pasaje->venta->pagos as $pago)
                                <div class="border rounded p-2 mb-2">
                                    <div><strong>Método:</strong> {{ $pago->metodoPago?->descripcion }}</div>
                                    <div><strong>Total:</strong> {{ number_format((float) $pago->total, 2) }}</div>
                                    @if ($pago->billetera)
                                        <div><strong>Billetera:</strong> {{ $pago->billetera->descripcion }}</div>
                                    @endif
                                </div>
                            @empty
                                <small class="text-muted">No hay pagos registrados.</small>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const PASAJE_ID = @json($pasaje->id);
        const SALIDA_ACTUAL_ID = @json($pasaje->salida_id);
        let asientoActual = @json($pasaje->asiento_numero);
        let pasajeId = PASAJE_ID;
        let nuevoAsientoSeleccionado = null;
        let descuentoCambio = {
            descuento_id: null,
            monto: 0,
        };
    </script>
    <script src="{{ asset('js/pasajes_editar.js') }}"></script>
@endpush
