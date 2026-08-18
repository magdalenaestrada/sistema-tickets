@extends('layouts.app')

@section('title', 'Comprobantes')

@section('content')

    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>
                <h4 class="mb-0">Comprobantes Electrónicos</h4>
                <small class="text-muted">Gestión de boletas, facturas y notas SUNAT</small>
            </div>

            <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalComprobante">
                + Nueva venta
            </button>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Total ventas</h6>
                        <h4>{{ $ventas->total() }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Emitidas</h6>
                        <h4>{{ $emitidas }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Pendientes</h6>
                        <h4>{{ $pendientes }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Rechazadas</h6>
                        <h4>{{ $rechazadas }}</h4>
                    </div>
                </div>
            </div>

        </div>

        {{-- TABLE --}}
        <div class="card shadow-sm">

            <div class="card-body p-0">
                <div class="card shadow-sm mb-3">



                </div>

                <div class="card-body">

                    <form method="GET">

                        <div class="row g-2">

                            <div class="col-md-3">

                                <label class="form-label">
                                    N° Documento
                                </label>

                                <input type="text" class="form-control" name="documento"
                                    value="{{ request('documento') }}">

                            </div>


                            <div class="col-md-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="fecha" value="{{ request('fecha') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Tipo</label>

                                <select class="form-select" name="tipo_documento_factura_id">

                                    <option value="">Todos</option>

                                    @foreach ($tiposDocumento as $tipo)
                                        <option value="{{ $tipo->id }}" @selected(request('tipo_documento_factura_id') == $tipo->id)>
                                            {{ $tipo->descripcion }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-2">

                                <label class="form-label">Estado</label>

                                <select class="form-select" name="estado">

                                    <option value="">Todos</option>

                                    @foreach (\App\Enums\EstadoVenta::cases() as $estado)
                                        <option value="{{ $estado->value }}" @selected(request('estado') == $estado->value)>

                                            {{ $estado->value }}

                                        </option>
                                    @endforeach

                                </select>

                            </div>


                            <div class="col-md-1 d-grid">

                                <label class="form-label">&nbsp;</label>

                                <button class="btn btn-primary">
                                    Buscar
                                </button>

                            </div>
                            <div class="col-md-1 d-grid">

                                <label class="form-label">&nbsp;</label>

                                <a href="{{ route('facturacion.index') }}" class="btn btn-outline-secondary">

                                    Limpiar

                                </a>

                            </div>
                        </div>

                    </form>

                </div>

                <div class="table-responsive  p-4">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>
                                <th>#</th>
                                <th>Documento</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($ventas as $venta)
                                <tr>

                                    <td class="text-muted">
                                        {{ $ventas->total() - (($ventas->currentPage() - 1) * $ventas->perPage() + $loop->index) }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $venta->serie }}-{{ $venta->numero }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $venta->persona?->nombre_facturacion ?? 'CLIENTE VARIOS' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $venta->tipoDocumentoFactura?->descripcion }}
                                        </span>
                                    </td>

                                    <td>
                                        <strong>
                                            S/ {{ number_format($venta->total, 2) }}
                                        </strong>
                                    </td>

                                    <td>

                                        @switch($venta->estado)
                                            @case(\App\Enums\EstadoVenta::EMITIDO)
                                                <span class="badge bg-success">EMITIDA
                                                    {{ $venta->documento_referencia ? 'A REF: ' . $venta->documento_referencia : '' }}</span>
                                            @break

                                            @case(\App\Enums\EstadoVenta::RECHAZADO)
                                                <span class="badge bg-danger">RECHAZADA</span>
                                            @break

                                            @case(\App\Enums\EstadoVenta::ANULADO)
                                                <span class="badge bg-danger">
                                                    ANULADO
                                                </span>
                                            @break

                                            @case(\App\Enums\EstadoVenta::ANULADO_CON_NOTA_CREDITO)
                                                <span class="badge bg-danger">
                                                    ANULADO CON NOTA DE CREDITO
                                                </span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">
                                                    {{ $venta->estado->value }}
                                                </span>
                                        @endswitch

                                    </td>

                                    <td class="text-muted">
                                        {{ optional($venta->fecha_emision)->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="text-end align-middle">
                                        <div class="d-flex justify-content-end align-items-center flex-wrap gap-1">

                                            @if (!($esPdf ?? false))
                                                <a href="{{ route('ventas.ticket.pdf', $venta->id) }}"
                                                    class="btn btn-xs btn-warning" title="Ticket">
                                                    <i data-lucide="receipt-text"></i>

                                                </a>
                                            @endif

                                            <a href="{{ route('facturacion.show', $venta) }}"
                                                class="btn btn-xs btn-primary">
                                                <i data-lucide="eye"></i>

                                            </a>

                                            @if ($venta->ruta_xml)
                                                <a href="{{ route('facturacion.xml', $venta) }}"
                                                    class="btn btn-xs btn-info text-white">
                                                    <i data-lucide="file-code-2"></i>
                                                    XML
                                                </a>
                                            @endif

                                            @if ($venta->ruta_cdr)
                                                <a href="{{ route('facturacion.cdr', $venta) }}"
                                                    class="btn btn-xs btn-success">
                                                    <i data-lucide="badge-check"></i>
                                                    CDR
                                                </a>
                                            @endif

                                            @hasrole('Administrador')
                                                @if ($venta->estado === \App\Enums\EstadoVenta::EMITIDO)
                                                    @if ($venta->tipo_documento_factura_id == 3)
                                                        <button type="button" class="btn btn-xs btn-outline-danger"
                                                            onclick="anularNotaVenta({{ $venta->id }}, {{ $venta->total }})">
                                                            <i data-lucide="trash-2"></i>
                                                            Anular
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-xs btn-outline-danger"
                                                            onclick="anularVenta({{ $venta->id }}, '{{ route('facturacion.anular', $venta) }}')">
                                                            <i data-lucide="trash-2"></i>
                                                            Anular
                                                        </button>
                                                    @endif
                                                @endif
                                            @endhasrole

                                        </div>
                                    </td>

                                </tr>

                                @empty

                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            No hay comprobantes registrados
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    {{ $ventas->links() }}
                </div>

            </div>

        </div>

        @include('facturacion.modals.crear')
        @include('facturacion.modals.anular_nota_venta')
        @include('facturacion.modals.solicitud_anulacion')
        @include('facturacion.modals.seleccionar')
        @include('facturacion.modals.existente')

    @endsection
    @push('scripts')
        <script>
            let xd = 2;
            let items = [];
            const urlAnular = "{{ route('facturacion.anular', ':id') }}";
            const urlAnularNotaVenta = "{{ route('facturacion.anular.nota', ':id') }}";
            const IGV_VIAJE = {{ $empresa->igv ?? 0 }};
            const IGV_ENCOMIENDA = {{ $empresa->igv_encomienda ?? 0 }};

            let comprobanteSeleccionado = null;

            window.buscarComprobanteReferencia = async function() {
                const q = document.getElementById('buscar_comprobante_input').value.trim();
                const cont = document.getElementById('resultado_busqueda_comprobante');

                if (!q) {
                    cont.innerHTML =
                        '<div class="text-danger small">Ingrese un criterio de búsqueda.</div>';
                    return;
                }

                cont.innerHTML = '<div class="text-muted small">Buscando...</div>';

                try {

                    const url = route('facturacion.buscar-comprobante', {
                        q: q
                    });

                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!res.ok) {
                        throw new Error(`Error HTTP ${res.status}`);
                    }

                    const json = await res.json();

                    if (!json.success || !json.data?.length) {
                        cont.innerHTML =
                            `<div class="text-muted small">${json.message ?? 'No se encontraron resultados.'}</div>`;

                        resetSeleccion();
                        return;
                    }

                    cont.innerHTML = json.data.map(c => `
            <div
                class="bg-light bg-opacity-10 border border-primary-subtle rounded-3 p-3
                       d-flex align-items-center justify-content-between mb-2 comprobante-item"
                style="cursor:pointer"
                data-id="${c.id}"
            >

                <div class="d-flex align-items-center gap-3">

                    <span class="badge bg-light bg-opacity-20 text-primary px-2 py-1 rounded">
                        ${c.tipo}
                    </span>

                    <div>
                        <strong class="d-block text-dark small">
                            ${c.cliente}
                        </strong>

                        <span class="text-muted d-block" style="font-size:.75rem;">
                            Doc: ${c.documento}
                        </span>
                    </div>

                </div>

                <div class="text-center">
                    <span class="text-muted d-block" style="font-size:.7rem;">
                        Fecha emisión
                    </span>

                    <span class="fw-semibold text-dark small">
                        ${c.fecha_emision}
                    </span>
                </div>

                <div class="text-end">
                    <span class="text-muted d-block" style="font-size:.7rem;">
                        Total
                    </span>

                    <strong class="text-dark fs-6">
                        S/ ${c.total}
                    </strong>
                </div>

                <div class="small text-muted">
                    ${c.serie_numero}
                </div>

            </div>
        `).join('');

                    document.querySelectorAll('.comprobante-item').forEach(el => {
                        el.addEventListener('click', () => {
                            seleccionarComprobante(el.dataset.id, json.data);
                        });
                    });

                } catch (e) {

                    cont.innerHTML =
                        '<div class="text-danger small">Error al buscar el comprobante.</div>';

                    console.error(e);
                }
            }

            function seleccionarComprobante(id, lista) {
                comprobanteSeleccionado = lista.find(c => c.id == id);
                if (!comprobanteSeleccionado) return;

                document.getElementById('referencia_venta_id').value = comprobanteSeleccionado.id;
                document.getElementById('texto_documento_referencia').textContent =
                    `${comprobanteSeleccionado.tipo} ${comprobanteSeleccionado.serie_numero}`;
                document.getElementById('total_a_emitir').textContent = `S/ ${comprobanteSeleccionado.total}`;

                document.querySelectorAll('.comprobante-item').forEach(el =>
                    el.classList.toggle('border-primary', el.dataset.id == id)
                );

                // Consulta al backend si esta conversión requerirá NC (ver endpoint sugerido abajo)
                consultarImpactoConversion();

                document.getElementById('btnContinuarComprobanteExistente').disabled = false;
            }

            function resetSeleccion() {
                comprobanteSeleccionado = null;
                document.getElementById('referencia_venta_id').value = '';
                document.getElementById('texto_documento_referencia').textContent = 'Ninguno seleccionado';
                document.getElementById('total_a_emitir').textContent = 'S/ 0.00';
                document.getElementById('aviso_anulacion_origen').style.display = 'none';
                document.getElementById('btnContinuarComprobanteExistente').disabled = true;
            }

            async function consultarImpactoConversion() {
                const aviso = document.getElementById('aviso_anulacion_origen');
                const tipoDestino = document.getElementById('tipo_comprobante_destino').value;

                if (!comprobanteSeleccionado) return;

                try {
                    const res = await fetch(
                        `/facturacion/preview-conversion?venta_id=${comprobanteSeleccionado.id}&tipo_destino=${tipoDestino}`
                    );
                    const json = await res.json();

                    if (json.requiere_nc) {
                        aviso.className = 'rounded-3 p-2 px-3 mb-2 bg-warning bg-opacity-25 text-dark';
                        aviso.innerHTML =
                            '<i class="bi bi-exclamation-triangle me-1"></i> Este comprobante ya está fuera del plazo de anulación directa. Se generará automáticamente una <strong>Nota de Crédito</strong> antes de emitir el nuevo comprobante.';
                    } else {
                        aviso.className = 'rounded-3 p-2 px-3 mb-2 bg-info bg-opacity-25 text-dark';
                        aviso.innerHTML =
                            '<i class="bi bi-info-circle me-1"></i> El comprobante de origen será anulado directamente (dentro del plazo permitido).';
                    }
                    aviso.style.display = 'flex';
                } catch (e) {
                    aviso.style.display = 'none';
                    console.error(e);
                }
            }

            function actualizarSerieDestino() {
                // Opcional: si tienes un endpoint que devuelva la próxima serie/número disponible
                // por sucursal + tipo, actualiza serie_destino/numero_destino aquí.
                // Si no, deja "—" y que el backend asigne la serie real al confirmar.
                if (comprobanteSeleccionado) consultarImpactoConversion();
            }

            async function continuarConversion() {
                if (!comprobanteSeleccionado) {
                    alert('Seleccione un comprobante de referencia.');
                    return;
                }

                const payload = {
                    venta_referencia_id: comprobanteSeleccionado.id,
                    tipo_documento_factura_id: document.getElementById('tipo_comprobante_destino').value,
                    fecha_emision: document.getElementById('fecha_emision_destino').value,
                };

                try {
                    const res = await fetch('/facturacion/convertir-comprobante', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });
                    const json = await res.json();

                    if (!json.success) {
                        alert(json.message ?? 'No se pudo generar el comprobante.');
                        return;
                    }

                    let mensaje = 'Comprobante generado correctamente.';
                    if (json.data?.nota_credito) {
                        mensaje += ` Se emitió la Nota de Crédito ${json.data.nota_credito} para anular el origen.`;
                    }
                    alert(mensaje);
                    location.reload();
                } catch (e) {
                    alert('Ocurrió un error al generar el comprobante.');
                    console.error(e);
                }
            }


            function buscarCliente() {
                let documento = $("#doc_cliente").val().trim();
                $("#btnBuscarCliente").prop("disabled", true);
                $.getJSON(route("buscar.buscar") + "?documento=" + documento)

                    .done(function(data) {

                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        // RUC
                        if (data.razon_social) {

                            $("#nombres").val(data.razon_social);
                            $("#apellidos").val("");

                            $("#direccion").val(data.direccion || "-");

                        }
                        // DNI
                        else {

                            $("#nombres").val(data.nombres || "");

                            $("#apellidos").val(
                                ((data.apellido_paterno || "") + " " +
                                    (data.apellido_materno || "")).trim()
                            );

                            $("#direccion").val("-");
                        }

                        actualizarCamposCliente();

                    })

                    .fail(function() {

                        alert("No se encontró el documento.");

                    })

                    .always(function() {

                        $("#btnBuscarCliente").prop("disabled", false);

                    });

            }

            function obtenerIGV() {

                const servicio = parseInt($("#tipo_servicio_id").val());

                if (servicio === 1) {
                    return IGV_VIAJE / 100;
                }

                return IGV_ENCOMIENDA / 100;
            }

            function anularNotaVenta(id, total, caja_anulacion_id) {
                $("#venta_id_anular").val(id);
                $("#modal_total_devolver").text(parseFloat(total).toFixed(2));

                if ($("#modal_caja_anulacion option[value='" + caja_anulacion_id + "']").length) {
                    $("#modal_caja_anulacion").val(caja_anulacion_id);
                }

                limpiarDevolucion();
                distribuirDevolucionPorMetodo();

                const modal = bootstrap.Modal.getOrCreateInstance(
                    document.getElementById("modalAnulacion")
                );
                modal.show();
            }

            function limpiarDevolucion() {

                $("#devolucion_efectivo").val("0.00");
                $("#devolucion_tarjeta").val("0.00");
                $("#devolucion_yape").val("0.00");
                $("#devolucion_plin").val("0.00");
                $("#devolucion_transferencia").val("0.00");

                $("#alerta_devolucion").addClass("d-none");
            }

            function obtenerSerie() {
                const series = $("#caja_id option:selected").data("series");
                if (!series) return "";
                const tipoDocumentoId = $("#tipo_documento_modal").val();
                return series[tipoDocumentoId] || "";
            }

            function actualizarSerie() {
                $("#serie").val(obtenerSerie());
            }

            $("#caja_id").on("change", actualizarSerie);
            $("#tipo_documento_modal").on("change", actualizarSerie);
            $(document).ready(actualizarSerie);

            function actualizarCamposCliente() {

                const documento = $("#doc_cliente").val().trim();

                if (documento.length === 11) {

                    $("#lblNombre").text("Razón Social");
                    $("#divApellidos").hide();
                    $("#apellidos").val("");

                } else {

                    $("#lblNombre").text("Nombres");
                    $("#divApellidos").show();

                }
            }

            $("#doc_cliente").on("keyup change", actualizarCamposCliente);

            $(document).ready(function() {
                actualizarCamposCliente();
            });

            function anularVenta(id, url) {

                Swal.fire({
                    title: 'Anular documento',
                    text: 'Si esta dentro de la fecha para anulacion, se procedera con la anulacion correspondiente. De no ser el caso por excedente de limite de fecha, se procedera a realizar una nota de crédito en casos de Boleta/factura.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Procesando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                        })
                        .then(res => res.json())
                        .then(data => {

                            Swal.close();

                            if (data.success) {
                                Swal.fire('OK', data.message, 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }

                        })
                        .catch(() => {
                            Swal.fire('Error', 'Error de servidor', 'error');
                        });

                });
            }

            function solicitarAnulacion(ventaId) {
                $("#venta_solicitud").val(ventaId);
                $("#motivo_solicitud").val("");

                const modal = bootstrap.Modal.getOrCreateInstance(
                    document.getElementById("modalSolicitudAnulacion")
                );

                modal.show();
            }

            $("#btnEnviarSolicitud").click(function() {
                $.ajax({
                    url: route('solicitudes.anulacion'),
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        venta_id: $("#venta_solicitud").val(),
                        motivo: $("#motivo_solicitud").val()
                    },
                    success: function(resp) {

                        Swal.fire(
                            'Correcto',
                            'La solicitud fue enviada al administrador.',
                            'success'
                        ).then(() => location.reload());

                    },
                    error: function() {

                        Swal.fire(
                            'Error',
                            'No se pudo enviar la solicitud.',
                            'error'
                        );

                    }
                });

            });

            function distribuirDevolucionPorMetodo() {
                const metodo = parseInt($("#modal_metodo_devolucion").val()) || 1;

                const efectivo = $("#devolucion_efectivo");
                const tarjeta = $("#devolucion_tarjeta");
                const yape = $("#devolucion_yape");
                const plin = $("#devolucion_plin");
                const transferencia = $("#devolucion_transferencia");

                const div_efectivo = $("#devolucion_efectivo_div");
                const div_tarjeta = $("#devolucion_tarjeta_div");
                const div_yape = $("#devolucion_yape_div");
                const div_plin = $("#devolucion_plin_div");
                const div_transferencia = $("#devolucion_transferencia_div");

                [
                    efectivo,
                    tarjeta,
                    yape,
                    plin,
                    transferencia
                ].forEach(input => {
                    input.val("0.00");
                    input.prop("disabled", true);
                });


                // Mostrar todos primero
                [
                    div_efectivo,
                    div_tarjeta,
                    div_yape,
                    div_plin,
                    div_transferencia
                ].forEach(div => {
                    div.prop("hidden", false);
                });


                const total = parseFloat($("#modal_total_devolver").text()) || 0;


                switch (metodo) {

                    case 1:

                        efectivo
                            .prop("disabled", false)
                            .prop("readonly", true)
                            .val(total.toFixed(2));


                        div_tarjeta.prop("hidden", true);
                        div_yape.prop("hidden", true);
                        div_plin.prop("hidden", true);
                        div_transferencia.prop("hidden", true);

                        break;

                    case 2:

                        yape.prop("disabled", false);
                        plin.prop("disabled", false);
                        tarjeta.prop("disabled", false);
                        transferencia.prop("disabled", false);

                        yape.val(total.toFixed(2));


                        div_efectivo.prop("hidden", true);

                        break;

                    case 3:

                        efectivo.prop("disabled", false);
                        tarjeta.prop("disabled", false);
                        yape.prop("disabled", false);
                        plin.prop("disabled", false);
                        transferencia.prop("disabled", false);

                        efectivo.val(total.toFixed(2));

                        break;
                }

                aplicarLimiteDevolucion(metodo);
            }

            $("#modal_metodo_devolucion").on("change", function() {
                distribuirDevolucionPorMetodo();
            });


            function aplicarLimiteDevolucion(metodo) {
                if (metodo === 1) return;

                const total = parseFloat($("#modal_total_devolver").text()) || 0;
                const campos = camposDevolucionHabilitados();

                if (campos.length <= 1) return;

                campos.off("input.limite").on("input.limite", function() {
                    ajustarMontosDevolucion(campos, total, $(this));
                });
            }

            function ajustarMontosDevolucion(campos, total, actual) {
                let sumaOtros = 0;

                campos.each(function() {
                    if (this !== actual[0]) {
                        sumaOtros += parseFloat($(this).val()) || 0;
                    }
                });

                const restanteParaEste = Math.max(total - sumaOtros, 0);
                let valorActual = parseFloat(actual.val()) || 0;

                if (valorActual > restanteParaEste) {
                    valorActual = restanteParaEste;
                    actual.val(valorActual.toFixed(2));
                }

                campos.each(function() {
                    if (this !== actual[0]) {
                        const otroValor = parseFloat($(this).val()) || 0;
                        const sumaSinEste = (sumaOtros - otroValor) + valorActual;
                        const max = Math.max(total - sumaSinEste, 0);
                        $(this).attr("max", max.toFixed(2));
                    }
                });

                actual.attr("max", restanteParaEste.toFixed(2));
            }

            function camposDevolucionHabilitados() {
                return $(
                        "#devolucion_efectivo, #devolucion_tarjeta, #devolucion_yape, #devolucion_plin, #devolucion_transferencia"
                    )
                    .filter(function() {
                        return !$(this).prop("disabled");
                    });
            }

            let procesandoAnulacion = false;

            $("#btnConfirmarAnulacion").on("click", function() {
                if (procesandoAnulacion) return;

                const total = parseFloat($("#modal_total_devolver").text()) || 0;
                const ventaId = $("#venta_id_anular").val();
                const motivo = $("#motivo_anulacion").val().trim();
                const caja_anulacion_id = $("#caja_anulacion_id").val().trim();

                if (!motivo) {
                    Swal.fire("Error", "Debe ingresar un motivo.", "error");
                    return;
                }

                const metodos = [{
                        id: "devolucion_efectivo",
                        metodo_pago_id: 1,
                        billetera_id: null
                    },
                    {
                        id: "devolucion_tarjeta",
                        metodo_pago_id: 2,
                        billetera_id: null
                    },
                    {
                        id: "devolucion_yape",
                        metodo_pago_id: 3,
                        billetera_id: 1
                    },
                    {
                        id: "devolucion_plin",
                        metodo_pago_id: 3,
                        billetera_id: 2
                    },
                    {
                        id: "devolucion_transferencia",
                        metodo_pago_id: 4,
                        billetera_id: null
                    },
                ];

                const devoluciones = metodos
                    .map(m => ({
                        metodo_pago_id: m.metodo_pago_id,
                        billetera_id: m.billetera_id,
                        total: parseFloat($("#" + m.id).val()) || 0
                    }))
                    .filter(d => d.total > 0);

                const suma = devoluciones.reduce((acc, d) => acc + d.total, 0);

                if (Math.abs(suma - total) > 0.01) {
                    $("#alerta_devolucion").removeClass("d-none");
                    return;
                }
                $("#alerta_devolucion").addClass("d-none");

                // 🔒 bloqueo activado
                procesandoAnulacion = true;

                const $btn = $(this);
                const textoOriginal = $btn.html();

                $btn.prop("disabled", true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...'
                );
                $("#btnCancelarAnulacion, [data-bs-dismiss='modal']").prop("disabled", true);

                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(urlAnularNotaVenta.replace(":id", ventaId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            motivo,
                            devoluciones,
                            caja_anulacion_id
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.close();

                        if (data.success) {
                            Swal.fire('OK', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                            restaurarBoton();
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Error de servidor', 'error');
                        restaurarBoton();
                    });

                function restaurarBoton() {
                    procesandoAnulacion = false;
                    $btn.prop("disabled", false).html(textoOriginal);
                    $("[data-bs-dismiss='modal']").prop("disabled", false);
                }
            });

            function agregarItem() {
                let descripcion = $("#descripcion").val().trim();
                const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.-]+$/;

                if (!regex.test(descripcion)) {
                    Swal.fire("Error", "La descripción solo puede contener letras, números y espacios.", "error");
                    return;
                }


                let precio = parseFloat($("#precio").val());
                let unidad = parseFloat($("#unidad").val());
                let tipoServicioId = parseInt($("#tipo_servicio_id").val());

                if (!descripcion || isNaN(precio) || isNaN(unidad) || precio <= 0 || unidad <= 0) {
                    Swal.fire("Error", "Completa unidades y precio correctamente.", "error");
                    return;
                }

                const subtotal = unidad * precio;
                const porcentajeIgv = tipoServicioId === 1 ? IGV_VIAJE / 100 : IGV_ENCOMIENDA / 100;

                items.push({
                    tipo_servicio_id: tipoServicioId,
                    descripcion,
                    cantidad: unidad,
                    precio, // precio unitario CON IGV
                    igv: porcentajeIgv,
                    subtotal // unidad * precio, con IGV incluido
                });

                $("#descripcion").val("");
                $("#precio").val("");
                $("#unidad").val("");

                render();
            }

            function eliminarItem(i) {
                items.splice(i, 1);
                render();
            }

            function render() {
                let tbody = $("#tablaItems");
                tbody.empty();

                let totalGeneral = 0;
                let baseGeneral = 0;
                let igvGeneral = 0;

                items.forEach((item, i) => {
                    totalGeneral += item.subtotal;

                    const baseItem = item.subtotal / (1 + item.igv); // valor sin IGV
                    const igvItem = item.subtotal - baseItem; // monto de IGV

                    baseGeneral += baseItem;
                    igvGeneral += igvItem;

                    const NOMBRES_SERVICIO = {
                        1: "Pasaje",
                        2: "Encomienda",
                        3: "Sobreequipaje"
                    };

                    const fila = `
    <tr>
        <td class="text-center">${NOMBRES_SERVICIO[item.tipo_servicio_id] || '-'}</td>
        <td>${item.descripcion}</td>
        <td class="text-center">${item.cantidad}</td>
        <td class="text-end">${item.precio.toFixed(2)}</td>
        <td class="text-end">${baseItem.toFixed(2)}</td>
        <td class="text-end"><small>S/ ${igvItem.toFixed(2)}</small></td>
        <td class="text-end">${item.subtotal.toFixed(2)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-xs" onclick="eliminarItem(${i})">X</button>
        </td>
    </tr>
`;

                    tbody.append(fila);
                });

                $("#subtotal").text(baseGeneral.toFixed(2));
                $("#igv").text(igvGeneral.toFixed(2));
                $("#total").text(totalGeneral.toFixed(2));

                $("#itemsInput").val(JSON.stringify(items));
            }

            function ocultarPasosComprobante() {
                $("#pasoOpciones").addClass("d-none");
                $("#pasoNuevo").addClass("d-none");
                $("#pasoExistente").addClass("d-none");
            }

            function mostrarNuevoComprobante() {
                ocultarPasosComprobante();

                $("#pasoNuevo").removeClass("d-none");
                $("#btnVolverModal").removeClass("d-none");

                $("#modalComprobanteLabel").text("Generar comprobante");
                $("#subtituloModalComprobante").text(
                    "Completa los datos del cliente y del servicio."
                );

                actualizarSerie();
            }

            function mostrarComprobanteExistente() {
                ocultarPasosComprobante();

                $("#pasoExistente").removeClass("d-none");
                $("#btnVolverModal").removeClass("d-none");

                $("#modalComprobanteLabel").text("Generar desde comprobante existente");
                $("#subtituloModalComprobante").text(
                    "Selecciona un comprobante de referencia."
                );
            }

            function volverOpciones() {
                ocultarPasosComprobante();

                $("#pasoOpciones").removeClass("d-none");
                $("#btnVolverModal").addClass("d-none");

                $("#modalComprobanteLabel").text("Nuevo comprobante");
                $("#subtituloModalComprobante").text(
                    "Selecciona cómo deseas generar el comprobante."
                );
            }

            document.addEventListener("DOMContentLoaded", function() {
                const modalComprobante = document.getElementById("modalComprobante");

                if (!modalComprobante) return;

                modalComprobante.addEventListener("hidden.bs.modal", function() {
                    resetModalComprobante();
                });
            });

            function resetModalComprobante() {
                ocultarPasosComprobante();

                $("#pasoOpciones").removeClass("d-none");
                $("#btnVolverModal").addClass("d-none");

                $("#modalComprobanteLabel").text("Nuevo comprobante");
                $("#subtituloModalComprobante").text(
                    "Selecciona cómo deseas generar el comprobante."
                );

                const formVenta = document.getElementById("formVentaRapida");

                if (formVenta) {
                    formVenta.reset();
                }

                items = [];

                if (typeof render === "function") {
                    render();
                }

                $("#doc_cliente").val("");
                $("#nombres").val("");
                $("#apellidos").val("");
                $("#direccion").val("");

                $("#descripcion").val("");
                $("#unidad").val("");
                $("#precio").val("");

                $("#serie").val("");

                actualizarCamposCliente();

                $("#buscar_comprobante_input").val("");
                $("#resultado_busqueda_comprobante").empty();

                $("#referencia_venta_id").val("");

                $("#texto_documento_referencia").text(
                    "Ninguno seleccionado"
                );

                $("#total_a_emitir").text("S/ 0.00");

                $("#aviso_anulacion_origen")
                    .hide()
                    .empty();

                $("#btnContinuarComprobanteExistente").prop(
                    "disabled",
                    true
                );

                comprobanteSeleccionado = null;
            }
        </script>
    @endpush
