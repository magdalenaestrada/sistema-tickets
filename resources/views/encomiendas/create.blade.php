@extends('layouts.app')

@section('content')
    <form id="formEncomienda">
        @csrf
        <div class="row">
            <div class="col-md-9">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Datos del Emisor</h6>
                        <hr>

                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label">Tipo de documento <span style="color: red">*</span></label>
                                <select class="form-select" name="emisor_tipo_documento_id" id="emisor_tipo_documento_id"
                                    required>
                                    @foreach ($tipos_documentos as $tipo_documento)
                                        <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->codigo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Documento <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm solo-numeros" id="emisor_documento"
                                        name="emisor_documento" required>
                                    <button type="button" class="btn btn-primary btn-buscar-persona" data-tipo="emisor"
                                        title="Buscar emisor">
                                        <i data-lucide="search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombres <span style="color: red">*</span></label>
                                <input type="text" class="form-control form-control-sm solo-letras" id="emisor_nombres"
                                    name="emisor_nombres" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Apellidos <span style="color: red">*</span></label>
                                <input type="text" class="form-control form-control-sm solo-letras" id="emisor_apellidos"
                                    name="emisor_apellidos" required>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Celular</label>
                                <input type="text" class="form-control form-control-sm solo-numeros" id="emisor_celular" maxlength="9"
                                    name="emisor_celular">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" class="form-control form-control-sm solo-numeros" id="emisor_telefono"
                                    name="emisor_telefono" maxlength="9">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Correo electrónico</label>
                                <input type="text" class="form-control form-control-sm" id="emisor_direccion" name="emisor_direccion">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Ubigeo</label>
                                <input type="text" class="form-control form-control-sm" id="emisor_ubigeo" name="emisor_ubigeo"
                                    value="{{ $user->sucursal->distrito->ubigeo ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

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
                                        <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->codigo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Documento</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm solo-numeros" id="receptor_documento"
                                        name="receptor_documento">
                                    <button type="button" class="btn btn-primary btn-buscar-persona" data-tipo="receptor"
                                        title="Buscar receptor">
                                        <i data-lucide="search"></i>
                                    </button>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <label class="form-label">Nombres <span style="color: red">*</span></label>
                                <input type="text" class="form-control form-control-sm  solo-letras" id="receptor_nombres"
                                    name="receptor_nombres" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Apellidos <span style="color: red">*</span></label>
                                <input type="text" class="form-control form-control-sm solo-letras" id="receptor_apellidos"
                                    name="receptor_apellidos" required>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Celular</label>
                                <input type="text" class="form-control form-control-sm solo-numeros" id="receptor_celular"
                                    maxlength="9" name="receptor_celular">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" class="form-control form-control-sm solo-numeros" maxlength="9"
                                    id="receptor_telefono" name="receptor_telefono">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="text" class="form-control form-control-sm" id="receptor_direccion"
                                    name="receptor_direccion">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">DEPARTAMENTO <span style="color: red">*</span></label>
                                <select name="receptor_departamento_id" id="departamento_id" class="form-select"
                                    required>
                                    <option value="">Seleccione</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">PROVINCIA <span style="color: red">*</span></label>
                                <select name="receptor_provincia_id" id="provincia_id" class="form-select" required>
                                    <option value="">Seleccione</option>

                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">DISTRITO <span style="color: red">*</span></label>
                                <select name="receptor_distrito_id" id="distrito_id" class="form-select" required>
                                    <option value="">Seleccione</option>
                                </select>
                            </div>


                            <div class="col-md-3">
                                <label class="form-label">Ubigeo</label>
                                <input type="text" class="form-control form-control-sm" id="receptor_ubigeo" name="receptor_ubigeo"
                                    readonly>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Ruta</h6>
                        <hr>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">PARADA ORIGEN <span style="color: red">*</span></label>
                                <select id="origen" class="form-select" name="origen_pueblito_id">
                                    <option value="" disabled>Seleccione una sucursal</option>
                                    @foreach ($pueblitos as $pueblito)
                                        <option value="{{ $pueblito->id }}">
                                            {{ $pueblito->descripcion }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="col-md-6">
                                <label class="form-label">PARADA DESTINO <span style="color: red">*</span></label>
                                <select id="destino" class="form-select" name="destino_pueblito_id" required>
                                    <option value="" disabled selected>Seleccione una sucursal</option>
                                    @foreach ($pueblitos as $pueblito)
                                        <option value="{{ $pueblito->id }}">{{ $pueblito->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Detalles de Encomienda <span style="color: red">*</span></h6>
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
                                    <th>Costo (S/)</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-1">Tipo de servicio: Encomienda</h6>

                        <div class="row mb-1">
                            <label for="peso_total" class="col-6 col-form-label">Peso total <b>(KG)</b></label>
                            <div class="col-6">
                                <input type="number" id="peso_total" class="form-control form-control-sm form-control form-control-sm-xs" readonly>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="cantidad_bultos" class="col-6 col-form-label">Cantidad Bultos</label>
                            <div class="col-6">
                                <input type="number" id="cantidad_bultos" class="form-control form-control-sm form-control form-control-sm-xs" readonly>
                            </div>
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
                                <select name="caja_id" id="caja_id" class="form-select">
                                    <option value="">Seleccionar sucursal</option>
                                    @foreach ($cajas_emision as $caja)
                                        <option value="{{ $caja->id }}"
                                            data-serie="{{ $caja->sucursal->serie->codigo ?? '001' }}"
                                            @if ($caja->sucursal_id == $user->sucursal_id) selected @endif>
                                            {{ $caja->sucursal->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>
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
                                <input type="text" id="razon_social" name="razon_social" class="form-control form-control-sm">
                            </div>

                            <div class="mb-1">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" id="direccion" name="direccion" class="form-control form-control-sm"
                                    value="-" readonly>
                            </div>

                            <div class="d-grid gap-2">

                                <button type="button" class="btn btn-success btn-sm" id="btnAbrirPago">
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
    <script src="{{ asset('js/encomiendas_create.js') }}"></script>
@endpush
