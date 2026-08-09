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
                            <label for="inputCodigo" class="form-label font-medium text-secondary">
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
                            <div class="form-text mt-1 text-muted">
                                <i class="bi bi-info-circle me-1"></i> Ingrese el código impreso en su comprobante o el
                                número de documento del consignatario.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mensaje de no encontrado o error -->
        <div id="mensajeError" class="alert alert-danger d-none text-center shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle fs-5 me-2"></i>
            <span id="textoError">No se encontró ninguna encomienda con el código o documento ingresado.</span>
        </div>

        <!-- Contenedor de Resultado (Oculto inicialmente) -->
        <div id="resultadoConsulta" class="d-none">

            <!-- Ficha de Estado General -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-3 gap-2">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Código de Envío</span>
                            <h4 class="mb-0 fw-bold text-primary" id="resCodigo">EC-000000</h4>
                        </div>
                        <div>
                            <span class="badge px-3 py-2 fs-6 border" id="resEstado">
                                <!-- Estado dinámico -->
                            </span>
                        </div>
                        <div class="d-flex gap-2">
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

                    <!-- Línea del Tiempo de Envío (Línea de Vida) -->
                    <div class="row text-center my-4 g-3">
                        <div class="col-3">
                            <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2"
                                style="width: 42px; height: 42px;" id="stepBgRegistrado">
                                <i class="bi bi-receipt fs-5"></i>
                            </div>
                            <div class="fw-bold small">Registrado</div>
                            <div class="text-muted text-xs" id="stepFechaRegistrado">--/--/--</div>
                        </div>
                        <div class="col-3">
                            <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2"
                                style="width: 42px; height: 42px;" id="stepBgTransito">
                                <i class="bi bi-truck fs-5"></i>
                            </div>
                            <div class="fw-bold small">En Tránsito</div>
                            <div class="text-muted text-xs" id="stepFechaTransito">--/--/--</div>
                        </div>
                        <div class="col-3">
                            <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2"
                                style="width: 42px; height: 42px;" id="stepBgLlegada">
                                <i class="bi bi-geo-alt fs-5"></i>
                            </div>
                            <div class="fw-bold small">En Agencia</div>
                            <div class="text-muted text-xs" id="stepFechaLlegada">--/--/--</div>
                        </div>
                        <div class="col-3">
                            <div class="rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2"
                                style="width: 42px; height: 42px;" id="stepBgEntregado">
                                <i class="bi bi-check-lg fs-5"></i>
                            </div>
                            <div class="fw-bold small">Entregado</div>
                            <div class="text-muted text-xs" id="stepFechaEntregado">--/--/--</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles de Origen, Destino y Personas -->
            <div class="row g-3 mb-4">
                <!-- Ruta / Origen / Destino -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white font-semibold text-secondary py-3 border-0">
                            <i class="bi bi-signpost-split me-2 text-primary"></i> Trayecto
                        </div>
                        <div class="card-body pt-0">
                            <div class="mb-3">
                                <small class="text-muted d-block">Agencia Origen</small>
                                <span class="fw-bold text-dark" id="resOrigen">-</span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Agencia Destino</small>
                                <span class="fw-bold text-dark" id="resDestino">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remitente -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white font-semibold text-secondary py-3 border-0">
                            <i class="bi bi-person-up me-2 text-primary"></i> Remitente (Envía)
                        </div>
                        <div class="card-body pt-0">
                            <div class="fw-bold text-dark mb-1" id="resEmisorNombre">-</div>
                            <div class="text-muted small mb-1">Doc: <span id="resEmisorDoc"
                                    class="text-dark fw-medium">-</span></div>
                            <div class="text-muted small">Tel: <span id="resEmisorTel"
                                    class="text-dark fw-medium">-</span></div>
                        </div>
                    </div>
                </div>

                <!-- Destinatario -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white font-semibold text-secondary py-3 border-0">
                            <i class="bi bi-person-down me-2 text-primary"></i> Destinatario (Recibe)
                        </div>
                        <div class="card-body pt-0">
                            <div class="fw-bold text-dark mb-1" id="resReceptorNombre">-</div>
                            <div class="text-muted small mb-1">Doc: <span id="resReceptorDoc"
                                    class="text-dark fw-medium">-</span></div>
                            <div class="text-muted small">Tel: <span id="resReceptorTel"
                                    class="text-dark fw-medium">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles de la Carga (Tabla de Bultos) -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white font-semibold text-secondary py-3 border-0">
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
                            <!-- Se genera dinámicamente mediante JS -->
                        </tbody>
                        <tfoot class="table-light font-semibold">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                <td class="text-end pe-4 fw-bold text-primary" id="resTotalMonto">S/ 0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <!-- Script de Búsqueda Native JS -->
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

                // Estado de Carga
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
                // Header e Imprimir
                document.getElementById('resCodigo').textContent = data.codigo || '-';

                const badge = document.getElementById('resEstado');
                badge.className = 'badge px-3 py-2 fs-6 border ' + getBadgeStyle(data.estado);
                badge.innerHTML = `<i class="bi ${getIconEstado(data.estado)} me-1"></i> ${data.estado}`;

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

                // Línea del Tiempo
                actualizarLineaTiempo(data.estado, data.created_at || data.fecha_creacion);

                // Trayecto
                document.getElementById('resOrigen').textContent = data.sucursal_origen ? data.sucursal_origen
                    .nombre : (data.origen_pueblito ? data.origen_pueblito.descripcion : '-');
                document.getElementById('resDestino').textContent = data.sucursal_destino ? data.sucursal_destino
                    .nombre : (data.destino_pueblito ? data.destino_pueblito.descripcion : '-');

                // Emisor
                if (data.emisor) {
                    document.getElementById('resEmisorNombre').textContent = data.emisor.nombre_completo || (data
                        .emisor.nombres + ' ' + (data.emisor.apellidos || ''));
                    document.getElementById('resEmisorDoc').textContent = data.emisor.documento || '-';
                    document.getElementById('resEmisorTel').textContent = data.emisor.celular || '-';
                }

                // Receptor
                if (data.receptor) {
                    document.getElementById('resReceptorNombre').textContent = data.receptor.nombre_completo || (
                        data.receptor.nombres + ' ' + (data.receptor.apellidos || ''));
                    document.getElementById('resReceptorDoc').textContent = data.receptor.documento || '-';
                    document.getElementById('resReceptorTel').textContent = data.receptor.celular || '-';
                }

                // Detalles / Bultos
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

            function getBadgeStyle(estado) {
                switch (estado) {
                    case 'REGISTRADO':
                        return 'bg-secondary-subtle text-secondary border-secondary-subtle';
                    case 'EN TRANSITO':
                        return 'bg-primary-subtle text-primary border-primary-subtle';
                    case 'EN DESTINO':
                        return 'bg-warning-subtle text-warning border-warning-subtle';
                    case 'ENTREGADO':
                        return 'bg-success-subtle text-success border-success-subtle';
                    case 'ANULADO':
                        return 'bg-danger-subtle text-danger border-danger-subtle';
                    default:
                        return 'bg-light text-dark';
                }
            }

            function getIconEstado(estado) {
                switch (estado) {
                    case 'REGISTRADO':
                        return 'bi-receipt';
                    case 'EN TRANSITO':
                        return 'bi-truck';
                    case 'EN DESTINO':
                        return 'bi-geo-alt';
                    case 'ENTREGADO':
                        return 'bi-check-circle';
                    case 'ANULADO':
                        return 'bi-x-circle';
                    default:
                        return 'bi-box';
                }
            }

            function actualizarLineaTiempo(estado, fecha) {
                const bgActive = 'bg-primary';
                const bgInactive = 'bg-secondary';

                const reg = document.getElementById('stepBgRegistrado');
                const tra = document.getElementById('stepBgTransito');
                const lle = document.getElementById('stepBgLlegada');
                const ent = document.getElementById('stepBgEntregado');

                reg.className =
                    `rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 ${bgActive}`;
                tra.className =
                    `rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 ${bgInactive}`;
                lle.className =
                    `rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 ${bgInactive}`;
                ent.className =
                    `rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 ${bgInactive}`;

                const fechaFormateada = fecha ? new Date(fecha).toLocaleDateString('es-PE') : '--/--/--';
                document.getElementById('stepFechaRegistrado').textContent = fechaFormateada;

                if (estado === 'EN TRANSITO' || estado === 'EN DESTINO' || estado === 'ENTREGADO') {
                    tra.className =
                        `rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 ${bgActive}`;
                }
                if (estado === 'EN DESTINO' || estado === 'ENTREGADO') {
                    lle.className =
                        `rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 ${bgActive}`;
                }
                if (estado === 'ENTREGADO') {
                    ent.className =
                        `rounded-circle text-white d-inline-flex align-items-center justify-content-center mb-2 bg-success`;
                }
            }
        });
    </script>
@endsection
