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

            <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalVentaRapida">

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

                <div class="table-responsive">

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
                                                    class="btn btn-sm btn-warning" title="Ticket">
                                                    <i data-lucide="receipt-text" class="me-1"></i>

                                                </a>
                                            @endif

                                            <a href="{{ route('facturacion.show', $venta) }}"
                                                class="btn btn-sm btn-primary">
                                                <i data-lucide="eye" class="me-1"></i>

                                            </a>

                                            @if ($venta->ruta_xml)
                                                <a href="{{ route('facturacion.xml', $venta) }}"
                                                    class="btn btn-sm btn-info text-white">
                                                    <i data-lucide="file-code-2" class="me-1"></i>
                                                    XML
                                                </a>
                                            @endif

                                            @if ($venta->ruta_cdr)
                                                <a href="{{ route('facturacion.cdr', $venta) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i data-lucide="badge-check" class="me-1"></i>
                                                    CDR
                                                </a>
                                            @endif

                                            @hasanyrole('Administrador')
                                                @if ($venta->estado === \App\Enums\EstadoVenta::EMITIDO)
                                                    @if ($venta->tipo_documento_factura_id == 3)
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="anularNotaVenta({{ $venta->id }})">
                                                            Anular
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="anularVenta({{ $venta->id }}, '{{ route('facturacion.anular', $venta) }}')">
                                                            Anular
                                                        </button>
                                                    @endif
                                                @endif
                                            @endhasanyrole

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

    @endsection
    @push('scripts')
        <script>
            let xd = 2;
            let items = [];
            const urlAnular = "{{ route('facturacion.anular', ':id') }}";
            const IGV_ENTERO = "{{ $empresa->igv ?? 0 }}";
            const IGV = IGV_ENTERO / 100;

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


            function anularNotaVenta(id) {

                $("#venta_id_anular").val(id);

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

                const descripcion = $("#sucursal_id option:selected").data("series");

                if (!descripcion) return "";

                const [boleta, factura, nota] =
                descripcion.split("/").map(s => s.trim());

                const tipo = $('select[name="tipo_documento_factura_id"] option:selected')
                    .text()
                    .toLowerCase();

                if (tipo.includes("boleta")) return boleta;
                if (tipo.includes("factura")) return factura;

                return nota;
            }

            function actualizarSerie() {
                $("#serie").val(obtenerSerie());
            }

            $("#sucursal_id").on("change", actualizarSerie);

            $('select[name="tipo_documento_factura_id"]').on("change", actualizarSerie);

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


                // Reiniciar valores
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

                    // EFECTIVO
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


                        // DIGITAL
                    case 2:

                        yape.prop("disabled", false);
                        plin.prop("disabled", false);
                        tarjeta.prop("disabled", false);
                        transferencia.prop("disabled", false);


                        // por defecto todo a yape
                        yape.val(total.toFixed(2));


                        div_efectivo.prop("hidden", true);

                        break;


                        // MIXTO
                    case 3:

                        efectivo.prop("disabled", false);
                        tarjeta.prop("disabled", false);
                        yape.prop("disabled", false);
                        plin.prop("disabled", false);
                        transferencia.prop("disabled", false);

                        efectivo.val(total.toFixed(2));

                        break;
                }
            }

            $("#modal_metodo_devolucion").on("change", function() {
                distribuirDevolucionPorMetodo();
            });

            function agregarItem() {

                let descripcion = $("#descripcion").val().trim();

                const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.-]+$/;

                if (!regex.test(descripcion)) {
                    Swal.fire(
                        "Error",
                        "La descripción solo puede contener letras, números y espacios.",
                        "error"
                    );
                    return;
                }
                let precio = parseFloat($("#precio").val());
                let unidad = parseFloat($("#unidad").val());
                let subtotal = unidad * precio;
                if (!descripcion || isNaN(precio) || isNaN(precio)) return;

                items.push({
                    descripcion,
                    precio,
                    unidad,
                    subtotal
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
                tbody.html("");

                let total = 0;

                items.forEach((item, i) => {

                    total += item.subtotal;

                    tbody.append(`
            <tr>
                <td>${item.descripcion}</td>
                <td>${item.unidad}</td>
                <td>${item.precio.toFixed(2)}</td>
                <td>${item.subtotal.toFixed(2)}</td>
                <td>
                    <button type="button"
                            class="btn btn-danger btn-sm"
                            onclick="eliminarItem(${i})">
                        X
                    </button>
                </td>
            </tr>
        `);
                });

                let base = total / (1 + IGV);
                let igv = total - base;

                $("#subtotal").text(base.toFixed(2));
                $("#igv").text(igv.toFixed(2));
                $("#total").text(total.toFixed(2));

                $("#itemsInput").val(JSON.stringify(items));
            }
        </script>
    @endpush
