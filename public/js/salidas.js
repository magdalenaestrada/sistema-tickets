let tablaSalidas;
let horariosSalida = window.HORARIOS_SALIDA || [];
let rutasSalida = window.RUTAS_SALIDA || [];
console.log(window.IS_ADMIN);
function cargarHorasDisponibles() {
    let horario_id = $("#horario_id").val();
    let fecha = $("#fecha_salida").val();

    if (!horario_id || !fecha) return;

    console.log("Cargando horas disponibles...");
}

$(document).on("change", "#fecha_salida, #horario_id", function () {
    cargarHorasDisponibles();
});

$(document).ready(function () {
    tablaSalidas = $("#tablaSalidas").DataTable({
        ajax: {
            url: route("salidas.datatable"),
            data: function (d) {
                d.estado = $("#filtroEstado").val();
                d.ruta_id = $("#filtroRuta").val();
            },
        },
        columns: [
            { data: "DT_RowIndex" },
            { data: "ruta" },
            { data: "fecha_formateada" },
            { data: "hora_salida" },
            { data: "hora_llegada" },
            {
                data: "estado",
                render: function (data, type, row) {
                    if (type === "display") return row.estado_badge;
                    return data;
                },
            },
            { data: "acciones" },
        ],

        responsive: true,
        info: false,
        dom: "rtip",
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#filtroEstado, #filtroRuta").on("change", function () {
        tablaSalidas.ajax.reload();
    });
});

new TomSelect("#filtroRuta", {
    valueField: "id",
    labelField: "nombre",
    searchField: "nombre",

    load: function (query, callback) {
        fetch(route("rutas.buscar") + "?q=" + query)
            .then((response) => response.json())
            .then((json) => callback(json))
            .catch(() => callback());
    },
});

new TomSelect("#filtroEstado", {
    create: false,
    allowEmptyOption: false,
    placeholder: "Todos los estados",
});

function hoy() {
    let fecha = new Date();
    return fecha.toISOString().split("T")[0];
}

function getSeleccionados() {
    let ids = [];

    $(".chk-salida:checked").each(function () {
        ids.push($(this).val());
    });

    return ids;
}

function validarHoraDuplicada(horario_id, fecha_salida, hora_salida) {
    let existe = false;

    tablaSalidas.rows().every(function () {
        let data = this.data();

        if (
            String(data.horario_id) === String(horario_id) &&
            data.fecha_salida === fecha_salida &&
            data.hora_salida === hora_salida
        ) {
            existe = true;
        }
    });

    return existe;
}

$("#btnEliminarSeleccionados").on("click", function () {
    let ids = getSeleccionados();

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
            data: {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "DELETE",
                ids: ids,
            },
            success: function () {
                Swal.fire("Eliminadas", "", "success");
                tablaSalidas.ajax.reload();
            },
            error: function (err) {
                Swal.fire(
                    "Error",
                    err.responseJSON?.message || "No se pudo eliminar",
                    "error",
                );
            },
        });
    });
});

$(document).on("change", "#chk-todos", function () {
    $(".chk-salida").prop("checked", $(this).is(":checked"));
});

function ahora() {
    let fecha = new Date();
    return fecha.toTimeString().split(" ")[0].substring(0, 5);
}

function opcionesHorarios(selected = "") {
    let html = `<option value="">Seleccione horario</option>`;

    horariosSalida.forEach((h) => {
        html += `<option value="${h.id}" ${String(selected) === String(h.id) ? "selected" : ""}>${h.nombre}</option>`;
    });

    return html;
}

function opcionesRutas(selected = "") {
    let html = `<option value="">Seleccione horario</option>`;

    rutasSalida.forEach((r) => {
        html += `<option value="${r.id}" ${String(selected) === String(r.id) ? "selected" : ""}>${r.nombre}</option>`;
    });

    return html;
}

function opcionesEstados(selected = "programado") {
    let estados = [
        { value: "programado", label: "Programado" },
        { value: "reprogramado", label: "Reprogramado" },
        { value: "en_ruta", label: "En ruta" },
        { value: "finalizado", label: "Finalizado" },
        { value: "cancelado", label: "Cancelado" },
    ];

    let html = `<option value="">Seleccione estado</option>`;

    estados.forEach((e) => {
        html += `<option value="${e.value}" ${String(selected) === String(e.value) ? "selected" : ""}>${e.label}</option>`;
    });

    return html;
}

function opcionesTiposVehiculo(selected = "") {
    let html = `<option value="">Seleccione tipo de vehículo</option>`;

    window.TIPOS_VEHICULO.forEach((t) => {
        html += `
            <option value="${t.id}"
                ${String(selected) === String(t.id) ? "selected" : ""}>
                ${t.descripcion}
            </option>
        `;
    });

    return html;
}

