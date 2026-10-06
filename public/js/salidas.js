/* =========================================================
   SALIDAS - salidas.js
   Requiere: jQuery, DataTables, TomSelect, SweetAlert2, lucide, Ziggy (route)
   Window vars esperadas (Blade): IS_ADMIN, USER_SUCURSAL,
   HORARIOS_SALIDA, RUTAS_SALIDA, TIPOS_VEHICULO
   (VEHICULOS y CONDUCTORES ya NO se usan: salen de recursos_disponibles)
   ========================================================= */

let tablaSalidas;
let estadoActual = "programado";
let xhrDetalle = null;

const horariosSalida = window.HORARIOS_SALIDA || [];
const rutasSalida = window.RUTAS_SALIDA || [];
const tiposVehiculo = window.TIPOS_VEHICULO || [];

/* ---------- Configuración global ---------- */
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

/* ---------- Helpers generales ---------- */
const csrf = () => $("meta[name=csrf-token]").attr("content");

function hoy() {
    return new Date().toISOString().split("T")[0];
}

function ahora() {
    return new Date().toTimeString().split(" ")[0].substring(0, 5);
}

function recargarTabla(resetPagina = false) {
    tablaSalidas.ajax.reload(null, resetPagina);
}

function setPanel(titulo, html) {
    $("#tituloPanelSalida").text(titulo);
    $("#panelSalidaContenido").html(html);
    lucide.createIcons();
}

function panelVacio() {
    setPanel(
        "Detalle de salida",
        `<div class="text-center py-5">
            <div class="mb-3 text-secondary opacity-25">
                <i data-lucide="mouse-pointer-click" style="width: 42px; height: 42px;"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1 fs-7">Ninguna salida seleccionada</h6>
            <p class="text-muted fs-8 mb-0">Haz clic en alguna fila o acción de la tabla para ver el recorrido completo.</p>
        </div>`,
    );
}

function panelCargando(texto = "Cargando...") {
    return `
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
            <div>${texto}</div>
        </div>`;
}

function errorAjax(titulo = "Error", porDefecto = "Ocurrió un error") {
    return (xhr) => {
        if (xhr.statusText === "abort") return;
        Swal.fire(titulo, xhr.responseJSON?.message || porDefecto, "error");
    };
}

function opcionesDesde(lista, { placeholder, selected = "", label }) {
    let html = `<option value="">${placeholder}</option>`;
    lista.forEach((item) => {
        html += `<option value="${item.id}" ${String(selected) === String(item.id) ? "selected" : ""}>${label(item)}</option>`;
    });
    return html;
}

const opcionesHorarios = (selected = "") =>
    opcionesDesde(horariosSalida, {
        placeholder: "Seleccione horario",
        selected,
        label: (h) => h.nombre,
    });

const opcionesRutas = (selected = "") =>
    opcionesDesde(rutasSalida, {
        placeholder: "Seleccione ruta",
        selected,
        label: (r) => r.nombre,
    });

const opcionesTiposVehiculo = (selected = "") =>
    opcionesDesde(tiposVehiculo, {
        placeholder: "Seleccione tipo de vehículo",
        selected,
        label: (t) => t.descripcion,
    });

function opcionesEstados(actual = "programado") {
    const nombres = {
        programado: "Programado",
        reprogramado: "Reprogramado",
        en_ruta: "En ruta",
        finalizado: "Finalizado",
        cancelado: "Cancelado",
    };
    const permitidos = {
        programado: ["programado", "en_ruta", "reprogramado", "cancelado"],
        reprogramado: ["reprogramado", "en_ruta", "cancelado"],
        en_ruta: ["en_ruta", "finalizado", "cancelado"],
        finalizado: ["finalizado"],
        cancelado: ["cancelado"],
    }[actual] ?? [actual];

    return permitidos
        .map(
            (e) =>
                `<option value="${e}" ${e === actual ? "selected" : ""}>${nombres[e]}</option>`,
        )
        .join("");
}

const columnas = [
    ...(window.IS_ADMIN && !window.MODO_MANIFIESTOS
        ? [
              {
                  data: "checkbox",
                  orderable: false,
                  searchable: false,
                  className: "text-center",
              },
          ]
        : []),
    {
        data: "ruta",
        name: "rutas.nombre",
        render: (data) => `
            <div class="ruta-cell">
                <div>
                    <div class="ruta-nombre">${data ?? "-"}</div>
                    <div class="ruta-label">Servicio programado</div>
                </div>
            </div>`,
    },
    {
        data: "fecha_formateada",
        name: "salidas.fecha_salida",
        className: "text-nowrap",
        render: (data) => `
            <div class="fecha-cell">
                <i data-lucide="calendar-days"></i>
                <span>${data ?? "-"}</span>
            </div>`,
    },
    {
        data: "hora_salida",
        name: "horarios.hora_salida",
        className: "text-center",
        render: (data) => `
            <span class="hora-badge salida">
                <i data-lucide="clock-3"></i> ${data ?? "-"}
            </span>`,
    },
    {
        data: "hora_llegada",
        name: "horarios.hora_llegada",
        className: "text-center",
        render: (data) => `
            <span class="hora-badge llegada">
                <i data-lucide="flag"></i> ${data ?? "-"}
            </span>`,
    },
    {
        data: "estado",
        name: "salidas.estado",
        className: "text-center",
        render: (data, type, row) =>
            type === "display" ? row.estado_badge : data,
    },
    {
        data: "acciones",
        orderable: false,
        searchable: false,
        className: "text-center text-nowrap",
    },
];

