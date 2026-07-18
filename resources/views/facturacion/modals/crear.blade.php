<div class="modal fade" id="modalVentaRapida" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <form method="POST" action="{{ route('facturacion.pos.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">GENERAR COMPROBANTE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 mb-3 ">
                        <label>Sucursal <span style="color: red">*</span></label>
                        <select id="caja_id" name="caja_id" class="form-select" required>
                            @foreach ($cajas as $caja)
                                <option value="{{ $caja->id }}" data-series='@json($caja->sucursal->serie->pluck('serie', 'tipo_documento_factura_id'))'>
                                    {{ $caja->sucursal->nombre_comercial }} —
                                    {{ $caja->usuario->persona->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Tipo documento <span style="color: red">*</span></label>
                        <select id="tipo_documento_modal" name="tipo_documento_factura_id" class="form-select" required>
                            <option value="">Seleccionar un tipo</option>
                            @foreach ($tiposDocumento as $tipo)
                                <option value="{{ $tipo->id }}">
                                    {{ $tipo->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Serie<span style="color: red">*</span></label>

                        <input type="text" id="serie" class="form-control" readonly required>
                    </div>


                </div>
                <div class="row mb-2">
                    <div class="col-md-4 mb-3">
                        <label>Documento <span style="color: red">*</span></label>

                        <div class="input-group">

                            <input type="text" id="doc_cliente" name="documento" class="form-control" placeholder="DNI o RUC" required >

                            <button type="button" id="btnBuscarCliente" class="btn btn-primary"
                                onclick="buscarCliente()">

                                Buscar
                            </button>

                        </div>

                    </div>


                    <div class="col-md-4 mb-3">
                        <label id="lblNombre">Nombres <span style="color: red">*</span></label>
                        <input type="text" id="nombres" name="nombres" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3" id="divApellidos">
                        <label>Apellidos <span style="color: red">*</span></label>
                        <input type="text" id="apellidos" name="apellidos" class="form-control" required>
                    </div>

                </div>


                <div class="row mb-3">

                    <div class="col-md-12">
                        <label>Dirección</label>
                        <input type="text" id="direccion" name="direccion" class="form-control">
                    </div>

                </div>

                <hr>

                <h6><b>DETALLE DEL SERVICIO</b></h6>
                <br>
                <div class="row mb-2">
                    <div class="col-md-2 mb-3">

                        <select name="tipo_servicio_id" id="tipo_servicio_id" class="form-select">

                            <option value="1">Pasaje</option>
                            <option value="2">Encomienda</option>
                            <option value="3">Sobreequipaje</option>

                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="descripcion" class="form-control" placeholder="Escribir descripción">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" id="unidad" class="form-control" placeholder="Unidades">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" id="precio" class="form-control"
                            placeholder="Precio (incluye IGV)">
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-success w-100" onclick="agregarItem()">

                            +
                        </button>
                    </div>
                </div>

                <table class="table table-sm table-bordered">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="90">Tipo</th>
                            <th>Descripción</th>
                            <th width="90">Unidades</th>
                            <th width="110">P. Unit. (c/IGV)</th>
                            <th width="110">Valor s/IGV</th>
                            <th width="90">IGV</th>
                            <th width="110">Subtotal</th>
                            <th width="60">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaItems"></tbody>
                </table>

                <input type="hidden" name="items" id="itemsInput">

                <hr>

                <div class="text-end">
                    <h6>Subtotal: S/ <span id="subtotal">0.00</span></h6>
                    <h6>IGV: S/ <span id="igv">0.00</span></h6>
                    <h4>Total: S/ <span id="total">0.00</span></h4>
                </div>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                    Cancelar
                </button>

                <button type="submit" class="btn btn-primary">

                    Generar y Emitir
                </button>

            </div>



        </form>

    </div>

</div>
