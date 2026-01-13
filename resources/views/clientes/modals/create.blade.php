<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form id="formCliente" class="modal-content">
            @csrf
            <input type="hidden" name="cliente_id" id="cliente_id">
            @php
                use Carbon\Carbon;
                $hoy = Carbon::now('America/Lima')->format('Y-m-d');
            @endphp

            <div class="modal-header">
                <h5 class="modal-title">Registrar / Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-1">
                    <div class="row">
                        <h5 style="font-weight: bold">DATOS PERSONALES</h5>
                        <hr class="border-gray-300 my-1">
                        <br>
                        <div class="col-md-2 mb-3">
                            <label for="tipo_documento_id" class="form-label">Tipo Documento</label>
                            <select name="tipo_documento_id" id="tipo_documento_id" class="form-select" required>
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Documento</label>
                            <div class="input-group">
                                <input type="text" name="documento" id="documento" class="form-control" required
                                    inputmode="numeric" pattern="\d+" title="Solo números">
                                <button type="button" id="btnBuscarDocumento" class="btn btn-outline-primary">
                                    <i class="link-icon" data-lucide="search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" id="nombres" class="form-control" required
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" class="form-control" required
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                max="{{ $hoy }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" id="correo" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control"
                                inputmode="numeric" pattern="\d+" title="Solo números">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Celular</label>
                            <input type="text" name="celular" id="celular" class="form-control" inputmode="numeric"
                                pattern="\d{9}" maxlength="9" title="Ingrese 9 dígitos numéricos">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" id="direccion" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Departamento</label>
                            <select name="departamento_id" id="departamento_id" class="form-select">
                                <option value="">Espere...</option>
                                @foreach ($departamentos as $departamento)
                                    <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Provincia</label>
                            <select name="provincia_id" id="provincia_id" class="form-select">
                                <option value="">Espere...</option>
                                @foreach ($provincias as $provincia)
                                    <option value="{{ $provincia->id }}">{{ $provincia->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Distrito</label>
                            <select name="distrito_id" id="distrito_id" class="form-select">
                                <option value="">Espere...</option>
                                @foreach ($distritos as $distrito)
                                    <option value="{{ $distrito->id }}">{{ $distrito->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" id="btnGuardar" class="btn btn-primary">
                    <i data-lucide="save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>
