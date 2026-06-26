$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(function () {
    let tabla = $("#tablaTipoCupones").DataTable({
        ajax: route("tipo-cupones.datatable"),
        columns: [
            {
                data: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { title: "Descripción", data: "descripcion" },
            { title: "Estado", data: "estado" },
            {
                title: "Acciones",
                data: "acciones",
                orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "desc"]],
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

    $("#btnNuevoTipoCupon").click(function () {
        $("#formTipoCupon")[0].reset();
        $("#tipo_cupon_id").val("");
        $("#descripcion").val("");
        $("#modalTitulo").text("Registrar Tipo de cupón");
        $("#btnGuardarTipoCupon").text("Guardar");
        $("#modalTipoCupon").modal("show");
    });

    $("#tablaTipoCupones").on("click", ".editar", function () {
        const id = $(this).data("id");
        $.get(route("tipo-cupones.mostrar", id), function (data) {
            $("#tipo_cupon_id").val(id);
            $("#descripcion").val(data.descripcion);
            $("#modalTitulo").text("Editar TipoCupon");
            $("#btnGuardarTipoCupon").text("Actualizar");
            $("#modalTipoCupon").modal("show");
        });
    });

    $("#tablaTipoCupones").on("click", ".ver", function () {
        const id = $(this).data("id");
        $.get(route("tipo-cupones.mostrar", id), function (data) {
            Swal.fire({
                title: "Detalles de TipoCupon",
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

    $("#tablaTipoCupones").on("click", ".eliminar", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "¿Eliminar tipo de cupón?",
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
                    url: route("tipo-cupones.eliminar", id),
                    type: "DELETE",
                    success: function (res) {
                        if (res.success) {
                            $("#tablaTipoCupones")
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
                            text: "No se pudo eliminar el tipo de cupón. Ver consola para más detalles.",
                        });
                    },
                });
            }
        });
    });

    $("#formTipoCupon").on("submit", function (e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop("disabled", true);
        const id = $("#tipo_cupon_id").val();
        const url = id
            ? route("tipo-cupones.actualizar", id)
            : route("tipo-cupones.guardar");
        const method = id ? "PUT" : "POST";
        let formData = $(this).serializeArray();
        $.ajax({
            url: url,
            type: method,
            data: $.param(formData),
            success: function (res) {
                if (res.success) {
                    $("#modalTipoCupon").modal("hide");
                    $("#tablaTipoCupones").DataTable().ajax.reload();
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text:
                            res.message ||
                            "Tipo de cupon guardado correctamente",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    $btn.prop("disabled", false);
                } else {
                    $btn.prop("disabled", false);
                    console.error(xhr.responseText);

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

    $("#tablaTipoCupones").on("click", ".desactivar", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "¿Desactivar tipo de cupón?",
            html: `
            <p>Si desactivas este <b>tipo de cupón</b>:</p>
            <ul style="text-align:left">
                <li>El tipo de cupón quedará inactivo</li>
                <li><b>Todos los cupones asociados también se desactivarán</b></li>
            </ul>
            <p>Esta acción puede revertirse.</p>
        `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, desactivar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(route("tipo-cupones.desactivar", id), function (res) {
                    Swal.fire({
                        icon: "success",
                        title: "Desactivado",
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    $("#tablaTipoCupones").DataTable().ajax.reload();
                });
            }
        });
    });

    $("#tablaTipoCupones").on("click", ".activar", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "¿Activar tipo de cupón?",
            text: "Los cupones asociados volverán a estar activos.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, activar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(route("tipo-cupones.activar", id), function (res) {
                    Swal.fire({
                        icon: "success",
                        title: "Activado",
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    $("#tablaTipoCupones").DataTable().ajax.reload();
                });
            }
        });
    });
});
