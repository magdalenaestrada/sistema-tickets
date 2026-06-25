@extends('layouts.app')

@section('title', 'Detalle Comprobante')

@section('content')

    <div class="container-fluid">

        <div class="row">

            <div class="col-md-12">

                <div class="card shadow-sm">

                    <div class="card-header d-flex justify-content-between">

                        <h5 class="mb-0">
                            {{ $venta->serie }}-{{ $venta->numero }}
                        </h5>

                        <div>

                            <a href="{{ route('facturacion.index') }}" class="btn btn-secondary">

                                Volver
                            </a>

                        </div>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-6">

                                <h6>Información Cliente</h6>

                                <table class="table table-sm">

                                    <tr>
                                        <th>Cliente</th>
                                        <td>{{ $venta->persona?->nombre_facturacion }}</td>
                                    </tr>

                                    <tr>
                                        <th>Documento</th>
                                        <td>{{ $venta->persona?->documento }}</td>
                                    </tr>

                                    <tr>
                                        <th>Dirección</th>
                                        <td>{{ $venta->persona?->direccion }}</td>
                                    </tr>

                                </table>

                            </div>

                            <div class="col-md-6">

                                <h6>Información Comprobante</h6>

                                <table class="table table-sm">

                                    <tr>
                                        <th>Tipo</th>
                                        <td>{{ $venta->tipoDocumentoFactura?->descripcion }}</td>
                                    </tr>

                                    <tr>
                                        <th>Fecha</th>
                                        <td>{{ optional($venta->fecha_emision)->format('d/m/Y H:i') }}</td>
                                    </tr>

                                    <tr>
                                        <th>Estado</th>
                                        <td>{{ $venta->estado }}</td>
                                    </tr>

                                    <tr>
                                        <th>Hash</th>
                                        <td>{{ $venta->hash }}</td>
                                    </tr>

                                </table>

                            </div>

                        </div>

                        <hr>

                        <h6>Detalle</h6>

                        <div class="table-responsive">

                            <table class="table table-bordered">

                                <thead>

                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Valor Unitario</th>
                                        <th>IGV</th>
                                        <th>Total</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach ($venta->detalles as $detalle)
                                        <tr>

                                            <td>
                                                {{ $detalle->codigo }}
                                            </td>

                                            <td>
                                                {{ $detalle->descripcion }}
                                            </td>

                                            <td>
                                                {{ $detalle->cantidad }}
                                            </td>

                                            <td>
                                                S/ {{ number_format($detalle->valor_unitario, 2) }}
                                            </td>

                                            <td>
                                                S/ {{ number_format($detalle->igv, 2) }}
                                            </td>

                                            <td>
                                                S/ {{ number_format($detalle->valor_venta, 2) }}
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        <div class="row mt-4">

                            <div class="col-md-4 offset-md-8">

                                <table class="table table-bordered">

                                    <tr>
                                        <th>Subtotal</th>
                                        <td>
                                            S/ {{ number_format($venta->subtotal_sin_igv, 2) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>IGV</th>
                                        <td>
                                            S/ {{ number_format($venta->impuesto, 2) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Total</th>
                                        <td>
                                            <strong>
                                                S/ {{ number_format($venta->total, 2) }}
                                            </strong>
                                        </td>
                                    </tr>

                                </table>

                            </div>

                        </div>

                        <hr>

                        <div class="d-flex gap-2 flex-wrap">

                            @if (!in_array($venta->estado, ['ACEPTADA', 'PENDIENTE_RESUMEN']))
                                <form action="{{ route('facturacion.emitir', $venta) }}" method="POST">

                                    @csrf

                                    <button type="submit" class="btn btn-success">

                                        Emitir SUNAT
                                    </button>

                                </form>
                            @endif

                            @if ($venta->ruta_xml)
                                <a href="{{ route('facturacion.xml', $venta) }}" class="btn btn-info">

                                    Descargar XML
                                </a>
                            @endif

                            @if ($venta->ruta_cdr)
                                <a href="{{ route('facturacion.cdr', $venta) }}" class="btn btn-primary">

                                    Descargar CDR
                                </a>
                            @endif

                            @if ($venta->ruta_pdf)
                                <a href="{{ route('facturacion.pdf', $venta) }}" class="btn btn-danger">

                                    Descargar PDF
                                </a>
                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