window.modoCrearSalida = function () {
    let html = `
        <div id="contenedorRuta">
            <div class="mb-3">
                <label class="form-label">
                    Seleccionar ruta programada <span style="color:red">*</span>
                </label>

                <select id="ruta_id" name="ruta_id" required>
                    ${opcionesRutas()}
                </select>
            </div>
        </div>
<div class="mb-3">
    <label class="form-label">
        Tipo de vehículo <span class="text-danger">*</span>
    </label>

    <select id="tipo_vehiculo_id" class="form-select">
        ${opcionesTiposVehiculo()}
    </select>
</div>
        <div class="mb-3">
            <label class="form-label">
                Fecha <span style="color:red">*</span>
            </label>

            <input type="date"
                   id="fecha_salida"
                   class="form-control" name="fecha_salida" required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Hora <span style="color:red">*</span>
            </label>

            <input type="time"
                   id="hora_salida"
                   class="form-control" name="hora_salida">
        </div>

        <button
            class="btn btn-primary w-100"
            onclick="guardarSalidaDirecta()">
            Guardar salida
        </button>
    `;

    $("#tituloPanelSalida").text("Crear salida");
    $("#panelSalidaContenido").html(html);

    new TomSelect("#ruta_id");
    new TomSelect("#origen_id");
    new TomSelect("#destino_id");

    $("#tipo_salida").on("change", function () {
        const tipo = $(this).val();

        if (tipo === "ruta") {
            $("#contenedorRuta").show();
            $("#contenedorDirecto").hide();
        } else {
            $("#contenedorRuta").hide();
            $("#contenedorDirecto").show();
        }
    });
};

window.guardarSalidaDirecta = function () {
    const data = {
        ruta_id: $("#ruta_id").val(),
        tipo_vehiculo_id: $("#tipo_vehiculo_id").val(),
        fecha_salida: $("#fecha_salida").val(),
        hora_salida: $("#hora_salida").val(),
        _token: $('meta[name="csrf-token"]').attr("content"),
    };

    $.post(route("salidas.store.directa"), data)
        .done(function () {
            Swal.fire("Correcto", "Salida creada", "success");
            tablaSalidas.ajax.reload();
        })
        .fail(function (xhr) {
            Swal.fire(
                "Error",
                xhr.responseJSON?.message ?? "Error al guardar",
                "error",
            );
        });
};

window.modoGenerarSalidas = function () {
    let html = `
        <div class="mb-2">
            <label class="form-label">Horario <span
                                style="color: red">*</span></label>
            <select id="horario_id_generar">
                ${opcionesHorarios()}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">Fecha inicio <span
                                style="color: red">*</span></label>
            <input type="date" id="fecha_inicio" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Fecha fin <span
                                style="color: red">*</span></label>
            <input type="date" id="fecha_fin" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Días <span
                                style="color: red">*</span></label>

            <div class="d-flex flex-column gap-1">
                <label><input type="checkbox" class="dia" value="1"> Lunes</label>
                <label><input type="checkbox" class="dia" value="2"> Martes</label>
                <label><input type="checkbox" class="dia" value="3"> Miércoles</label>
                <label><input type="checkbox" class="dia" value="4"> Jueves</label>
                <label><input type="checkbox" class="dia" value="5"> Viernes</label>
                <label><input type="checkbox" class="dia" value="6"> Sábado</label>
                <label><input type="checkbox" class="dia" value="7"> Domingo</label>
            </div>
        </div>

        <button class="btn btn-success w-100 mt-2" onclick="generarSalidas()">
            Generar salidas
        </button>
    `;

    $("#tituloPanelSalida").text("Generar salidas");
    $("#panelSalidaContenido").html(html);
    new TomSelect("#horario_id_generar", {
        create: false,
        placeholder: "Seleccione horario...",
    });
    lucide.createIcons();
};

window.guardarSalida = function () {
    let horario_id = $("#horario_id").val();
    let fecha_salida = $("#fecha_salida").val();
    let hora_salida = $("#hora_salida").val(); // 👈 NUEVO
    let estado = $("#estado").val();

    if (validarHoraDuplicada(horario_id, fecha_salida, hora_salida)) {
        Swal.fire(
            "Error",
            "Ya existe una salida con esta fecha y hora",
            "error",
        );
        return;
    }

    if (!horario_id || !fecha_salida || !hora_salida || !estado) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }

    $.post(route("salidas.store"), {
        _token: $("meta[name=csrf-token]").attr("content"),
        horario_id,
        fecha_salida,
        hora_salida, // 👈 NUEVO
        estado,
    })
        .done(function () {
            Swal.fire("Guardado", "", "success");
            tablaSalidas.ajax.reload();
            $("#panelSalidaContenido").html(
                '<p class="text-muted">Selecciona una salida</p>',
            );
        })
        .fail(function (err) {
            Swal.fire(
                "Error",
                err.responseJSON?.message || "No se pudo guardar",
                "error",
            );
        });
};

