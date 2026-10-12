@extends('layouts.app')

@section('title', 'Detalle del comprobante')

@section('content')
<div class="container-fluid px-4 py-4" style="background-color: #eef2f6; min-height: 100vh;">
    
    <!-- ENCABEZADO SUPERIOR -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 p-3 bg-white rounded-4 shadow-sm border-start border-4 border-primary">
        <div>
            <div class="text-muted small mb-1" style="font-size: 12px;">
                <span class="text-primary fw-semibold"><i class="fas fa-home me-1"></i>Inicio</span> / 
                <span>Comprobantes</span> / 
                <span class="text-dark fw-bold">{{ $venta->serie }}-{{ $venta->numero }}</span>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase fw-bold mb-1" style="font-size: 10px; letter-spacing: 0.5px;">
                Consulta de documento
            </span>
            <h2 class="h3 fw-bold text-dark mb-0">Detalle del comprobante</h2>
            <p class="text-muted small mb-0">Revisa la información del cliente, los conceptos registrados y el resumen de importes.</p>
        </div>
        
        <div class="d-flex gap-2 mt-2 mt-md-0 flex-wrap">
            <a href="{{ route('facturacion.index') }}" class="btn btn-light border shadow-sm px-3 py-2 fw-semibold text-secondary rounded-3">
                <i class="fas fa-arrow-left me-1 text-primary"></i> Volver
            </a>
            <button onclick="navigator.clipboard.writeText('{{ $venta->serie }}-{{ $venta->numero }}')" class="btn btn-light border shadow-sm px-3 py-2 fw-semibold text-secondary rounded-3">
                <i class="far fa-copy me-1 text-info"></i> Copiar número
            </button>
            @if ($venta->ruta_pdf)
                <a href="{{ route('facturacion.pdf', $venta) }}" class="btn btn-outline-danger shadow-sm px-3 py-2 fw-semibold rounded-3">
                    <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                </a>
                <a href="{{ route('facturacion.pdf', $venta) }}" class="btn btn-primary shadow-sm px-4 py-2 fw-semibold rounded-3" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); border: none;">
                    <i class="fas fa-print me-1"></i> Imprimir
                </a>
            @endif
        </div>
    </div>

    @php
        $estadoVal = is_object($venta->estado) ? $venta->estado->value : $venta->estado;
        $esAnulado = in_array(strtolower($estadoVal), ['anulado', 'rechazado']);
    @endphp

    <!-- ALERTA DE ESTADO -->
    @if($esAnulado)
    <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3" style="background-color: #fef2f2; color: #991b1b; border-left: 5px solid #ef4444 !important;">
        <div class="bg-danger text-white p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class="fas fa-exclamation-triangle fs-6"></i>
        </div>
        <div>
            <div class="fw-bold fs-6">Este comprobante figura como {{ strtolower($estadoVal) }}</div>
            <div class="small text-muted">Se muestra el detalle histórico registrado para facilitar la consulta. Verifica el estado antes de usar esta información.</div>
        </div>
    </div>
    @endif

    <div class="row g-4">
        <!-- COLUMNA IZQUIERDA -->
        <div class="col-xl-8">
            <div class="row g-4">
                
                <!-- Información del Cliente -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                        <div class="card-header py-3 px-4 border-0 text-white" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-white text-primary p-2 rounded-3 shadow-sm" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Información del cliente</h6>
                                    <span class="text-white-50" style="font-size: 11px;">Datos asociados al comprobante</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3 p-2 rounded-3" style="background-color: #f0f7ff;">
                                <div class="text-primary fw-semibold" style="font-size: 11px;">CLIENTE</div>
                                <div class="fw-bold text-dark fs-6">{{ $venta->persona?->nombre_facturacion ?? 'CLIENTE VARIOS' }}</div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-2 rounded-3 border" style="background-color: #fafafa;">
                                        <div class="text-muted" style="font-size: 11px;">DOCUMENTO</div>
                                        <div class="fw-semibold text-dark">{{ $venta->persona?->documento ?? '00000000' }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-3 border" style="background-color: #fafafa;">
                                        <div class="text-muted" style="font-size: 11px;">DIRECCIÓN</div>
                                        <div class="fw-semibold text-dark text-truncate">{{ $venta->persona?->direccion ?? 'No registrada' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Comprobante -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                        <div class="card-header py-3 px-4 border-0 text-white" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-white text-indigo p-2 rounded-3 shadow-sm" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #4f46e5;">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Información del comprobante</h6>
                                    <span class="text-white-50" style="font-size: 11px;">Identificación y emisión</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="p-2 rounded-3 border" style="background-color: #f5f3ff;">
                                        <div class="text-indigo fw-semibold" style="font-size: 11px; color: #4f46e5;">NÚMERO</div>
                                        <div class="fw-bold text-dark">{{ $venta->serie }}-{{ $venta->numero }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-3 border" style="background-color: #fafafa;">
                                        <div class="text-muted" style="font-size: 11px;">TIPO</div>
                                        <div class="fw-semibold text-dark">{{ $venta->tipoDocumentoFactura?->descripcion ?? 'Comprobante' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-2 rounded-3 border" style="background-color: #fafafa;">
                                        <div class="text-muted" style="font-size: 11px;">FECHA Y HORA</div>
                                        <div class="fw-semibold text-dark" style="font-size: 12px;">{{ optional($venta->fecha_emision)->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-3 border" style="background-color: #fafafa;">
                                        <div class="text-muted" style="font-size: 11px;">ESTADO</div>
                                        <span class="badge {{ $esAnulado ? 'bg-danger text-white' : 'bg-success text-white' }} px-2 py-1 text-uppercase fw-bold">
                                            {{ $estadoVal }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DETALLE DE CONCEPTOS (TABLA) -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                        <div class="card-header py-3 px-4 border-0 d-flex justify-content-between align-items-center" style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0 !important;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary text-white p-2 rounded-3 shadow-sm" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Detalle de conceptos</h6>
                                    <span class="text-muted" style="font-size: 11px;">Productos y servicios incluidos</span>
                                </div>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold" style="font-size: 12px;">
                                {{ count($venta->detalles) }} conceptos registrados
                            </span>
                        </div>

                        <div class="table-responsive p-3">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="text-uppercase" style="font-size: 11px; background-color: #0f172a; color: #ffffff;">
                                    <tr>
                                        <th class="py-3 px-3 rounded-start">Código</th>
                                        <th class="py-3">Descripción</th>
                                        <th class="py-3 text-center">Cantidad</th>
                                        <th class="py-3 text-end">V. Unitario</th>
                                        <th class="py-3 text-end">IGV</th>
                                        <th class="py-3 text-end px-3 rounded-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($venta->detalles as $detalle)
                                        <tr class="border-bottom">
                                            <td class="px-3">
                                                <span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1" style="font-size: 11px;">
                                                    {{ $detalle->codigo ?: 'NIU' }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark text-wrap" style="font-size: 13px; min-width: 220px;">
                                                {{ $detalle->descripcion }}
                                            </td>
                                            <td class="text-center fw-semibold text-primary" style="font-size: 13px;">
                                                <span class="bg-primary bg-opacity-10 px-2 py-1 rounded-2">{{ number_format($detalle->cantidad, 2) }}</span>
                                            </td>
                                            <td class="text-end text-secondary fw-semibold" style="font-size: 13px;">
                                                S/ {{ number_format($detalle->valor_unitario, 2) }}
                                            </td>
                                            <td class="text-end text-secondary fw-semibold" style="font-size: 13px;">
                                                S/ {{ number_format($detalle->igv, 2) }}
                                            </td>
                                            <td class="text-end px-3 fw-bold text-success fs-6">
                                                S/ {{ number_format($detalle->valor_venta ?? ($detalle->cantidad * $detalle->valor_unitario + $detalle->igv), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: TARJETA DE RESUMEN Y TOTALEZ -->
        <div class="col-xl-4">
            <!-- TARJETA AZUL CON DEGRADADO E IMPORTES -->
            <div class="card border-0 shadow-lg rounded-4 text-white overflow-hidden mb-3" style="background: linear-gradient(145deg, #0f172a, #1e293b, #1e3a8a);">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-info text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.8px;">Importe Total Registrado</span>
                        <i class="fas fa-coins text-warning fs-4"></i>
                    </div>
                    
                    <div class="display-6 fw-bold text-white mb-1">S/ {{ number_format($venta->total, 2) }}</div>
                    <div class="text-white-50 small mb-4"><i class="fas fa-money-bill-wave text-success me-1"></i> Soles Peruanos (PEN)</div>

                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 mb-4" style="background-color: rgba(255, 255, 255, 0.1); backdrop-filter: blur(5px);">
                        <span class="small text-white-50"><i class="fas fa-file-invoice me-1"></i>{{ $venta->serie }}-{{ $venta->numero }}</span>
                        <span class="badge {{ $esAnulado ? 'bg-danger' : 'bg-success' }} text-white fw-bold px-2 py-1" style="font-size: 10px;">{{ strtoupper($estadoVal) }}</span>
                    </div>

                    <!-- DESGLOSE ANTES DEL TOTAL -->
                    <div class="p-3 rounded-3 mb-3" style="background-color: rgba(0, 0, 0, 0.2);">
                        <div class="d-flex justify-content-between align-items-center mb-2 text-white-50" style="font-size: 13px;">
                            <span>Op. Gravadas</span>
                            <span class="text-white fw-semibold">S/ {{ number_format($venta->total_gravada ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 text-white-50" style="font-size: 13px;">
                            <span>Op. Inafectas / Exoneradas</span>
                            <span class="text-white fw-semibold">S/ {{ number_format(($venta->total_inafecta ?? 0) + ($venta->total_exonerada ?? 0), 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-white-50" style="font-size: 13px;">
                            <span class="text-info fw-semibold">IGV (18%)</span>
                            <span class="text-info fw-bold">S/ {{ number_format($venta->impuesto ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <div class="border-top border-secondary pt-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-white fs-5">TOTAL FINAL</span>
                        <span class="fw-bold text-warning fs-3">S/ {{ number_format($venta->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- DESCARGAS Y RESUMEN TRIBUTARIO -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-download me-1 text-primary"></i> Archivos del comprobante</h6>
                
                <div class="d-grid gap-2 mb-4">
                    @if ($venta->ruta_xml)
                        <a href="{{ route('facturacion.xml', $venta) }}" class="btn btn-outline-info text-dark fw-semibold d-flex justify-content-between align-items-center p-2 rounded-3">
                            <span><i class="fas fa-file-code text-info me-2"></i> Descargar XML</span>
                            <i class="fas fa-download fs-7"></i>
                        </a>
                    @endif

                    @if ($venta->ruta_cdr)
                        <a href="{{ route('facturacion.cdr', $venta) }}" class="btn btn-outline-primary text-dark fw-semibold d-flex justify-content-between align-items-center p-2 rounded-3">
                            <span><i class="fas fa-shield-alt text-primary me-2"></i> Descargar CDR</span>
                            <i class="fas fa-download fs-7"></i>
                        </a>
                    @endif

                    @if ($venta->ruta_pdf)
                        <a href="{{ route('facturacion.pdf', $venta) }}" class="btn btn-outline-danger text-dark fw-semibold d-flex justify-content-between align-items-center p-2 rounded-3">
                            <span><i class="fas fa-file-pdf text-danger me-2"></i> Descargar PDF</span>
                            <i class="fas fa-download fs-7"></i>
                        </a>
                    @endif
                </div>

                <div class="p-3 rounded-3" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
                    <div class="text-success fw-bold small mb-1"><i class="fas fa-check-circle me-1"></i> Resumen tributario:</div>
                    <div class="text-muted" style="font-size: 11px; line-height: 1.6;">
                        Subtotal sin IGV: <strong class="text-dark">S/ {{ number_format($venta->subtotal_sin_igv ?? ($venta->total - ($venta->impuesto ?? 0)), 2) }}</strong><br>
                        Base Gravada: <strong class="text-dark">S/ {{ number_format($venta->total_gravada ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection