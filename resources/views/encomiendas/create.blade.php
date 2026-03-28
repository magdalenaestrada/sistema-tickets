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
                                <input type="text" class="form-control solo-numeros" id="emisor_documento"
                                    name="emisor_documento" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombres <span style="color: red">*</span></label>
                                <input type="text" class="form-control solo-letras" id="emisor_nombres"
                                    name="emisor_nombres" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Apellidos <span style="color: red">*</span></label>
                                <input type="text" class="form-control solo-letras" id="emisor_apellidos"
                                    name="emisor_apellidos" required>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Celular <span style="color: red">*</span></label>
                                <input type="text" class="form-control solo-numeros" id="emisor_celular" maxlength="9"
                                    name="emisor_celular" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" class="form-control solo-numeros" id="emisor_telefono"
                                    name="emisor_telefono" maxlength="9">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Correo electrónico</label>
                                <input type="text" class="form-control" id="emisor_direccion" name="emisor_direccion">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Ubigeo</label>
                                <input type="text" class="form-control" id="emisor_ubigeo" name="emisor_ubigeo"
                                    value="{{ $user->sucursal->distrito->ubigeo }}"readonly>
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
                                <input type="text" class="form-control solo-numeros" id="receptor_documento"
                                    name="receptor_documento">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombres <span style="color: red">*</span></label>
                                <input type="text" class="form-control  solo-letras" id="receptor_nombres"
                                    name="receptor_nombres" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Apellidos <span style="color: red">*</span></label>
                                <input type="text" class="form-control solo-letras" id="receptor_apellidos"
                                    name="receptor_apellidos" required>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Celular <span style="color: red">*</span></label>
                                <input type="text" class="form-control solo-numeros" id="receptor_celular"
                                    maxlength="9" name="receptor_celular" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" class="form-control solo-numeros" maxlength="9"
                                    id="receptor_telefono" name="receptor_telefono">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="text" class="form-control" id="receptor_direccion"
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
                                <input type="text" class="form-control" id="receptor_ubigeo" name="receptor_ubigeo"
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
                                <label class="form-label">SUCURSAL ORIGEN <span style="color: red">*</span></label>
                                <select id="origen" class="form-select" name="origen">
                                    <option value="" disabled>Seleccione una sucursal</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}"
                                            @if ($s->id == $user->sucursal_id) selected @endif>
                                            {{ $s->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="col-md-6">
                                <label class="form-label">SUCURSAL DESTINO <span style="color: red">*</span></label>
                                <select id="destino" class="form-select" name="destino" required>
                                    <option value="" disabled selected>Seleccione una sucursal</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
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

                        <button type="button" class="btn btn-success btn-sm mb-2" id="btnAgregarDetalle">
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
                        <h6 class="mb-3">Tipo de servicio: Encomienda</h6>

                        <div class="row mb-2">
                            <label for="peso_total" class="col-6 col-form-label">Peso total <b>(KG)</b></label>
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
                            <input type="number" id="numero_documento_id" name="numero_documento_id"
                                class="form-control solo-numeros">
                        </div>

                        <div class="mb-3">
                            <label for="razon_social" class="form-label">Razón social</label>
                            <input type="text" id="razon_social" name="razon_social" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="pago_instantaneo"
                                name="pago_instantaneo" value="1">
                            <label class="form-check-label" for="pago_instantaneo">
                                Registrar pago
                            </label>
                        </div>
                        <div id="container_pago">
                            <div class="row mb-2">
                                <label for="metodo_pago_id" class="col-6 col-form-label">Método de pago</label>
                                <div class="col-6">
                                    <select name="metodo_pago_id" id="metodo_pago_id" class="form-select">
                                        @foreach ($metodos_pago as $metodo_pago)
                                            <option value="{{ $metodo_pago->id }}">
                                                {{ $metodo_pago->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2 grupo_costo_total" hidden>
                                <label for="costo_total" class="col-6 col-form-label">Costo total <b>(S/)</b></label>
                                <div class="col-6">
                                    <input type="number" step="0.01" id="costo_total" name="costo_total"
                                        class="form-control" readonly>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="pago_efectivo" class="col-6 col-form-label">Pago efectivo <b>(S/)</b></label>
                                <div class="col-6">
                                    <input type="number" step="0.01" id="pago_efectivo" name="pago_efectivo"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="billetera_id" class="col-6 col-form-label">Yape/Plin/POS</label>
                                <div class="col-6">
                                    <select name="billetera_id" id="billetera_id" class="form-select">
                                        @foreach ($billeteras_digitales as $billetera_digital)
                                            <option value="{{ $billetera_digital->id }}">
                                                {{ $billetera_digital->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="pago_billetera" class="col-6 col-form-label">Pago digital <b>(S/)</b></label>
                                <div class="col-6">
                                    <input type="number" step="0.01" id="pago_billetera" name="pago_billetera"
                                        class="form-control">
                                </div>
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
@endpush
