{{--
    resources/views/series_sucursal/index.blade.php

    Ajusta @extends / @section al layout real de tu proyecto.
    Se asume Bootstrap 4/5 ya cargado en el layout.
--}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Series por Sucursal</h5>
            </div>

            <div class="card-body">

                <div id="alertaSeries"></div>

                <table class="table table-bordered table-hover align-middle" id="tablaSeries">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 26%">SUCURSAL</th>
                            <th style="width: 26%">TIPO DOCUMENTO</th>
                            <th style="width: 20%">SERIE</th>
                            <th style="width: 28%">GUARDAR</th>
                        </tr>
                    </thead>

                    {{-- Fila que funciona como formulario --}}
                    <tbody>
                        <tr>
                            <td>
                                <select id="sucursal_id" class="form-select form-control">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_comercial }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select id="tipo_documento_factura_id" class="form-select form-control">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($tiposDocumento as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->descripcion }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" id="serie" class="form-control" placeholder="Ej: F001"
                                    maxlength="10">
                            </td>
                            <td>
                                <button type="button" id="btnGuardarSerie" class="btn btn-primary w-100">
                                    Guardar
                                </button>
                            </td>
                        </tr>
                    </tbody>

                    {{-- Listado actual, se le van agregando filas nuevas por JS --}}
                    <tbody id="tbodySeries">
                        @forelse ($series as $item)
                            <tr id="fila-serie-{{ $item->id }}" data-id="{{ $item->id }}"
                                data-sucursal-id="{{ $item->sucursal_id }}"
                                data-tipo-id="{{ $item->tipo_documento_factura_id }}" data-serie="{{ $item->serie }}">
                                <td class="celda-sucursal">{{ $item->sucursal->nombre_comercial ?? '-' }}</td>
                                <td class="celda-tipo">{{ $item->tipoDocumentoFactura->descripcion ?? '-' }}</td>
                                <td class="celda-serie">{{ $item->serie }}</td>
                                <td class="celda-acciones">
                                    <button type="button" class="btn btn-sm btn-outline-primary btnEditarSerie"
                                        data-id="{{ $item->id }}">
                                        Editar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btnEliminarSerie"
                                        data-id="{{ $item->id }}">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="filaVacia">
                                <td colspan="4" class="text-center text-muted">
                                    Aún no hay series registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const CSRF_TOKEN = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content');

            const btnGuardar = document.getElementById('btnGuardarSerie');
            const tbodySeries = document.getElementById('tbodySeries');
            const alertaBox = document.getElementById('alertaSeries');

            function mostrarAlerta(mensaje, tipo) {
                alertaBox.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            }

            function agregarFilaATabla(data) {
                // Quita el mensaje de "no hay series" si existe
                const filaVacia = document.getElementById('filaVacia');
                if (filaVacia) filaVacia.remove();

                const fila = document.createElement('tr');
                fila.id = 'fila-serie-' + data.id;
                fila.dataset.id = data.id;
                fila.dataset.sucursalId = data.sucursal_id ?? '';
                fila.dataset.tipoId = data.tipo_documento_factura_id ?? '';
                fila.dataset.serie = data.serie;
                fila.innerHTML = `
            <td class="celda-sucursal">${data.sucursal}</td>
            <td class="celda-tipo">${data.tipo_documento}</td>
            <td class="celda-serie">${data.serie}</td>
            <td class="celda-acciones">
                <button type="button"
                        class="btn btn-sm btn-outline-primary btnEditarSerie"
                        data-id="${data.id}">
                    Editar
                </button>
                <button type="button"
                        class="btn btn-sm btn-outline-danger btnEliminarSerie"
                        data-id="${data.id}">
                    Eliminar
                </button>
            </td>
        `;
                // Se agrega arriba de todo (como orderByDesc('id') en el index)
                tbodySeries.prepend(fila);
            }

            function limpiarFormulario() {
                document.getElementById('sucursal_id').value = '';
                document.getElementById('tipo_documento_factura_id').value = '';
                document.getElementById('serie').value = '';
            }

            btnGuardar.addEventListener('click', function() {
                const sucursal_id = document.getElementById('sucursal_id').value;
                const tipo_documento_factura_id = document.getElementById('tipo_documento_factura_id')
                .value;
                const serie = document.getElementById('serie').value.trim();

                if (!sucursal_id || !tipo_documento_factura_id || !serie) {
                    mostrarAlerta('Completa sucursal, tipo de documento y serie.', 'warning');
                    return;
                }

                btnGuardar.disabled = true;
                btnGuardar.textContent = 'Guardando...';

                fetch('{{ route('series-sucursal.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            sucursal_id,
                            tipo_documento_factura_id,
                            serie,
                        }),
                    })
                    .then(async (response) => {
                        const json = await response.json();
                        if (!response.ok) throw json;
                        return json;
                    })
                    .then((json) => {
                        agregarFilaATabla(json.data);
                        limpiarFormulario();
                        mostrarAlerta(json.message, 'success');
                    })
                    .catch((error) => {
                        let mensaje = 'Ocurrió un error al guardar.';
                        if (error && error.errors) {
                            mensaje = Object.values(error.errors).flat().join('<br>');
                        } else if (error && error.message) {
                            mensaje = error.message;
                        }
                        mostrarAlerta(mensaje, 'danger');
                    })
                    .finally(() => {
                        btnGuardar.disabled = false;
                        btnGuardar.textContent = 'Guardar';
                    });
            });

            // Eliminar (delegación de eventos porque las filas se crean dinámicamente)
            tbodySeries.addEventListener('click', function(e) {
                const btn = e.target.closest('.btnEliminarSerie');
                if (!btn) return;

                if (!confirm('¿Eliminar esta serie?')) return;

                const id = btn.dataset.id;

                fetch('{{ url('series-sucursal') }}/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                    })
                    .then((response) => response.json())
                    .then((json) => {
                        document.getElementById('fila-serie-' + id).remove();
                        mostrarAlerta(json.message, 'success');
                    })
                    .catch(() => {
                        mostrarAlerta('No se pudo eliminar la serie.', 'danger');
                    });
            });

            // ---------- EDICIÓN INLINE ----------

            // Mismas opciones que el formulario de arriba, para reconstruir los <select> en modo edición
            const opcionesSucursales = @json($sucursales->map(fn($s) => ['id' => $s->id, 'nombre' => $s->nombre]));
            const opcionesTipos = @json($tiposDocumento->map(fn($t) => ['id' => $t->id, 'nombre' => $t->nombre]));

            function construirSelect(id, opciones, seleccionadoId) {
                let html = `<select id="${id}" class="form-select form-control form-select-sm">`;
                html += `<option value="">-- Seleccione --</option>`;
                opciones.forEach((op) => {
                    const selected = String(op.id) === String(seleccionadoId) ? 'selected' : '';
                    html += `<option value="${op.id}" ${selected}>${op.nombre}</option>`;
                });
                html += `</select>`;
                return html;
            }

            function entrarModoEdicion(fila) {
                const id = fila.dataset.id;
                const sucursalId = fila.dataset.sucursalId;
                const tipoId = fila.dataset.tipoId;
                const serie = fila.dataset.serie;

                fila.querySelector('.celda-sucursal').innerHTML =
                    construirSelect('edit_sucursal_' + id, opcionesSucursales, sucursalId);

                fila.querySelector('.celda-tipo').innerHTML =
                    construirSelect('edit_tipo_' + id, opcionesTipos, tipoId);

                fila.querySelector('.celda-serie').innerHTML =
                    `<input type="text" id="edit_serie_${id}" class="form-control form-control-sm" value="${serie}" maxlength="10">`;

                fila.querySelector('.celda-acciones').innerHTML = `
            <button type="button" class="btn btn-sm btn-success btnGuardarEdicion" data-id="${id}">Guardar</button>
            <button type="button" class="btn btn-sm btn-secondary btnCancelarEdicion" data-id="${id}">Cancelar</button>
        `;
            }

            function salirModoEdicion(fila, data) {
                fila.dataset.sucursalId = data.sucursal_id ?? '';
                fila.dataset.tipoId = data.tipo_documento_factura_id ?? '';
                fila.dataset.serie = data.serie;

                fila.querySelector('.celda-sucursal').textContent = data.sucursal;
                fila.querySelector('.celda-tipo').textContent = data.tipo_documento;
                fila.querySelector('.celda-serie').textContent = data.serie;

                fila.querySelector('.celda-acciones').innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-primary btnEditarSerie" data-id="${data.id}">Editar</button>
            <button type="button" class="btn btn-sm btn-outline-danger btnEliminarSerie" data-id="${data.id}">Eliminar</button>
        `;
            }

            tbodySeries.addEventListener('click', function(e) {

                // Entrar en modo edición
                const btnEditar = e.target.closest('.btnEditarSerie');
                if (btnEditar) {
                    const fila = document.getElementById('fila-serie-' + btnEditar.dataset.id);
                    entrarModoEdicion(fila);
                    return;
                }

                // Cancelar edición (vuelve a los valores originales sin llamar al servidor)
                const btnCancelar = e.target.closest('.btnCancelarEdicion');
                if (btnCancelar) {
                    const fila = document.getElementById('fila-serie-' + btnCancelar.dataset.id);
                    salirModoEdicion(fila, {
                        id: fila.dataset.id,
                        sucursal_id: fila.dataset.sucursalId,
                        tipo_documento_factura_id: fila.dataset.tipoId,
                        sucursal: opcionesSucursales.find(o => String(o.id) === fila.dataset
                            .sucursalId)?.nombre ?? '-',
                        tipo_documento: opcionesTipos.find(o => String(o.id) === fila.dataset
                            .tipoId)?.nombre ?? '-',
                        serie: fila.dataset.serie,
                    });
                    return;
                }

                // Guardar edición (PUT al servidor)
                const btnGuardarEdicion = e.target.closest('.btnGuardarEdicion');
                if (btnGuardarEdicion) {
                    const id = btnGuardarEdicion.dataset.id;
                    const fila = document.getElementById('fila-serie-' + id);

                    const sucursal_id = document.getElementById('edit_sucursal_' + id).value;
                    const tipo_documento_factura_id = document.getElementById('edit_tipo_' + id).value;
                    const serie = document.getElementById('edit_serie_' + id).value.trim();

                    if (!sucursal_id || !tipo_documento_factura_id || !serie) {
                        mostrarAlerta('Completa sucursal, tipo de documento y serie.', 'warning');
                        return;
                    }

                    btnGuardarEdicion.disabled = true;
                    btnGuardarEdicion.textContent = 'Guardando...';

                    fetch('{{ url('series-sucursal') }}/' + id, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                sucursal_id,
                                tipo_documento_factura_id,
                                serie,
                            }),
                        })
                        .then(async (response) => {
                            const json = await response.json();
                            if (!response.ok) throw json;
                            return json;
                        })
                        .then((json) => {
                            salirModoEdicion(fila, json.data);
                            mostrarAlerta(json.message, 'success');
                        })
                        .catch((error) => {
                            let mensaje = 'Ocurrió un error al actualizar.';
                            if (error && error.errors) {
                                mensaje = Object.values(error.errors).flat().join('<br>');
                            } else if (error && error.message) {
                                mensaje = error.message;
                            }
                            mostrarAlerta(mensaje, 'danger');
                            btnGuardarEdicion.disabled = false;
                            btnGuardarEdicion.textContent = 'Guardar';
                        });
                }
            });

        });
    </script>
@endpush
