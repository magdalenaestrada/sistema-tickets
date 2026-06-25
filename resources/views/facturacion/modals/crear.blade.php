<div class="modal fade" id="modalVentaRapida" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <form method="POST" action="{{ route('facturacion.pos.store') }}">
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">GENERAR COMPROBANTE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- TIPO DOCUMENTO (TE FALTA ESTO 🔥 IMPORTANTE) --}}
                    <div class="row mb-3">

                        <div class="col-md-6">
                            <label>Tipo documento</label>

                            <select name="tipo_documento_factura_id" class="form-control" required>

                                @foreach ($tiposDocumento as $tipo)
                                    <option value="{{ $tipo->id }}">
                                        {{ $tipo->descripcion }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>DNI / RUC</label>

                            <div class="input-group">

                                <input type="text" id="doc_cliente" class="form-control" placeholder="DNI o RUC">

                                <button type="button" id="btnBuscarCliente" class="btn btn-primary"
                                    onclick="buscarCliente()">

                                    Buscar
                                </button>

                            </div>

                        </div>

                        <div class="col-md-12">
                            <label>Cliente</label>
                            <input type="text" id="razon_social" name="razon_social" class="form-control">
                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-12">
                            <label>Dirección</label>
                            <input type="text" id="direccion" name="direccion" class="form-control">
                        </div>

                    </div>

                    <hr>

                    {{-- ITEMS --}}
                    <h6>Detalle de servicio</h6>

                    <div class="row mb-2">

                        <div class="col-md-7">
                            <input type="text" id="descripcion" class="form-control"
                                placeholder="Escribir descripción">
                        </div>

                        <div class="col-md-3">
                            <input type="number" step="0.01" id="precio" class="form-control"
                                placeholder="Precio (incluye IGV)">
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-success w-100" onclick="agregarItem()">

                                +
                            </button>
                        </div>

                    </div>

                    {{-- TABLE --}}
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Descripción</th>
                                <th width="120">Precio</th>
                                <th width="80"></th>
                            </tr>
                        </thead>

                        <tbody id="tablaItems"></tbody>
                    </table>

                    <input type="hidden" name="items" id="itemsInput">

                    <hr>

                    {{-- TOTALES --}}
                    <div class="text-end">

                        <h6>Base: S/ <span id="subtotal">0.00</span></h6>
                        <h6>IGV (18%): S/ <span id="igv">0.00</span></h6>
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

            </div>

        </form>

    </div>

</div>
