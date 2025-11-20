@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="card mb-3 col-md-9">
            <div class="card-body">
                <div>
                    <h6>Datos del Emisor</h6>
                    <hr>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label"> Tipo de documento</label>
                        <select class="form-select" name="emisor_tipo_documento_id" id="emisor_tipo_documento_id">
                            @foreach ($tipos_documentos as $tipo_documento)
                                <option value="{{ $tipo_documento->id }}">
                                    {{ $tipo_documento->codigo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Documento</label>
                        <input type="text" class="form-control" id="emisor_documento">

                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" id="emisor_nombres">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" id="emisor_apellidos">
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Celular</label>
                        <input type="text" class="form-control" id="emisor_celular">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telefono</label>
                        <input type="text" class="form-control" id="emisor_telefono">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo electrónico</label>
                        <input type="text" class="form-control" id="emisor_direccion">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ubigeo</label>
                        <input type="text" class="form-control" id="emisor_ubigeo">
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3 col-md-9">
            <div class="card-body">
                <div class="col-12 mt-3">
                    <h6>Datos del Receptor</h6>
                    <hr>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label"> Tipo de documento</label>
                        <select class="form-select" name="receptor_tipo_documento_id" id="receptor_tipo_documento_id">
                            @foreach ($tipos_documentos as $tipo_documento)
                                <option value="{{ $tipo_documento->id }}">
                                    {{ $tipo_documento->codigo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Documento</label>
                        <input type="text" class="form-control" id="receptor_documento">

                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" id="receptor_nombres">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" id="receptor_apellidos">
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Celular</label>
                        <input type="text" class="form-control" id="receptor_celular">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telefono</label>
                        <input type="text" class="form-control" id="receptor_telefono">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo electrónico</label>
                        <input type="text" class="form-control" id="receptor_direccion">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ubigeo</label>
                        <input type="text" class="form-control" id="receptor_ubigeo">
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3 col-md-9">
            <div class="card-body">
                <div class="col-12 mt-3">
                    <h6>Ruta</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Origen</label>
                            <select id="origen" class="form-select" name="origen" required>
                                <option value="" selected disabled>Seleccione una sucursal</option>
                                @foreach ($sucursales as $s)
                                    <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Destino</label>
                            <select id="destino" class="form-select" name="destino" required>
                                <option value="" selected disabled>Seleccione una sucursal</option>
                                @foreach ($sucursales as $s)
                                    <option value="{{ $s->id }}">{{ $s->nombre_comercial }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3 col-md-9">
            <div class="card-body">
                <div class="col-12 mt-3">
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
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-3">

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-2">Tipo de servicio: Encomienda</h6>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center gap-2">
                            <label for="total" class="form-label mb-0">Total</label>
                            <input type="number" id="total" class="form-control" readonly>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-2">Opciones adicionales</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-2">Resumen</h6>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/encomiendas.js') }}"></script>
@endpush
