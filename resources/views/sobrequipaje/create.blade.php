@extends('layouts.app')

@section('content')
    <form id="formEncomienda">
        @csrf
        <div class="row">
            <div class="col-md-9">

                @if ($esSobreequipaje)
                    {{-- ================= BLOQUE "TICKET ENCONTRADO" =================
                         El pasaje ya llega resuelto por route model binding, así que
                         no hace falta buscar: solo mostramos el resumen ya encontrado,
                         tal como en el mockup. --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6><i data-lucide="search"></i> Ticket / Pasajero</h6>
                            <hr>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Ticket</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $pasaje->codigo_boleto ?? $pasaje->id }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <span class="badge bg-success">
                                        <i data-lucide="check-circle"></i> Ticket encontrado
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card mb-3">
                    <div class="card-body">
                        <h6>{{ $esSobreequipaje ? 'Datos del Pasajero' : 'Datos del Emisor' }}</h6>
                        <hr>
                        <input type="hidden" name="tipo_doc_sunat" id="tipo_doc_sunat">
                        @if ($esSobreequipaje)
                            <input type="hidden" name="pasaje_id" value="{{ $pasaje->id }}">
                            <input type="hidden" name="sobrequipaje" value="true">
                        @endif
                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label">Tipo de documento <span style="color: red">*</span></label>
                                <select class="form-select" name="emisor_tipo_documento_id" id="emisor_tipo_documento_id"
                                    required {{ $esSobreequipaje ? 'disabled' : '' }}>
                                    @foreach ($tipos_documentos as $tipo_documento)
                                        <option value="{{ $tipo_documento->id }}"
                                            {{ $esSobreequipaje && ($pasaje->persona->tipo_documento_id ?? null) == $tipo_documento->id ? 'selected' : '' }}>
                                            {{ $tipo_documento->codigo }}</option>
                                    @endforeach
                                </select>
                                {{-- Los <select> disabled no se envían en el submit, así que
                                     mandamos el valor real por un hidden aparte. --}}
                                @if ($esSobreequipaje)
                                    <input type="hidden" name="emisor_tipo_documento_id"
                                        value="{{ $pasaje->persona->tipo_documento_id ?? '' }}">
                                @endif
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Documento <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm solo-numeros"
                                        id="emisor_documento" name="emisor_documento"
                                        value="{{ $esSobreequipaje ? $pasaje->persona->documento : '' }}"
                                        {{ $esSobreequipaje ? 'readonly' : 'required' }}>
                                    <button type="button" class="btn btn-primary btn-buscar-persona" data-tipo="emisor"
                                        title="Buscar emisor" {{ $esSobreequipaje ? 'disabled' : '' }}>
                                        <i data-lucide="search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombres <span style="color: red">*</span></label>
                                <input type="text" class="form-control form-control-sm solo-letras" id="emisor_nombres"
                                    name="emisor_nombres" value="{{ $esSobreequipaje ? $pasaje->persona->nombres : '' }}"
                                    {{ $esSobreequipaje ? 'readonly' : 'required' }}>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Apellidos <span style="color: red">*</span></label>
                                <input type="text" class="form-control form-control-sm solo-letras" id="emisor_apellidos"
                                    name="emisor_apellidos"
                                    value="{{ $esSobreequipaje ? $pasaje->persona->apellidos : '' }}"
                                    {{ $esSobreequipaje ? 'readonly' : 'required' }}>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Celular</label>
                                <input type="text" class="form-control form-control-sm solo-numeros" id="emisor_celular"
                                    maxlength="9" name="emisor_celular"
                                    value="{{ $esSobreequipaje ? $pasaje->persona->celular ?? '' : '' }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" class="form-control form-control-sm solo-numeros" id="emisor_telefono"
                                    name="emisor_telefono" maxlength="9">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Correo electrónico</label>
                                <input type="text" class="form-control form-control-sm" id="emisor_direccion"
                                    name="emisor_direccion"
                                    value="{{ $esSobreequipaje ? $pasaje->persona->correo ?? '' : '' }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Ubigeo</label>
                                <input type="text" class="form-control form-control-sm" id="emisor_ubigeo"
                                    name="emisor_ubigeo" value="{{ $user->sucursal->distrito->ubigeo ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($esSobreequipaje)
                    {{-- ================= INFORMACIÓN DEL VIAJE =================
                         TODO: ajusta las rutas de acceso (->salida->horario, ->asiento,
                         ->codigo_boleto) a los nombres reales de tu modelo Pasaje. --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6><i data-lucide="bus"></i> Información del viaje</h6>
                            <hr>
                            <div class="row g-2">
                                <div class="col-md-2">
                                    <label class="form-label small mb-0">Fecha de viaje</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ optional($pasaje->salida->fecha_salida ?? null)->format('d/m/Y') }}"
                                        readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-0">Hora</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $pasaje->salida->horario->hora_formateada ?? '' }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-0">Origen</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $pasaje->origenPueblito->descripcion ?? '' }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-0">Destino</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $pasaje->destinoPueblito->descripcion ?? '' }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-0">Asiento</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $pasaje->asiento ?? '' }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-0">Código de boleto</label>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $pasaje->codigo_boleto ?? $pasaje->id }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (!$esSobreequipaje)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Datos del Receptor</h6>
                            <hr>

                            <div class="row g-2">
                                <div class="col-md-2">
                                    <label class="form-label">Tipo de documento <span style="color: red">*</span></label>
                                    <select class="form-select" name="receptor_tipo_documento_id"
                                        id="receptor_tipo_documento_id" required>
                                        @foreach ($tipos_documentos as $tipo_documento)
                                            <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->codigo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Documento</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm solo-numeros"
                                            id="receptor_documento" name="receptor_documento">
                                        <button type="button" class="btn btn-primary btn-buscar-persona"
                                            data-tipo="receptor" title="Buscar receptor">
                                            <i data-lucide="search"></i>
                                        </button>
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <label class="form-label">Nombres <span style="color: red">*</span></label>
                                    <input type="text" class="form-control form-control-sm  solo-letras"
                                        id="receptor_nombres" name="receptor_nombres" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Apellidos <span style="color: red">*</span></label>
                                    <input type="text" class="form-control form-control-sm solo-letras"
                                        id="receptor_apellidos" name="receptor_apellidos" required>
                                </div>
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-md-3">
                                    <label class="form-label">Celular</label>
                                    <input type="text" class="form-control form-control-sm solo-numeros"
                                        id="receptor_celular" maxlength="9" name="receptor_celular">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Telefono</label>
                                    <input type="text" class="form-control form-control-sm solo-numeros"
                                        maxlength="9" id="receptor_telefono" name="receptor_telefono">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Correo electrónico</label>
                                    <input type="text" class="form-control form-control-sm" id="receptor_direccion"
                                        name="receptor_direccion">
                                </div>
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-md-4">
                                    <label class="form-label">DEPARTAMENTO</label>
                                    <select name="receptor_departamento_id" id="departamento_id" class="form-select">
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">PROVINCIA</label>
                                    <select name="receptor_provincia_id" id="provincia_id" class="form-select">
                                        <option value="">Seleccione</option>

                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">DISTRITO</label>
                                    <select name="receptor_distrito_id" id="distrito_id" class="form-select">
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>
                            {{ $esSobreequipaje ? 'Detalles de Sobreequipaje' : 'Detalles de Encomienda' }} <span
                                style="color: red">*</span>
                        </h6>
                        <hr>

                        <button type="button" class="btn btn-success btn-sm mb-1" id="btnAgregarDetalle">
                            <i data-lucide="plus"></i> Agregar Detalle
                        </button>

                        <table class="table table-sm table-bordered" id="tablaDetalles">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Peso (KG)</th>
                                    @if ($esSobreequipaje)
                                        <th>Precio / KG (S/)</th>
                                    @endif
                                    <th>Costo (S/)</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        @if ($esSobreequipaje)
                            <div class="row g-2 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">Observaciones (opcional)</label>
                                    <textarea class="form-control form-control-sm" name="observaciones" rows="3"
                                        placeholder="Ejemplo: caña de pescar, instrumento musical, etc."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Últimos sobreequipajes del pasajero</label>
                                    <div class="border rounded p-2" style="max-height: 100px; overflow-y: auto;">
                                        @forelse (($historialSobreequipaje ?? []) as $registro)
                                            <div class="d-flex justify-content-between small border-bottom py-1">
                                                <span>{{ optional($registro->fecha_creacion)->format('d/m/Y') }}</span>
                                                <span>Ticket {{ $registro->id }}</span>
                                                <span>S/ {{ number_format($registro->total, 2) }}</span>
                                            </div>
                                        @empty
                                            <span class="text-muted small">Sin historial previo</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-1">Tipo de servicio: {{ $esSobreequipaje ? 'Sobreequipaje' : 'Encomienda' }}</h6>

                        @if ($esSobreequipaje)
                            <div class="row mb-1">
                                <label class="col-6 col-form-label">Peso permitido <b>(KG)</b></label>
                                <div class="col-6">
                                    <input type="text" id="peso_permitido" class="form-control form-control-sm"
                                        value="{{ number_format($pesoPermitido ?? 0, 2) }}" readonly>
                                    <input type="hidden" id="peso_permitido_valor" value="{{ $pesoPermitido ?? 0 }}">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label class="col-6 col-form-label text-danger">Peso excedente <b>(KG)</b></label>
                                <div class="col-6">
                                    <input type="number" id="peso_excedente"
                                        class="form-control form-control-sm text-danger" readonly>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-1">
                            <label for="peso_total" class="col-6 col-form-label">Peso total <b>(KG)</b></label>
                            <div class="col-6">
                                <input type="number" id="peso_total"
                                    class="form-control form-control-sm form-control form-control-sm-xs" readonly>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="cantidad_bultos" class="col-6 col-form-label">Cantidad Bultos</label>
                            <div class="col-6">
                                <input type="number" id="cantidad_bultos"
                                    class="form-control form-control-sm form-control form-control-sm-xs" readonly>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label class="form-label">ORIGEN <span style="color: red">*</span></label>
                            <select id="origen" class="form-select" name="origen_pueblito_id"
                                {{ $esSobreequipaje ? 'disabled' : 'required' }}>
                                <option value="">Seleccione una parada</option>
                                @foreach ($pueblitos as $pueblito)
                                    <option value="{{ $pueblito->id }}"
                                        {{ ($esSobreequipaje ? $pasaje->origen_pueblito_id : old('origen_pueblito_id')) == $pueblito->id ? 'selected' : '' }}>
                                        {{ $pueblito->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($esSobreequipaje)
                                <input type="hidden" name="origen_pueblito_id"
                                    value="{{ $pasaje->origen_pueblito_id }}">
                            @endif
                        </div>
                        <div class="row mb-1">
                            <label class="form-label">DESTINO <span style="color: red">*</span></label>
                            <select id="destino" class="form-select" name="destino_pueblito_id"
                                {{ $esSobreequipaje ? 'disabled' : 'required' }}>
                                <option value="">Seleccione una parada</option>
                                @foreach ($pueblitos as $pueblito)
                                    <option value="{{ $pueblito->id }}"
                                        {{ ($esSobreequipaje ? $pasaje->destino_pueblito_id : old('destino_pueblito_id')) == $pueblito->id ? 'selected' : '' }}>
                                        {{ $pueblito->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($esSobreequipaje)
                                <input type="hidden" name="destino_pueblito_id"
                                    value="{{ $pasaje->destino_pueblito_id }}">
                            @endif
                        </div>

                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card shadow-sm border-0 panel-venta">
                        <div class="card-body">
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">EMITIR SUNAT:</span>

                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="emitir_sunat">
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mb-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary doc-btn"
                                        id="btn_boleta" data-doc="boleta">
                                        Boleta
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary doc-btn"
                                        id="btn_factura" data-doc="factura">
                                        Factura
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success doc-btn active"
                                        id="btn_nota_venta" data-doc="4">
                                        N. Venta
                                    </button>
                                </div>
                            </div>

                            <div class="mb-1 text-center fw-semibold">Sucursal de venta: <span style="color: red">*</span>
                            </div>


                            <div class="mb-1">
                                <select name="caja_id" id="caja_id" class="form-select"
                                    {{ $esSobreequipaje ? 'disabled' : 'required' }}>
                                    <option value="">Seleccionar sucursal</option>
                                    @foreach ($cajas_emision as $caja)
                                        <option value="{{ $caja->id }}"
                                            {{ ($esSobreequipaje ? $pasaje->venta->caja_id ?? null : $user->sucursal_id) == $caja->id ? 'selected' : '' }}>
                                            {{ $caja->sucursal->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($esSobreequipaje)
                                    <input type="hidden" name="caja_id" value="{{ $pasaje->venta->caja_id ?? '' }}">
                                @endif
                            </div>
                            <div class="mb-1 fw-semibold">Serie sucursal:</div>
                            <div class="panel-box mb-1 text-center" id="serie_doc">Seleccionar sucursal</div>

                            <div class="resumen-totales">
                                <div class="d-flex justify-content-between">
                                    <span>Sub total:</span>
                                    <strong>S/ <span id="subtotal">0.00</span></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Descuentos:</span>
                                    <strong>S/ <span id="total_descuento">0.00</span></strong>
                                </div>
                                <div class="d-flex justify-content-between text-primary">
                                    <span>Total a pagar:</span>
                                    <strong>S/ <span id="total_pagar">0.00</span></strong>
                                </div>
                            </div>


                            <input type="hidden" name="emitir_sunat_estado" id="emitir_sunat_estado" value="0">

                            <div class="mb-1">
                                <label class="form-label">Documento cliente: </label>

                                <div class="input-group">
                                    <input type="text" id="doc_cliente" name="numero_documento_id"
                                        class="form-control form-control-sm solo-numeros">

                                    <button type="button" id="btnBuscarCliente" class="btn btn-primary">
                                        <i class="link-icon" data-lucide="search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-1">
                                <label class="form-label">Razón social:</label>
                                <input type="text" id="razon_social" name="razon_social"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="mb-1">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" id="direccion" name="direccion"
                                    class="form-control form-control-sm" value="-" readonly>
                            </div>

                            <div class="d-grid gap-2">

                                <button type="submit" class="btn btn-success btn-sm" id="btnAbrirPago">
                                    Terminar Venta
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
    @include('pasajes.modals.metodos_pago')
@endsection

@push('scripts')
    {{-- Bandera global para que encomiendas_create.js sepa si debe activar
         la columna Precio/KG y el cálculo de excedente. --}}
    <script>
        window.ES_SOBREEQUIPAJE = @json($esSobreequipaje);
        window.PESO_PERMITIDO_SOBREEQUIPAJE = @json($pesoPermitido ?? 0);
    </script>
    <script src="{{ asset('js/encomiendas_create.js') }}"></script>
@endpush