window.generarSalidas = function () {
    let horario_id = $("#horario_id_generar").val();
    let fecha_inicio = $("#fecha_inicio").val();
    let fecha_fin = $("#fecha_fin").val();

    let dias = [];
    $(".dia:checked").each(function () {
        dias.push($(this).val());
    });

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
        _token: $("meta[name=csrf-token]").attr("content"),
        horario_id: horario_id,
        fecha_inicio: fecha_inicio,
        fecha_fin: fecha_fin,
        dias: dias,
    })
        .done(function (res) {
            Swal.fire(
                "Correcto",
                res.mensaje || "Salidas generadas",
                "success",
            );
            tablaSalidas.ajax.reload();
            $("#panelSalidaContenido").html(
                '<p class="text-muted">Selecciona una salida</p>',
            );
        })
        .fail(function (err) {
            Swal.fire(
                "Error",
                err.responseJSON?.message || "No se pudieron generar",
                "error",
            );
        });
};

// Configura jQuery para mandar el token CSRF en cada petición AJAX
// (evita el error "CSRF token mismatch" en los POST como registrarCheck)
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

function registrarCheck(salidaId, sucursalId = null) {
    let idSucursal = sucursalId || $("#sucursal_manifiesto").val();

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
        if (result.isConfirmed) {
            $.ajax({
                url: route("salidas.registrar_check", {
                    salida: salidaId,
                }),
                method: "POST",
                // 👇 se manda explícito aquí, sin depender del ajaxSetup
                // global de arriba, por si otro script lo pisa o se
                // ejecuta después (típico con módulos @vite con defer).
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                data: {
                    sucursal_id: idSucursal,
                },
                success: function (response) {
                    Swal.fire(
                        "¡Registrado!",
                        "El check fue guardado y las ventas de esta sucursal se congelaron.",
                        "success",
                    );

                    verSalida(salidaId);
                },
                error: function (xhr) {
                    Swal.fire(
                        "Error",
                        xhr.responseJSON?.message ||
                            "No se pudo registrar el check",
                        "error",
                    );
                },
            });
        }
    });
}

