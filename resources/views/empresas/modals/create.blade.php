<div class="modal fade" id="modalEmpresa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Empresa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="formEmpresa">
        @csrf
        <input type="hidden" name="id" id="empresa_id">

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">RUC / Documento</label>
              <input type="text" name="documento" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Razón Social</label>
              <input type="text" name="razon_social" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nombre Comercial</label>
              <input type="text" name="nombre_comercial" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Dirección</label>
              <input type="text" name="direccion" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Usuario Facturación</label>
              <input type="text" name="usuario_facturacion" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Contraseña Facturación</label>
              <input type="password" name="contrasena_facturacion" class="form-control">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
