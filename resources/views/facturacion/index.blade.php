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

        {{-- CARDS RESUMEN --}}
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
                        <h4>{{ $ventas->where('estado', 'ACEPTADA')->count() }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Pendientes</h6>
                        <h4>{{ $ventas->where('estado', 'PENDIENTE_RESUMEN')->count() }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Rechazadas</h6>
                        <h4>{{ $ventas->where('estado', 'RECHAZADA')->count() }}</h4>
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
                                        {{ $venta->id }}
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
                                            @case('ACEPTADA')
                                                <span class="badge bg-success">ACEPTADA</span>
                                            @break

                                            @case('RECHAZADA')
                                                <span class="badge bg-danger">RECHAZADA</span>
                                            @break

                                            @case('PENDIENTE_RESUMEN')
                                                <span class="badge bg-warning text-dark">
                                                    PENDIENTE
                                                </span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">
                                                    {{ $venta->estado }}
                                                </span>
                                        @endswitch

                                    </td>

                                    <td class="text-muted">
                                        {{ optional($venta->fecha_emision)->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="text-end">

                                        <div class="btn-group btn-group-sm">

                                            <a href="{{ route('facturacion.show', $venta) }}" class="btn btn-primary">

                                                Ver
                                            </a>

                                            @if ($venta->ruta_xml)
                                                <a href="{{ route('facturacion.xml', $venta) }}" class="btn btn-info">

                                                    XML
                                                </a>
                                            @endif

                                            @if ($venta->ruta_cdr)
                                                <a href="{{ route('facturacion.cdr', $venta) }}" class="btn btn-success">

                                                    CDR
                                                </a>
                                            @endif

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

    @endsection
    @push('scripts')
        <script>
            let items = [];
            const IGV = 0.18;

             function buscarCliente() {

                let documento = $("#doc_cliente").val().trim();
                if (!documento) return;

                $("#btnBuscarCliente").prop("disabled", true);

                $.getJSON(route("buscar.buscar") + "?documento=" + documento)

                    .done(function(data) {

                        if (data.error) {
                            Swal.fire("Aviso", data.error, "warning");
                            return;
                        }

                        if (data.razon_social) {

                            $("#razon_social").val(data.razon_social);
                            $("#direccion").val(data.direccion || "-");

                        } else {

                            let nombreCompleto =
                                ((data.nombres || "") + " " +
                                    (data.apellido_paterno || "") + " " +
                                    (data.apellido_materno || "")).trim();

                            $("#razon_social").val(nombreCompleto);
                            $("#direccion").val("-");
                        }

                    })

                    .fail(function() {
                        Swal.fire("Error", "No se encontró el documento", "error");
                    })

                    .always(function() {
                        $("#btnBuscarCliente").prop("disabled", false);
                    });
            }

            function agregarItem() {

                let descripcion = $("#descripcion").val().trim();
                let precio = parseFloat($("#precio").val());

                if (!descripcion || isNaN(precio)) return;

                items.push({
                    descripcion,
                    precio
                });

                $("#descripcion").val("");
                $("#precio").val("");

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

                    total += item.precio;

                    tbody.append(`
            <tr>
                <td>${item.descripcion}</td>
                <td>${item.precio.toFixed(2)}</td>
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

                // 🔥 PRECIO YA INCLUYE IGV
                let base = total / (1 + IGV);
                let igv = total - base;

                $("#subtotal").text(base.toFixed(2));
                $("#igv").text(igv.toFixed(2));
                $("#total").text(total.toFixed(2));

                $("#itemsInput").val(JSON.stringify(items));
            }
        </script>
    @endpush
