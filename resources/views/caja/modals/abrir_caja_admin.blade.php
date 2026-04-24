{{-- Modal abrir caja --}}
<div class="modal fade" id="modalAbrirCaja" tabindex="-1" aria-labelledby="modalAbrirCajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAbrirCajaLabel">Abrir caja por sucursal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('caja.store') }}" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="sucursal_id_open" class="form-label">Sucursal</label>
                        <select name="sucursal_id" id="sucursal_id_open" class="form-select" required>
                            <option value="">Seleccione una sucursal</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">
                                    {{ $sucursal->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="monto_apertura" class="form-label">Monto de apertura</label>
                        <input type="number" step="0.01" min="0" name="monto_apertura" id="monto_apertura"
                            class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        Abrir caja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
