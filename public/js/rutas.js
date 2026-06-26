let tablaRutas;
let UBIGEO = [];
let sucursales = [];
let tramosActuales = [];
let pueblitos = [];
let puntosOriginales = [];
let hayCambiosEnTramos = false;
let puntosOriginalesOrdenados = [];

$(document).ready(async function () {
    pueblitos = await $.get(route("pueblitos.lista"));
    sucursales = await $.get(route("sucursales.lista"));

    tablaRutas = $("#tablaRutas").DataTable({
        ajax: {
            url: route("rutas.datatable"),
            data: function (d) {
                d.nombre = $("#filtro_nombre").val();
            },
        },

        columns: [
            { data: "DT_RowIndex" },
            { data: "nombre" },
            { data: "puntos" },
            { data: "estado" },
            { data: "acciones" },
        ],

        responsive: true,
        info: false,
        dom: "rtip",
        order: [[0, "desc"]],

        drawCallback: function () {
            lucide.createIcons();
        },
    });

    UBIGEO = await $.get(route("ubigeos.todo"));
});

$(document).on("click", ".btn-crear", function () {
    modoCrear();
});

function modoCrear() {
    let html = `
       <form id="formRuta">
    <h6 class="mb-2"><b>NOMBRE DE LA RUTA <span
                                style="color: red">*</span></b></h6>

    <input type="text" id="nombreNuevaRuta"
        class="form-control form-control-sm mb-3"
        placeholder="Nombre de la ruta" required>

<div id="contenedorPuntosWrapper">
        <div id="contenedorPuntos"></div>
    </div>
 <button type="button"
    class="btn btn-success btn-xs mb-2"
    onclick="agregarPunto()">

    <i data-lucide="plus"></i>
    Añadir punto
</button>

    <hr>

    <h6>TRAMOS</h6>
    <div id="contenedorTramos"></div>

    <button type="submit" class="btn btn-primary w-100 mt-2">
        Guardar ruta
    </button>
</form>
    `;

    $("#tituloPanel").text("Crear Ruta");
    $("#panelContenido").html(html);
    $("#formRuta").on("submit", function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }

        guardarRuta();
    });
    setTimeout(() => {
        agregarPunto();
        agregarPunto();
        activarOrdenamiento();
    }, 100);
}

function guardarRuta() {
    let nombre = $("#nombreNuevaRuta").val();
    let puntos = [];
    let duracion = [];
    let costo = [];

    $("#contenedorPuntos .punto").each(function () {
        console.log("TRAMOS", tramosActuales);
        console.log("ORIGEN", origenActual);
        console.log("DESTINO", destinoActual);
        console.log("tramoExistente", tramoExistente);
        let pueblitoId = $(this).find(".pueblito").val();

        let pueblito = pueblitos.find((p) => p.id == pueblitoId);

        puntos.push({
            distrito_id: pueblito?.distrito_id || null,
            pueblito_id: pueblitoId,
            sucursal_id: $(this).find(".sucursal").val(),
        });
    });

    if (puntos.some((p) => !p.pueblito_id)) {
        Swal.fire("Error", "Todos los puntos deben tener parada", "error");
        return;
    }

    $("input[name='horas[]']").each(function (index) {
        let horas = parseInt($(this).val()) || "";
        let minutos =
            parseInt($("input[name='minutos[]']").eq(index).val()) || "";

        let total = horas * 60 + minutos;

        duracion.push(total);
    });

    $("input[name='costo[]']").each(function () {
        costo.push($(this).val() || null);
    });

    Swal.fire({
        title: "Guardando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.post(route("rutas.store"), {
        _token: $("meta[name=csrf-token]").attr("content"),
        nombre: nombre,
        puntos: puntos,
        duracion: duracion,
        costo: costo,
    })
        .done(function () {
            Swal.fire("Guardado", "", "success");

            tablaRutas.ajax.reload();

            $("#panelContenido").html(
                '<p class="text-muted">Selecciona una ruta</p>',
            );
        })
        .fail(function () {
            Swal.fire("Error", "No se pudo guardar", "error");
        });
}

