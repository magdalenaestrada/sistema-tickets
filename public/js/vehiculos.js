$(document).ready(function () {
    const modal = new bootstrap.Modal($("#modalVehiculo")[0]);

    // Inicializar DataTable
    const tabla = $("#tablaVehiculos").DataTable({
        ajax: "/vehiculos/datatable",
        columns: [
            { data: "id" },
            { data: "tipo_vehiculo", title: "Tipo de vehiculo" },
            { data: "numero_placa" },
            { data: "cantidad_conductores" },
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
    function cargarTiposVehiculo(selectedId = null) {
        const tipoSelect = $("#tipo_vehiculo_id");
        tipoSelect.empty().append('<option value="">Seleccione</option>');

        $.get("/listas/vehiculos/tipos", function (tipos) {
            tipos.forEach((tipo) => {
                tipoSelect.append(
                    `<option value="${tipo.id}" ${
                        tipo.id == selectedId ? "selected" : ""
                    }>${tipo.descripcion}</option>`
                );
            });
        });
    }

    // Abrir modal para nuevo vehículo
    $("#btnNuevaVehiculo").click(function () {
        $("#formVehiculo")[0].reset();
        $("#Vehiculo_id").val("");
        cargarTiposVehiculo();
        modal.show();
    });

    // Guardar o actualizar vehículo
    // Guardar o actualizar vehículo
    $("#formVehiculo").on("submit", function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const id = $("#Vehiculo_id").val();

        if (id) {
            // Actualizar → método PUT
            $.ajax({
                url: `/vehiculos/${id}`,
                type: "PUT",
                data: formData,
                success: function (res) {
                    if (res.success) {
                        Swal.fire("Éxito", res.message, "success");
                        modal.hide();
                        tabla.ajax.reload(null, false);
                    } else {
                        Swal.fire("Error", res.message, "error");
                    }
                },
                error: function () {
                    Swal.fire("Error", "Error en la petición", "error");
                },
            });
        } else {
            // Guardar → método POST
            $.post("/vehiculos", formData, function (res) {
                if (res.success) {
                    Swal.fire("Éxito", res.message, "success");
                    modal.hide();
                    tabla.ajax.reload(null, false);
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            }).fail(() => Swal.fire("Error", "Error en la petición", "error"));
        }
    });

    // Editar vehículo
    $("#tablaVehiculos").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(`/vehiculos/${id}`, function (res) {
            $("#Vehiculo_id").val(res.id);
            $("#numero_placa").val(res.numero_placa);
            $("#cantidad_conductores").val(res.cantidad_conductores);
            cargarTiposVehiculo(res.tipo_vehiculo_id);
            modal.show();
        });
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
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(
                    `/vehiculos/${id}/eliminar`,
                    { _token: $('input[name="_token"]').val() },
                    function (res) {
                        if (res.success) {
                            Swal.fire("Eliminado", res.message, "success");
                            tabla.ajax.reload(null, false);
                        } else {
                            Swal.fire("Error", res.message, "error");
                        }
                    }
                );
            }
        });
    });
});
