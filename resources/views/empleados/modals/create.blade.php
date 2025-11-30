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
                                <input type="text" name="documento" id="documento" class="form-control" required>
                                <button type="button" id="btnBuscarDocumento" class="btn btn-outline-primary">
                                    <i class="link-icon" data-lucide="search"></i>
                                </button>
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
                            <input type="text" name="celular" id="celular" class="form-control" pattern="\d{9}"
                                maxlength="9" title="Ingrese 9 dígitos numéricos" required>
                        </div>


                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" id="direccion" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Departamento</label>
                            <select name="departamento_id" id="departamento_id" class="form-select" required>
                                <option value="">Espere...</option>
                                @foreach ($departamentos as $departamento)
                                    <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Provincia</label>
                            <select name="provincia_id" id="provincia_id" class="form-select" required>
                                <option value="">Espere...</option>
                                @foreach ($provincias as $provincia)
                                    <option value="{{ $provincia->id }}">{{ $provincia->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Distrito</label>
                            <select name="distrito_id" id="distrito_id" class="form-select" required>
                                <option value="">Espere...</option>
                                @foreach ($distritos as $distrito)
                                    <option value="{{ $distrito->id }}">{{ $distrito->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <br>
                    <div class="row">
                        <h5 style="font-weight: bold">DATOS LABORALES</h5>
                        <hr class="border-gray-300 my-1">
                        <br>
                        <div class="col-md-4 mb-3">
                            <label for="sucursal_id" class="form-label">Sucursal</label>
                            <select name="sucursal_id" id="sucursal_id" class="form-select" required>
                                <option value="">Seleccione una sucursal</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cargo_id" class="form-label">Cargo</label>
                            <select name="cargo_id" id="cargo_id" class="form-select" required>
                                <option value="">Seleccione un cargo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha de ingreso</label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control"
                                required>
                        </div>

                        <div class="col-md-6 mb-3 conductor" hidden>
                            <label for="tipo_licencia_id" class="form-label">Categoría de licencia</label>
                            <select name="tipo_licencia_id" id="tipo_licencia_id" class="form-select">
                                <option value="">Seleccione una categoría</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 conductor" hidden>
                            <label class="form-label">Licencia</label>
                            <input type="text" name="licencia_conducir" id="licencia_conducir"
                                class="form-control">
                        </div>
                        <div class="col-md-3 mb-3 conductor" hidden>
                            <label class="form-label">Fecha de vencimiento licencia</label>
                            <input type="date" name="fecha_vencimiento_licencia" id="fecha_vencimiento_licencia"
                                class="form-control">
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" id="chkUsuario" class="form-check-input">
                            <label for="chkUsuario" class="form-check-label">¿Tendrá usuario?</label>
                        </div>

                        <div id="seccionUsuario" hidden>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="usuario" class="form-label">Usuario</label>
                                    <input type="text" id="usuario" name="usuario" class="form-control"
                                        autocomplete="off">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" id="password" name="password" class="form-control"
                                            autocomplete="new-password">
                                        <button type="button" id="togglePassword" class="btn btn-outline-secondary">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
