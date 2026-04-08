@extends('layouts.app')

@section('content')
    <form id="formEncomienda">
        @csrf
        @method('PUT')

        <input type="hidden" id="encomienda_id" value="{{ $encomienda->id }}">

        <div class="row">
            <div class="col-md-9">

                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Datos del Emisor</h6>
                        <hr>

                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label">Tipo de documento</label>
                                <select class="form-select" name="emisor[tipo_documento_id]" id="emisor_tipo_documento_id"
                                    required>
                                    @foreach ($tipos_documentos as $tipo_documento)
                                        <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->codigo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Documento</label>
                                <input type="text" class="form-control" id="emisor_documento" name="emisor[documento]"
                                    inputmode="numeric" pattern="\d+" title="Solo números"
                                    value="{{ $encomienda->emisor->documento ?? '' }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombres</label>
                                <input type="text" class="form-control" id="emisor_nombres" name="emisor[nombres]"
                                    pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" value="{{ $encomienda->emisor->nombres ?? '' }}"
                                    title="Solo letras" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Apellidos</label>
                                <input type="text" class="form-control" id="emisor_apellidos" name="emisor[apellidos]"
                                    pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras"
                                    value="{{ $encomienda->emisor->apellidos ?? '' }}" required>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Celular</label>
                                <input type="text" class="form-control" id="emisor_celular" name="emisor[celular]"
                                    inputmode="numeric" pattern="\d+" title="Solo números"
                                    value="{{ $encomienda->emisor->celular ?? '' }}" >
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" class="form-control" id="emisor_telefono" name="emisor[telefono]"
                                    inputmode="numeric" pattern="\d+" title="Solo números"
                                    value="{{ $encomienda->emisor->telefono ?? '' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Correo electrónico</label>
                                <input type="text" class="form-control" id="emisor_direccion"
                                    value="{{ $encomienda->emisor->direccion ?? '' }}" name="emisor[direccion]">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Ubigeo</label>
                                <input type="text" class="form-control" id="emisor_ubigeo" name="emisor[ubigeo]"
                                    value="{{ $user->sucursal->distrito->ubigeo }}"readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DATOS DEL RECEPTOR -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Datos del Receptor</h6>
                        <hr>

                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label">Tipo de documento</label>
                                <select class="form-select" name="receptor[tipo_documento_id]"
                                    id="receptor_tipo_documento_id" required>
                                    @foreach ($tipos_documentos as $tipo_documento)
                                        <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->codigo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Documento</label>
                                <input type="text" class="form-control" id="receptor_documento"
                                    name="receptor[documento]" inputmode="numeric" pattern="\d+" title="Solo números"
                                    value="{{ $encomienda->receptor->documento ?? '' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombres</label>
                                <input type="text" class="form-control" id="receptor_nombres" name="receptor[nombres]"
                                    pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" value="{{ $encomienda->receptor->nombres ?? '' }}"
                                    title="Solo letras" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Apellidos</label>
                                <input type="text" class="form-control" id="receptor_apellidos"
                                    name="receptor[apellidos]" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                                    value="{{ $encomienda->receptor->apellidos ?? '' }}" title="Solo letras" required>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Celular</label>
                                <input type="text" class="form-control" id="receptor_celular"
                                    name="receptor[celular]" inputmode="numeric" pattern="\d+" title="Solo números"
                                    value="{{ $encomienda->receptor->celular ?? '' }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" class="form-control" id="receptor_telefono" inputmode="numeric"
                                    pattern="\d+" title="Solo números"
                                    value="{{ $encomienda->receptor->telefono ?? '' }}" name="receptor[telefono]">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="text" class="form-control" id="receptor_direccion"
                                    name="receptor[direccion]" value="{{ $encomienda->receptor->direccion ?? '' }}">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">DEPARTAMENTO</label>
                                <select id="departamento_id" class="form-select">
                                    <option value="{{ $encomienda->distrito->provincia->departamento->id }}">
                                        {{ $encomienda->distrito->provincia->departamento->nombre }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">PROVINCIA</label>
                                <select id="provincia_id" class="form-select">
                                    <option value="{{ $encomienda->distrito->provincia->id }}">
                                        {{ $encomienda->distrito->provincia->nombre }}
                                    </option>
                                </select>

                            </div>

                            <div class="col-md-3">
                                <label class="form-label">DISTRITO</label>
                                <select id="distrito_id" class="form-select">
                                    <option value="{{ $encomienda->distrito->id }}"
                                        data-ubigeo="{{ $encomienda->distrito->ubigeo }}">
                                        {{ $encomienda->distrito->nombre }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Ubigeo</label>
                                <input type="text" class="form-control" id="receptor_ubigeo"
                                    value="{{ $encomienda->distrito->ubigeo ?? '' }}" name="receptor[ubigeo]" readonly>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RUTA -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Ruta</h6>
                        <hr>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">SUCURSAL ORIGEN</label>
                                <select id="origen" class="form-select" name="origen">
                                    <option value="" disabled>Seleccione una sucursal</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}" @selected($s->id == $encomienda->origen)>
                                            {{ $s->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="col-md-6">
                                <label class="form-label">SUCURSAL DESTINO</label>
                                <select id="destino" class="form-select" name="destino" required>
                                    <option value="" disabled selected>Seleccione una sucursal</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}" @selected($s->id == $encomienda->destino)>
                                            {{ $s->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- DETALLES -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Detalles de Encomienda</h6>
                        <hr>

                        <button type="button" class="btn btn-success btn-sm mb-2" id="btnAgregarDetalle">
                            <i data-lucide="plus"></i> Agregar Detalle
                        </button>

                        <table class="table table-sm table-bordered" id="tablaDetalles">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Peso</th>
                                    <th>Costo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($encomienda->detalles as $index => $detalle)
                                    <tr>
                                        <td>
                                            <select name="detalles[{{ $index }}][tipo_encomienda_id]"
                                                class="form-select tipo">
                                                @foreach ($tipo_encomiendas as $tipo)
                                                    <option value="{{ $tipo->id }}" @selected($tipo->id == $detalle->tipo_encomienda_id)>
                                                        {{ $tipo->descripcion }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text" name="detalles[{{ $index }}][descripcion]"
                                                class="form-control desc" value="{{ $detalle->descripcion }}">
                                        </td>

                                        <td>
                                            <input type="number" name="detalles[{{ $index }}][peso]"
                                                class="form-control peso" value="{{ $detalle->peso }}">
                                        </td>

                                        <td>
                                            <input type="number" name="detalles[{{ $index }}][costo]"
                                                class="form-control costo" value="{{ $detalle->costo }}">
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm btnEliminarDetalle">
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>

            <div class="col-md-3">

                <!-- Resumen Encomienda -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3">Tipo de servicio: Encomienda</h6>

                        <div class="row mb-2">
                            <label for="peso_total" class="col-6 col-form-label">Peso Equipaje</label>
                            <div class="col-6">
                                <input type="number" id="peso_total" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="cantidad_bultos" class="col-6 col-form-label">Cantidad Bultos</label>
                            <div class="col-6">
                                <input type="number" id="cantidad_bultos" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tipo_documento_factura_id" class="col-6 col-form-label">Tipo de documento</label>
                            <select name="tipo_documento_factura_id" id="tipo_documento_factura_id" class="form-select">
                                @foreach ($tipos_documentos_facturas as $index => $tipo_documento_factura)
                                    <option value="{{ $tipo_documento_factura->id }}"
                                        @if ($index === 1) selected @endif>
                                        {{ $tipo_documento_factura->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        

                        <div class="mb-3">
                            <label for="numero_documento_id" class="form-label">Número documento</label>
                            <input type="number"
                                value="{{ $encomienda->venta->persona->documento ?? $encomienda->emisor->documento }}"
                                id="numero_documento_id" name="numero_documento_id" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="razon_social" class="form-label">Razón social</label>
                            <input type="text"
                                value="{{ $encomienda->venta->persona->nombres ?? $encomienda->emisor->nombres }}"
                                id="razon_social" name="razon_social" class="form-control">
                        </div>
                    </div>
                </div>

                @php
                    $pagos = collect();
                    $metodoSeleccionado = null;

                    if ($encomienda->venta) {
                        $pagos = $encomienda->venta->pagos;

                        $tieneEfectivo = $pagos->where('metodo_pago_id', 1)->isNotEmpty();
                        $tieneDigital = $pagos->whereNotNull('billetera_id')->isNotEmpty();

                        if ($tieneEfectivo && $tieneDigital) {
                            $metodoSeleccionado = 3;
                        } elseif ($tieneDigital) {
                            $metodoSeleccionado = 2;
                        } elseif ($tieneEfectivo) {
                            $metodoSeleccionado = 1;
                        }
                    }
                @endphp

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="pago_instantaneo"
                                name="pago_instantaneo" value="1"
                                {{ $encomienda->pago_instantaneo ? 'checked' : '' }}>
                            <label class="form-check-label">Registrar pago</label>
                        </div>
                        <input type="hidden" id="tiene_venta" value="{{ $encomienda->venta ? 1 : 0 }}">

                        <div id="container_pago" {{ $encomienda->pago_instantaneo ? '' : 'hidden' }}>
                            <div class="row mb-2">
                                <label for="metodo_pago_id" class="col-6 col-form-label">Método de pago</label>
                                <div class="col-6">
                                    <select name="metodo_pago_id" id="metodo_pago_id" class="form-select">
                                        <option value="1" {{ $metodoSeleccionado == 1 ? 'selected' : '' }}>
                                            Efectivo
                                        </option>
                                        <option value="2" {{ $metodoSeleccionado == 2 ? 'selected' : '' }}>
                                            Digital
                                        </option>
                                        <option value="3" {{ $metodoSeleccionado == 3 ? 'selected' : '' }}>
                                            Mixto
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2 grupo_costo_total" hidden>
                                <label for="costo_total" class="col-6 col-form-label">Costo total</label>
                                <div class="col-6">
                                    <input type="number" id="costo_total" step="0.01" name="costo_total"
                                        class="form-control" readonly>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="pago_efectivo" class="col-6 col-form-label">Pago efectivo</label>
                                <div class="col-6">
                                    <input type="number" name="pago_efectivo" id="pago_efectivo" step="0.01"
                                        class="form-control"
                                        value="{{ optional($pagos->where('metodo_pago_id', 1)->first())->total }}">

                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="billetera_id" class="col-6 col-form-label">Yape/Plin/POS</label>
                                <div class="col-6">
                                    <select name="billetera_id" id="billetera_id" class="form-select">
                                        @foreach ($billeteras_digitales as $b)
                                            <option value="{{ $b->id }}"
                                                {{ $pagos->where('billetera_id', $b->id)->isNotEmpty() ? 'selected' : '' }}>
                                                {{ $b->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="pago_billetera" class="col-6 col-form-label">Pago digital</label>
                                <div class="col-6">
                                    <input type="number" name="pago_billetera" id="pago_billetera" step="0.01"
                                        value="{{ optional($pagos->whereNotNull('billetera_id')->first())->total }}"
                                        class="form-control">

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-end">
                    <a href="{{ url('/encomiendas') }}" class="btn btn-secondary">Volver</a>
                    <button type="submit" class="btn btn-primary">Guardar Encomienda</button>
                </div>
            </div>

    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/encomiendas.js') }}"></script>
    <script>
        window.IS_EDIT = {{ request()->routeIs('encomiendas.editar') ? 'true' : 'false' }};
    </script>
@endpush
