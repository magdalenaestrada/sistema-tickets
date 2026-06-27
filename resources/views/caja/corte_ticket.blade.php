<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Corte de Caja #{{ $caja->id }}</title>
    <style>
        /* ─── Variables de tamaño ─────────────────────── */
        :root {
            --page-width: 80mm;
            /* ticket 80mm por defecto */
            --font-base: 9px;
            --font-title: 11px;
            --font-header: 10px;
            --cell-pad: 3px 5px;
            --logo-h: 45px;
        }

        body.a4 {
            --page-width: 210mm;
            --font-base: 13px;
            --font-title: 16px;
            --font-header: 14px;
            --cell-pad: 6px 8px;
            --logo-h: 70px;
        }

        /* ─── Base ────────────────────────────────────── */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {

            font-size: var(--font-base);
            color: #000;
            background: #fff;
        }

        /* ─── Wrapper de impresión ────────────────────── */
        .ticket-wrap {
            width: var(--page-width);
            margin: 0 auto;
            padding: 6px 8px 12px;
        }

        body.a4 .ticket-wrap {
            padding: 20px 25px 30px;
        }

        /* ─── Cabecera ────────────────────────────────── */
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .header .empresa {
            font-size: var(--font-title);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header .sucursal {
            font-size: var(--font-header);
        }

        .header .doc-title {
            font-weight: bold;
            margin-top: 3px;
            letter-spacing: 1px;
        }

        .header img {
            height: var(--logo-h);
            margin: 4px 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        /* ─── Info filas ──────────────────────────────── */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .info-grid td {
            padding: 1px 2px;
            vertical-align: top;
        }

        /* ─── Tablas de datos ─────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .data-table th {
            background: #000;
            color: #fff;
            text-align: left;
            padding: var(--cell-pad);
            font-size: var(--font-base);
        }

        .data-table td {
            border-bottom: 1px solid #ccc;
            padding: var(--cell-pad);
            font-size: var(--font-base);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table .lbl {
            color: #000000;
        }

        .data-table .val {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
        }

        /* resumen: highlight fila total */
        .row-total td {
            border-top: 1px solid #000;
            font-weight: bold;
        }

        /* ─── Tabla movimientos ───────────────────────── */
        .mov-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .mov-table th {
            background: #000;
            color: #fff;
            padding: var(--cell-pad);
            font-size: var(--font-base);
            text-align: center;
        }

        .mov-table td {
            border: 1px solid #ddd;
            padding: var(--cell-pad);
            font-size: var(--font-base);
            vertical-align: top;
        }

        .mov-table .anulado {
            color: #888;
            text-decoration: line-through;
        }

        .mov-table .tag-anulado {
            display: inline-block;
            background: #c00;
            color: #fff;
            font-size: 7px;
            padding: 0 2px;
            border-radius: 2px;
            font-weight: bold;
        }

        .mov-table .egreso {
            color: #c00;
        }

        .mov-table .ingreso {
            color: #060;
        }

        /* ─── Separador ───────────────────────────────── */
        .dashed {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        /* ─── Firmas ──────────────────────────────────── */
        .firmas {
            display: flex;
            justify-content: space-around;
            margin-top: 18px;
            gap: 12px;
        }

        .firma-box {
            text-align: center;
            flex: 1;
        }

        .firma-line {
            border-top: 1px solid #000;
            margin-bottom: 3px;
        }

        /* ─── Botones (no imprimir) ───────────────────── */
        .no-print {
            text-align: center;
            padding: 16px 0 8px;
        }

        .btn {
            display: inline-block;
            padding: 7px 18px;
            margin: 4px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-family: Arial, sans-serif;
            font-weight: bold;
        }

        .btn-ticket {
            background: #1a1a1a;
            color: #fff;
        }

        .btn-a4 {
            background: #0055cc;
            color: #fff;
        }

        .size-label {
            display: inline-block;
            background: #eee;
            border: 1px solid #aaa;
            border-radius: 3px;
            padding: 2px 8px;
            font-size: 11px;
            font-family: Arial, sans-serif;
            margin-bottom: 8px;
        }

        /* ─── Reglas de impresión ─────────────────────── */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }

            /* ticket: sin márgenes de hoja */
            body:not(.a4) {
                width: 80mm;
            }

            body:not(.a4) @page {
                size: 80mm auto;
                margin: 0;
            }

            body.a4 {
                width: 210mm;
            }

            @page {
                size: var(--print-size, 80mm auto);
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <!-- ══════════════════════════════════════════════
     CONTROLES (sólo pantalla)
═══════════════════════════════════════════════ -->
    <div class="no-print" style="background:#f5f5f5;padding:10px;border-bottom:1px solid #ccc;">
        <div style="font-family:Arial,sans-serif;font-size:13px;margin-bottom:6px;font-weight:bold;">
            Formato de impresión:
        </div>
        <button class="btn btn-ticket" onclick="setSize('ticket')">🧾 Ticket (80mm)</button>
        <button class="btn btn-a4" onclick="setSize('a4')">📄 A4</button>
        <br><br>
        <span class="size-label" id="size-label">Formato actual: <strong>Ticket 80mm</strong></span>
        &nbsp;
        <button class="btn" style="background:#28a745;color:#fff;" onclick="window.print()">🖨️ Imprimir</button>
    </div>

    <!-- ══════════════════════════════════════════════
     CONTENIDO DEL CORTE
═══════════════════════════════════════════════ -->
    <div class="ticket-wrap">

        {{-- CABECERA --}}
        <div class="header">
            @if ($empresaGlobal && $empresaGlobal->logo)
                <img src="{{ asset('storage/' . $empresaGlobal->logo) }}" alt="Logo">
            @endif
            <div class="empresa">{{ $caja->sucursal->empresa->razon_social ?? 'EMPRESA' }}</div>
            <div class="sucursal">{{ $caja->sucursal->nombre_comercial ?? 'Sucursal' }}</div>
            <div class="doc-title">━ CORTE DE CAJA ━</div>
        </div>

        {{-- INFO CAJA --}}
        <table class="info-grid">
            <tr>
                <td><strong>Caja:</strong> #{{ $caja->id }}</td>
                <td style="text-align:right">
                    <strong>Estado:</strong>
                    {{ in_array($caja->estado, ['C', 'cerrada']) ? 'CERRADA' : 'ABIERTA' }}
                </td>
            </tr>
            <tr>
                <td colspan="2"><strong>Cajero:</strong> {{ $caja->usuario->persona->nombre_completo ?? '---' }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Sucursal:</strong> {{ $caja->sucursal->nombre_comercial ?? '---' }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Apertura:</strong>
                    {{ optional($caja->fecha_creacion)->format('d/m/Y h:i A') ?? '---' }}
                </td>
            </tr>
            <tr>
                <td colspan="2"><strong>Cierre:</strong>
                    {{ optional($caja->fecha_cierre)->format('d/m/Y h:i A') ?? 'SIN CIERRE' }}
                </td>
            </tr>
        </table>

        <div class="dashed"></div>

        {{-- RESUMEN --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th colspan="2">RESUMEN GENERAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="lbl">Monto apertura</td>
                    <td class="val">S/ {{ number_format($caja->monto_apertura, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Yape</td>
                    <td class="val">S/ {{ number_format($caja->ingresos_yape, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Plin</td>
                    <td class="val">S/ {{ number_format($caja->ingresos_plin, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Transferencia</td>
                    <td class="val">S/ {{ number_format($caja->ingresos_transferencia, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Tarjeta</td>
                    <td class="val">S/ {{ number_format($caja->ingresos_tarjeta, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Efectivo</td>
                    <td class="val">S/ {{ number_format($caja->ingresos_efectivo, 2) }}</td>
                </tr>
                <tr class="row-total">
                    <td class="lbl">Total ingresos</td>
                    <td class="val">S/ {{ number_format($caja->total_ingresos, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Total egresos</td>
                    <td class="val">S/ {{ number_format($caja->total_salidas, 2) }}</td>
                </tr>
                <tr>
                    <td class="lbl">Egresos efectivo</td>
                    <td class="val">S/ {{ number_format($caja->egresos_efectivo, 2) }}</td>
                </tr>
                <tr class="row-total">
                    <td class="lbl">Efectivo esperado</td>
                    <td class="val">S/ {{ number_format($caja->efectivo_esperado, 2) }}</td>
                </tr>
                <tr class="row-total">
                    <td class="lbl">Saldo sistema</td>
                    <td class="val">S/ {{ number_format($caja->monto_actual, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="dashed"></div>

        {{-- MOVIMIENTOS --}}

        {{-- ── Ticket: columnas simplificadas ── --}}
        <div class="mode-ticket">
            <table class="mov-table">
                <thead>
                    <tr>
                        <th colspan="4">MOVIMIENTOS</th>
                    </tr>
                    <tr>
                        <th>Fecha</th>
                        <th>Ticket</th>
                        <th>Método</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($caja->detalles as $d)
                        <tr>
                            <td>{{ $d->created_at?->format('d/m/y H:i') }}</td>
                            <td>{{ $d->numero_ticket ?? '---' }}</td>
                            <td> {{ collect([$d->metodoPago?->descripcion, $d->billetera_digital?->descripcion])->filter()->implode(' - ') ?:
                                '---' }}
                                @if ($d->anulado)
                                    <span class="tag-anulado">ANUL</span>
                                @endif
                            </td>
                            <td class="{{ $d->amount > 0 ? 'ingreso' : 'egreso' }}"
                                style="text-align:right;white-space:nowrap">
                                {{ $d->amount > 0 ? '+' : '-' }}S/{{ number_format(abs($d->amount), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center">Sin movimientos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── A4: columnas completas ── --}}
        <div class="mode-a4" style="display:none">
            <table class="mov-table">
                <thead>
                    <tr>
                        <th colspan="7">DETALLE DE MOVIMIENTOS</th>
                    </tr>
                    <tr>
                        <th>Fecha</th>
                        <th>Ticket</th>
                        <th>Tipo</th>
                        <th>Subtipo</th>
                        <th>Método</th>
                        <th>Descripción</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($caja->detalles as $d)
                        <tr>
                            <td style="white-space:nowrap">{{ $d->created_at?->format('d/m/Y h:i A') }}</td>
                            <td>{{ $d->numero_ticket ?? '---' }}</td>
                            <td>{{ $d->amount > 0 ? 'Ingreso' : 'Egreso' }}</td>
                            <td>{{ $d->subtipo->descripcion ?? '---' }}</td>
                            <td>{{ $d->metodoPago->descripcion ?? '---' }}</td>
                            <td>
                                <span class="{{ $d->anulado ? 'anulado' : '' }}">{{ $d->description ?? '---' }}</span>
                                @if ($d->anulado)
                                    <span class="tag-anulado">ANULADO</span>
                                @endif
                            </td>
                            <td class="{{ $d->amount > 0 ? 'ingreso' : 'egreso' }}"
                                style="text-align:right;white-space:nowrap">
                                {{ $d->amount > 0 ? '+' : '-' }}S/{{ number_format(abs($d->amount), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center">Sin movimientos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- FIRMAS --}}
        <div class="firmas">
            <div class="firma-box">
                <div class="firma-line"></div>
                <div>Firma cajero</div>
            </div>
            <div class="firma-box">
                <div class="firma-line"></div>
                <div>Firma supervisor</div>
            </div>
        </div>

    </div><!-- /ticket-wrap -->

    <script>
        function setSize(size) {
            const body = document.body;
            const label = document.getElementById('size-label');
            const tmov = document.querySelector('.mode-ticket');
            const amov = document.querySelector('.mode-a4');

            if (size === 'a4') {
                body.classList.add('a4');
                label.innerHTML = 'Formato actual: <strong>A4</strong>';
                tmov.style.display = 'none';
                amov.style.display = '';
                // ajusta @page para impresión A4
                let style = document.getElementById('print-size-style');
                if (!style) {
                    style = document.createElement('style');
                    style.id = 'print-size-style';
                    document.head.appendChild(style);
                }
                style.textContent = '@media print { @page { size: A4; margin: 12mm 15mm; } }';
            } else {
                body.classList.remove('a4');
                label.innerHTML = 'Formato actual: <strong>Ticket 80mm</strong>';
                tmov.style.display = '';
                amov.style.display = 'none';
                let style = document.getElementById('print-size-style');
                if (!style) {
                    style = document.createElement('style');
                    style.id = 'print-size-style';
                    document.head.appendChild(style);
                }
                style.textContent = '@media print { @page { size: 80mm auto; margin: 0; } }';
            }
        }

        // Inicializar como ticket
        setSize('ticket');
    </script>

</body>

</html>