$(function () {
    tablaSalidas = $("#tablaSalidas").DataTable({
        order: [], // <- agrega esto
        processing: true,
        serverSide: true,
        deferRender: true,
        searchDelay: 400,
        pageLength: 10,
        autoWidth: false,
        info: false,
        dom: "rtip",
        ajax: {
            url: route("salidas.datatable"),
            data: (d) => {
                d.estado = estadoActual;
                d.ruta_id = $("#filtroRuta").val();
                d.modo = window.MODO_MANIFIESTOS ? "manifiestos" : "";
            },
        },
        columns: columnas,
        language: {
            emptyTable: "No hay salidas disponibles",
            zeroRecords: "No se encontraron salidas",
            processing: "Cargando...",
            paginate: { previous: "‹", next: "›" },
        },
        drawCallback: () => {
            lucide.createIcons();
        },
    });

    // Filtro de ruta (opciones renderizadas por Blade)
    new TomSelect("#filtroRuta", {
        allowEmptyOption: true,
        maxOptions: 50,
        placeholder: "Todas las rutas",
    });

    $("#filtroRuta").on("change", () => recargarTabla(true));

    // Pestañas de estado
    $("#pills-tab-estados").on("click", ".btn-pill-tab", function () {
        $("#pills-tab-estados .btn-pill-tab").removeClass("active");
        $(this).addClass("active");
        estadoActual = $(this).data("estado");
        recargarTabla(true);
    });
});

/* ---------- Acciones de la tabla ---------- */
$(document).on("click", ".ver", function () {
    verSalida($(this).data("id"));
});
$(document).on("click", ".editar", function () {
    editarSalida($(this).data("id"));
});
$(document).on("click", ".eliminar", function () {
    eliminarSalida($(this).data("id"));
});
$(document).on("click", ".iniciar-ruta", function () {
    iniciarRuta($(this).data("id"));
});
$(document).on("click", ".finalizar-ruta", function () {
    finalizarRuta($(this).data("id"));
});

/* ---------- Selección múltiple / eliminar ---------- */
$(document).on("change", "#chk-todos", function () {
    $(".chk-salida").prop("checked", $(this).is(":checked"));
});