function verSalida(id) {
    $("#tituloPanelSalida").text("Detalle de salida");
    $("#panelSalidaContenido").html(`
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
            <div>Cargando detalle de la salida...</div>
        </div>
    `);

    $.get(route("salidas.show", { id: id }), function (salida) {
        let timelineHtml = "";
        if (salida.ruta?.puntos?.length) {
            timelineHtml = salida.ruta.puntos
                .map((punto, index) => {
                    const esCompletado = punto.check_registrado;
                    const esActual = punto.es_actual;

                    let iconClass = "border-secondary bg-white text-secondary";
                    let badgeEstado = `<span class="badge bg-light text-muted border fs-9 fw-semibold py-0 px-1"> 
                <i data-lucide="circle" style="width:8px;"></i> Habilitado 
            </span>`;
                    let icono = "circle";

                    if (esCompletado) {
                        iconClass = "bg-success text-white border-success";
                        icono = "check";
                        badgeEstado = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-9 fw-semibold py-0 px-1"> 
                    <i data-lucide="lock" style="width:8px;"></i> Bloqueado 
                </span>`;
                    } else if (esActual) {
                        iconClass = "bg-primary text-white border-primary";
                        icono = "play";
                        badgeEstado = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-9 fw-semibold py-0 px-1"> 
                    <i data-lucide="navigation" style="width:8px;"></i> Próxima 
                </span>`;
                    }

                    return ` 
            <div class="d-flex align-items-center mb-1 py-1 position-relative"> 
                <div class="me-2 flex-shrink-0" style="z-index: 1;"> 
                    <div class="rounded-circle border d-flex align-items-center justify-content-center ${iconClass}" style="width: 22px; height: 22px;"> 
                        <i data-lucide="${icono}" style="width: 10px;"></i> 
                    </div> 
                </div> 
                <div class="flex-grow-1 border-bottom pb-1 min-w-0"> 
                    <div class="d-flex justify-content-between align-items-center gap-2"> 
                        <span class="fw-bold fs-8 text-truncate ${esActual ? "text-primary" : esCompletado ? "text-muted text-decoration-line-through" : "text-dark"}" title="${punto.nombre}">
                            ${String.fromCharCode(65 + index)}. ${punto.nombre}
                        </span> 
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <span class="fw-bold fs-8 ${esActual ? "text-primary" : "text-muted"}">${punto.hora ?? "-"}</span> 
                            ${badgeEstado}
                        </div>
                    </div> 
                </div> 
            </div> 
        `;
                })
                .join("");
        } else {
            timelineHtml = `<p class="text-muted fs-7">No hay puntos de ruta registrados.</p>`;
        }

        const totalPuntos = salida.ruta?.puntos?.length ?? 0;
        const bloqueadas =
            salida.ruta?.puntos?.filter((p) => p.check_registrado).length ?? 0;
        const habilitadas = totalPuntos - bloqueadas;

        let botonEditarAsignacion = "";
        if (salida.puede_editar_asignacion) {
            botonEditarAsignacion = `
        <button class="btn btn-outline-warning btn-sm w-100 mb-3 d-flex align-items-center justify-content-center gap-1"
            onclick="editarAsignacion(${salida.id})">
            <i data-lucide="user-cog" style="width:14px;"></i>
            Cambiar vehículo / conductor
        </button>
    `;
        }

        let html = `
            <!-- KPIs Superiores: Tarjeta de Ruta + Progreso y Ventas -->
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

            <!-- Timeline Vertical -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fs-8 fw-bold text-muted">Estado de la ruta</span>
                <span class="fs-8 fw-bold text-muted">${habilitadas} sucursal(es) aún venden</span>
            </div>

            <div class="w-100 px-1 mb-3 custom-scrollbar" style="max-height: 120px; overflow-y: auto;">
                ${timelineHtml}
            </div>

            <!-- AJAX Container de Sucursal, Dar Check y Manifiestos -->
            <div id="loadingSucursales" class="text-center text-muted py-3">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <span class="fs-7 d-block mt-1">Cargando datos de sucursal...</span>
            </div>
        `;

        $("#tituloPanelSalida").text("Detalle de salida");
        $("#panelSalidaContenido").html(html);
        lucide.createIcons();

        if (salida.estado !== "en_ruta" && salida.estado !== "finalizado") {
            $("#loadingSucursales").replaceWith(`
                <div class="alert alert-info fs-7 mb-0">
                    Inicia el viaje para habilitar el registro de check y bloquear ventas.
                </div>
            `);
            return;
        }

        $.get(
            route("salidas.sucursales_ruta", { salida: id }),
            function (sucursalesRuta) {
                let tarjetaCheckHtml = "";

                if (window.IS_ADMIN) {
                    const opciones = sucursalesRuta
                        .map((s) => {
                            const bloqueada = s.check_registrado;
                            return `<option value="${s.id}" ${bloqueada ? "disabled" : ""}>
                                ${s.nombre}${bloqueada ? " — Ventas bloqueadas" : ""}
                            </option>`;
                        })
                        .join("");

                    tarjetaCheckHtml = `
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
                    </div>
                `;
                } else {
                    let mia = sucursalesRuta.find(
                        (s) =>
                            String(s.id) === String(window.USER_SUCURSAL?.id),
                    );

                    if (!mia) {
                        tarjetaCheckHtml = `
                        <div class="alert alert-warning fs-7 mt-3">
                            Tu sucursal no forma parte de esta ruta.
                        </div>
                    `;
                    } else {
                        const yaDioCheck = mia.check_registrado;

                        tarjetaCheckHtml = `
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
                        </div>
                    `;
                    }
                }

                let botonesManifiestos = "";
                if (
                    window.IS_ADMIN ||
                    sucursalesRuta.some(
                        (s) =>
                            String(s.id) === String(window.USER_SUCURSAL?.id),
                    )
                ) {
                    botonesManifiestos = `
                    <div class="mt-3">
                        <small class="fw-bold text-muted d-block mb-2 fs-8">Manifiestos de la sucursal</small>
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-primary btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-1" onclick="abrirManifiesto(${salida.id}, 'pasajeros')">
                                    <i data-lucide="user" style="width:14px;"></i> Pasajeros
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-info btn-sm text-white w-100 py-2 d-flex align-items-center justify-content-center gap-1" onclick="abrirManifiesto(${salida.id}, 'encomiendas')">
                                    <i data-lucide="package" style="width:14px;"></i> Encomiendas
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-warning btn-sm text-white w-100 py-2 d-flex align-items-center justify-content-center gap-1" onclick="abrirManifiesto(${salida.id}, 'bodega')">
                                    <i data-lucide="archive" style="width:14px;"></i> Bodega
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-success btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-1" onclick="abrirManifiesto(${salida.id}, 'conductores')">
                                    <i data-lucide="truck" style="width:14px;"></i> Conductores
                                </button>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-secondary btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-1" onclick="abrirManifiesto(${salida.id}, 'pasajeros_real')">
                                    <i data-lucide="file-text" style="width:14px;"></i> Pasajeros detallado
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                    if (salida.estado === "finalizado" && window.IS_ADMIN) {
                        botonesManifiestos += `
                        <div class="mt-2">
                            <button class="btn btn-dark btn-sm w-100 py-2" onclick="imprimirTodosManifiestos(${salida.id})">
                                Imprimir todos los manifiestos (todas las sucursales)
                            </button>
                        </div>
                    `;
                    }
                }

                $("#loadingSucursales").replaceWith(
                    tarjetaCheckHtml + botonesManifiestos,
                );
                lucide.createIcons();
            },
        );
    });
}

function editarAsignacion(id) {
    $.get(route("salidas.show", { id: id }), function (salida) {
        let html = `
    <div class="alert alert-warning fs-7 mb-3">
        Estás cambiando el vehículo/conductor de una salida ya iniciada.
        Usa esto solo en caso de imprevistos (ej. conductor no puede salir).
    </div>

    <div class="mb-2">
        <label class="form-label">Vehículo <span class="text-danger">*</span></label>
        <select id="vehiculo_id" class="form-select">
            <option value="">Seleccione vehículo</option>
        </select>
    </div>

    <div class="mb-2">
        <label class="form-label">Conductor principal <span class="text-danger">*</span></label>
        <select id="conductor_principal_id" class="form-select">
            <option value="">Seleccione</option>
        </select>
    </div>

    <div class="mb-2">
        <label class="form-label">Conductor secundario</label>
        <select id="conductor_secundario_id" class="form-select">
            <option value="">Opcional</option>
            ${window.CONDUCTORES.map(
                (c) => `
                    <option value="${c.id}" ${c.id == salida.conductor_secundario_id ? "selected" : ""}>
                        ${c.persona.nombres} ${c.persona.apellidos}
                    </option>
                `,
            ).join("")}
        </select>
    </div>

    <button class="btn btn-warning w-100 mt-2" onclick="guardarEdicionAsignacion(${salida.id}, '${salida.horario_id}', '${salida.fecha_salida}')">
        Guardar cambio
    </button>

    <button class="btn btn-link w-100 mt-1" onclick="verSalida(${salida.id})">
        Cancelar
    </button>
`;

        $("#tituloPanelSalida").text("Editar asignación (emergencia)");
        $("#panelSalidaContenido").html(html);

        cargarRecursosDisponibles(salida);
        lucide.createIcons();
    });
}

window.guardarEdicionAsignacion = function (id, horario_id, fecha_salida) {
    let vehiculo_id = $("#vehiculo_id").val();
    let conductor_principal_id = $("#conductor_principal_id").val();
    let conductor_secundario_id = $("#conductor_secundario_id").val();

    if (!vehiculo_id || !conductor_principal_id) {
        Swal.fire("Error", "Debe asignar vehículo y conductor", "error");
        return;
    }

    $.ajax({
        url: route("salidas.update", { id: id }),
        method: "POST",
        data: {
            _token: $("meta[name=csrf-token]").attr("content"),
            _method: "PUT",
            horario_id,
            fecha_salida,
            estado: "en_ruta", // no cambia el estado, solo la asignación
            vehiculo_id,
            conductor_principal_id,
            conductor_secundario_id,
        },
        success: function () {
            Swal.fire("Actualizado", "Vehículo/conductor cambiado", "success");
            tablaSalidas.ajax.reload();
            verSalida(id); // vuelve al detalle
        },
        error: function (err) {
            Swal.fire(
                "Error",
                err.responseJSON?.message || "No se pudo actualizar",
                "error",
            );
        },
    });
};

function actualizarOpcionesConductores() {
    let principal = $("#conductor_principal_id").val();
    let secundario = $("#conductor_secundario_id").val();

    $("#conductor_principal_id option").each(function () {
        $(this).prop("disabled", !!secundario && $(this).val() === secundario);
    });

    $("#conductor_secundario_id option").each(function () {
        $(this).prop("disabled", !!principal && $(this).val() === principal);
    });
}

function cargarRecursosDisponibles(salida) {
    $.get(
        route("salidas.recursos_disponibles", { salida: salida.id }),
        function (res) {
            let vehiculosHtml = `<option value="">Seleccione vehículo</option>`;
            res.vehiculos.forEach((v) => {
                vehiculosHtml += `
                <option value="${v.id}" ${v.id == salida.vehiculo_id ? "selected" : ""}>
                    ${v.tipo_vehiculo.descripcion} - ${v.numero_placa}
                </option>
            `;
            });
            $("#vehiculo_id").html(vehiculosHtml);

            let conductoresHtml = `<option value="">Seleccione</option>`;
            res.conductores.forEach((c) => {
                conductoresHtml += `<option value="${c.id}">${c.persona.nombres} ${c.persona.apellidos}</option>`;
            });

            $("#conductor_principal_id").html(conductoresHtml);
            $("#conductor_secundario_id").html(
                `<option value="">Opcional</option>` +
                    conductoresHtml.replace(
                        '<option value="">Seleccione</option>',
                        "",
                    ),
            );

            $("#conductor_principal_id").val(
                salida.conductor_principal_id ?? "",
            );
            $("#conductor_secundario_id").val(
                salida.conductor_secundario_id ?? "",
            );

            actualizarOpcionesConductores();
        },
    );
}

$(document).on(
    "change",
    "#conductor_principal_id, #conductor_secundario_id",
    actualizarOpcionesConductores,
);

function abrirManifiesto(salidaId, tipo) {
    let sucursalId = $("#sucursal_manifiesto").val();

    if (!sucursalId) {
        Swal.fire("Atención", "Selecciona una sucursal", "warning");
        return;
    }

    let rutasPorTipo = {
        pasajeros: "salidas.manifiesto_pasajeros",
        encomiendas: "salidas.manifiesto_encomiendas",
        bodega: "salidas.manifiesto_bodega",
        conductores: "salidas.manifiesto_conductores",
        pasajeros_real: "salidas.manifiesto_pasajeros_real",
    };

    let url =
        route(rutasPorTipo[tipo], { salida: salidaId }) +
        "?sucursal_id=" +
        sucursalId;

    window.open(url, "_blank");
}

function imprimirTodosManifiestos(salidaId) {
    let url = route("salidas.manifiesto_pasajeros.todos", { salida: salidaId });
    window.open(url, "_blank");
}

function bloqueCambioEstado(salida = {}) {
    let visible = ["reprogramado", "cancelado"].includes(salida.estado)
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

            <input 
                type="date" 
                id="fecha_cambio_estado" 
                class="form-control" 
                min="${hoy()}"
                value="${salida.fecha_cambio_estado ?? hoy()}">
        </div>

        <div class="mb-2">
            <label class="form-label">Nueva hora <span class="text-danger">*</span></label>

            <input 
                type="time" 
                id="hora_cambio_estado" 
                class="form-control" 
                value="${salida.hora_cambio_estado ?? ahora()}">
        </div>

        <div class="mb-2">
            <label class="form-label">Motivo <span class="text-danger">*</span></label>

            <textarea 
                id="motivo_cambio_estado" 
                class="form-control" 
                rows="3">${salida.motivo_cambio_estado ?? ""}
            </textarea>
        </div>
    </div>
`;
}

$(document).on("click", ".iniciar-ruta", function () {
    let id = $(this).data("id");
    iniciarRuta(id);
});

function iniciarRuta(id) {
    $.get(route("salidas.show", { id: id }), function (salida) {
        let html = `
    <div class="alert alert-info fs-7 mb-3">
        Vas a iniciar la ruta <strong>${salida.ruta?.nombre ?? ""}</strong>,
        programada para el ${salida.fecha_formateada ?? "-"} a las ${salida.hora_salida ?? "-"}.
    </div>

    <input type="hidden" id="fecha_salida_hidden" value="${salida.fecha_salida}">

    <div class="mb-2">
        <label class="form-label">
            Vehículo <span class="text-danger">*</span>
        </label>

        <select id="vehiculo_id" class="form-select">
            <option value="">Seleccione vehículo</option>
        </select>
    </div>

    <div class="mb-2">
        <label class="form-label">
            Conductor principal <span class="text-danger">*</span>
        </label>

        <select id="conductor_principal_id" class="form-select">
            <option value="">Seleccione</option>
        </select>
    </div>

    <div class="mb-2">
        <label class="form-label">
            Conductor secundario
        </label>

        <select id="conductor_secundario_id" class="form-select">
            <option value="">Opcional</option>

            ${window.CONDUCTORES.map(
                (c) => `
                    <option value="${c.id}">
                        ${c.persona.nombres} ${c.persona.apellidos}
                    </option>
                `,
            ).join("")}
        </select>
    </div>

    <button
        class="btn btn-success w-100 mt-2"
        onclick="guardarInicioRuta(${salida.id}, ${salida.horario_id})">
        Iniciar ruta
    </button>
`;

        $("#tituloPanelSalida").text("Iniciar ruta");
        $("#panelSalidaContenido").html(html);

        // Reutiliza el mismo endpoint que ya usa editarSalida
        // para llenar vehículo y conductores disponibles
        cargarRecursosDisponibles(salida);

        lucide.createIcons();
    });
}

window.guardarInicioRuta = function (id, horario_id) {
    let fecha_salida = $("#fecha_salida_hidden").val();
    let vehiculo_id = $("#vehiculo_id").val();
    let conductor_principal_id = $("#conductor_principal_id").val();
    let conductor_secundario_id = $("#conductor_secundario_id").val();

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
            url: route("salidas.update", { id: id }),
            method: "POST",
            data: {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "PUT",
                horario_id,
                fecha_salida,
                estado: "en_ruta",
                vehiculo_id,
                conductor_principal_id,
                conductor_secundario_id,
            },
            success: function () {
                Swal.fire("Ruta iniciada", "", "success");
                tablaSalidas.ajax.reload();
                $("#panelSalidaContenido").html(
                    '<p class="text-muted">Selecciona una salida</p>',
                );
            },
            error: function (err) {
                Swal.fire(
                    "Error",
                    err.responseJSON?.message || "No se pudo iniciar la ruta",
                    "error",
                );
            },
        });
    });
};

$(document).on("click", ".finalizar-ruta", function () {
    let id = $(this).data("id");
    finalizarRuta(id);
});

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

        $.get(route("salidas.show", { id: id }), function (salida) {
            $.ajax({
                url: route("salidas.update", { id: id }),
                method: "POST",
                data: {
                    _token: $("meta[name=csrf-token]").attr("content"),
                    _method: "PUT",
                    horario_id: salida.horario_id,
                    fecha_salida: salida.fecha_salida,
                    estado: "finalizado",
                    vehiculo_id: salida.vehiculo_id,
                    conductor_principal_id: salida.conductor_principal_id,
                    conductor_secundario_id: salida.conductor_secundario_id,
                },
                success: function () {
                    Swal.fire("Ruta finalizada", "", "success");
                    tablaSalidas.ajax.reload();
                    $("#panelSalidaContenido").html(
                        '<p class="text-muted">Selecciona una salida</p>',
                    );
                },
                error: function (err) {
                    Swal.fire(
                        "Error",
                        err.responseJSON?.message ||
                            "No se pudo finalizar la ruta",
                        "error",
                    );
                },
            });
        });
    });
}

