<div class="modal fade" id="modalDescuento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Registrar Descuento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Formulario -->
            <form id="formDescuento" method="POST" action="{{ route('descuentos.guardar') }}">
                @csrf
                <input type="hidden" name="id" id="descuento_id">

                <div class="modal-body">
                    <h5 style="font-weight: bold">DATOS PERSONALES (OPCIONAL)</h5>
                    <hr class="border-gray-300 my-1">
                    <br>
                    <div class="row g-3">
                        <div class="row mb-2">
                            <div class="col-md-2 mb-3">
                                <label for="tipo_documento_id" class="form-label">Tipo</label>
                                <select class="form-select" name="tipo_documento_id" id="tipo_documento_id" required>
                                    @foreach ($tipos_documentos as $tipo_documento)
                                        <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->codigo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="documento" class="form-label">Documento</label>
                                <input type="text" name="documento" id="documento" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="nombres" class="form-label">Nombres</label>
                                <input type="text" name="nombres" id="nombres" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="apellidos" class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label for="razon_social" class="form-label">Razón social</label>
                                <input type="text" name="razon_social" id="razon_social" class="form-control">
                            </div>
                        </div>
                        <h5 style="font-weight: bold">DATOS DEL CUPÓN</h5>
                        <hr class="border-gray-300 my-1">
                        <br>
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" name="codigo" id="codigo" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label for="monto_efectivo" class="form-label">Monto Efectivo (S/)</label>
                                <input type="number" step="0.01" name="monto_efectivo" id="monto_efectivo"
                                    class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label for="porcentaje" class="form-label">Porcentaje (%)</label>
                                <input type="number" step="0.01" name="porcentaje" id="porcentaje"
                                    class="form-control" min="0" max="100">
                            </div>
                            <div class="col-md-2">
                                <label for="cantidad_usos" class="form-label">Usos</label>
                                <input type="number" name="cantidad_usos" id="cantidad_usos" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label for="fecha_maxima" class="form-label">Fecha Máxima</label>
                                <input type="date" name="fecha_maxima" id="fecha_maxima" class="form-control">
                            </div>
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