function getSeleccionados() {
    return $(".chk-salida:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

$("#btnEliminarSeleccionados").on("click", function () {
    const ids = getSeleccionados();

    if (ids.length === 0) {
        Swal.fire("Atención", "Selecciona al menos una salida", "warning");
        return;
    }

    Swal.fire({
        title: `¿Eliminar ${ids.length} salidas?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: route("salidas.destroy.bulk"),
            method: "POST",
            data: { _token: csrf(), _method: "DELETE", ids },
            success: () => {
                Swal.fire("Eliminadas", "", "success");
                $("#chk-todos").prop("checked", false);
                recargarTabla();
            },
            error: errorAjax("Error", "No se pudo eliminar"),
        });
    });
});

function eliminarSalida(id) {
    Swal.fire({
        title: "¿Eliminar salida?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: route("salidas.destroy", { id }),
            method: "POST",
            data: { _token: csrf(), _method: "DELETE" },
            success: () => {
                Swal.fire("Eliminado", "", "success");
                recargarTabla();
                panelVacio();
            },
            error: errorAjax("Error", "No se pudo eliminar"),
        });
    });
}

/* =========================================================
   CREAR SALIDA ÚNICA
   ========================================================= */
window.modoCrearSalida = function () {
    setPanel(
        "Crear salida",
        `
        <div class="mb-3">
            <label class="form-label">Seleccionar ruta programada <span class="text-danger">*</span></label>
            <select id="ruta_id" name="ruta_id">${opcionesRutas()}</select>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo de vehículo <span class="text-danger">*</span></label>
            <select id="tipo_vehiculo_id" class="form-select">${opcionesTiposVehiculo()}</select>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" id="fecha_salida" class="form-control" min="${hoy()}">
        </div>

        <div class="mb-3">
            <label class="form-label">Hora <span class="text-danger">*</span></label>
            <input type="time" id="hora_salida" class="form-control">
        </div>

        <button class="btn btn-primary w-100" onclick="guardarSalidaDirecta()">
            Guardar salida
        </button>`,
    );

    new TomSelect("#ruta_id", { placeholder: "Seleccione ruta..." });
};

window.guardarSalidaDirecta = function () {
    const data = {
        ruta_id: $("#ruta_id").val(),
        tipo_vehiculo_id: $("#tipo_vehiculo_id").val(),
        fecha_salida: $("#fecha_salida").val(),
        hora_salida: $("#hora_salida").val(),
    };

    if (
        !data.ruta_id ||
        !data.tipo_vehiculo_id ||
        !data.fecha_salida ||
        !data.hora_salida
    ) {
        Swal.fire("Error", "Completa todos los campos", "error");
        return;
    }

    $.post(route("salidas.store.directa"), data)
        .done(() => {
            Swal.fire("Correcto", "Salida creada", "success");
            recargarTabla();
            panelVacio();
        })
        .fail(errorAjax("Error", "Error al guardar"));
};

/* =========================================================
   PROGRAMAR VARIAS SALIDAS
   ========================================================= */
window.modoGenerarSalidas = function () {
    const dias = [
        [1, "Lunes"],
        [2, "Martes"],
        [3, "Miércoles"],
        [4, "Jueves"],
        [5, "Viernes"],
        [6, "Sábado"],
        [7, "Domingo"],
    ]
        .map(
            ([v, n]) =>
                `<label><input type="checkbox" class="dia" value="${v}"> ${n}</label>`,
        )
        .join("");

    setPanel(
        "Generar salidas",
        `
        <div class="mb-2">
            <label class="form-label">Horario <span class="text-danger">*</span></label>
            <select id="horario_id_generar">${opcionesHorarios()}</select>
        </div>

        <div class="mb-2">
            <label class="form-label">Fecha inicio <span class="text-danger">*</span></label>
            <input type="date" id="fecha_inicio" class="form-control" min="${hoy()}">
        </div>

        <div class="mb-2">
            <label class="form-label">Fecha fin <span class="text-danger">*</span></label>
            <input type="date" id="fecha_fin" class="form-control" min="${hoy()}">
        </div>

        <div class="mb-2">
            <label class="form-label">Días <span class="text-danger">*</span></label>
            <div class="d-flex flex-column gap-1">${dias}</div>
        </div>

        <button class="btn btn-success w-100 mt-2" onclick="generarSalidas()">
            Generar salidas
        </button>`,
    );

    new TomSelect("#horario_id_generar", {
        create: false,
        placeholder: "Seleccione horario...",
    });
};

window.generarSalidas = function () {
    const horario_id = $("#horario_id_generar").val();
    const fecha_inicio = $("#fecha_inicio").val();
    const fecha_fin = $("#fecha_fin").val();
    const dias = $(".dia:checked")
        .map(function () {
            return $(this).val();
        })
        .get();

    if (!horario_id || !fecha_inicio || !fecha_fin || dias.length === 0) {
        Swal.fire("Error", "Completa todos los campos", "error");
        return;
    }

    Swal.fire({
        title: "Generando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.post(route("salidas.generar"), {
        horario_id,
        fecha_inicio,
        fecha_fin,
        dias,
    })
        .done((res) => {
            Swal.fire(
                "Correcto",
                res.mensaje || "Salidas generadas",
                "success",
            );
            recargarTabla();
            panelVacio();
        })
        .fail(errorAjax("Error", "No se pudieron generar"));
};

/* =========================================================
   DETALLE DE SALIDA
   ========================================================= */
function verSalida(id) {
    xhrDetalle?.abort(); // evita respuestas desordenadas al hacer clic rápido

    setPanel("Detalle de salida", panelCargando("Cargando detalle..."));

    xhrDetalle = $.get(route("salidas.show", { id }));
    xhrDetalle
        .done((salida) => renderDetalle(salida))
        .fail(errorAjax("Error", "No se pudo cargar la salida"));
}

function renderTimeline(puntos) {
    if (!puntos.length) {
        return `<p class="text-muted fs-7">No hay puntos de ruta registrados.</p>`;
    }

    return puntos
        .map((punto, index) => {
            const esCompletado = punto.check_registrado;
            const esActual = punto.es_actual;

            let iconClass = "border-secondary bg-white text-secondary";
            let icono = "circle";
            let badge = `<span class="badge bg-light text-muted border fs-9 fw-semibold py-0 px-1">
                <i data-lucide="circle" style="width:8px;"></i> Habilitado
            </span>`;

            if (esCompletado) {
                iconClass = "bg-success text-white border-success";
                icono = "check";
                badge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-9 fw-semibold py-0 px-1">
                    <i data-lucide="lock" style="width:8px;"></i> Bloqueado
                </span>`;
            } else if (esActual) {
                iconClass = "bg-primary text-white border-primary";
                icono = "play";
                badge = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-9 fw-semibold py-0 px-1">
                    <i data-lucide="navigation" style="width:8px;"></i> Próxima
                </span>`;
            }

            const claseNombre = esActual
                ? "text-primary"
                : esCompletado
                  ? "text-muted text-decoration-line-through"
                  : "text-dark";

            const sucursal = punto.sucursal?.nombre_comercial
                ? ` - ${punto.sucursal.nombre_comercial}`
                : "";

            return `
            <div class="d-flex align-items-center mb-1 py-1 position-relative">
                <div class="me-2 flex-shrink-0" style="z-index: 1;">
                    <div class="rounded-circle border d-flex align-items-center justify-content-center ${iconClass}" style="width: 22px; height: 22px;">
                        <i data-lucide="${icono}" style="width: 10px;"></i>
                    </div>
                </div>
                <div class="flex-grow-1 border-bottom pb-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="fw-bold fs-8 text-truncate ${claseNombre}" title="${punto.nombre}">
                            ${String.fromCharCode(65 + index)}. ${punto.nombre}${sucursal}
                        </span>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <span class="fw-bold fs-8 ${esActual ? "text-primary" : "text-muted"}">${punto.hora ?? "-"}</span>
                            ${badge}
                        </div>
                    </div>
                </div>
            </div>`;
        })
        .join("");
}

// Saca las sucursales únicas de la ruta a partir de los puntos que ya manda show()
function sucursalesDeRuta(puntos) {
    const mapa = new Map();
    puntos.forEach((p) => {
        if (p.sucursal && !mapa.has(p.sucursal.id)) {
            mapa.set(p.sucursal.id, {
                id: p.sucursal.id,
                nombre: p.sucursal.nombre_comercial,
                check_registrado: p.check_registrado,
            });
        }
    });
    return [...mapa.values()];
}

function renderTarjetaCheck(salida, sucursalesRuta) {
    if (window.IS_ADMIN) {
        const primeraHabilitada = sucursalesRuta.find(
            (s) => !s.check_registrado,
        );
        const opciones = sucursalesRuta
            .map(
                (s) => `<option value="${s.id}"
                    ${s.check_registrado ? "disabled" : ""}
                    ${primeraHabilitada?.id === s.id ? "selected" : ""}>
                    ${s.nombre}${s.check_registrado ? " — Ventas bloqueadas" : ""}
                </option>`,
            )
            .join("");

        return `
        <div class="card bg-light border-0 mb-3">
            <div class="card-body p-3">
                <label class="form-label fs-8 fw-bold text-muted mb-1">Sucursal actual (Modo Admin)</label>
                <select id="sucursal_manifiesto" class="form-select form-select-sm mb-2">
                    ${opciones}
                </select>
                <button class="btn btn-dark btn-sm w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-1" onclick="registrarCheck(${salida.id})">
                    <i data-lucide="shield-check" style="width:16px;"></i> Dar check y bloquear ventas
                </button>
            </div>
        </div>`;
    }

    const mia = sucursalesRuta.find(
        (s) => String(s.id) === String(window.USER_SUCURSAL?.id),
    );

    if (!mia) {
        return `<div class="alert alert-warning fs-7 mt-3">Tu sucursal no forma parte de esta ruta.</div>`;
    }

    const yaDioCheck = mia.check_registrado;

    return `
    <div class="card bg-light border-0 mb-3">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted d-block fs-8">Sucursal actual</small>
                <strong class="d-block text-dark">${mia.nombre}</strong>
                ${
                    yaDioCheck
                        ? `<small class="text-danger fs-8 fw-semibold">
                            <i data-lucide="lock" style="width:12px;"></i> El bus ya pasó por esta sucursal
                           </small>`
                        : `<small class="text-primary fs-8 fw-semibold">Próxima ${salida.hora_salida ?? ""}</small>`
                }
                <input type="hidden" id="sucursal_manifiesto" value="${mia.id}">
            </div>
            <div>
                ${
                    yaDioCheck
                        ? `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-2 fs-8">
                            <i data-lucide="lock" style="width:14px;"></i> Ventas bloqueadas
                           </span>`
                        : `<button class="btn btn-dark btn-sm px-3 py-2 fw-semibold d-flex align-items-center gap-1" onclick="registrarCheck(${salida.id}, ${mia.id})">
                            <i data-lucide="shield-check" style="width:16px;"></i> Dar check
                           </button>`
                }
            </div>
        </div>
    </div>`;
}

function renderManifiestos(salida, sucursalesRuta) {
    const pertenece = sucursalesRuta.some(
        (s) => String(s.id) === String(window.USER_SUCURSAL?.id),
    );

    if (!window.IS_ADMIN && !pertenece) return "";

    const boton = (tipo, clase, icono, texto, col = "col-6") => `
        <div class="${col}">
            <button class="btn ${clase} btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-1" onclick="abrirManifiesto(${salida.id}, '${tipo}')">
                <i data-lucide="${icono}" style="width:14px;"></i> ${texto}
            </button>
        </div>`;

    let html = `
    <div class="mt-3">
        <small class="fw-bold text-muted d-block mb-2 fs-8">Manifiestos de la sucursal</small>
        <div class="row g-2">
            ${boton("pasajeros", "btn-primary", "user", "Pasajeros")}
            ${boton("encomiendas", "btn-info text-white", "package", "Encomiendas")}
            ${boton("bodega", "btn-warning text-white", "archive", "Bodega")}
            ${boton("conductores", "btn-success", "truck", "Conductores")}
            ${boton("pasajeros_real", "btn-secondary", "file-text", "Pasajeros detallado", "col-12")}
        </div>
    </div>`;

    if (salida.estado === "finalizado" && window.IS_ADMIN) {
        html += `
        <div class="mt-2">
            <button class="btn btn-dark btn-sm w-100 py-2" onclick="imprimirTodosManifiestos(${salida.id})">
                Imprimir todos los manifiestos (todas las sucursales)
            </button>
        </div>`;
    }

    return html;
}

function renderDetalle(salida) {
    const puntos = salida.ruta?.puntos ?? [];
    const totalPuntos = puntos.length;
    const bloqueadas = puntos.filter((p) => p.check_registrado).length;
    const habilitadas = totalPuntos - bloqueadas;

    const botonEditarAsignacion = salida.puede_editar_asignacion
        ? `<button class="btn btn-outline-warning btn-sm w-100 mb-3 d-flex align-items-center justify-content-center gap-1"
                onclick="editarAsignacion(${salida.id})">
                <i data-lucide="user-cog" style="width:14px;"></i>
                Cambiar vehículo / conductor
           </button>`
        : "";

    let extra = "";
    if (salida.estado === "en_ruta" || salida.estado === "finalizado") {
        const sucursalesRuta = sucursalesDeRuta(puntos);
        extra =
            renderTarjetaCheck(salida, sucursalesRuta) +
            renderManifiestos(salida, sucursalesRuta);
    } else {
        extra = `<div class="alert alert-info fs-7 mb-0">
            Inicia el viaje para habilitar el registro de check y bloquear ventas.
        </div>`;
    }

    const html = `
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="p-2 border rounded bg-light text-center">
                    <span class="d-block text-muted fs-8 fw-semibold text-uppercase">Ruta</span>
                    <strong class="fs-6 text-dark d-block text-truncate" title="${salida.ruta?.nombre ?? "Sin ruta"}">
                        ${salida.ruta?.nombre ?? "Sin ruta"}
                    </strong>
                    <span class="d-block text-muted fs-8">${salida.fecha_formateada ?? "-"} • ${salida.hora_salida ?? "-"}</span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded bg-light text-center">
                    <span class="d-block text-muted fs-8 fw-semibold text-uppercase">Progreso</span>
                    <strong class="fs-5 text-dark">${salida.parada_actual_index ?? 0}/${totalPuntos}</strong>
                    <span class="d-block text-muted fs-8">paradas</span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded bg-light text-center">
                    <span class="d-block text-muted fs-8 fw-semibold text-uppercase">Ventas</span>
                    <strong class="fs-5 text-dark">${salida.asientos_vendidos ?? 0}</strong>
                    <span class="d-block text-muted fs-8">asientos</span>
                </div>
            </div>
        </div>

        ${botonEditarAsignacion}

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fs-8 fw-bold text-muted">Estado de la ruta</span>
            <span class="fs-8 fw-bold text-muted">${habilitadas} sucursal(es) aún venden</span>
        </div>

        <div class="w-100 px-1 mb-3 custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
            ${renderTimeline(puntos)}
        </div>

        ${extra}`;

    setPanel("Detalle de salida", html);
}

/* =========================================================
   CHECK DE SUCURSAL Y MANIFIESTOS
   ========================================================= */
function registrarCheck(salidaId, sucursalId = null) {
    const idSucursal = sucursalId || $("#sucursal_manifiesto").val();

    if (!idSucursal) {
        Swal.fire("Error", "Selecciona una sucursal válida", "error");
        return;
    }

    Swal.fire({
        title: "¿Confirmar llegada del vehículo?",
        text: "Al dar 'check' se bloquearán las nuevas ventas para esta salida en esta sucursal.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#10b981",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, dar check",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: route("salidas.registrar_check", { salida: salidaId }),
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrf() },
            data: { sucursal_id: idSucursal },
            success: () => {
                Swal.fire(
                    "¡Registrado!",
                    "El check fue guardado y las ventas de esta sucursal se congelaron.",
                    "success",
                );
                verSalida(salidaId);
            },
            error: errorAjax("Error", "No se pudo registrar el check"),
        });
    });
}

function abrirManifiesto(salidaId, tipo) {
    const sucursalId = $("#sucursal_manifiesto").val();

    if (!sucursalId) {
        Swal.fire("Atención", "Selecciona una sucursal", "warning");
        return;
    }

    const rutasPorTipo = {
        pasajeros: "salidas.manifiesto_pasajeros",
        encomiendas: "salidas.manifiesto_encomiendas",
        bodega: "salidas.manifiesto_bodega",
        conductores: "salidas.manifiesto_conductores",
        pasajeros_real: "salidas.manifiesto_pasajeros_real",
    };

    const url =
        route(rutasPorTipo[tipo], { salida: salidaId }) +
        "?sucursal_id=" +
        sucursalId;

    window.open(url, "_blank");
}

function imprimirTodosManifiestos(salidaId) {
    window.open(
        route("salidas.manifiesto_pasajeros.todos", { salida: salidaId }),
        "_blank",
    );
}

/* =========================================================
   RECURSOS (vehículos / conductores) bajo demanda
   ========================================================= */
function actualizarOpcionesConductores() {
    const principal = $("#conductor_principal_id").val();
    const secundario = $("#conductor_secundario_id").val();

    $("#conductor_principal_id option").each(function () {
        $(this).prop("disabled", !!secundario && $(this).val() === secundario);
    });

    $("#conductor_secundario_id option").each(function () {
        $(this).prop("disabled", !!principal && $(this).val() === principal);
    });
}

$(document).on(
    "change",
    "#conductor_principal_id, #conductor_secundario_id",
    actualizarOpcionesConductores,
);

// Llena #vehiculo_id, #conductor_principal_id y #conductor_secundario_id
function cargarRecursosDisponibles(salida) {
    [
        "#vehiculo_id",
        "#conductor_principal_id",
        "#conductor_secundario_id",
    ].forEach((s) => $(s).prop("disabled", true));

    $.get(route("salidas.recursos_disponibles", { salida: salida.id }))
        .done((res) => {
            $("#vehiculo_id").html(
                opcionesDesde(res.vehiculos, {
                    placeholder: "Seleccione vehículo",
                    selected: salida.vehiculo_id ?? "",
                    label: (v) =>
                        `${v.tipo_vehiculo?.descripcion ?? ""} - ${v.numero_placa}`,
                }),
            );

            const labelConductor = (c) =>
                `${c.persona.nombres} ${c.persona.apellidos}`;

            $("#conductor_principal_id").html(
                opcionesDesde(res.conductores, {
                    placeholder: "Seleccione",
                    selected: salida.conductor_principal_id ?? "",
                    label: labelConductor,
                }),
            );

            $("#conductor_secundario_id").html(
                opcionesDesde(res.conductores, {
                    placeholder: "Opcional",
                    selected: salida.conductor_secundario_id ?? "",
                    label: labelConductor,
                }),
            );

            actualizarOpcionesConductores();
        })
        .fail(
            errorAjax("Error", "No se pudieron cargar vehículos y conductores"),
        )
        .always(() => {
            [
                "#vehiculo_id",
                "#conductor_principal_id",
                "#conductor_secundario_id",
            ].forEach((s) => $(s).prop("disabled", false));
        });
}

// HTML común de los 3 selects (vacíos; se llenan con cargarRecursosDisponibles)
function bloqueAsignacionHtml() {
    return `
    <div class="mb-2">
        <label class="form-label">Vehículo <span class="text-danger">*</span></label>
        <select id="vehiculo_id" class="form-select">
            <option value="">Cargando...</option>
        </select>
    </div>

    <div class="mb-2">
        <label class="form-label">Conductor principal <span class="text-danger">*</span></label>
        <select id="conductor_principal_id" class="form-select">
            <option value="">Cargando...</option>
        </select>
    </div>

    <div class="mb-2">
        <label class="form-label">Conductor secundario</label>
        <select id="conductor_secundario_id" class="form-select">
            <option value="">Cargando...</option>
        </select>
    </div>`;
}

/* =========================================================
   EDITAR ASIGNACIÓN (emergencia, salida ya en ruta)
   ========================================================= */
function editarAsignacion(id) {
    setPanel("Editar asignación (emergencia)", panelCargando());

    $.get(route("salidas.show", { id }))
        .done((salida) => {
            setPanel(
                "Editar asignación (emergencia)",
                `
                <div class="alert alert-warning fs-7 mb-3">
                    Estás cambiando el vehículo/conductor de una salida ya iniciada.
                    Usa esto solo en caso de imprevistos (ej. conductor no puede salir).
                </div>

                ${bloqueAsignacionHtml()}

                <button class="btn btn-warning w-100 mt-2"
                    onclick="guardarEdicionAsignacion(${salida.id}, '${salida.horario_id}', '${salida.fecha_salida}')">
                    Guardar cambio
                </button>

                <button class="btn btn-link w-100 mt-1" onclick="verSalida(${salida.id})">
                    Cancelar
                </button>`,
            );

            cargarRecursosDisponibles(salida);
        })
        .fail(errorAjax("Error", "No se pudo cargar la salida"));
}

window.guardarEdicionAsignacion = function (id, horario_id, fecha_salida) {
    const vehiculo_id = $("#vehiculo_id").val();
    const conductor_principal_id = $("#conductor_principal_id").val();
    const conductor_secundario_id = $("#conductor_secundario_id").val();

    if (!vehiculo_id || !conductor_principal_id) {
        Swal.fire("Error", "Debe asignar vehículo y conductor", "error");
        return;
    }

    $.ajax({
        url: route("salidas.update", { id }),
        method: "POST",
        data: {
            _token: csrf(),
            _method: "PUT",
            horario_id,
            fecha_salida,
            estado: "en_ruta", // no cambia el estado, solo la asignación
            vehiculo_id,
            conductor_principal_id,
            conductor_secundario_id,
        },
        success: () => {
            Swal.fire("Actualizado", "Vehículo/conductor cambiado", "success");
            recargarTabla();
            verSalida(id);
        },
        error: errorAjax("Error", "No se pudo actualizar"),
    });
};

/* =========================================================
   INICIAR / FINALIZAR RUTA
   ========================================================= */
function iniciarRuta(id) {
    setPanel("Iniciar ruta", panelCargando());

    $.get(route("salidas.show", { id }))
        .done((salida) => {
            setPanel(
                "Iniciar ruta",
                `
                <div class="alert alert-info fs-7 mb-3">
                    Vas a iniciar la ruta <strong>${salida.ruta?.nombre ?? ""}</strong>,
                    programada para el ${salida.fecha_formateada ?? "-"} a las ${salida.hora_salida ?? "-"}.
                </div>

                <input type="hidden" id="fecha_salida_hidden" value="${salida.fecha_salida}">

                ${bloqueAsignacionHtml()}

                <button class="btn btn-success w-100 mt-2"
                    onclick="guardarInicioRuta(${salida.id}, ${salida.horario_id})">
                    Iniciar ruta
                </button>`,
            );

            cargarRecursosDisponibles(salida);
        })
        .fail(errorAjax("Error", "No se pudo cargar la salida"));
}

window.guardarInicioRuta = function (id, horario_id) {
    const fecha_salida = $("#fecha_salida_hidden").val();
    const vehiculo_id = $("#vehiculo_id").val();
    const conductor_principal_id = $("#conductor_principal_id").val();
    const conductor_secundario_id = $("#conductor_secundario_id").val();

    if (!vehiculo_id || !conductor_principal_id) {
        Swal.fire("Error", "Debe asignar vehículo y conductor", "error");
        return;
    }

    Swal.fire({
        title: "¿Iniciar esta ruta?",
        text: "El estado cambiará a 'En ruta'.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, iniciar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: route("salidas.update", { id }),
            method: "POST",
            data: {
                _token: csrf(),
                _method: "PUT",
                horario_id,
                fecha_salida,
                estado: "en_ruta",
                vehiculo_id,
                conductor_principal_id,
                conductor_secundario_id,
            },
            success: () => {
                Swal.fire("Ruta iniciada", "", "success");
                recargarTabla();
                panelVacio();
            },
            error: errorAjax("Error", "No se pudo iniciar la ruta"),
        });
    });
};

function finalizarRuta(id) {
    Swal.fire({
        title: "¿Finalizar esta ruta?",
        text: "El estado cambiará a 'Finalizado'. Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#0d6efd",
        confirmButtonText: "Sí, finalizar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.get(route("salidas.show", { id }))
            .done((salida) => {
                $.ajax({
                    url: route("salidas.update", { id }),
                    method: "POST",
                    data: {
                        _token: csrf(),
                        _method: "PUT",
                        horario_id: salida.horario_id,
                        fecha_salida: salida.fecha_salida,
                        estado: "finalizado",
                        vehiculo_id: salida.vehiculo_id,
                        conductor_principal_id: salida.conductor_principal_id,
                        conductor_secundario_id: salida.conductor_secundario_id,
                    },
                    success: () => {
                        Swal.fire("Ruta finalizada", "", "success");
                        recargarTabla();
                        panelVacio();
                    },
                    error: errorAjax("Error", "No se pudo finalizar la ruta"),
                });
            })
            .fail(errorAjax("Error", "No se pudo cargar la salida"));
    });
}

/* =========================================================
   EDITAR SALIDA (admin)
   ========================================================= */
function bloqueCambioEstado(salida = {}) {
    const visible = ["reprogramado", "cancelado"].includes(salida.estado)
        ? ""
        : "display:none;";

    return `
    <div id="bloqueCambioEstado" style="${visible}">
        <hr>

        <div class="alert alert-warning">
            <strong>Salida original:</strong><br>
            Fecha: ${salida.fecha_formateada ?? "-"}<br>
            Hora: ${salida.hora_salida ?? "-"}
        </div>

        <div class="mb-2">
            <label class="form-label">Nueva fecha <span class="text-danger">*</span></label>
            <input type="date" id="fecha_cambio_estado" class="form-control"
                min="${hoy()}" value="${salida.fecha_cambio_estado ?? hoy()}">
        </div>

        <div class="mb-2">
            <label class="form-label">Nueva hora <span class="text-danger">*</span></label>
            <input type="time" id="hora_cambio_estado" class="form-control"
                value="${salida.hora_cambio_estado ?? ahora()}">
        </div>

        <div class="mb-2">
            <label class="form-label">Motivo <span class="text-danger">*</span></label>
            <textarea id="motivo_cambio_estado" class="form-control" rows="3">${salida.motivo_cambio_estado ?? ""}</textarea>
        </div>
    </div>`;
}

function editarSalida(id) {
    setPanel("Editar salida", panelCargando());

    $.get(route("salidas.show", { id }))
        .done((salida) => {
            const bloqueado = ["finalizado", "cancelado"].includes(
                salida.estado,
            );

            setPanel(
                "Editar salida",
                `
                <div class="p-2 border rounded bg-light mb-3">
                    <small class="text-muted d-block">Ruta</small>
                    <strong class="d-block">${salida.ruta?.nombre ?? "-"}</strong>
                    <small class="text-muted">${salida.fecha_formateada ?? "-"} • ${salida.hora_salida ?? "-"}</small>
                </div>

                <div class="mb-2">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select id="estado" class="form-select">${opcionesEstados(salida.estado)}</select>
                </div>

                ${bloqueCambioEstado(salida)}

                <div id="bloqueAsignacionRuta" style="display:none;">
                    <hr>
                    ${bloqueAsignacionHtml()}
                </div>

                ${
                    bloqueado
                        ? `<div class="alert alert-secondary fs-7 mt-2">Esta salida ya está ${salida.estado} y no admite cambios.</div>`
                        : `<button class="btn btn-success w-100 mt-2"
                          onclick="guardarEdicionSalida(${salida.id}, ${salida.horario_id}, '${salida.fecha_salida}')">
                          Guardar cambios
                       </button>`
                }

                <button class="btn btn-link w-100 mt-1" onclick="verSalida(${salida.id})">Cancelar</button>
            `,
            );

            let recursosCargados = false;

            function aplicarReglasEstado() {
                const estado = $("#estado").val();
                const enRuta = estado === "en_ruta";
                const reprogramado = estado === "reprogramado";
                const cancelado = estado === "cancelado";

                $("#bloqueAsignacionRuta").toggle(enRuta);
                $("#bloqueCambioEstado").toggle(reprogramado || cancelado);
                $("#fecha_cambio_estado").closest(".mb-2").toggle(reprogramado);
                $("#hora_cambio_estado").closest(".mb-2").toggle(reprogramado);

                if (enRuta && !recursosCargados) {
                    recursosCargados = true;
                    cargarRecursosDisponibles(salida);
                }
            }

            $("#estado").on("change", aplicarReglasEstado);
            aplicarReglasEstado();
        })
        .fail(errorAjax("Error", "No se pudo cargar la salida"));
}

window.guardarEdicionSalida = function (id, horario_id, fecha_salida) {
    // horario y fecha SIEMPRE son los originales
    const estado = $("#estado").val();
    const fecha_cambio_estado = $("#fecha_cambio_estado").val();
    const hora_cambio_estado = $("#hora_cambio_estado").val();
    const motivo_cambio_estado = (
        $("#motivo_cambio_estado").val() || ""
    ).trim();
    const vehiculo_id = $("#vehiculo_id").val();
    const conductor_principal_id = $("#conductor_principal_id").val();
    const conductor_secundario_id = $("#conductor_secundario_id").val();

    if (
        estado === "reprogramado" &&
        (!fecha_cambio_estado || !hora_cambio_estado || !motivo_cambio_estado)
    ) {
        return Swal.fire(
            "Error",
            "Debe ingresar fecha, hora y motivo",
            "error",
        );
    }
    if (estado === "cancelado" && !motivo_cambio_estado) {
        return Swal.fire("Error", "Debe ingresar motivo", "error");
    }
    if (estado === "en_ruta" && (!vehiculo_id || !conductor_principal_id)) {
        return Swal.fire("Error", "Debe asignar vehículo y conductor", "error");
    }

    $.ajax({
        url: route("salidas.update", { id }),
        method: "POST",
        data: {
            _token: csrf(),
            _method: "PUT",
            horario_id,
            fecha_salida,
            estado,
            vehiculo_id,
            conductor_principal_id,
            conductor_secundario_id,
            fecha_cambio_estado,
            hora_cambio_estado,
            motivo_cambio_estado,
        },
        success: () => {
            Swal.fire("Actualizado", "", "success");
            recargarTabla();
            verSalida(id);
        },
        error: errorAjax("Error", "No se pudo actualizar la salida"),
    });
};
