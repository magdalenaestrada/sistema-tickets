@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <!-- Header Principal -->
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary mb-1">
                <i class="bi bi-box-seam me-2"></i>Rastreo y Consulta de Encomiendas
            </h3>
            <p class="text-muted">Consulte el estado actual, trayecto y detalles de su envío en tiempo real.</p>
        </div>

        <!-- Tarjeta Principal de Búsqueda -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <form id="formConsulta">
                    <div class="row g-3 justify-content-center">
                        <div class="col-md-8 col-lg-7">
                            <label for="inputCodigo" class="form-label font-medium text-secondary fw-semibold">
                                Número de Guía / Código de Encomienda o DNI
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="inputCodigo"
                                    class="form-control border-start-0 text-uppercase fw-bold text-dark px-2"
                                    placeholder="Ej: EC-0012345 o DNI / RUC" autofocus autocomplete="off">
                                <button type="submit" class="btn btn-primary px-4 font-semibold" id="btnBuscar">
                                    <span class="spinner-border spinner-border-sm d-none me-1" id="spinner"></span>
                                    <span id="btnTexto">Consultar</span>
                                </button>
                            </div>
                            <div class="form-text mt-2 text-muted">
                                <i class="bi bi-info-circle me-1"></i> Ingrese el código impreso en su comprobante o el
                                número de documento del consignatario.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mensaje de error -->
        <div id="mensajeError" class="alert alert-danger d-none text-center shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle fs-5 me-2"></i>
            <span id="textoError">No se encontró ninguna encomienda con el código o documento ingresado.</span>
        </div>

        <!-- Contenedor de Resultado (Oculto inicialmente) -->
        <div id="resultadoConsulta" class="d-none">

            <!-- Ficha de Estado General -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <!-- Encabezado de Resultados -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-4 gap-2">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold d-block">Código de Envío</span>
                            <h4 class="mb-0 fw-bold text-primary" id="resCodigo">EC-000000</h4>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge px-3 py-2 fs-6 border" id="resEstado">
                                <!-- Estado dinámico -->
                            </span>
                            <a href="#" target="_blank" class="btn btn-outline-secondary btn-sm d-none"
                                id="btnImprimirTicket">
                                <i class="bi bi-printer me-1"></i> Imprimir Ticket
                            </a>
                            <a href="#" target="_blank" class="btn btn-outline-primary btn-sm d-none"
                                id="btnImprimirComprobante">
                                <i class="bi bi-file-earmark-text me-1"></i> Ver Comprobante
                            </a>
                        </div>
                    </div>

                    <!-- Línea del Tiempo (Stepper Corregido) -->
                    <div class="position-relative my-4 px-2">
                        <div class="row text-center position-relative z-1">
                            <!-- Paso 1: Registrado -->
                            <div class="col-3">
                                <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 shadow-sm bg-secondary"
                                    style="width: 48px; height: 48px;" id="stepBgRegistrado">
                                    <i data-lucide="receipt"></i>
                                </div>
                                <div class="fw-bold small text-dark d-block">Registrado</div>
                            </div>

                            <!-- Paso 2: En Tránsito -->
                            <div class="col-3">
                                <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 shadow-sm bg-secondary"
                                    style="width: 48px; height: 48px;" id="stepBgTransito">
                                    <i data-lucide="truck"></i>
                                </div>
                                <div class="fw-bold small text-dark d-block">En Tránsito</div>
                            </div>

                            <!-- Paso 3: En Agencia -->
                            <div class="col-3">
                                <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 shadow-sm bg-secondary"
                                    style="width: 48px; height: 48px;" id="stepBgLlegada">
                                    <i data-lucide="map-pin"></i>
                                </div>
                                <div class="fw-bold small text-dark d-block">En Agencia</div>
                            </div>

                            <!-- Paso 4: Entregado -->
                            <div class="col-3">
                                <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 shadow-sm bg-secondary"
                                    style="width: 48px; height: 48px;" id="stepBgEntregado">
                                    <i data-lucide="circle-check"></i>
                                </div>
                                <div class="fw-bold small text-dark d-block">Entregado</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles de Origen, Destino y Personas -->
            <div class="row g-3 mb-4">
                <!-- Trayecto -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white font-semibold text-secondary py-3 border-0 fw-bold">
                            <i class="bi bi-signpost-split me-2 text-primary"></i> Trayecto
                        </div>
                        <div class="card-body pt-0">
                            <div class="mb-3">
                                <small class="text-muted d-block text-uppercase font-xs">Agencia Origen</small>
                                <span class="fw-bold text-dark fs-6" id="resOrigen">-</span>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase font-xs">Agencia Destino</small>
                                <span class="fw-bold text-dark fs-6" id="resDestino">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remitente -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white font-semibold text-secondary py-3 border-0 fw-bold">
                            <i class="bi bi-person-up me-2 text-primary"></i> Remitente (Envía)
                        </div>
                        <div class="card-body pt-0">
                            <div class="fw-bold text-dark mb-2 fs-6" id="resEmisorNombre">-</div>
                            <div class="text-muted small mb-1">
                                <i class="bi bi-card-heading me-1"></i> Doc: <span id="resEmisorDoc"
                                    class="text-dark fw-semibold">-</span>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-telephone me-1"></i> Tel: <span id="resEmisorTel"
                                    class="text-dark fw-semibold">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Destinatario -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white font-semibold text-secondary py-3 border-0 fw-bold">
                            <i class="bi bi-person-down me-2 text-primary"></i> Destinatario (Recibe)
                        </div>
                        <div class="card-body pt-0">
                            <div class="fw-bold text-dark mb-2 fs-6" id="resReceptorNombre">-</div>
                            <div class="text-muted small mb-1">
                                <i class="bi bi-card-heading me-1"></i> Doc: <span id="resReceptorDoc"
                                    class="text-dark fw-semibold">-</span>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-telephone me-1"></i> Tel: <span id="resReceptorTel"
                                    class="text-dark fw-semibold">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Bultos / Detalles -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white font-semibold text-secondary py-3 border-0 fw-bold">
                    <i class="bi bi-box me-2 text-primary"></i> Detalle del Contenido
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaDetalles">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Item</th>
                                <th>Tipo / Empaque</th>
                                <th>Descripción</th>
                                <th class="text-center">Peso (Kg)</th>
                                <th class="text-end pe-4">Monto</th>
                            </tr>
                        </thead>
                        <tbody id="resDetallesBody">
                            <!-- Dinámico vía JS -->
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold fs-6">Total:</td>
                                <td class="text-end pe-4 fw-bold fs-5 text-primary" id="resTotalMonto">S/ 0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formConsulta = document.getElementById('formConsulta');
            const inputCodigo = document.getElementById('inputCodigo');
            const btnBuscar = document.getElementById('btnBuscar');
            const btnTexto = document.getElementById('btnTexto');
            const spinner = document.getElementById('spinner');

            const mensajeError = document.getElementById('mensajeError');
            const textoError = document.getElementById('textoError');
            const resultadoConsulta = document.getElementById('resultadoConsulta');

            formConsulta.addEventListener('submit', async function(e) {
                e.preventDefault();

                const codigo = inputCodigo.value.trim();
                if (!codigo) return;

                btnBuscar.disabled = true;
                spinner.classList.remove('d-none');
                btnTexto.textContent = 'Buscando...';
                mensajeError.classList.add('d-none');
                resultadoConsulta.classList.add('d-none');

                try {
                    const response = await fetch(
                        `{{ route('encomiendas.consulta.buscar') }}?codigo=${encodeURIComponent(codigo)}`
                    );
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.error || 'No se encontró la encomienda ingresada.');
                    }

                    poblarDatos(result.encomienda);
                    resultadoConsulta.classList.remove('d-none');

                } catch (err) {
                    textoError.textContent = err.message;
                    mensajeError.classList.remove('d-none');
                } finally {
                    btnBuscar.disabled = false;
                    spinner.classList.add('d-none');
                    btnTexto.textContent = 'Consultar';
                }
            });

            function poblarDatos(data) {
                document.getElementById('resCodigo').textContent = data.codigo || '-';

                const badge = document.getElementById('resEstado');
                const estadoConfig = getEstadoConfig(data.estado);

                badge.className = `badge px-3 py-2 fs-6 border ${estadoConfig.badge}`;
                badge.innerHTML = `
            <i data-lucide="${estadoConfig.icon}" class="me-1" width="16" height="16"></i>
            ${estadoConfig.label}
        `;

                if (window.lucide) {
                    lucide.createIcons();
                }

                const btnTicket = document.getElementById('btnImprimirTicket');
                btnTicket.href = `/encomiendas/${data.id}/imprimir-ticket`;
                btnTicket.classList.remove('d-none');

                const btnComprobante = document.getElementById('btnImprimirComprobante');
                if (data.venta_id) {
                    btnComprobante.href = `/ventas/${data.venta_id}/comprobante`;
                    btnComprobante.classList.remove('d-none');
                } else {
                    btnComprobante.classList.add('d-none');
                }

                actualizarLineaTiempo(data.estado);

                document.getElementById('resOrigen').textContent = data.sucursal_origen ? data.sucursal_origen
                    .nombre : (data.origen_pueblito ? data.origen_pueblito.descripcion : '-');
                document.getElementById('resDestino').textContent = data.sucursal_destino ? data.sucursal_destino
                    .nombre : (data.destino_pueblito ? data.destino_pueblito.descripcion : '-');

                if (data.emisor) {
                    document.getElementById('resEmisorNombre').textContent = data.emisor.nombre_completo || (data
                        .emisor.nombres + ' ' + (data.emisor.apellidos || ''));
                    document.getElementById('resEmisorDoc').textContent = data.emisor.documento || '-';
                    document.getElementById('resEmisorTel').textContent = data.emisor.celular || '-';
                }

                if (data.receptor) {
                    document.getElementById('resReceptorNombre').textContent = data.receptor.nombre_completo || (
                        data.receptor.nombres + ' ' + (data.receptor.apellidos || ''));
                    document.getElementById('resReceptorDoc').textContent = data.receptor.documento || '-';
                    document.getElementById('resReceptorTel').textContent = data.receptor.celular || '-';
                }

                const tbody = document.getElementById('resDetallesBody');
                tbody.innerHTML = '';

                if (data.detalles && data.detalles.length > 0) {
                    data.detalles.forEach((item, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                    <td class="ps-4 text-muted font-medium">${index + 1}</td>
                    <td>${item.tipo_encomienda ? item.tipo_encomienda.descripcion : '-'}</td>
                    <td>${item.descripcion || '-'}</td>
                    <td class="text-center">${item.peso || '-'}</td>
                    <td class="text-end pe-4 fw-bold">S/ ${parseFloat(item.costo || 0).toFixed(2)}</td>
                `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML =
                        '<tr><td colspan="5" class="text-center text-muted py-3">Sin bultos registrados</td></tr>';
                }

                document.getElementById('resTotalMonto').textContent = 'S/ ' + parseFloat(data.total || 0).toFixed(
                    2);
            }

            function getEstadoConfig(estado) {
                switch (estado) {
                    case 'SA':
                    case 'SA1':
                        return {
                            label: 'REGISTRADO', badge:
                                'bg-secondary-subtle text-secondary border-secondary-subtle', icon: 'receipt'
                        };
                    case 'EC':
                        return {
                            label: 'EN TRÁNSITO', badge: 'bg-primary-subtle text-primary border-primary-subtle',
                                icon: 'truck'
                        };
                    case 'PE':
                        return {
                            label: 'EN AGENCIA', badge: 'bg-warning-subtle text-warning border-warning-subtle',
                                icon: 'map-pin'
                        };
                    case 'ET':
                        return {
                            label: 'ENTREGADO', badge: 'bg-success-subtle text-success border-success-subtle', icon:
                                'circle-check'
                        };
                    case 'X':
                        return {
                            label: 'ANULADO', badge: 'bg-danger-subtle text-danger border-danger-subtle', icon:
                                'circle-x'
                        };
                    default:
                        return {
                            label: estado || 'SIN ESTADO', badge: 'bg-light text-dark border-secondary-subtle',
                                icon: 'package'
                        };
                }
            }

            function actualizarLineaTiempo(estado) {
                const pasos = [
                    document.getElementById('stepBgRegistrado'),
                    document.getElementById('stepBgTransito'),
                    document.getElementById('stepBgLlegada'),
                    document.getElementById('stepBgEntregado')
                ];

                pasos.forEach(paso => {
                    if (paso) {
                        paso.className =
                            "rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 shadow-sm bg-secondary";
                    }
                });

                let nivel = -1;
                switch (estado) {
                    case 'SA':
                    case 'SA1':
                        nivel = 0;
                        break;
                    case 'EC':
                        nivel = 1;
                        break;
                    case 'PE':
                        nivel = 2;
                        break;
                    case 'ET':
                        nivel = 3;
                        break;
                    case 'X':
                        pasos.forEach(p => p && p.classList.replace('bg-secondary', 'bg-danger'));
                        return;
                }

                for (let i = 0; i <= nivel; i++) {
                    if (pasos[i]) {
                        pasos[i].classList.remove('bg-secondary');
                        if (i === nivel && estado === 'ENTREGADO') {
                            pasos[i].classList.add('bg-success');
                        } else {
                            pasos[i].classList.add('bg-primary');
                        }
                    }
                }
            }


        });
    </script>
@endsection
