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
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `<input type="checkbox" class="chk-salida" value="${data.id}">`;
                },
            },
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

function verSalida(id) {
    $.get(route("salidas.show", { id: id }), function (salida) {
        let puntos = "";

        if (salida.ruta?.puntos?.length) {
            puntos = `
        <ul class="list-group mt-2">
            ${salida.ruta.puntos
                .map(
                    (p) => `
                    <li class="list-group-item d-flex justify-content-between">
                        <span>${p.orden}. ${p.nombre}</span>
                        <span class="badge bg-primary">${p.hora}</span>
                    </li>
                `,
                )
                .join("")}
        </ul>
    `;
        }

        let html = `
            <h6>${salida.ruta?.nombre ?? "Sin ruta"}</h6>

            <div class="mb-2"><strong>Fecha:</strong> ${salida.fecha_formateada ?? "-"}</div>
            <div class="mb-2"><strong>Hora salida:</strong> ${salida.hora_salida ?? "-"}</div>
            <div class="mb-2"><strong>Hora llegada:</strong> ${salida.hora_llegada ?? "-"}</div>
            <div class="mb-2"><strong>Tipo viaje:</strong> ${salida.tipo_viaje ?? "-"}</div>
            <div class="mb-2"><strong>Tipo vehículo:</strong> ${salida.tipo_vehiculo ?? "-"}</div>
            <div class="mb-2"><strong>Estado:</strong> ${salida.estado ?? "-"}</div>
        `;

        // Render inicial sin selector/botones (llegan después vía AJAX)
        $("#tituloPanelSalida").text("Detalle de salida");
        $("#panelSalidaContenido").html(html + puntos);
        lucide.createIcons();

        if (salida.estado !== "en_ruta" && salida.estado !== "finalizado") {
            return; // no hay manifiestos que mostrar
        }

        $("#panelSalidaContenido").html(
            html +
                puntos +
                '<div id="loadingSucursales" class="text-muted mt-3">Cargando opciones de manifiesto...</div>',
        );

        $.get(
            route("salidas.sucursales_ruta", { salida: id }),
            function (sucursalesRuta) {
                let selectorSucursal = "";

                if (window.IS_ADMIN) {
                    selectorSucursal = `
                    <div class="mb-2 mt-3">
                        <label class="form-label">Seleccionar sucursal</label>
                        <select id="sucursal_manifiesto" class="form-select">
                            ${sucursalesRuta
                                .map(
                                    (s) =>
                                        `<option value="${s.id}">${s.nombre}</option>`,
                                )
                                .join("")}
                        </select>
                    </div>
                `;
                } else {
                    let mia = sucursalesRuta.find(
                        (s) =>
                            String(s.id) === String(window.USER_SUCURSAL?.id),
                    );

                    if (!mia) {
                        selectorSucursal = `
                        <div class="alert alert-warning mt-3">
                            Tu sucursal no forma parte de esta ruta.
                        </div>
                    `;
                    } else {
                        selectorSucursal = `
                        <div class="mb-2 mt-3">
                            <label class="form-label">Sucursal</label>
                            <input type="text" class="form-control" value="${mia.nombre}" disabled>
                            <input type="hidden" id="sucursal_manifiesto" value="${mia.id}">
                        </div>
                    `;
                    }
                }

                let botones = "";

                // Solo mostramos botones si hay una sucursal válida seleccionable
                if (
                    window.IS_ADMIN ||
                    sucursalesRuta.some(
                        (s) =>
                            String(s.id) === String(window.USER_SUCURSAL?.id),
                    )
                ) {
                    botones = `
                    <div class="d-grid gap-2 mt-2">
                        <button class="btn btn-primary" onclick="abrirManifiesto(${salida.id}, 'pasajeros')">
                            Manifiesto de pasajeros
                        </button>
                        <button class="btn btn-info" onclick="abrirManifiesto(${salida.id}, 'encomiendas')">
                            Manifiesto de encomiendas
                        </button>
                          <button class="btn btn-warning" onclick="abrirManifiesto(${salida.id}, 'bodega')">
                            Manifiesto de bodega
                        </button>
                        <button class="btn btn-success" onclick="abrirManifiesto(${salida.id}, 'conductores')">
                            Manifiesto de conductores
                        </button>
                        <button class="btn btn-secondary" onclick="abrirManifiesto(${salida.id}, 'pasajeros_real')">
                            Manifiesto de pasajeros (Detallado)
                        </button>
                    </div>
                `;

                    if (salida.estado === "finalizado" && window.IS_ADMIN) {
                        botones += `
                        <div class="d-grid gap-2 mt-2">
                            <button class="btn btn-dark" onclick="imprimirTodosManifiestos(${salida.id})">
                                Imprimir todos los manifiestos (todas las sucursales)
                            </button>
                        </div>
                    `;
                    }
                }

                $("#loadingSucursales").replaceWith(selectorSucursal + botones);
                lucide.createIcons();
            },
        );
    });
}

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
