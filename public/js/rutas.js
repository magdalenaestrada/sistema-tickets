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
 <div id="alertaTramos" class="alert alert-danger py-2 mb-2 d-none">
    <strong>
        Verifique los tiempos entre paradas antes de guardar.
    </strong>
</div>

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

    // VALIDAR Y ARMAR PUNTOS
    $("#contenedorPuntos .punto").each(function () {
        const pueblitoId = $(this).find(".pueblito").val();

        if (!pueblitoId) {
            $(this).find(".pueblito").addClass("is-invalid");
            return;
        }

        $(this).find(".pueblito").removeClass("is-invalid");

        const pueblito = pueblitos.find(
            (p) => String(p.id) === String(pueblitoId),
        );

        puntos.push({
            distrito_id: pueblito?.distrito_id || null,
            pueblito_id: pueblitoId,
            sucursal_id: pueblito?.sucursal_id || null,
        });
    });

    // Validar que TODOS los selects tengan parada
    const totalPuntos = $("#contenedorPuntos .punto").length;

    if (puntos.length !== totalPuntos) {
        Swal.fire("Error", "Todos los puntos deben tener parada", "error");
        return;
    }

    if (puntos.length < 2) {
        Swal.fire("Error", "Debe tener al menos 2 puntos", "error");
        return;
    }

    if (!nombre || nombre.trim() === "") {
        Swal.fire("Error", "El nombre de la ruta es obligatorio", "error");
        return;
    }

    // DURACIÓN
    $("input[name='horas[]']").each(function (index) {
        const horas = parseInt($(this).val()) || 0;
        const minutos =
            parseInt($("input[name='minutos[]']").eq(index).val()) || 0;

        const total = horas * 60 + minutos;

        duracion.push(total);
    });

    // COSTO
    $("input[name='costo[]']").each(function () {
        costo.push($(this).val() || null);
    });

    console.log("PUNTOS A GUARDAR:", puntos);
    console.log("DURACION:", duracion);
    console.log("COSTO:", costo);

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
        .fail(function (xhr) {
            console.error(xhr.responseJSON);

            let mensaje = xhr.responseJSON?.message || "No se pudo guardar";

            Swal.fire("Error", mensaje, "error");
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

      
            <input type="hidden" class="sucursal" value="">

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
        let sucursalTexto = p.sucursal?.nombre_comercial
            ? `<span class="badge bg-success">${p.sucursal.nombre_comercial.toUpperCase()}</span>`
            : "Sin sucursal";

        selectPueblito.append(`
        <option value="${p.id}">
            ${p.descripcion} - ${sucursalTexto}
        </option>
    `);
    });

    if (data) {
        selectPueblito.val(String(data.pueblito_id));
    }

    actualizarSucursalPunto(punto);

    generarTramos();
    lucide.createIcons();
}

function actualizarSucursalPunto(puntoEl) {
    let pueblitoId = puntoEl.find(".pueblito").val();
    let pueblito = pueblitos.find((p) => String(p.id) === String(pueblitoId));

    let sucursalId = pueblito?.sucursal_id || "";
    let texto = "Selecciona un pueblito";

    if (pueblitoId) {
        if (sucursalId) {
            let sucursal = sucursales.find(
                (s) => String(s.id) === String(sucursalId),
            );
            texto = sucursal?.nombre_comercial || "Sucursal no encontrada";
        } else {
            texto = "Sin sucursal asignada";
        }
    }

    puntoEl.find(".sucursal").val(sucursalId);
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
            .find(".numero-punto")
            .text(`${i + 1}`);
    });
}

function validarPuntos() {
    let valido = true;

    $("#contenedorPuntos .punto").each(function () {
        const select = $(this).find(".pueblito");
        const pueblito_id = select.val();

        if (!pueblito_id) {
            select.addClass("is-invalid");
            valido = false;
        } else {
            select.removeClass("is-invalid");
        }
    });

    if (!valido) {
        Swal.fire("Error", "Todos los puntos deben tener parada", "error");

        return false;
    }

    return true;
}

function actualizarOpcionesPueblitos() {
    $(".pueblito option").show();
}

$(document).on("change", "#contenedorPuntos select", function () {
    generarTramos(tramosActuales);
});

let timeout;

$(document).on("change", ".pueblito", function () {
    actualizarSucursalPunto($(this).closest(".punto"));

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
                            ${i + 1}. ${p.pueblito || "Sin parada"} - ${p.sucursal || "Sin sucursal"}
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
        return;
    }

    let nombre = $("#nombreRuta").val();
    let puntos = [];
    let duracion = [];
    let costo = [];

    $("#contenedorPuntos .punto").each(function () {
        const pueblito_id = $(this).find(".pueblito").val();

        if (!pueblito_id) {
            return;
        }

        const pueblito = pueblitos.find(
            (p) => String(p.id) === String(pueblito_id),
        );

        puntos.push({
            pueblito_id: pueblito_id,
            distrito_id: pueblito?.distrito_id || null,

            // LA SUCURSAL SALE DEL PUEBLITO
            sucursal_id: pueblito?.sucursal_id || null,
        });
    });

    $("input[name='horas[]']").each(function (index) {
        const horas = parseInt($(this).val()) || 0;
        const minutos =
            parseInt($("input[name='minutos[]']").eq(index).val()) || 0;

        duracion.push(horas * 60 + minutos);
    });

    $("input[name='costo[]']").each(function () {
        costo.push($(this).val() || null);
    });

    if (!nombre || nombre.trim() === "") {
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
                "La ruta fue actualizada correctamente",
                "success",
            );

            tablaRutas.ajax.reload();

            $("#panelContenido").html(
                '<p class="text-muted">Selecciona una ruta</p>',
            );
        },
        error: function (err) {
            console.error(err.responseJSON);

            const mensaje = err.responseJSON?.message || "Error al actualizar";

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
            sucursal: item?.sucursal?.nombre_comercial || "",
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
                <small class="text-muted d-block">Tramo</small>
                <strong>
                    ${puntos[i].nombre}${puntos[i].sucursal ? ` (${puntos[i].sucursal})` : ""}
                    →
                    ${puntos[i + 1].nombre}${puntos[i + 1].sucursal ? ` (${puntos[i + 1].sucursal})` : ""}
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