function editarSalida(id) {
    $.get(route("salidas.show", { id: id }), function (salida) {
        let html = `
    <div class="mb-2">
        <label class="form-label">
            Estado <span class="text-danger">*</span>
        </label>

        <select id="estado" class="form-select">
            ${opcionesEstados(salida.estado)}
        </select>
    </div>

    <div id="bloqueDatosSalida">

        <div class="mb-2">
            <label class="form-label">
                Horario <span class="text-danger">*</span>
            </label>

            <select id="horario_id">
                ${opcionesHorarios(salida.horario_id)}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">
                Fecha <span class="text-danger">*</span>
            </label>

            <input
                type="date"
                id="fecha_salida"
                class="form-control"
                value="${salida.fecha_salida}">
        </div>

    </div>

    ${bloqueCambioEstado(salida)}

    <div id="bloqueAsignacionRuta"
        style="${salida.estado === "en_ruta" ? "" : "display:none;"}">

        <hr>

        <div class="mb-2">
            <label class="form-label">
                Vehículo <span class="text-danger">*</span>
            </label>

            <select id="vehiculo_id" class="form-select">
                <option value="">Seleccione vehículo</option>

               ${cargarRecursosDisponibles(salida)}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">
                Conductor principal
                <span class="text-danger">*</span>
            </label>

            <select id="conductor_principal_id" class="form-select">
                <option value="">Seleccione</option>

               ${cargarRecursosDisponibles(salida)}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">
                Conductor secundario
            </label>

            <select id="conductor_secundario_id" class="form-select">
                <option value="">Opcional</option>

                ${window.CONDUCTORES.map(
                    (c) => `
                        <option
                            value="${c.id}"
                            ${c.id == salida.conductor_secundario_id ? "selected" : ""}>

                            ${c.persona.nombres}
                            ${c.persona.apellidos}
                        </option>
                    `,
                ).join("")}
            </select>
        </div>

    </div>

  <button
    class="btn btn-success w-100 mt-2"
    onclick="guardarEdicionSalida(${salida.id}, ${salida.horario_id})">
    Guardar cambios
</button>
`;

        $("#tituloPanelSalida").text("Editar salida");
        $("#panelSalidaContenido").html(html);

        let horario = window.HORARIOS_SALIDA.find(
            (h) => String(h.id) === String(salida.horario_id),
        );

        let horaOriginal = salida.hora_salida;
        let fechaOriginal = salida.fecha_salida;

        if (horario) {
            let filtrados = window.VEHICULOS.filter(
                (v) =>
                    String(v.tipo_vehiculo_id) ===
                    String(horario.tipo_vehiculo_id),
            );

            let options = `<option value="">Seleccione vehículo</option>`;

            filtrados.forEach((v) => {
                options += `
            <option value="${v.id}" ${String(v.id) === String(salida.vehiculo_id) ? "selected" : ""}>
                ${v.tipo_vehiculo.descripcion} - ${v.numero_placa}
            </option>
        `;
            });

            $("#vehiculo_id").html(options);
        }

        new TomSelect("#horario_id", {
            create: false,
        });

        function aplicarReglasEstado() {
            let estado = $("#estado").val();

            let enRuta = estado === "en_ruta";
            let reprogramado = estado === "reprogramado";
            let cancelado = estado === "cancelado";
            let finalizado = estado === "finalizado";

            let bloquearBase = enRuta || finalizado;

            // 🔒 HORARIO
            if ($("#horario_id")[0]?.tomselect) {
                if (bloquearBase || reprogramado) {
                    $("#horario_id")[0].tomselect.lock();
                } else {
                    $("#horario_id")[0].tomselect.unlock();
                }
            }

            // 🔒 FECHA BASE
            $("#fecha_salida").prop("readonly", bloquearBase || reprogramado);

            // 📌 BLOQUE BASE (si está en ruta, ocultas esto)
            $("#bloqueDatosSalida").toggle(!enRuta);

            // 🚛 ASIGNACIÓN SOLO EN RUTA
            $("#bloqueAsignacionRuta").toggle(enRuta);

            // 🔁 CAMBIO DE ESTADO
            $("#bloqueCambioEstado").toggle(reprogramado || cancelado);

            $("#fecha_cambio_estado").closest(".mb-2").toggle(reprogramado);
            $("#hora_cambio_estado").closest(".mb-2").toggle(reprogramado);
        }

        $("#estado").on("change", aplicarReglasEstado);
        aplicarReglasEstado();

        lucide.createIcons();
    });
}

