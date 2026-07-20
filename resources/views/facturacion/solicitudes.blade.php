@extends('layouts.app')

@section('title', 'Solicitudes de Anulación')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>
                <h4>Solicitudes de Anulación</h4>
                <small class="text-muted">
                    Solicitudes enviadas por los vendedores
                </small>
            </div>

        </div>

        <div class="card shadow-sm">

            <div class="card-body">

                <form method="GET">

                    <div class="row">

                        <div class="col-md-3">

                            <label>Desde</label>

                            <input type="date" class="form-control" name="fecha_desde"
                                value="{{ request('fecha_desde') }}">

                        </div>

                        <div class="col-md-3">

                            <label>Hasta</label>

                            <input type="date" class="form-control" name="fecha_hasta"
                                value="{{ request('fecha_hasta') }}">

                        </div>

                        <div class="col-md-3">

                            <label>Estado</label>

                            <select class="form-select" name="estado">

                                <option value="">Todos</option>

                                <option value="Pendiente">Pendiente</option>

                                <option value="Aprobada">Aprobada</option>

                                <option value="Rechazada">Rechazada</option>

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label>Documento</label>

                            <input type="text" class="form-control" name="documento">

                        </div>

                    </div>

                    <div class="mt-3">

                        <button class="btn btn-primary">
                            Buscar
                        </button>

                        <a href="{{ route('facturacion.solicitudes') }}" class="btn btn-secondary">
                            Limpiar
                        </a>

                    </div>

                </form>

            </div>

        </div>

        <div class="card mt-3 shadow-sm">

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Documento</th>

                            <th>Cliente</th>

                            <th>Solicitante</th>

                            <th>Motivo</th>

                            <th>Estado</th>

                            <th>Fecha</th>

                            <th></th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($solicitudes as $solicitud)
                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>

                                    <strong>

                                        {{ $solicitud->venta->serie }}

                                        -

                                        {{ $solicitud->venta->numero }}

                                    </strong>

                                </td>

                                <td>

                                    {{ $solicitud->venta->persona?->nombre_facturacion }}

                                </td>

                                <td>

                                    {{ $solicitud->solicitante->persona->nombre_completo ?? '' }}

                                </td>

                                <td>

                                    {{ Str::limit($solicitud->motivo, 60) }}

                                </td>

                                <td>

                                    @if ($solicitud->estado == 'Pendiente')
                                        <span class="badge bg-warning">
                                            Pendiente
                                        </span>
                                    @elseif($solicitud->estado == 'Aprobada')
                                        <span class="badge bg-success">
                                            Aprobada
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Rechazada
                                        </span>
                                    @endif

                                </td>

                                <td>

                                    {{ $solicitud->fecha_solicitud->format('d/m/Y H:i') }}

                                </td>

                                <td>

                                    @if ($solicitud->estado == 'Pendiente')
                                        <a href="{{ route('facturacion.solicitudes.show', $solicitud) }}"
                                            class="btn btn-sm btn-primary">
                                            <i data-lucide="eye"></i>
                                            Revisar
                                        </a>

                                        <button class="btn btn-success"
                                            onclick="anularNotaVenta(
        {{ $solicitud->venta_id }},
        {{ $solicitud->venta->total }},
        @js($solicitud->motivo),
        {{ $solicitud->id }}
    )">
                                            Aprobar y anular
                                        </button>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center">

                                    No existen solicitudes.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="card-footer">

                {{ $solicitudes->links() }}

            </div>

        </div>
        @include('facturacion.modals.anular_nota_venta')
    </div>

@endsection
@push('scripts')
    <script>
        let esSolicitud = false;
        let solicitudId = null;

        function anularNotaVenta(id, total, motivo = null, solicitud = null) {

            $("#venta_id_anular").val(id);
            $("#modal_total_devolver").text(parseFloat(total).toFixed(2));

            limpiarDevolucion();
            distribuirDevolucionPorMetodo();

            if (motivo) {
                esSolicitud = true;
                solicitudId = solicitud;

                $("#motivo_anulacion")
                    .val(motivo)
                    .prop("readonly", true);

            } else {

                esSolicitud = false;
                solicitudId = null;

                $("#motivo_anulacion")
                    .val("")
                    .prop("readonly", false);
            }

            bootstrap.Modal
                .getOrCreateInstance(document.getElementById("modalAnulacion"))
                .show();
        }

        function limpiarDevolucion() {

            $("#devolucion_efectivo").val("0.00");
            $("#devolucion_tarjeta").val("0.00");
            $("#devolucion_yape").val("0.00");
            $("#devolucion_plin").val("0.00");
            $("#devolucion_transferencia").val("0.00");

            $("#alerta_devolucion").addClass("d-none");
        }

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
        const urlAprobarSolicitud =
            "{{ route('facturacion.solicitudes.aprobar', ':id') }}";

        const urlAnularNotaVenta =
            "{{ route('facturacion.anular.nota', ':id') }}";

        $("#btnConfirmarAnulacion").on("click", function() {

            if (procesandoAnulacion) return;

            const total = parseFloat($("#modal_total_devolver").text()) || 0;
            const ventaId = $("#venta_id_anular").val();
            const motivo = $("#motivo_anulacion").val().trim();

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

            const suma = devoluciones.reduce((a, b) => a + b.total, 0);

            if (Math.abs(suma - total) > 0.01) {
                $("#alerta_devolucion").removeClass("d-none");
                return;
            }

            procesandoAnulacion = true;

            let url = esSolicitud ?
                urlAprobarSolicitud.replace(":id", solicitudId) :
                urlAnularNotaVenta.replace(":id", ventaId);

            fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        motivo: motivo,
                        devoluciones: devoluciones
                    })
                })
                .then(r => r.json())
                .then(resp => {

                    procesandoAnulacion = false;

                    if (resp.success) {

                        Swal.fire(
                            "Correcto",
                            resp.message,
                            "success"
                        ).then(() => location.reload());

                    } else {

                        Swal.fire(
                            "Error",
                            resp.message,
                            "error"
                        );

                    }

                })
                .catch(() => {

                    procesandoAnulacion = false;

                    Swal.fire(
                        "Error",
                        "Ocurrió un error.",
                        "error"
                    );

                });

        });
    </script>
@endpush
