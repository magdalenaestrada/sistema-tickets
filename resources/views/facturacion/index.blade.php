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

        @include('facturacion.modals.anular_nota_venta')
        @include('facturacion.modals.solicitud_anulacion')
        @include('facturacion.modals.seleccionar')

    @endsection
    @push('scripts')
        <script>
            let pasoComprobanteActual = 1;

            let flujoComprobante = {
                modo: null,
                origen: null,
                tipoDestino: null,
                tipoDocumentoFacturaId: null,
                accion: null,
                cliente: null,
                motivoNotaCredito: null,
                items: []
            };

            let comprobanteSeleccionado = null;

            let procesandoComprobante = false;
            let procesandoConversion = false;

            let xd = 2;
            let items = [];
            const urlAnular = "{{ route('facturacion.anular', ':id') }}";
            const urlAnularNotaVenta = "{{ route('facturacion.anular.nota', ':id') }}";
            const IGV_VIAJE = {{ $empresa->igv ?? 0 }};
            const IGV_ENCOMIENDA = {{ $empresa->igv_encomienda ?? 0 }};

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

                            const comprobante = json.data.find(
                                c => String(c.id) === String(el.dataset.id)
                            );

                            if (!comprobante) {
                                console.error('No se encontró el comprobante:', el.dataset.id);
                                return;
                            }

                            seleccionarComprobante(comprobante);
                        });
                    });

                } catch (e) {

                    cont.innerHTML =
                        '<div class="text-danger small">Error al buscar el comprobante.</div>';

                    console.error(e);
                }
            }

            function seleccionarComprobante(comprobante) {

                console.log(
                    'COMPROBANTE SELECCIONADO:',
                    comprobante
                );


                comprobanteSeleccionado = comprobante;


                flujoComprobante.origen = comprobante;


                $('#referencia_venta_id')
                    .val(comprobante.id);


                $('#texto_documento_referencia')
                    .text(
                        comprobante.serie_numero ?? '-'
                    );


                $('#total_a_emitir')
                    .text(
                        `S/ ${Number(comprobante.total ?? 0).toFixed(2)}`
                    );


                $('#resultado_busqueda_comprobante .card')
                    .removeClass('border-primary bg-primary-subtle');


                $(
                    `#comprobante-${comprobante.id}`
                ).addClass(
                    'border-primary bg-primary-subtle'
                );


                cargarOpcionesConversion(
                    comprobante
                );
            }

            function agregarOpcionConversion(tipo, texto, color = 'primary') {

                const contenedor = $('#contenedorOpcionesConversion');

                let tipoDocumentoId = null;
                let accion = 'CONVERTIR';
                let tipoDestino = tipo;

                switch (tipo) {

                    case 'FACTURA':
                        tipoDocumentoId = 1;
                        tipoDestino = 'FACTURA';
                        accion = 'CONVERTIR';
                        break;

                    case 'BOLETA':
                        tipoDocumentoId = 2;
                        tipoDestino = 'BOLETA';
                        accion = 'CONVERTIR';
                        break;

                    case 'NOTA_CREDITO_BOLETA':
                        tipoDocumentoId = 4;
                        tipoDestino = 'NC_BOLETA';
                        accion = 'NOTA_CREDITO';
                        break;

                    case 'NOTA_CREDITO_FACTURA':
                        tipoDocumentoId = 7;
                        tipoDestino = 'NC_FACTURA';
                        accion = 'NOTA_CREDITO';
                        break;

                    default:
                        console.error('Tipo de conversión desconocido:', tipo);
                        return;
                }

                const boton = `
        <div class="col-md-6 mb-2">
            <button
                type="button"
                class="btn btn-outline-${color} w-100 py-3"
                onclick="seleccionarTipoConversion(
                    '${tipoDestino}',
                    ${tipoDocumentoId},
                    '${accion}'
                )"
            >
                ${texto}
            </button>
        </div>
    `;

                contenedor.append(boton);
            }

            function cargarOpcionesConversion(comprobante) {

                const contenedor = $('#contenedorOpcionesConversion');

                contenedor.empty();

                $('#opcionesConversion').removeClass('d-none');

                const tipo = (comprobante.tipo ?? '')
                    .toUpperCase()
                    .trim();

                console.log('TIPO ORIGEN:', tipo);
                console.log('COMPROBANTE:', comprobante);

                if (tipo.includes('NOTA DE VENTA')) {

                    agregarOpcionConversion(
                        'BOLETA',
                        'Convertir a Boleta',
                        'primary'
                    );

                    agregarOpcionConversion(
                        'FACTURA',
                        'Convertir a Factura',
                        'primary'
                    );

                    return;
                }

                if (
                    tipo.includes('BOLETA') &&
                    !tipo.includes('NOTA')
                ) {

                    agregarOpcionConversion(
                        'FACTURA',
                        'Convertir a Factura',
                        'primary'
                    );

                    agregarOpcionConversion(
                        'NOTA_CREDITO_BOLETA',
                        'Nota de Crédito de Boleta',
                        'warning'
                    );

                    return;
                }

                if (
                    tipo.includes('FACTURA') &&
                    !tipo.includes('NOTA')
                ) {

                    agregarOpcionConversion(
                        'NOTA_CREDITO_FACTURA',
                        'Nota de Crédito de Factura',
                        'warning'
                    );

                    return;
                }


                contenedor.html(`
        <div class="col-12">
            <div class="alert alert-warning mb-0">
                Este documento no tiene opciones disponibles.
            </div>
        </div>
    `);
            }

            function resetSeleccion() {
                comprobanteSeleccionado = null;
                document.getElementById('referencia_venta_id').value = '';
                document.getElementById('texto_documento_referencia').textContent = 'Ninguno seleccionado';
                document.getElementById('total_a_emitir').textContent = 'S/ 0.00';
                document.getElementById('aviso_anulacion_origen').style.display = 'none';
                document.getElementById('btnContinuarComprobanteExistente').disabled = true;
            }

            function prepararPaso3(tipo) {

                $('.campo-cliente')
                    .addClass('d-none');

                $('.campo-factura')
                    .addClass('d-none');

                $('.campo-boleta')
                    .addClass('d-none');

                $('.campo-nota-credito')
                    .addClass('d-none');


                if (tipo === 'FACTURA') {

                    $('.campo-cliente')
                        .removeClass('d-none');

                    $('.campo-factura')
                        .removeClass('d-none');


                    $('#lblDocumentoConversion')
                        .text('RUC');


                    $('#doc_cliente_conversion')
                        .attr('maxlength', 11)
                        .val('');


                    $('#nombre_cliente_conversion')
                        .val('');

                    $('#direccion_cliente_conversion')
                        .val('');

                } else if (tipo === 'BOLETA') {

                    $('.campo-cliente')
                        .removeClass('d-none');

                    $('.campo-boleta')
                        .removeClass('d-none');


                    $('#lblDocumentoConversion')
                        .text('DNI');


                    $('#doc_cliente_conversion')
                        .attr('maxlength', 8)
                        .val('');


                    $('#nombre_cliente_conversion')
                        .val('');

                    $('#direccion_cliente_conversion')
                        .val('');
                } else if (
                    tipo === 'NC_FACTURA' ||
                    tipo === 'NC_BOLETA'
                ) {

                    $('.campo-nota-credito')
                        .removeClass('d-none');


                    cargarDatosNotaCredito();
                }
            }

            function cargarDatosNotaCredito() {

                const origen = flujoComprobante.origen;

                if (!origen) {

                    console.error(
                        'No existe comprobante de origen para la nota de crédito.'
                    );

                    return;
                }


                $('#nc_documento_origen')
                    .text(
                        origen.serie_numero ?? '-'
                    );


                $('#nc_cliente')
                    .text(
                        origen.cliente ?? '-'
                    );


                $('#nc_total')
                    .text(
                        `S/ ${Number(origen.total ?? 0).toFixed(2)}`
                    );

                flujoComprobante.cliente = {

                    documento: origen.documento ?? '',

                    nombre: origen.cliente ?? '',

                    direccion: origen.direccion ?? ''
                };
            }

            function seleccionarTipoConversion(
                tipo,
                tipoDocumentoId,
                accion
            ) {

                flujoComprobante.tipoDestino = tipo;

                flujoComprobante.tipoDocumentoFacturaId =
                    parseInt(tipoDocumentoId);

                flujoComprobante.accion = accion;

                $("#formularioComprobanteNuevo")
                    .addClass("d-none");

                $("#formularioConversionExistente")
                    .removeClass("d-none");

                prepararPaso3(tipo);

                mostrarPasoComprobante(3);
            }

            function continuarNuevoComprobante() {

                const tipoDocumentoId =
                    $("#tipo_documento_modal").val();

                const documento =
                    $("#doc_cliente").val().trim();

                const nombres =
                    $("#nombres").val().trim();

                if (!tipoDocumentoId) {

                    Swal.fire(
                        "Atención",
                        "Seleccione el tipo de comprobante.",
                        "warning"
                    );

                    return;
                }

                if (!documento) {

                    Swal.fire(
                        "Atención",
                        "Ingrese el documento del cliente.",
                        "warning"
                    );

                    return;
                }

                if (!nombres) {

                    Swal.fire(
                        "Atención",
                        "Ingrese los datos del cliente.",
                        "warning"
                    );

                    return;
                }

                if (!items.length) {

                    Swal.fire(
                        "Atención",
                        "Debe agregar al menos un servicio.",
                        "warning"
                    );

                    return;
                }

                flujoComprobante.tipoDocumentoFacturaId =
                    parseInt(tipoDocumentoId);

                flujoComprobante.cliente = {

                    documento: documento,

                    nombre: [
                            $("#nombres").val().trim(),
                            $("#apellidos").val().trim()
                        ]
                        .filter(Boolean)
                        .join(" "),

                    direccion: $("#direccion").val().trim()
                };

                flujoComprobante.items = [...items];

                const textoTipo =
                    $("#tipo_documento_modal option:selected")
                    .text()
                    .trim()
                    .toUpperCase();

                flujoComprobante.tipoDestino =
                    textoTipo.includes("FACTURA") ?
                    "FACTURA" :
                    textoTipo.includes("BOLETA") ?
                    "BOLETA" :
                    textoTipo;

                construirPreviewComprobanteNuevo();

                mostrarPasoComprobante(4);
            }

            function construirPreviewComprobanteNuevo() {

                const nombresTipo = {
                    FACTURA: "Factura",
                    BOLETA: "Boleta"
                };

                $("#preview_tipo").text(
                    nombresTipo[flujoComprobante.tipoDestino] ??
                    flujoComprobante.tipoDestino ??
                    "-"
                );

                $("#preview_fecha").text(
                    "{{ now()->format('d/m/Y') }}"
                );

                $("#preview_origen").text(
                    "Nuevo comprobante"
                );

                $("#preview_cliente").text(
                    flujoComprobante.cliente?.nombre ?? "-"
                );

                $("#preview_documento").text(
                    flujoComprobante.cliente?.documento ?? "-"
                );

                const total = items.reduce(
                    (acumulado, item) =>
                    acumulado + Number(item.subtotal || 0),
                    0
                );

                $("#preview_total").text(
                    `S/ ${total.toFixed(2)}`
                );

                $("#preview_motivo_container")
                    .addClass("d-none");
            }

            function validarPaso3() {

                const tipo = flujoComprobante.tipoDestino;

                if (tipo === "FACTURA") {

                    const documento =
                        $("#doc_cliente_conversion").val().trim();

                    const nombre =
                        $("#nombre_cliente_conversion").val().trim();

                    if (!/^\d{11}$/.test(documento)) {

                        Swal.fire(
                            "Atención",
                            "Ingrese un RUC válido de 11 dígitos.",
                            "warning"
                        );

                        return false;
                    }

                    if (!nombre) {

                        Swal.fire(
                            "Atención",
                            "Debe buscar y validar el RUC.",
                            "warning"
                        );

                        return false;
                    }
                }

                if (tipo === "BOLETA") {

                    const documento =
                        $("#doc_cliente_conversion").val().trim();

                    const nombre =
                        $("#nombre_cliente_conversion").val().trim();

                    if (!/^\d{8}$/.test(documento)) {

                        Swal.fire(
                            "Atención",
                            "Ingrese un DNI válido de 8 dígitos.",
                            "warning"
                        );

                        return false;
                    }

                    if (!nombre) {

                        Swal.fire(
                            "Atención",
                            "Debe buscar y validar el DNI.",
                            "warning"
                        );

                        return false;
                    }
                }

                if (
                    tipo === "NC_BOLETA" ||
                    tipo === "NC_FACTURA"
                ) {

                    const motivo =
                        $("#motivo_nota_credito").val()?.trim();

                    if (!motivo) {

                        Swal.fire(
                            "Atención",
                            "Ingrese el motivo de la nota de crédito.",
                            "warning"
                        );

                        return false;
                    }
                }

                return true;
            }

            function actualizarSerieDestino() {
                if (comprobanteSeleccionado) consultarImpactoConversion();
            }

            function cambiarTipoComprobanteConversion() {

                actualizarSerieDestino();

                const select = document.getElementById("tipo_comprobante_destino");
                const texto = select.options[select.selectedIndex]?.text
                    ?.trim()
                    ?.toUpperCase() ?? "";

                const $documento = $("#doc_cliente_conversion");

                // Limpiamos cliente al cambiar de tipo
                $documento.val("");
                $("#nombre_cliente_conversion").val("");
                $("#direccion_cliente_conversion").val("");

                if (texto.includes("FACTURA")) {

                    $("#lblDocumentoConversion").text("RUC");
                    $("#lblNombreConversion").html(
                        'Razón Social <span class="text-danger">*</span>'
                    );

                    $("#ayudaDocumentoConversion").text(
                        "La factura requiere un RUC de 11 dígitos."
                    );

                    $documento
                        .attr("maxlength", 11)
                        .attr("placeholder", "Ingrese RUC");

                } else if (texto.includes("BOLETA")) {

                    $("#lblDocumentoConversion").text("DNI");
                    $("#lblNombreConversion").html(
                        'Cliente <span class="text-danger">*</span>'
                    );

                    $("#ayudaDocumentoConversion").text(
                        "Ingrese el DNI de 8 dígitos."
                    );

                    $documento
                        .attr("maxlength", 8)
                        .attr("placeholder", "Ingrese DNI");

                } else {

                    $("#lblDocumentoConversion").text("DNI / RUC");

                    $("#ayudaDocumentoConversion").text(
                        "Ingrese el documento del cliente."
                    );

                    $documento
                        .attr("maxlength", 11)
                        .attr("placeholder", "Ingrese DNI o RUC");
                }
            }

            function guardarDatosPaso3() {

                const tipo = flujoComprobante.tipoDestino;

                if (
                    tipo === "FACTURA" ||
                    tipo === "BOLETA"
                ) {

                    flujoComprobante.cliente = {

                        documento: $("#doc_cliente_conversion")
                            .val()
                            .trim(),

                        nombre: $("#nombre_cliente_conversion")
                            .val()
                            .trim(),

                        direccion: $("#direccion_cliente_conversion")
                            .val()
                            .trim()
                    };
                }

                if (
                    tipo === "NC_BOLETA" ||
                    tipo === "NC_FACTURA"
                ) {

                    flujoComprobante.cliente = null;

                    flujoComprobante.motivoNotaCredito =
                        $("#motivo_nota_credito")
                        .val()
                        .trim();
                }
            }

            function construirPreviewComprobante() {

                const origen = flujoComprobante.origen;

                const nombresTipo = {
                    BOLETA: "Boleta",
                    FACTURA: "Factura",
                    NC_BOLETA: "Nota de crédito de Boleta",
                    NC_FACTURA: "Nota de crédito de Factura"
                };

                $("#preview_tipo").text(
                    nombresTipo[flujoComprobante.tipoDestino] ?? "-"
                );

                $("#preview_fecha").text(
                    "{{ now()->format('d/m/Y') }}"
                );

                if (origen) {

                    $("#preview_origen").text(
                        `${origen.tipo} ${origen.serie_numero}`
                    );

                    $("#preview_total").text(
                        `S/ ${origen.total}`
                    );

                } else {

                    $("#preview_origen").text(
                        "Nuevo comprobante"
                    );
                }

                if (flujoComprobante.cliente) {

                    $("#preview_cliente").text(
                        flujoComprobante.cliente.nombre
                    );

                    $("#preview_documento").text(
                        flujoComprobante.cliente.documento
                    );

                } else if (origen) {

                    $("#preview_cliente").text(
                        origen.cliente
                    );

                    $("#preview_documento").text(
                        origen.documento
                    );
                }

                if (flujoComprobante.motivoNotaCredito) {

                    $("#preview_motivo_container")
                        .removeClass("d-none");

                    $("#preview_motivo").text(
                        flujoComprobante.motivoNotaCredito
                    );

                } else {

                    $("#preview_motivo_container")
                        .addClass("d-none");
                }
            }

            async function buscarClienteConversion() {

                const documento = $('#doc_cliente_conversion')
                    .val()
                    .trim();

                const tipo = flujoComprobante.tipoDestino;

                // ==========================================
                // VALIDAR DOCUMENTO
                // ==========================================

                if (tipo === 'FACTURA') {

                    if (documento.length !== 11) {

                        Swal.fire(
                            'Atención',
                            'Para una factura debe ingresar un RUC de 11 dígitos.',
                            'warning'
                        );

                        return;
                    }

                } else if (tipo === 'BOLETA') {

                    if (documento.length !== 8) {

                        Swal.fire(
                            'Atención',
                            'Para una boleta debe ingresar un DNI de 8 dígitos.',
                            'warning'
                        );

                        return;
                    }
                }


                if (!documento) {

                    Swal.fire(
                        'Atención',
                        'Ingrese un documento.',
                        'warning'
                    );

                    return;
                }


                const btn = $('#btnBuscarClienteConversion');

                const textoOriginal = btn.html();

                btn.prop('disabled', true);

                btn.html(`
        <span class="spinner-border spinner-border-sm me-1"></span>
        Buscando...
    `);


                try {

                    const response = await fetch(
                        `{{ route('buscar.buscar') }}?documento=${encodeURIComponent(documento)}`
                    );

                    const data = await response.json();


                    if (!response.ok) {

                        throw new Error(
                            data.message ||
                            'No se pudo consultar el documento.'
                        );
                    }


                    console.log('CLIENTE ENCONTRADO:', data);


                    let nombre = '';
                    let direccion = '';


                    // Dependiendo de cómo responda tu API
                    nombre =
                        data.nombre ??
                        data.razon_social ??
                        data.nombre_completo ??
                        '';

                    direccion =
                        data.direccion ??
                        '';


                    $('#nombre_cliente_conversion')
                        .val(nombre);

                    $('#direccion_cliente_conversion')
                        .val(direccion);


                    flujoComprobante.cliente = {

                        documento: documento,

                        nombre: nombre,

                        direccion: direccion
                    };


                    if (!nombre) {

                        Swal.fire(
                            'Atención',
                            'No se encontraron datos para este documento.',
                            'warning'
                        );
                    }


                } catch (error) {

                    console.error(error);

                    Swal.fire(
                        'Error',
                        error.message,
                        'error'
                    );

                } finally {

                    btn.prop('disabled', false);

                    btn.html(textoOriginal);
                }
            }

            function continuarConversion() {

                if (!validarPaso3()) {
                    return;
                }

                guardarDatosPaso3();

                construirPreviewComprobante();

                mostrarPasoComprobante(4);
            }

            $("#formVentaRapida").on("submit", function(e) {

                if (procesandoComprobante) {
                    e.preventDefault();
                    return false;
                }

                procesandoComprobante = true;

                $("#btnGenerarEmitir")
                    .prop("disabled", true)
                    .html(`
            <span class="spinner-border spinner-border-sm me-2"></span>
            Generando comprobante...
        `);

                $(this)
                    .find("button[type='button']")
                    .prop("disabled", true);

                return true;
            });



            function buscarCliente() {
                const documento = $("#doc_cliente").val().trim();
                const $btn = $("#btnBuscarCliente");

                if (!documento) {
                    Swal.fire("Atención", "Ingrese un DNI o RUC.", "warning");
                    return;
                }

                if (documento.length !== 8 && documento.length !== 11) {
                    Swal.fire(
                        "Atención",
                        "El DNI debe tener 8 dígitos y el RUC 11 dígitos.",
                        "warning"
                    );
                    return;
                }

                if (!/^\d+$/.test(documento)) {
                    Swal.fire(
                        "Atención",
                        "El documento solo debe contener números.",
                        "warning"
                    );
                    return;
                }

                $btn.prop("disabled", true);

                $.ajax({
                    url: route("buscar.buscar"),
                    type: "GET",
                    dataType: "json",
                    data: {
                        documento: documento
                    },

                    success: function(data) {

                        console.log("RESPUESTA DNI/RUC:", data);

                        if (data.error) {
                            Swal.fire(
                                "No encontrado",
                                data.error,
                                "warning"
                            );
                            return;
                        }

                        // RUC
                        if (documento.length === 11) {

                            $("#nombres").val(
                                data.razon_social ??
                                data.nombre_o_razon_social ??
                                ""
                            );

                            $("#apellidos").val("");

                            $("#direccion").val(
                                data.direccion ??
                                data.domicilio_fiscal ??
                                "-"
                            );

                        }

                        // DNI
                        else {

                            $("#nombres").val(
                                data.nombres ?? ""
                            );

                            $("#apellidos").val(
                                [
                                    data.apellido_paterno,
                                    data.apellido_materno
                                ]
                                .filter(Boolean)
                                .join(" ")
                            );

                            $("#direccion").val("-");
                        }

                        actualizarCamposCliente();
                    },

                    error: function(xhr) {

                        console.error("ERROR BUSCAR DNI/RUC:", xhr);
                        console.error("STATUS:", xhr.status);
                        console.error("RESPUESTA:", xhr.responseText);

                        let mensaje = "No se pudo consultar el documento.";

                        if (xhr.responseJSON?.message) {
                            mensaje = xhr.responseJSON.message;
                        }

                        Swal.fire(
                            "Error",
                            mensaje,
                            "error"
                        );
                    },

                    complete: function() {
                        $btn.prop("disabled", false);
                    }
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

            function prepararFormularioNuevo() {

                flujoComprobante.tipoDestino = null;
                flujoComprobante.tipoDocumentoFacturaId = null;
                flujoComprobante.cliente = null;
                flujoComprobante.motivoNotaCredito = null;

                $("#formVentaRapida")[0]?.reset();

                items = [];

                render();

                actualizarSerie();
            }

            async function emitirComprobanteFinal() {

                if (procesandoConversion) return;

                procesandoConversion = true;

                const $btn = $("#btnEmitirComprobante");

                $btn.prop("disabled", true).html(`
        <span class="spinner-border spinner-border-sm me-2"></span>
        Emitiendo...
    `);

                try {

                    let res;

                    if (flujoComprobante.modo === 'nuevo') {

                        const form = document.getElementById('formVentaRapida');

                        if (!form) {
                            throw new Error('No se encontró el formulario de venta.');
                        }

                        const formData = new FormData(form);

                        formData.set(
                            'items',
                            JSON.stringify(flujoComprobante.items)
                        );

                        formData.set(
                            'tipo_documento_factura_id',
                            flujoComprobante.tipoDocumentoFacturaId
                        );

                        console.log(
                            'DATOS NUEVA VENTA:',
                            Object.fromEntries(formData.entries())
                        );

                        res = await fetch(
                            form.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            }
                        );
                    } else {

                        if (!flujoComprobante.origen?.id) {
                            throw new Error(
                                'No se encontró el comprobante de referencia.'
                            );
                        }

                        res = await fetch(
                            route("facturacion.convertir-comprobante"), {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json",
                                    "X-Requested-With": "XMLHttpRequest",
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content
                                },
                                body: JSON.stringify({
                                    venta_referencia_id: flujoComprobante.origen.id,

                                    tipo_documento_factura_id: flujoComprobante.tipoDocumentoFacturaId,

                                    accion: flujoComprobante.accion,

                                    documento_cliente: flujoComprobante.cliente?.documento ?? null,

                                    nombre_cliente: flujoComprobante.cliente?.nombre ?? null,

                                    direccion_cliente: flujoComprobante.cliente?.direccion ?? null,

                                    motivo_nota_credito: flujoComprobante.motivoNotaCredito ?? null
                                })
                            }
                        );
                    }

                    const json = await res.json();

                    if (!res.ok) {

                        const mensaje =
                            json.message ??
                            Object.values(json.errors ?? {})
                            .flat()
                            .join("\n") ??
                            "No se pudo emitir el comprobante.";

                        throw new Error(mensaje);
                    }

                    await Swal.fire(
                        "Correcto",
                        json.message ?? "Comprobante emitido correctamente.",
                        "success"
                    );

                    location.reload();

                } catch (error) {

                    console.error(error);

                    procesandoConversion = false;

                    $btn
                        .prop("disabled", false)
                        .html(`
                <i data-lucide="send"></i>
                Emitir comprobante
            `);

                    lucide.createIcons();

                    Swal.fire(
                        "Error",
                        error.message,
                        "error"
                    );
                }
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

            window.agregarItem = function() {

                const descripcion = $("#descripcion").val()?.trim() ?? "";
                const precio = parseFloat($("#precio").val());
                const cantidad = parseFloat($("#unidad").val());
                const tipoServicioId = parseInt($("#tipo_servicio_id").val());

                console.log({
                    descripcion,
                    precio,
                    cantidad,
                    tipoServicioId
                });

                if (!tipoServicioId || isNaN(tipoServicioId)) {
                    Swal.fire(
                        "Error",
                        "Seleccione un tipo de servicio.",
                        "error"
                    );
                    return;
                }

                if (!descripcion) {
                    Swal.fire(
                        "Error",
                        "Ingrese una descripción.",
                        "error"
                    );
                    return;
                }

                if (isNaN(cantidad) || cantidad <= 0) {
                    Swal.fire(
                        "Error",
                        "Ingrese una cantidad válida.",
                        "error"
                    );
                    return;
                }

                if (isNaN(precio) || precio <= 0) {
                    Swal.fire(
                        "Error",
                        "Ingrese un precio válido.",
                        "error"
                    );
                    return;
                }

                const porcentajeIgv =
                    tipoServicioId === 1 ?
                    IGV_VIAJE / 100 :
                    IGV_ENCOMIENDA / 100;

                const subtotal = cantidad * precio;

                const item = {
                    tipo_servicio_id: tipoServicioId,
                    descripcion: descripcion,
                    cantidad: cantidad,
                    precio: precio,
                    igv: porcentajeIgv,
                    subtotal: subtotal
                };

                items.push(item);

                console.log("ITEM AGREGADO:", item);
                console.log("ITEMS:", items);

                $("#descripcion").val("");
                $("#unidad").val("");
                $("#precio").val("");

                render();
            };

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

            function mostrarPasoComprobante(paso) {

                pasoComprobanteActual = paso;

                $("#paso1Comprobante").addClass("d-none");
                $("#paso2Comprobante").addClass("d-none");
                $("#paso3Comprobante").addClass("d-none");
                $("#paso4Comprobante").addClass("d-none");

                $(`#paso${paso}Comprobante`).removeClass("d-none");

                $("#btnVolverModal").toggleClass("d-none", paso === 1);

                actualizarTituloPaso();
            }

            function actualizarTituloPaso() {

                const titulos = {
                    1: "Generar comprobante",
                    2: "Seleccionar comprobante",
                    3: "Completar información",
                    4: "Previsualizar comprobante"
                };

                const subtitulos = {
                    1: "Selecciona cómo deseas generar el comprobante.",
                    2: "Busca el documento de origen y selecciona qué deseas generar.",
                    3: "Completa la información necesaria para el nuevo documento.",
                    4: "Revisa la información antes de emitir."
                };

                $("#modalComprobanteLabel").text(titulos[pasoComprobanteActual]);
                $("#subtituloModalComprobante").text(
                    subtitulos[pasoComprobanteActual]
                );
            }

            function iniciarComprobanteNuevo() {

                flujoComprobante.modo = "nuevo";
                flujoComprobante.accion = "NUEVO";
                flujoComprobante.origen = null;

                $("#formularioComprobanteNuevo")
                    .removeClass("d-none");

                $("#formularioConversionExistente")
                    .addClass("d-none");

                prepararFormularioNuevo();

                mostrarPasoComprobante(3);
            }

            function iniciarDesdeExistente() {

                flujoComprobante.modo = "existente";
                flujoComprobante.accion = null;
                flujoComprobante.origen = null;

                mostrarPasoComprobante(2);
            }

            function volverPasoComprobante() {

                if (pasoComprobanteActual === 1) {
                    return;
                }

                // Existente:
                // 4 → 3 → 2 → 1
                if (flujoComprobante.modo === "existente") {

                    if (pasoComprobanteActual === 4) {
                        mostrarPasoComprobante(3);
                        return;
                    }

                    if (pasoComprobanteActual === 3) {
                        mostrarPasoComprobante(2);
                        return;
                    }

                    mostrarPasoComprobante(1);
                    return;
                }

                if (flujoComprobante.modo === "nuevo") {

                    if (pasoComprobanteActual === 4) {
                        mostrarPasoComprobante(3);
                        return;
                    }

                    mostrarPasoComprobante(1);
                }
            }

            document.addEventListener("DOMContentLoaded", function() {
                const modalComprobante = document.getElementById("modalComprobante");

                if (!modalComprobante) return;

                modalComprobante.addEventListener("hidden.bs.modal", function() {
                    resetModalComprobante();
                });
            });

            function resetModalComprobante() {

                pasoComprobanteActual = 1;

                flujoComprobante = {
                    modo: null,
                    origen: null,
                    tipoDestino: null,
                    tipoDocumentoFacturaId: null,
                    accion: null,
                    cliente: null,
                    motivoNotaCredito: null,
                    items: []
                };

                comprobanteSeleccionado = null;

                procesandoComprobante = false;
                procesandoConversion = false;

                $("#paso1Comprobante").removeClass("d-none");
                $("#paso2Comprobante").addClass("d-none");
                $("#paso3Comprobante").addClass("d-none");
                $("#paso4Comprobante").addClass("d-none");

                $("#btnVolverModal").addClass("d-none");

                $("#buscar_comprobante_input").val("");
                $("#resultado_busqueda_comprobante").empty();

                $("#opcionesConversion")
                    .addClass("d-none");

                $("#contenedorOpcionesConversion")
                    .empty();

                $("#referencia_venta_id").val("");

                $("#texto_documento_referencia")
                    .text("Ninguno seleccionado");

                $("#total_a_emitir")
                    .text("S/ 0.00");

                $("#doc_cliente_conversion").val("");
                $("#nombre_cliente_conversion").val("");
                $("#direccion_cliente_conversion").val("");

                $("#motivo_nota_credito").val("");

                items = [];

                if (typeof render === "function") {
                    render();
                }

                actualizarTituloPaso();
            }
        </script>
    @endpush