window.guardarEdicionSalida = function (id, horarioOriginal) {
    let horario_id = $("#horario_id").val() || horarioOriginal;
    let fecha_salida = $("#fecha_salida").val();
    let estado = $("#estado").val();
    let fecha_cambio_estado = $("#fecha_cambio_estado").val();
    let hora_cambio_estado = $("#hora_cambio_estado").val();
    let motivo_cambio_estado = $("#motivo_cambio_estado").val();
    let vehiculo_id = $("#vehiculo_id").val();
    let conductor_principal_id = $("#conductor_principal_id").val();
    let conductor_secundario_id = $("#conductor_secundario_id").val();

    let cambioHora = String(horario_id) !== String(horarioOriginal);

    if (cambioHora && estado !== "reprogramado") {
        Swal.fire(
            "Error",
            "No puedes cambiar la hora sin reprogramar",
            "error",
        );
        return;
    }
    if (!horario_id || !fecha_salida || !estado) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }

    if (estado === "reprogramado") {
        if (
            !fecha_cambio_estado ||
            !hora_cambio_estado ||
            !motivo_cambio_estado
        ) {
            Swal.fire("Error", "Debe ingresar fecha, hora y motivo", "error");
            return;
        }
    }

    if (estado === "cancelado") {
        if (!motivo_cambio_estado) {
            Swal.fire("Error", "Debe ingresar motivo", "error");
            return;
        }
    }

    if (estado === "en_ruta") {
        if (!vehiculo_id || !conductor_principal_id) {
            Swal.fire("Error", "Debe asignar vehículo y conductor", "error");
            return;
        }
    }

    $.ajax({
        url: route("salidas.update", { id: id }),
        method: "POST",
        data: {
            _token: $("meta[name=csrf-token]").attr("content"),
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
        success: function () {
            Swal.fire("Actualizado", "", "success");
            tablaSalidas.ajax.reload();
        },
    });
};

