<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formUsuario">
            @csrf
            @method('PUT')

            <input type="hidden" id="usuario_id">

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Usuario del Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <fieldset class="border p-3 mb-3 rounded">
                        <legend class="float-none w-auto px-2 fs-6">Empleado</legend>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Documento <span style="color: red">*</span></label></label>
                                <input type="text" id="persona_documento" class="form-control" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nombre <span style="color: red">*</span></label></label>
                                <input type="text" id="persona_nombre" class="form-control" disabled>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="border p-3 rounded">
                        <legend class="float-none w-auto px-2 fs-6">Acceso al sistema</legend>

                        <div class="row g-3">
                            <input type="hidden" id="usuario_id">

                            <div class="mb-2">
                                <label class="form-label">Usuario <span style="color: red">*</span></label></label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>

                            <div class="mb-2">
                                <label for="rol_id">Rol <span style="color: red">*</span></label></label>
                                <select name="rol_id" id="rol_id" class="form-select" required>
                                    <option value="">Seleccione un rol</option>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>


                            <small class="text-muted">
                                Deje la contraseña en blanco si no desea cambiarla.
                            </small>
                        </div>
                    </fieldset>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">
                        Guardar cambios
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
