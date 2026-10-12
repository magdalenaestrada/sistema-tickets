@extends('layouts.app')

@section('title', 'Detalle del comprobante · ' . $venta->serie . '-' . $venta->numero)

@section('content')
    @php
        // EstadoVenta es un enum en el modelo; también admite valores string.
        $estadoOriginal = $venta->estado;
        $estadoVal =
            $estadoOriginal instanceof \BackedEnum
                ? (string) $estadoOriginal->value
                : ($estadoOriginal instanceof \UnitEnum
                    ? $estadoOriginal->name
                    : (string) ($estadoOriginal ?? 'SIN ESTADO'));
        $esAnulado = in_array(strtolower($estadoVal), ['anulado', 'rechazado'], true);

        // Las relaciones persona, tipoDocumentoFactura y detalles pertenecen a Venta.
        $detalles = $venta->detalles ?? collect();
        $numeroComprobante = trim((string) ($venta->serie ?? '') . '-' . (string) ($venta->numero ?? ''), '-');
        $nombreCliente = $venta->persona?->nombre_completo ?: 'CLIENTE VARIOS';

        // Compatible con atributos casteados como datetime y con fechas string.
        $fechaEmisionFormateada = 'No registrada';
        if (!empty($venta->fecha_emision)) {
            try {
                $fechaEmisionFormateada = \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y H:i');
            } catch (\Throwable $e) {
                $fechaEmisionFormateada = 'Fecha no válida';
            }
        }

        // No se asume que las rutas estén registradas en todas las instalaciones.
        $puedeVerPdf = filled($venta->ruta_pdf) && \Illuminate\Support\Facades\Route::has('facturacion.pdf');
        $puedeVerXml = filled($venta->ruta_xml) && \Illuminate\Support\Facades\Route::has('facturacion.xml');
    @endphp

    <style>
        .comprobante-page {
            --bg: #f4f7fb;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --line: #e5eaf2;
            --line-strong: #d8e0ec;
            --text: #172338;
            --muted: #718096;
            --primary: #3659a8;
            --primary-dark: #284886;
            --primary-soft: #eef3ff;
            --danger: #b42318;
            --danger-bg: #fff1f0;
            --danger-line: #ffd4d0;
            --shadow: 0 12px 32px rgba(25, 42, 70, .055), 0 2px 5px rgba(25, 42, 70, .035);
            --radius: 18px;

            background: var(--bg);
            min-height: 100vh;
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        }


        .comprobante-page .page-container {
            width: min(1440px, calc(100% - 48px));
            margin: 0 auto;
            padding: 26px 0 42px;
        }

        .comprobante-page .breadcrumbs {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 20px;
        }

        .comprobante-page .breadcrumbs .current {
            color: #35445d;
            font-weight: 650;
        }

        .comprobante-page .crumb-separator {
            color: #a6b0c0;
        }

        .comprobante-page .hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 24px;
        }

        .comprobante-page .hero-copy {
            min-width: 0;
        }

        .comprobante-page .eyebrow {
            color: var(--primary);
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: .09em;
            font-size: 10px;
            margin-bottom: 7px;
        }

        .comprobante-page h1 {
            font-size: clamp(25px, 3vw, 32px);
            line-height: 1.15;
            letter-spacing: -.045em;
            margin: 0;
            font-weight: 780;
            color: var(--text);
        }

        .comprobante-page .hero p {
            margin: 9px 0 0;
            color: var(--muted);
            max-width: 650px;
        }

        .comprobante-page .hero-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 9px;
        }

        .comprobante-page .btn-custom {
            border: 1px solid var(--line-strong);
            background: var(--surface);
            color: #34425a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 9px 13px;
            border-radius: 10px;
            cursor: pointer;
            transition: border-color .16s, background .16s, transform .16s, box-shadow .16s;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 680;
            text-decoration: none;
        }

        .comprobante-page .btn-custom:hover {
            border-color: #aab9d2;
            background: #fbfcff;
            box-shadow: 0 3px 9px rgba(31, 48, 77, .05);
            color: #172338;
        }

        .comprobante-page .btn-custom.btn-primary-custom {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .comprobante-page .btn-custom.btn-primary-custom:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: #fff;
        }

        .comprobante-page .btn-custom svg {
            width: 15px;
            height: 15px;
            flex: 0 0 auto;
        }

        .comprobante-page .notice {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px 17px;
            margin-bottom: 22px;
            border: 1px solid var(--danger-line);
            background: linear-gradient(105deg, #fff8f7, #fffdfd);
            border-radius: 14px;
            color: #7a271a;
        }

        .comprobante-page .notice-icon {
            flex: 0 0 auto;
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #ffe5e1;
        }

        .comprobante-page .notice strong {
            display: block;
            font-size: 13px;
            margin: 1px 0 3px;
        }

        .comprobante-page .notice p {
            margin: 0;
            color: #8d4a43;
            font-size: 12px;
        }

        .comprobante-page .workspace-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.75fr) minmax(290px, .82fr);
            align-items: start;
            gap: 20px;
        }

        .comprobante-page .main-column,
        .comprobante-page .side-column {
            display: grid;
            gap: 20px;
            min-width: 0;
        }

        .comprobante-page .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .comprobante-page .card-custom {
            min-width: 0;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .comprobante-page .card-custom-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            padding: 19px 21px 15px;
            border-bottom: 1px solid #edf0f5;
        }

        .comprobante-page .card-title-wrap {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            min-width: 0;
        }

        .comprobante-page .section-icon {
            display: grid;
            place-items: center;
            width: 35px;
            height: 35px;
            flex: 0 0 auto;
            border: 1px solid #e0e8fb;
            background: var(--primary-soft);
            color: var(--primary);
            border-radius: 11px;
        }

        .comprobante-page .section-icon svg {
            width: 17px;
            height: 17px;
        }

        .comprobante-page .card-custom h2 {
            margin: 0;
            font-size: 14px;
            letter-spacing: -.01em;
            font-weight: 760;
            color: var(--text);
        }

        .comprobante-page .card-subtitle {
            margin-top: 3px;
            font-size: 11px;
            color: var(--muted);
        }

        .comprobante-page .card-custom-body {
            padding: 17px 21px 21px;
        }

        .comprobante-page .detail-list {
            display: grid;
            gap: 0;
        }

        .comprobante-page .detail-row {
            display: grid;
            grid-template-columns: minmax(85px, .8fr) minmax(0, 1.2fr);
            gap: 10px;
            padding: 11px 0;
            border-bottom: 1px solid #f0f2f6;
        }

        .comprobante-page .detail-row:first-child {
            padding-top: 0;
        }

        .comprobante-page .detail-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .comprobante-page .detail-label {
            color: var(--muted);
            font-size: 12px;
        }

        .comprobante-page .detail-value {
            min-width: 0;
            overflow-wrap: anywhere;
            font-size: 12px;
            font-weight: 650;
            color: #26364f;
        }

        .comprobante-page .detail-value.muted {
            color: #8691a3;
            font-weight: 500;
            font-style: italic;
        }

        .comprobante-page .table-wrap {
            overflow-x: auto;
        }

        .comprobante-page .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 12px;
        }

        .comprobante-page .custom-table thead {
            background: #f8fafd;
        }

        .comprobante-page .custom-table th {
            color: #71819a;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .055em;
            font-weight: 750;
            white-space: nowrap;
            padding: 13px 15px;
            border-bottom: 1px solid var(--line);
        }

        .comprobante-page .custom-table td {
            padding: 15px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: top;
            color: #35445b;
        }

        .comprobante-page .custom-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .comprobante-page .custom-table tbody tr:hover {
            background: #fbfcff;
        }

        .comprobante-page .code-tag {
            background: #f1f4f9;
            border: 1px solid #e5eaf2;
            color: #576780;
            border-radius: 6px;
            padding: 4px 6px;
            font-size: 10px;
            font-weight: 750;
        }

        .comprobante-page .description-main {
            font-weight: 670;
            color: #25344b;
            min-width: 220px;
        }

        .comprobante-page .numeric {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .comprobante-page .amount-strong {
            color: #1b2d49;
            font-weight: 780;
        }

        .comprobante-page .table-footer {
            border-top: 1px solid var(--line);
            padding: 12px 17px;
            color: var(--muted);
            background: #fcfdff;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .comprobante-page .summary-card {
            overflow: hidden;
        }

        .comprobante-page .summary-top {
            padding: 23px 22px 21px;
            background: linear-gradient(145deg, #294a8e, #3b61b0);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .comprobante-page .summary-top:after {
            content: "";
            position: absolute;
            width: 175px;
            height: 175px;
            right: -62px;
            top: -78px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .14);
            box-shadow: 0 0 0 22px rgba(255, 255, 255, .035), 0 0 0 45px rgba(255, 255, 255, .025);
        }

        .comprobante-page .summary-kicker {
            color: rgba(255, 255, 255, .78);
            text-transform: uppercase;
            letter-spacing: .09em;
            font-size: 10px;
            font-weight: 750;
            position: relative;
            z-index: 1;
        }

        .comprobante-page .grand-total {
            font-size: clamp(28px, 3vw, 34px);
            font-weight: 800;
            letter-spacing: -.045em;
            margin-top: 8px;
            position: relative;
            z-index: 1;
            line-height: 1.1;
        }

        .comprobante-page .currency-note {
            color: rgba(255, 255, 255, .72);
            margin-top: 7px;
            font-size: 11px;
            position: relative;
            z-index: 1;
        }

        .comprobante-page .summary-meta {
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            position: relative;
            z-index: 1;
        }

        .comprobante-page .summary-ref {
            color: rgba(255, 255, 255, .9);
            font-size: 11px;
        }

        .comprobante-page .summary-state {
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .24);
            padding: 5px 8px;
            border-radius: 7px;
            color: white;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .045em;
            text-transform: uppercase;
        }

        .comprobante-page .summary-body {
            padding: 20px 22px 21px;
        }

        .comprobante-page .summary-lines {
            display: grid;
            gap: 13px;
        }

        .comprobante-page .summary-line {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
            font-size: 12px;
        }

        .comprobante-page .summary-line .label {
            color: var(--muted);
        }

        .comprobante-page .summary-line .value {
            color: #34435a;
            font-weight: 670;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .comprobante-page .summary-divider {
            height: 1px;
            background: var(--line);
            margin: 18px 0;
        }

        .comprobante-page .summary-line.total .label,
        .comprobante-page .summary-line.total .value {
            color: #1d2e49;
            font-weight: 800;
            font-size: 14px;
        }

        .comprobante-page .tax-note {
            background: #f7f9fd;
            border: 1px solid #edf1f7;
            padding: 12px;
            border-radius: 10px;
            color: #7b879a;
            font-size: 10px;
            margin-top: 18px;
            line-height: 1.55;
        }

        .comprobante-page .tax-note strong {
            color: #58677f;
        }

        .comprobante-page .summary-actions {
            display: grid;
            gap: 9px;
            margin-top: 18px;
        }

        .comprobante-page .summary-actions .btn-custom {
            width: 100%;
        }

        .comprobante-page .mini-card {
            padding: 17px 19px;
        }

        .comprobante-page .mini-heading {
            display: flex;
            align-items: center;
            gap: 9px;
            font-weight: 760;
            font-size: 12px;
            margin-bottom: 13px;
            color: var(--text);
        }

        .comprobante-page .mini-heading svg {
            color: var(--primary);
            width: 17px;
            height: 17px;
        }

        .comprobante-page .mini-detail {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            font-size: 11px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f2f6;
        }

        .comprobante-page .mini-detail:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .comprobante-page .mini-detail span {
            color: var(--muted);
        }

        .comprobante-page .mini-detail strong {
            color: #394960;
            text-align: right;
            font-weight: 670;
        }

        .comprobante-page .footer-note {
            color: #94a0b1;
            font-size: 10px;
            text-align: center;
            padding: 26px 8px 0;
        }

        @media (max-width: 1000px) {
            .comprobante-page .workspace-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .comprobante-page .side-column {
                grid-template-columns: minmax(0, 1.1fr) minmax(230px, .9fr);
                align-items: start;
            }
        }

        @media (max-width: 700px) {
            .comprobante-page .page-container {
                width: min(100% - 28px, 1440px);
                padding-top: 20px;
            }

            .comprobante-page .hero {
                display: block;
            }

            .comprobante-page .hero-actions {
                justify-content: flex-start;
                margin-top: 18px;
            }

            .comprobante-page .info-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .comprobante-page .side-column {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>

    <div class="comprobante-page">
        <main class="page-container">

            <!-- BREADCRUMBS -->
            <nav class="breadcrumbs" aria-label="Ruta de navegación">
                <span>Inicio</span>
                <span class="crumb-separator">/</span>
                <span>Comprobantes</span>
                <span class="crumb-separator">/</span>
                <span class="current">{{ $numeroComprobante }}</span>
            </nav>

            <!-- HERO HEADER -->
            <section class="hero" aria-labelledby="pageTitle">
                <div class="hero-copy">
                    <div class="eyebrow">Consulta de documento</div>
                    <h1 id="pageTitle">Detalle del comprobante</h1>
                    <p>Revisa la información del cliente, los conceptos registrados y el resumen de importes.</p>
                </div>
                <div class="hero-actions">
                    <button class="btn-custom" type="button" id="copiar-numero-comprobante"
                        data-numero="{{ $numeroComprobante }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="8" y="8" width="12" height="12" rx="2" />
                            <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3" />
                        </svg>
                        <span class="copy-label">Copiar número</span>
                    </button>
                    @if ($puedeVerPdf)
                        <a class="btn-custom" href="{{ route('facturacion.pdf', $venta) }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3v12m-4-4 4 4 4-4" />
                                <path d="M5 17v3h14v-3" />
                            </svg>
                            Exportar PDF
                        </a>
                    @endif
                    @if ($puedeVerPdf)
                        <a class="btn-custom btn-primary-custom" href="{{ route('facturacion.pdf', $venta) }}"
                            target="_blank">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                <path d="M7 14h10v7H7z" />
                                <path d="M17 11h.01" />
                            </svg>
                            Imprimir
                        </a>
                    @endif
                </div>
            </section>

            <!-- ALERTA SI ESTÁ ANULADO O RECHAZADO -->
            @if ($esAnulado)
                <div class="notice" role="status">
                    <div class="notice-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#b42318"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 9v4m0 4h.01" />
                            <path d="M10.3 3.9 2.7 17a2 2 0 0 0 1.73 3h15.14a2 2 0 0 0 1.73-3L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                        </svg>
                    </div>
                    <div>
                        <strong>Este comprobante figura como {{ strtolower($estadoVal) }}</strong>
                        <p>Se muestra el detalle histórico registrado para facilitar la consulta. Verifica el estado antes
                            de usar esta información en una nueva operación.</p>
                    </div>
                </div>
            @endif

            <div class="workspace-grid">
                <div class="main-column">
                    <div class="info-grid">

                        <!-- INFORMACIÓN DEL CLIENTE -->
                        <section class="card-custom" aria-labelledby="clientHeading">
                            <div class="card-custom-header">
                                <div class="card-title-wrap">
                                    <div class="section-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="9" cy="8" r="3.5" />
                                            <path
                                                d="M2.8 20a6.2 6.2 0 0 1 12.4 0M16 5.2a3.5 3.5 0 0 1 0 6.6M17.3 14.2a5.9 5.9 0 0 1 3.9 5.6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 id="clientHeading">Información del cliente</h2>
                                        <div class="card-subtitle">Datos asociados al comprobante</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-custom-body">
                                <div class="detail-list">
                                    <div class="detail-row">
                                        <div class="detail-label">Cliente</div>
                                        <div class="detail-value">{{ $nombreCliente }}</div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-label">Documento</div>
                                        <div class="detail-value">{{ $venta->persona?->documento ?: 'No registrado' }}</div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-label">Dirección</div>
                                        <div class="detail-value {{ empty($venta->persona?->direccion) ? 'muted' : '' }}">
                                            {{ $venta->direccion_alternativa ?: ($venta->persona?->direccion ?: 'No registrada') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- INFORMACIÓN DEL COMPROBANTE -->
                        <section class="card-custom" aria-labelledby="receiptHeading">
                            <div class="card-custom-header">
                                <div class="card-title-wrap">
                                    <div class="section-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M6 3.5h12a1 1 0 0 1 1 1v16l-3-1.8-3 1.8-3-1.8-3 1.8v-16a1 1 0 0 1 1-1Z" />
                                            <path d="M9 8h6M9 12h6M9 16h3" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 id="receiptHeading">Información del comprobante</h2>
                                        <div class="card-subtitle">Identificación y emisión</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-custom-body">
                                <div class="detail-list">
                                    <div class="detail-row">
                                        <div class="detail-label">Número</div>
                                        <div class="detail-value">{{ $numeroComprobante }}</div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-label">Tipo</div>
                                        <div class="detail-value">
                                            {{ $venta->tipoDocumentoFactura?->descripcion ?? 'Comprobante' }}</div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-label">Fecha y hora</div>
                                        <div class="detail-value">{{ $fechaEmisionFormateada }}</div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-label">Estado</div>
                                        <div class="detail-value text-uppercase">{{ $estadoVal }}</div>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>

                    <!-- TABLA DE DETALLES / CONCEPTOS -->
                    <section class="card-custom" aria-labelledby="itemsHeading">
                        <div class="card-custom-header" style="align-items: center;">
                            <div class="card-title-wrap">
                                <div class="section-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 5h16v14H4z" />
                                        <path d="M4 10h16M9 5v14M15 5v14" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 id="itemsHeading">Detalle de conceptos</h2>
                                    <div class="card-subtitle">Productos y servicios incluidos en el comprobante</div>
                                </div>
                            </div>
                            <span
                                style="color: #5f6f87; background: #f1f5fa; padding: 5px 9px; border-radius: 8px; font-size: 11px; font-weight: 750;">
                                {{ $detalles->count() }} conceptos
                            </span>
                        </div>

                        <div class="table-wrap">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th class="numeric">Cantidad</th>
                                        <th class="numeric">Valor unitario</th>
                                        <th class="numeric">Subtotal</th>
                                        <th class="numeric">IGV</th>
                                        <th class="numeric">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detalles as $detalle)
                                        @php
                                            $cantidad = (float) ($detalle->cantidad ?? 0);
                                            $valorUnitario = (float) ($detalle->valor_unitario ?? 0);
                                            $igvDetalle = (float) ($detalle->igv ?? 0);
                                            $baseIgvDetalle = (float) ($detalle->base_igv ?? 0);
                                            $sumaIgvDetalle = (float) $detalle->sum('igv');
                                            $totalDetalle =
                                                $detalle->total ??
                                                ($detalle->valor_venta ?? $cantidad * $valorUnitario) + $igvDetalle;
                                        @endphp
                                        <tr>
                                            <td><span class="code-tag">{{ $detalle->codigo ?: '—' }}</span></td>
                                            <td>
                                                <div class="description-main">
                                                    {{ $detalle->descripcion ?: 'Sin descripción' }}</div>
                                            </td>
                                            <td class="numeric">{{ number_format($cantidad, 2) }}</td>
                                            <td class="numeric">S/ {{ number_format($valorUnitario, 2) }}</td>
                                            <td class="numeric">S/ {{ number_format($baseIgvDetalle, 2) }}</td>
                                            <td class="numeric">S/ {{ number_format($igvDetalle, 2) }}</td>
                                            <td class="numeric amount-strong">S/
                                                {{ number_format((float) $totalDetalle, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="text-align:center; color:#718096; padding:22px;">No
                                                hay conceptos registrados en este comprobante.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="table-footer">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 11v5m0-8h.01" />
                            </svg>
                            <span>Los importes se presentan en soles (PEN).</span>
                        </div>
                    </section>

                </div>

                <!-- COLUMNA LATERAL (RESUMEN FINANCIERO) -->
                <aside class="side-column" aria-label="Resumen del comprobante">

                    <section class="card-custom summary-card" aria-labelledby="summaryHeading">
                        <div class="summary-top">
                            <div class="summary-kicker" id="summaryHeading">Importe total registrado</div>
                            <div class="grand-total">S/ {{ number_format((float) ($venta->total ?? 0), 2) }}</div>
                            <div class="currency-note">Soles peruanos · PEN</div>
                            <div class="summary-meta">
                                <span class="summary-ref">Comprobante {{ $numeroComprobante }}</span>
                                <span class="summary-state">{{ $estadoVal }}</span>
                            </div>
                        </div>

                        <div class="summary-body">
                            <!-- DESGLOSE DE OPERACIONES Y TRIBUTOS -->
                            <div class="summary-lines">
                                @php
                                    $sumaIgvDetalle = (float) $venta->detalles()->sum('igv');
                                    $sumaSubtotalDetalle = (float) $venta->detalles()->sum('base_igv');
                                @endphp
                                <div class="summary-line">
                                    <span class="label">Subtotal</span>
                                    <span class="value">S/ {{ number_format((float) ($sumaSubtotalDetalle ?? 0), 2) }}</span>
                                </div>
                                <div class="summary-line">
                                    <span class="label">IGV</span>
                                    <span class="value">S/ {{ number_format((float) ($sumaIgvDetalle ?? 0), 2) }}</span>
                                </div>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-line total">
                                <span class="label">Total</span>
                                <span class="value">S/ {{ number_format((float) ($venta->total ?? 0), 2) }}</span>
                            </div>

                            <div class="tax-note">
                                <strong>Importes registrados en la venta</strong>
                                @if (!is_null($venta->subtotal_sin_igv))
                                    <div>Subtotal sin IGV: S/ {{ number_format((float) $venta->subtotal_sin_igv, 2) }}
                                    </div>
                                @endif
                                @if (!is_null($venta->total_sin_igv))
                                    <div>Total sin IGV: S/ {{ number_format((float) $venta->total_sin_igv, 2) }}</div>
                                @endif
                            </div>

                            <div class="summary-actions">
                                @if ($puedeVerPdf)
                                    <a class="btn-custom btn-primary-custom"
                                        href="{{ route('facturacion.pdf', $venta) }}" target="_blank">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                            <path d="M7 14h10v7H7z" />
                                        </svg>
                                        Imprimir comprobante
                                    </a>
                                @endif
                                @if ($venta->ruta_xml)
                                    <a class="btn-custom" href="{{ route('facturacion.xml', $venta) }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 3v12m-4-4 4 4 4-4" />
                                            <path d="M5 17v3h14v-3" />
                                        </svg>
                                        Descargar XML
                                    </a>
                                @endif

                                @if ($venta->ruta_cdr)
                                    <a class="btn-custom" href="{{ route('facturacion.cdr', $venta) }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 3v12m-4-4 4 4 4-4" />
                                            <path d="M5 17v3h14v-3" />
                                        </svg>
                                        Descargar CDR
                                    </a>
                                @endif

                                @if ($venta->ruta_pdf)
                                    <a class="btn-custom" href="{{ route('facturacion.pdf', $venta) }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 3v12m-4-4 4 4 4-4" />
                                            <path d="M5 17v3h14v-3" />
                                        </svg>
                                        Descargar PDF
                                    </a>
                                @endif

                            </div>
                        </div>
                    </section>


                    <!-- MINI TARJETA DE REFERENCIA -->
                    <section class="card-custom mini-card" aria-labelledby="traceHeading">
                        <div class="mini-heading" id="traceHeading">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 11v5m0-8h.01" />
                            </svg>
                            Datos de referencia
                        </div>
                        <div class="mini-detail"><span>Número</span><strong>{{ $numeroComprobante }}</strong></div>
                        <div class="mini-detail"><span>Tipo de
                                documento</span><strong>{{ $venta->tipoDocumentoFactura?->descripcion ?? 'Comprobante' }}</strong>
                        </div>
                        <div class="mini-detail"><span>Fecha de
                                emisión</span><strong>{{ $fechaEmisionFormateada }}</strong></div>
                        <div class="mini-detail"><span>Estado</span><strong
                                class="text-uppercase">{{ $estadoVal }}</strong>
                        </div>
                    </section>

                </aside>
            </div>

            <div class="footer-note">Detalle del comprobante · Consulta de venta</div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const boton = document.getElementById('copiar-numero-comprobante');
            if (!boton) return;
            boton.addEventListener('click', async function() {
                const numero = boton.dataset.numero || '';
                const etiqueta = boton.querySelector('.copy-label');
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(numero);
                    } else {
                        const textarea = document.createElement('textarea');
                        textarea.value = numero;
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        textarea.select();
                        const correcto = document.execCommand('copy');
                        textarea.remove();
                        if (!correcto) throw new Error('No se pudo copiar');
                    }
                    if (etiqueta) {
                        etiqueta.textContent = 'Copiado';
                        setTimeout(() => etiqueta.textContent = 'Copiar número', 1800);
                    }
                } catch (error) {
                    if (etiqueta) etiqueta.textContent = 'No se pudo copiar';
                }
            });
        });
    </script>
@endsection