function agregarPunto(data = null) {
    let index = $("#contenedorPuntos .punto").length;

    let html = `
<div class="punto card shadow-sm border-0 mb-2">

    <div class="card-body py-2 px-2">

        <div class="d-flex align-items-center gap-2">

            <div class="drag-handle px-1">
                <i data-lucide="grip-vertical"></i>
            </div>

          <span class="badge bg-dark numero-punto">
    ${index + 1}
</span>

            <select class="form-select form-select-sm pueblito">
                <option value="">Parada</option>
            </select>

            <select class="form-select form-select-sm sucursal">
                <option value="">Sucursal</option>
            </select>

            <button type="button"
                class="btn btn-danger btn-xs"
                onclick="eliminarPunto(this)">
                <i data-lucide="trash-2"></i>
            </button>

        </div>

    </div>

</div>
`;

    $("#contenedorPuntos").append(html);

    let punto = $("#contenedorPuntos .punto").last();

    let selectPueblito = punto.find(".pueblito");

    let listaPueblitos = [...pueblitos];

    let pueblitosOrdenados = [...pueblitos].sort((a, b) =>
        a.descripcion.localeCompare(b.descripcion, "es", {
            sensitivity: "base",
        }),
    );

    pueblitosOrdenados.forEach((p) => {
        selectPueblito.append(`
        <option value="${p.id}">
            ${p.descripcion}
        </option>
    `);
    });

    let sucursalSelect = punto.find(".sucursal");

    let listaSucursales = [...sucursales];

    listaSucursales.sort((a, b) =>
        a.nombre_comercial.localeCompare(b.nombre_comercial),
    );

    listaSucursales.forEach((s) => {
        sucursalSelect.append(`
            <option value="${s.id}">
                ${s.nombre_comercial}
            </option>
        `);
    });

    if (data) {
        selectPueblito.val(String(data.pueblito_id));

        if (data?.sucursal_id) {
            sucursalSelect.val(String(data.sucursal_id));
        }
    }

    generarTramos();
    lucide.createIcons();
}

function eliminarPunto(btn) {
    let total = $("#contenedorPuntos .punto").length;

    if (total <= 2) {
        Swal.fire("Debe tener minimo 2 puntos", "", "warning");
        return;
    }

    $(btn).closest(".punto").remove();

    reordenarPuntos();
    generarTramos();
}

function reordenarPuntos() {
    $("#contenedorPuntos .punto").each(function (i) {
        $(this)
            .find(".badge")
            .text(`${i + 1}`);
    });
}

function validarPuntos() {
    let valido = true;

    $("#contenedorPuntos .punto").each(function () {
        let select = $(this).find(".pueblito");

        if (!select.length) {
            valido = false;
            return false;
        }

        let val = select.val();

        if (val === null || val === undefined || val === "") {
            $(select).addClass("is-invalid");
            valido = false;
            return false;
        } else {
            $(select).removeClass("is-invalid");
        }
    });

    return valido;
}

function actualizarOpcionesPueblitos() {
    $(".pueblito option").show();
}

$(document).on("change", "#contenedorPuntos select", function () {
    generarTramos(tramosActuales);
});

let timeout;

$(document).on("change", ".pueblito", function () {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        generarTramos(tramosActuales);
    }, 200);
});

function verRuta(id) {
    $.get(route("rutas.show", id), function (ruta) {
        let total = ruta.puntos.length;
        tramosActuales = ruta.tramos;
        puntosOriginales = ruta.puntos.map((p) => String(p.pueblito_id));
        hayCambiosEnTramos = false;
        let html = `
            <h6>${ruta.nombre}</h6>

            <p class="text-muted mb-2">
                Total de puntos: <strong>${total}</strong>
            </p>

            <hr>

            <ul class="list-group">

                ${ruta.puntos
                    .map(
                        (p, i) => `

                    <li class="list-group-item d-flex justify-content-between">

                        <span>
                            ${i + 1}. ${p.pueblito || "Sin parada"}
                        </span>

                    </li>

                `,
                    )
                    .join("")}

            </ul>
        `;

        $("#tituloPanel").text("Detalle de Ruta");

        $("#panelContenido").html(html);
    });
}

