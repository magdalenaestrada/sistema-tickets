import route from "ziggy-js";

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(function () {
    let tabla = $("#tablaAreas").DataTable({
        ajax: route("areas.datatable"),
        columns: [
            { title: "ID", data: "id" },
            { title: "Descripcion", data: "descripcion" },
            {
                title: "Acciones",
                data: "acciones",
                orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "asc"]],
        responsive: false,
        scrollX: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#btnNuevaArea").click(function () {
        $("#formArea")[0].reset();
        $("#area_id").val("");
        $("#descripcion").val("");
        $("#modalTitulo").text("Registrar Area");
        $("#btnGuardarArea").text("Guardar");
        $("#modalArea").modal("show");
    });

    $("#tablaAreas").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(route("areas.show", id), function (data) {
            $("#area_id").val(id);
            $("#descripcion").val(data.descripcion);
            $("#modalTitulo").text("Editar Area");
            $("#btnGuardarArea").text("Actualizar");
            $("#modalArea").modal("show");
        });
    });

    $("#tablaAreas").on("click", ".ver", function () {
        const id = $(this).data("id");

        $.get(route("areas.show", id), function (data) {
            Swal.fire({
                title: "Detalles de Area",
                html: `
                <div style="text-align: left;">
                    <b> Descripcion:</b> ${data.descripcion}<br>
                </div>
            `,
                icon: "info",
                confirmButtonText: "Cerrar",
                customClass: {
                    popup: "swal-left-text",
                },
            });
        });
    });

    $("#tablaAreas").on("click", ".eliminar", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Eliminar área?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("areas.destroy", id),
                    type: "DELETE",
                    success: function (res) {
                        if (res.success) {
                            $("#tablaAreas")
                                .DataTable()
                                .ajax.reload(null, false);
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "No se puede eliminar",
                                text: res.message,
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo eliminar el área. Ver consola para más detalles.",
                        });
                    },
                });
            }
        });
    });

    // --- Guardar área (crear o actualizar) ---
    $("#formArea").on("submit", function (e) {
        e.preventDefault();

        const id = $("#area_id").val();
        const url = id ? route("areas.update", id) : route("areas.store");
        const method = id ? "PUT" : "POST";

        // Incluimos el _method si es edición
        let formData = $(this).serializeArray();

        $.ajax({
            url: url,
            type: method,
            data: $.param(formData),
            success: function (res) {
                if (res.success) {
                    $("#modalArea").modal("hide");
                    $("#tablaAreas").DataTable().ajax.reload();
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: res.message || "Área guardada correctamente",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: res.message || "Ocurrió un error al guardar.",
                    });
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo guardar el registro. Revisa la consola.",
                });
            },
        });
    });
});
