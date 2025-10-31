<div class="modal fade" id="modalEmpleado" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form id="formEmpleado" class="modal-content">
            @csrf
            <input type="hidden" name="empleado_id" id="empleado_id">
            @php
                use Carbon\Carbon;
                $hoy = Carbon::now('America/Lima')->format('Y-m-d');
            @endphp
            <div class="modal-header">
                <h5 class="modal-title">Registrar / Editar Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="tipo_documento_id" class="form-label">Tipo Documento</label>
                            <select name="tipo_documento_id" id="tipo_documento_id" class="form-select">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Documento</label>
                            <div class="input-group">
                                <input type="text" name="documento" id="documento" class="form-control" required>
                                <button type="button" id="btnBuscarDocumento" class="btn btn-outline-primary">
                                    <i class="link-icon" data-lucide="search"></i> </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" id="nombres" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-3">
                            <label class="form-label">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                max="{{ $hoy }}" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" id="correo" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Celular</label>
                            <input type="text" name="celular" id="celular" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" id="direccion" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="area_id" class="form-label">Área</label>
                            <select name="area_id" id="area_id" class="form-select">
                                <option value="">Seleccione un área</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="cargo_id" class="form-label">Cargo</label>
                            <select name="cargo_id" id="cargo_id" class="form-select">
                                <option value="">Seleccione un cargo</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="distrito_id" class="form-label">Distrito</label>
                            <select name="distrito_id" id="distrito_id" class="form-select">
                                <option value="">Seleccione un distrito</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sucursal_id" class="form-label">Sucursal</label>
                            <select name="sucursal_id" id="sucursal_id" class="form-select">
                                <option value="">Seleccione una sucursal</option>
                            </select>
                        </div>
                    </div>


                    {{-- 🔹 Sección de Usuario --}}
                    <div class="col-12 mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="chkUsuario">
                            <label class="form-check-label fw-bold">¿Tendrá usuario?</label>
                        </div>
                    </div>

                    <div class="col-12" id="seccionUsuario" style="display:none;">
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Usuario</label>
                                <input type="text" name="usuario" id="usuario" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Guardar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>