function editarRuta(id) {
    $.get(route("rutas.show", id), function (ruta) {
        tramosActuales = ruta.tramos;
        puntosOriginalesOrdenados = ruta.puntos.map((p) =>
            String(p.pueblito_id),
        );
        let html = `
            <h6>Editar Ruta</h6>

            <input type="text" class="form-control form-control-sm mb-2"
                id="nombreRuta"
                value="${ruta.nombre}">

            <div id="contenedorPuntos"></div>

           <button type="button"
    class="btn btn-success btn-xs w-100 btn-add-punto"
    onclick="agregarPunto()">

    <i data-lucide="plus"></i>
    Añadir punto
</button>

            <hr>

            <h6>TRAMOS</h6>
            <div id="contenedorTramos"></div>

          <div id="alertaTramos" class="alert alert-danger py-2 mb-2 d-none">
    <strong>
        Verifique los tiempos entre paradas antes de guardar cambios nuevos.
    </strong>
</div>

<button class="btn btn-success w-100 mt-2"
    onclick="guardarEdicion(${ruta.id})">
    Guardar cambios
</button>
        `;

        $("#panelContenido").html(html);
        setTimeout(() => {
            ruta.puntos.forEach((p) => agregarPunto(p));
        }, 100);
        setTimeout(() => {
            generarTramos(ruta.tramos);
            activarOrdenamiento();
        }, 400);
        lucide.createIcons();
    });
}

function verificarCambiosPuntos() {
    let actuales = [];

    $("#contenedorPuntos .pueblito").each(function () {
        actuales.push(String($(this).val() || ""));
    });

    if (actuales.length !== puntosOriginales.length) {
        return true;
    }

    for (let i = 0; i < actuales.length; i++) {
        if (actuales[i] !== puntosOriginales[i]) {
            return true;
        }
    }

    return false;
}

function eliminarPuntoEdit(puntoId) {
    Swal.fire({
        title: "¿Eliminar punto?",
        icon: "warning",
        showCancelButton: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route("ruta.punto.delete", puntoId),
                method: "DELETE",
                data: {
                    _token: $("meta[name=csrf-token]").attr("content"),
                },
                success: function () {
                    Swal.fire("Eliminado", "", "success");
                },
            });
        }
    });
}

$(document).on("click", ".ver", function () {
    let id = $(this).data("id");
    verRuta(id);
});

$(document).on("click", ".editar", function () {
    let id = $(this).data("id");
    editarRuta(id);
});

$(document).on("click", ".desactivar", function () {
    let id = $(this).data("id");

    Swal.fire({
        title: "¿Desactivar ruta?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, desactivar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(route("rutas.desactivar", id), {
                _token: $("meta[name=csrf-token]").attr("content"),
            }).done(function () {
                Swal.fire("Desactivado", "", "success");
                tablaRutas.ajax.reload();
            });
        }
    });
});

$(document).on("click", ".activar", function () {
    let id = $(this).data("id");

    Swal.fire({
        title: "¿Activar ruta?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, activar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(route("rutas.activar", id), {
                _token: $("meta[name=csrf-token]").attr("content"),
            }).done(function () {
                Swal.fire("Activado", "", "success");
                tablaRutas.ajax.reload();
            });
        }
    });
});

function guardarEdicion(id) {
    if (!validarPuntos()) {
        Swal.fire("Error", "Todos los puntos deben tener parada", "error");
        $("#contenedorPuntos .pueblito").addClass("is-invalid");

        return;
    }
    let nombre = $("#nombreRuta").val();
    let puntos = [];
    let duracion = [];
    let costo = [];

    $("#contenedorPuntos .punto").each(function () {
        let pueblito_id = $(this).find(".pueblito").val();
        let sucursal_id = $(this).find(".sucursal").val();

        let pueblito = pueblitos.find((p) => p.id == pueblito_id);

        if (pueblito_id) {
            puntos.push({
                distrito_id: pueblito?.distrito_id || null,
                pueblito_id: pueblito_id,
                sucursal_id: sucursal_id || null,
            });
        }
    });

    $("input[name='horas[]']").each(function (index) {
        let horas = parseInt($(this).val()) || "";
        let minutos =
            parseInt($("input[name='minutos[]']").eq(index).val()) || "";

        let total = horas * 60 + minutos;

        duracion.push(total);
    });

    $("input[name='costo[]']").each(function () {
        costo.push($(this).val() || null);
    });

    if (nombre.trim() === "") {
        Swal.fire("Error", "El nombre es obligatorio", "error");
        return;
    }

    if (puntos.length < 2) {
        Swal.fire("Error", "Debe tener al menos 2 puntos", "error");
        return;
    }

    Swal.fire({
        title: "Actualizando...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: route("rutas.update", { id: id }),
        method: "POST",
        data: {
            _token: $("meta[name=csrf-token]").attr("content"),
            _method: "PUT",
            nombre: nombre,
            puntos: puntos,
            duracion: duracion,
            costo: costo,
        },
        success: function () {
            Swal.fire(
                "Actualizado",
                "Todas la rutas asociadas será modificadas",
                "success",
            );

            tablaRutas.ajax.reload();

            $("#panelContenido").html(
                '<p class="text-muted">Selecciona una ruta</p>',
            );
        },
        error: function (err) {
            let mensaje = "Error al actualizar";

            if (err.responseJSON?.message) {
                mensaje = err.responseJSON.message;
            }

            Swal.fire("Error", mensaje, "error");
        },
    });
}

