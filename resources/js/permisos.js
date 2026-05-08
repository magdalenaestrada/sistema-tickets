$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(function () {
    let tabla = $("#tablaPermisos").DataTable({
        ajax: route("permisos.datatable"),
        columns: [
            { title: "ID", data: "id" },
            { title: "Nombre", data: "name" },
            {
                title: "Acciones",
                data: "acciones",
                orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "asc"]],
        responsive: false,
        scpermisolX: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        drawCallback: function () {
            lucide.createIcons();
        },
        dom: "rtip",
    });

    $("#btnNuevaPermiso").click(function () {
        $("#formPermiso")[0].reset();
        $("#permiso_id").val("");
        $("#name").val("");
        $("#modalTitulo").text("Registrar Permiso");
        $("#btnGuardarPermiso").text("Guardar");
        $("#modalPermiso").modal("show");
    });

    $("#tablaPermisos").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(route("permisos.mostrar", id), function (data) {
            $("#permiso_id").val(id);
            $("#name").val(data.name);
            $("#modalTitulo").text("Editar Permiso");
            $("#btnGuardarPermiso").text("Actualizar");
            $("#modalPermiso").modal("show");
        });
    });

    $("#tablaPermisos").on("click", ".ver", function () {
        const id = $(this).data("id");

        $.get(route("permisos.mostrar", id), function (data) {
            // ← Ziggy
            Swal.fire({
                title: "Detalles de Permiso",
                html: `
                <div style="text-align: left;">
                    <b> Descripcion:</b> ${data.name}<br>
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

    $("#tablaPermisos").on("click", ".eliminar", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Eliminar permiso?",
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
                    url: route("permisos.eliminar", id), // ← Ziggy
                    type: "DELETE",
                    success: function (res) {
                        if (res.success) {
                            $("#tablaPermisos")
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
                            text: "No se pudo eliminar el permiso. Ver consola para más detalles.",
                        });
                    },
                });
            }
        });
    });

    $("#formPermiso").on("submit", function (e) {
        e.preventDefault();

        const id = $("#permiso_id").val();
        const url = id
            ? route("permisos.actualizar", id)
            : route("permisos.guardar");
        const method = id ? "PUT" : "POST";

        let formData = $(this).serializeArray();

        $.ajax({
            url: url,
            type: method,
            data: $.param(formData),
            success: function (res) {
                if (res.success) {
                    $("#modalPermiso").modal("hide");
                    $("#tablaPermisos").DataTable().ajax.reload();
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: res.message || "Permiso guardado correctamente",
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
