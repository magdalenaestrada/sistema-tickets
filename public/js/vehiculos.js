$(document).ready(function () {
    const modal = new bootstrap.Modal($("#modalVehiculo")[0]);
    const modalMantenimiento = new bootstrap.Modal($("#modalMantenimiento")[0]);

    const tabla = $("#tablaVehiculos").DataTable({
        ajax: route("vehiculos.datatable"),
        dom: "rtip",
        columns: [
            { data: "id" },
            { data: "tipo_vehiculo", title: "Tipo de vehiculo" },
            { data: "numero_placa" },
            {
                data: "estado",
                title: "Estado",
                render: function (data) {
                    if (data === "A") {
                        return `<span class="badge bg-success">Activo</span>`;
                    }

                    if (data === "M") {
                        return `<span class="badge bg-danger">Mantenimiento</span>`;
                    }

                    return `<span class="badge bg-secondary">${data}</span>`;
                },
            },
            {
                data: "acciones",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
            <button class="btn btn-sm btn-secondary mantenimiento"
                data-id="${row.id}"
                data-estado="${row.estado}">
                <i data-lucide="wrench"></i>
            </button>

            <button class="btn btn-sm btn-warning editar" data-id="${row.id}">
                <i data-lucide="edit"></i>
            </button>

            <button class="btn btn-sm btn-danger eliminar" data-id="${row.id}">
                <i data-lucide="trash-2"></i>
            </button>
        `;
                },
            },
        ],
        scrollX: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#numero_placa").on("input", function () {
        let valor = $(this)
            .val()
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, "");

        valor = valor.substring(0, 6);

        if (valor.length > 3) {
            valor = valor.substring(0, 3) + "-" + valor.substring(3);
        }

        $(this).val(valor);
    });

    async function cargarTiposVehiculo(selectedId = null) {
        const tipoSelect = $("#tipo_vehiculo_id");
        tipoSelect.empty().append('<option value="">Seleccione</option>');

        try {
            const tipos = await $.get(route("listas.vehiculos.tipos"));
            tipos.forEach((tipo) => {
                tipoSelect.append(
                    `<option value="${tipo.id}" ${
                        tipo.id == selectedId ? "selected" : ""
                    }>${tipo.descripcion}</option>`,
                );
            });
        } catch (err) {
            console.error("Error cargando tipos de vehículo:", err);
        }
    }

    $("#btnNuevaVehiculo").click(async function () {
        $("#formVehiculo")[0].reset();
        $("#Vehiculo_id").val("");
        await cargarTiposVehiculo();
        modal.show();
    });

    $("#formVehiculo").on("submit", async function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const id = $("#Vehiculo_id").val();

        try {
            if (id) {
                const res = await $.ajax({
                    url: route("vehiculos.actualizar", id),
                    type: "PUT",
                    data: formData,
                });
                if (res.success) {
                    Swal.fire("Éxito", res.message, "success");
                    modal.hide();
                    tabla.ajax.reload(null, false);
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            } else {
                // Crear → POST
                const res = await $.post(route("vehiculos.guardar"), formData);
                if (res.success) {
                    Swal.fire("Éxito", res.message, "success");
                    modal.hide();
                    tabla.ajax.reload(null, false);
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            }
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "Error en la petición", "error");
        }
    });

    $("#formMantenimiento").on("submit", async function (e) {
        e.preventDefault();

        const id = $("#vehiculo_id").val();
        const formData = $(this).serialize();
        const fechaInicio = $("input[name='fecha_inicio']").val();
        const horaInicio = $("input[name='hora_inicio']").val();
        const fechaFin = $("input[name='fecha_fin']").val();
        const horaFin = $("input[name='hora_fin']").val();

        if (fechaInicio && horaInicio && fechaFin && horaFin) {
            const inicio = new Date(fechaInicio + "T" + horaInicio);
            const fin = new Date(fechaFin + "T" + horaFin);

            if (fin <= inicio) {
                Swal.fire(
                    "Fecha inválida",
                    "La fecha y hora de fin deben ser mayores que la de inicio.",
                    "warning",
                );
                return;
            }
        }
        try {
            const res = await $.post(
                route("vehiculos.mantenimiento", id),
                formData,
            );

            Swal.fire("Éxito", res.message, "success");
            modalMantenimiento.hide();
            tabla.ajax.reload(null, false);
        } catch (err) {
            Swal.fire("Error", "No se pudo procesar", "error");
        }
    });

    $("#tablaVehiculos").on("click", ".editar", async function () {
        const id = $(this).data("id");

        try {
            const res = await $.get(route("vehiculos.mostrar", id));
            $("#Vehiculo_id").val(res.id);
            $("#numero_placa").val(res.numero_placa);
            await cargarTiposVehiculo(res.tipo_vehiculo_id);
            modal.show();
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudo cargar el vehículo", "error");
        }
    });

    $("#tablaVehiculos").on("click", ".mantenimiento", function () {
        const id = $(this).data("id");
        const estado = $(this).data("estado");

        $("#formMantenimiento")[0].reset();
        $("#vehiculo_id").val(id);

        if (estado === "A") {
            $("#tituloMantenimiento").text("Enviar a mantenimiento");
            $("#inicioMantenimiento").removeClass("d-none");
            $("#fecha_inicio").prop("required", true);
            $("#hora_inicio").prop("required", true);
            $("#razon_id").prop("required", true);
            $("#finMantenimiento").addClass("d-none");
        } else {
            $("#tituloMantenimiento").text("Finalizar mantenimiento");
            $("#inicioMantenimiento").addClass("d-none");
            $("#finMantenimiento").removeClass("d-none");
            $("#fecha_inicio").prop("required", false);
            $("#hora_inicio").prop("required", false);
            $("#razon_id").prop("required", false);
            $("input[name='fecha_fin']").val("");
            $("input[name='hora_fin']").val("");
        }
        const hoy = new Date().toISOString().split("T")[0];

        if (estado === "A") {
            $("#fecha_inicio").attr("min", hoy);
            $("#fecha_fin").removeAttr("min");
        } else {
            $("#fecha_fin").attr("min", hoy);
        }
        modalMantenimiento.show();
    });

    $("input[name='fecha_inicio']").on("change", function () {
        let fechaInicio = $(this).val();

        $("input[name='fecha_fin']").attr("min", fechaInicio);
    });

    $("#tablaVehiculos").on("click", ".eliminar", function () {
        const id = $(this).data("id");
        Swal.fire({
            title: "¿Está seguro?",
            text: "No podrá revertir esto",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await $.ajax({
                        url: route("vehiculos.eliminar", id),
                        type: "DELETE",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr(
                                "content",
                            ),
                        },
                    });
                    if (res.success) {
                        Swal.fire("Eliminado", res.message, "success");
                        tabla.ajax.reload(null, false);
                    } else {
                        Swal.fire("Error", res.message, "error");
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire("Error", "Error en la petición", "error");
                }
            }
        });
    });
});
