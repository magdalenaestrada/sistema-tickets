<div class="modal fade" id="modalEncomienda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form id="formEncomienda" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Registrar Encomienda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-2">

                    <div class="col-12">
                        <h6>Datos del Emisor</h6>
                        <hr>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <label for="emisor_tipo_documento_id">Tipo documento</label>
                            <select name="emisor_tipo_documento_id" id="emisor_tipo_documento_id" class="form-select">
                                <option value="" disabled selected>Selecciona un documento</option>
                                @foreach ($tipos_documentos as $tipo_documento)
                                    <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Documento</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="emisor_documento">
                                <button type="button" class="btn btn-primary" onclick="buscarPersona('emisor')">
                                    <i class="link-icon" data-lucide="search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="emisor_nombres">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="emisor_apellidos">
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-2">
                            <label class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="emisor_telefono">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Celular</label>
                            <input type="text" class="form-control" id="emisor_celular">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Correo</label>
                            <input type="text" class="form-control" id="emisor_correo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="emisor_direccion">
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <h6>Datos del Receptor</h6>
                        <hr>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <label for="receptor_tipo_documento_id">Tipo documento</label>
                            <select name="receptor_tipo_documento_id" id="receptor_tipo_documento_id" class="form-select">
                                <option value="" disabled selected>Selecciona un documento</option>
                                @foreach ($tipos_documentos as $tipo_documento)
                                    <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Documento</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="receptor_documento">
                                <button type="button" class="btn btn-primary" onclick="buscarPersona('receptor')">
                                    <i class="link-icon" data-lucide="search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="receptor_nombres">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="receptor_apellidos">
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-2">
                            <label class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="receptor_telefono">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Celular</label>
                            <input type="text" class="form-control" id="receptor_celular">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Correo</label>
                            <input type="text" class="form-control" id="receptor_correo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="receptor_direccion">
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <h6>Ruta</h6>
                        <hr>
                    </div>
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

                    <!-- Detalles -->
                    <div class="col-12 mt-4">
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

                    <div class="col-md-3">
                        <label class="form-label">Total</label>
                        <input type="number" id="total" class="form-control" readonly>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" id="btnGuardar" class="btn btn-primary">
                    <i data-lucide="save"></i> Guardar
                </button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i data-lucide="x"></i> Cerrar
                </button>
            </div>
        </form>
    </div>
</div>