function eliminarSalida(id) {
    Swal.fire({
        title: "¿Eliminar salida?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: route("salidas.destroy", { id: id }),
            method: "POST",
            data: {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "DELETE",
            },
            success: function () {
                Swal.fire("Eliminado", "", "success");
                tablaSalidas.ajax.reload();
                $("#panelSalidaContenido").html(
                    '<p class="text-muted">Selecciona una salida</p>',
                );
            },
            error: function (err) {
                Swal.fire(
                    "Error",
                    err.responseJSON?.message || "No se pudo eliminar",
                    "error",
                );
            },
        });
    });
}

$(document).on("click", ".ver", function () {
    let id = $(this).data("id");
    verSalida(id);
});

$(document).on("click", ".editar", function () {
    let id = $(this).data("id");
    editarSalida(id);
});

$(document).on("click", ".eliminar", function () {
    let id = $(this).data("id");
    eliminarSalida(id);
});

$(document).on("change", "#horario_id", function () {
    let horario_id = $(this).val();

    let horario = window.HORARIOS_SALIDA.find(
        (h) => String(h.id) === String(horario_id),
    );

    if (!horario) {
        $("#vehiculo_id").html('<option value="">Seleccione vehículo</option>');
        return;
    }

    let filtrados = window.VEHICULOS.filter(
        (v) => String(v.tipo_vehiculo_id) === String(horario.tipo_vehiculo_id),
    );

    let options = `<option value="">Seleccione vehículo</option>`;

    filtrados.forEach((v) => {
        options += `
            <option value="${v.id}">
                ${v.tipo_vehiculo.descripcion} - ${v.numero_placa}
            </option>
        `;
    });

    $("#vehiculo_id").html(options);
});
