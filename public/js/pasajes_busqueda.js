document.addEventListener("DOMContentLoaded", function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
    });

    function soloFecha(fecha) {
        if (!fecha) return "";
        return fecha.split("T")[0].split("-").reverse().join("/");
    }

    function badgeEstado(estado) {
        switch (estado) {
            case "R":
                return '<span class="badge bg-secondary">Reservado</span>';
            case "V":
                return '<span class="badge bg-success">Vendido</span>';
            case "F":
                return '<span class="badge bg-success">Abordó</span>';
            case "X":
                return '<span class="badge bg-danger">No abordó</span>';
            default:
                return '<span class="badge bg-dark">Desconocido</span>';
        }
    }

    function cargarPasajes() {
        $.get(
            route("pasajes.vendidos"),
            {
                dni: $("#filtroDNI").val(),
                fecha: $("#filtroFecha").val(),
                origen: $("#filtroOrigen").val(),
                destino: $("#filtroDestino").val(),
            },
            function (data) {
                let tbody = "";
                if (data.length === 0) {
                    tbody = `
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No se encontraron pasajes.
                        </td>
                    </tr>
                `;
                } else {
                    data.forEach((p) => {
                        tbody += `
                <tr>
                    <td>${p.persona?.documento ?? ""}</td>
                    <td>${p.persona?.nombres ?? ""} ${
                            p.persona?.apellidos ?? ""
                        }</td>
                    <td>${p.horario.punto_origen.nombre_comercial}</td>
                    <td>${p.horario.punto_destino.nombre_comercial}</td>
                    <td>${soloFecha(p.horario.fecha_salida)}</td>
                    <td>${p.horario.hora_embarque}</td>
                    <td>${badgeEstado(p.estado)}</td>
                ${botonesAcciones(p)}

                </tr>`;
                    });
                }
                $("#tablaPasajes tbody").html(tbody);

                lucide.createIcons();
            }
        );
    }

    function botonesAcciones(p) {
        let html = `<td class="text-nowrap text-center">`;

        html += `
        <button class="btn btn-secondary btn-xs ver"
            data-url="${route("pasajes.show", p.id)}">
            <i data-lucide="info"></i>
        </button>
    `;

        if (p.estado === "R") {
            html += `
            <button class="btn btn-warning btn-xs editar"
                data-url="${route("pasajes.editar", p.id)}">
                <i data-lucide="pen"></i>
            </button>
        `;
        } else if (p.estado === "V") {
            html += `
            <button class="btn btn-success btn-xs abordar"
                data-url="${route("pasajes.abordar", p.id)}">
                <i data-lucide="check"></i>
            </button>

            <button class="btn btn-danger btn-xs no-abordo"
                data-url="${route("pasajes.noAbordo", p.id)}">
                <i data-lucide="x"></i>
            </button>

            <button class="btn btn-warning btn-xs editar"
                data-url="${route("pasajes.editar", p.id)}">
                <i data-lucide="pen"></i>
            </button>
        `;
        }
        html += `</td>`;
        return html;
    }

    $("#filtroDNI, #filtroFecha, #filtroOrigen, #filtroDestino").on(
        "change keyup",
        cargarPasajes
    );

    $(document).on("click", ".ver", function () {
        const url = $(this).data("url");

        $("#modalContenido").html(
            '<div class="text-center text-muted">Cargando...</div>'
        );

        $("#modalPasaje").modal("show");

        $.get(url, function (p) {
            let html = `
<div class="row">
    <div class="col-md-6">
        <strong>DNI:</strong> ${p.pasajero?.documento ?? "-"}<br>
        <strong>Pasajero:</strong> ${p.pasajero?.nombres ?? ""} ${
                p.pasajero?.apellidos ?? ""
            }<br>
        <strong>Asiento:</strong> ${p.asiento}
    </div>

    <div class="col-md-6">
        <strong>Origen:</strong> ${p.origen}<br>
        <strong>Destino:</strong> ${p.destino}<br>
        <strong>Fecha:</strong> ${soloFecha(p.fecha)}<br>
        <strong>Hora:</strong> ${p.hora}
    </div>
</div>

<hr>

<div>
    <strong>Estado:</strong> ${badgeEstado(p.estado)}
</div>
`;

            $("#modalContenido").html(html);
        });
    });

    $(document).on("click", ".editar", function () {
        window.location.href = $(this).data("url");
    });

    $(document).on("click", ".abordar", function () {
        const url = $(this).data("url");

        Swal.fire({
            title: "¿Confirmar abordaje?",
            text: "Este pasajero abordó el vehículo",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, abordó",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#198754",
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, {}, () => {
                    Swal.fire({
                        icon: "success",
                        title: "Confirmado",
                        text: "El pasajero fue marcado como abordado",
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    cargarPasajes();
                });
            }
        });
    });

    $(document).on("click", ".no-abordo", function () {
        const url = $(this).data("url");

        Swal.fire({
            title: "¿No abordó?",
            text: "Confirma que el pasajero NO abordó el vehículo",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, no abordó",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#dc3545",
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, {}, () => {
                    Swal.fire({
                        icon: "success",
                        title: "Actualizado",
                        text: "El pasajero fue marcado como no abordó",
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    cargarPasajes();
                });
            }
        });
    });

    $("#btnNueva").click(function () {
        window.location.href =
            route("pasajes.exportar") +
            "?" +
            $.param({
                dni: $("#filtroDNI").val(),
                fecha: $("#filtroFecha").val(),
                origen: $("#filtroOrigen").val(),
                destino: $("#filtroDestino").val(),
            });
    });

    cargarPasajes();
});
