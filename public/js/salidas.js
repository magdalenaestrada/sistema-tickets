let tablaSalidas;
let horariosSalida = window.HORARIOS_SALIDA || [];

$(document).ready(function () {
    tablaSalidas = $("#tablaSalidas").DataTable({
        ajax: {
            url: route("salidas.datatable"),
        },
        columns: [
            { data: "id" },
            { data: "ruta" },
            { data: "fecha_formateada" },
            { data: "hora_salida" },
            { data: "hora_llegada" },
            { data: "estado_badge" },
            { data: "acciones" },
        ],
        order: [[2, "asc"]],
        responsive: true,
        info: false,
        dom: "rtip",
        drawCallback: function () {
            lucide.createIcons();
        },
    });
});

function hoy() {
    let fecha = new Date();
    return fecha.toISOString().split("T")[0];
}

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

window.modoCrearSalida = function () {
    let html = `
        <div class="mb-2">
            <label class="form-label">Horario <span
                                style="color: red">*</span></label>
            <select id="horario_id">
                ${opcionesHorarios()}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">Fecha <span
                                style="color: red">*</span></label>
            <input type="date" id="fecha_salida" class="form-control">
        </div>

        <div class="mb-2">
            <label class="form-label">Estado <span
                                style="color: red">*</span></label>
            <select id="estado" class="form-select">
                ${opcionesEstados()}
            </select>
        </div>

        <button class="btn btn-primary w-100 mt-2" onclick="guardarSalida()">
            Guardar salida
        </button>
    `;

    $("#tituloPanelSalida").text("Crear salida");
    $("#panelSalidaContenido").html(html);

    new TomSelect("#horario_id", {
        create: false,
        placeholder: "Seleccione horario...",
    });

    lucide.createIcons();
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
    let estado = $("#estado").val();

    if (!horario_id || !fecha_salida || !estado) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }

    Swal.fire({
        title: "Guardando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.post(route("salidas.store"), {
        _token: $("meta[name=csrf-token]").attr("content"),
        horario_id: horario_id,
        fecha_salida: fecha_salida,
        estado: estado,
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
        let botones = "";

        if (salida.ruta?.puntos?.length) {
            puntos = `
                <ul class="list-group mt-2">
                    ${salida.ruta.puntos
                        .map(
                            (p) => `
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>${p.orden}. ${p.nombre}</span>
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

        if (salida.estado === "en_ruta") {
            botones = `
                <div class="d-grid gap-2 mt-3">
                    <a href="${route("salidas.manifiesto_pasajeros", { salida: salida.id })}" class="btn btn-primary" target="_blank">
                        Manifiesto de pasajeros
                    </a>

                    <a href="${route("salidas.manifiesto_encomiendas", { salida: salida.id })}" class="btn btn-warning" target="_blank">
                        Manifiesto de encomiendas
                    </a>

                    <a href="${route("salidas.manifiesto_conductores", { salida: salida.id })}" class="btn btn-success" target="_blank">
                        Manifiesto de conductores
                    </a>
                </div>
            `;
        }

        $("#tituloPanelSalida").text("Detalle de salida");
        $("#panelSalidaContenido").html(html + puntos + botones);
        lucide.createIcons();
    });
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

                ${window.VEHICULOS.map(
                    (v) => `
                        <option
                            value="${v.id}"
                            ${v.id == salida.vehiculo_id ? "selected" : ""}>

                            ${v.tipo_vehiculo.descripcion}
                            - ${v.numero_placa}
                        </option>
                    `,
                ).join("")}
            </select>
        </div>

        <div class="mb-2">
            <label class="form-label">
                Conductor principal
                <span class="text-danger">*</span>
            </label>

            <select id="conductor_principal_id" class="form-select">
                <option value="">Seleccione</option>

                ${window.CONDUCTORES.map(
                    (c) => `
                        <option
                            value="${c.id}"
                            ${c.id == salida.conductor_principal_id ? "selected" : ""}>

                            ${c.persona.nombres}
                            ${c.persona.apellidos}
                        </option>
                    `,
                ).join("")}
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
        onclick="guardarEdicionSalida(${salida.id})">

        Guardar cambios
    </button>
`;

        $("#tituloPanelSalida").text("Editar salida");
        $("#panelSalidaContenido").html(html);

        let horario = window.HORARIOS_SALIDA.find(
            (h) => String(h.id) === String(salida.horario_id),
        );

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

        function actualizarFormularioPorEstado() {
            let estado = $("#estado").val();

            let esEnRuta = estado === "en_ruta";
            let esReprogramado = estado === "reprogramado";
            let esCancelado = estado === "cancelado";
            let esFinalizado = estado === "finalizado";

            $("#bloqueAsignacionRuta").toggle(esEnRuta);

            $("#bloqueCambioEstado").toggle(esReprogramado || esCancelado);

            $("#fecha_cambio_estado").closest(".mb-2").toggle(esReprogramado);

            $("#hora_cambio_estado").closest(".mb-2").toggle(esReprogramado);

            $("#fecha_salida").prop("readonly", esFinalizado);

            if ($("#horario_id")[0]?.tomselect) {
                esFinalizado
                    ? $("#horario_id")[0].tomselect.disable()
                    : $("#horario_id")[0].tomselect.enable();
            }
        }

        $("#estado").on("change", actualizarFormularioPorEstado);

        actualizarFormularioPorEstado();

        lucide.createIcons();
    });
}

window.guardarEdicionSalida = function (id) {
    let horario_id = $("#horario_id").val();
    let fecha_salida = $("#fecha_salida").val();
    let estado = $("#estado").val();
    let fecha_cambio_estado = $("#fecha_cambio_estado").val();
    let hora_cambio_estado = $("#hora_cambio_estado").val();
    let motivo_cambio_estado = $("#motivo_cambio_estado").val();
    let vehiculo_id = $("#vehiculo_id").val();
    let conductor_principal_id = $("#conductor_principal_id").val();
    let conductor_secundario_id = $("#conductor_secundario_id").val();

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