function minutosAHorasMinutos(total) {
    total = parseInt(total);

    if (isNaN(total) || total <= 0) {
        return {
            horas: "",
            minutos: "",
        };
    }

    return {
        horas: Math.floor(total / 60),
        minutos: total % 60,
    };
}

$(document).on("input", "input[name='minutos[]']", function () {
    let valor = parseInt($(this).val()) || "";

    if (valor > 59) {
        $(this).val(59);
    }

    if (valor < 0) {
        $(this).val(0);
    }
});

function generarTramos(tramosData = null) {
    if (tramosData !== null) {
        tramosActuales = tramosData;
    }

    let puntos = [];

    $("#contenedorPuntos .punto").each(function () {
        let select = $(this).find(".pueblito")[0];
        let valor = $(select).val();
        let item = pueblitos.find((p) => String(p.id) === String(valor));

        puntos.push({
            id: valor,
            nombre: item?.descripcion || "Punto",
        });
    });

    console.log(puntos);

    let html = "";

    const cambioPuntos = verificarCambiosPuntos();
    hayCambiosEnTramos = cambioPuntos;
    if (cambioPuntos) {
        $("#alertaTramos").removeClass("d-none");
    } else {
        $("#alertaTramos").addClass("d-none");
    }

    for (let i = 0; i < puntos.length - 1; i++) {
        let duracion = 0;
        let costo = "";

        const origenActual = puntos[i].id;
        const destinoActual = puntos[i + 1].id;

        const tramoExistente = tramosActuales.find(
            (t) =>
                String(t.origen_id) === String(origenActual) &&
                String(t.destino_id) === String(destinoActual),
        );

        if (tramoExistente) {
            duracion = tramoExistente.duracion;
            costo = tramoExistente.costo;
        } else {
            duracion = 0;
            costo = "";
        }

        console.log(JSON.stringify(tramosActuales, null, 2));
        console.log(origenActual);
        console.log(destinoActual);
        //console.log(puntosOriginalesOrdenados, origenActual);
        //console.log(puntosOriginalesOrdenados, destinoActual);

        let data = minutosAHorasMinutos(duracion);
        console.log("duracion:", duracion);
        console.log("convertido:", data);

        html += `
<div class="tramo card border-0 shadow-sm mb-2">

    <div class="card-body py-2 px-3">

        <div class="d-flex align-items-center justify-content-between gap-3">

            <div class="flex-grow-1">

                <small class="text-muted d-block">
                    Tramo
                </small>

                <strong>
                    ${puntos[i].nombre}
                    →
                    ${puntos[i + 1].nombre}
                </strong>

            </div>

            <div class="d-flex align-items-center gap-2">

                <div style="width:80px">
                    <input type="number"
                        name="horas[]"
                        class="form-control form-control-sm"
                        value="${data.horas ?? ""}"
                        placeholder="Horas">
                </div>

                <div style="width:80px">
                    <input type="number"
                        name="minutos[]"
                        class="form-control form-control-sm"
                        value="${data.minutos ?? ""}"
                        placeholder="Min">
                </div>

                <div style="width:100px">
                    <input type="number"
                        name="costo[]"
                        class="form-control form-control-sm"
                        value="${costo ?? ""}"
                        placeholder="Costo">
                </div>

            </div>

        </div>

    </div>

</div>
`;
    }

    $("#contenedorTramos").html(html);
}

let sortablePuntos = null;

function activarOrdenamiento() {
    const contenedor = document.getElementById("contenedorPuntos");

    if (!contenedor) return;

    if (sortablePuntos) {
        sortablePuntos.destroy();
    }

    sortablePuntos = new Sortable(contenedor, {
        animation: 150,
        handle: ".drag-handle",
        onEnd: function () {
            reordenarPuntos();
            generarTramos();
        },
    });
}
