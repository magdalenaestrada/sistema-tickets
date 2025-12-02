$(document).ready(function () {
    const modal = new bootstrap.Modal($("#modalVehiculo")[0]);

    // Inicializar DataTable
    const tabla = $("#tablaVehiculos").DataTable({
        ajax: route("vehiculos.datatable"),
        columns: [
            { data: "id" },
            { data: "tipo_vehiculo", title: "Tipo de vehiculo" },
            { data: "numero_placa" },
            {
                data: "acciones",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning editar" data-id="${row.id}"><i data-lucide="edit"></i></button>
                        <button class="btn btn-sm btn-danger eliminar" data-id="${row.id}"><i data-lucide="trash-2"></i></button>
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

    // Función para cargar tipos de vehículo
    async function cargarTiposVehiculo(selectedId = null) {
        const tipoSelect = $("#tipo_vehiculo_id");
        tipoSelect.empty().append('<option value="">Seleccione</option>');

        try {
            const tipos = await $.get(route("listas.vehiculos.tipos")); // Ajusta tu ruta si es otra
            tipos.forEach((tipo) => {
                tipoSelect.append(
                    `<option value="${tipo.id}" ${
                        tipo.id == selectedId ? "selected" : ""
                    }>${tipo.descripcion}</option>`
                );
            });
        } catch (err) {
            console.error("Error cargando tipos de vehículo:", err);
        }
    }

    // Abrir modal para nuevo vehículo
    $("#btnNuevaVehiculo").click(async function () {
        $("#formVehiculo")[0].reset();
        $("#Vehiculo_id").val("");
        await cargarTiposVehiculo();
        modal.show();
    });

    // Guardar o actualizar vehículo
    $("#formVehiculo").on("submit", async function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const id = $("#Vehiculo_id").val();

        try {
            if (id) {
                // Actualizar → PUT
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

    // Editar vehículo
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

    // Eliminar vehículo
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
                                "content"
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
