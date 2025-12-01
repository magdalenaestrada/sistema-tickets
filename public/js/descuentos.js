$(document).ready(function () {
    let tabla = $("#tablaDescuentos").DataTable({
        processing: true,
        serverSide: true,
        ajax: route("descuentos.datatable"),
        columns: [
            { data: "id" },
            { data: "codigo" },
            { data: "persona" },
            { data: "cantidad_usos" },
            { data: "fecha_maxima" },
            { data: "monto_efectivo" },
            { data: "porcentaje" },
            { data: "activo" },
            { data: "acciones", orderable: false, searchable: false },
        ],
    });

    $("#btnNuevoDescuento").click(function () {
        $("#formDescuento")[0].reset();
        $("#descuento_id").val("");
        $("#modalTitulo").text("Registrar Descuento");
        $("#modalDescuento").modal("show");
    });

    $("#tablaDescuentos").on("click", ".editar", function () {
        let id = $(this).data("id");
        $.get(route("descuentos.mostrar", id), function (data) {
            $("#descuento_id").val(data.id);
            $("#codigo").val(data.codigo);
            $("#persona_documento").val(data.persona?.documento ?? "");
            $("#persona_nombres").val(data.persona?.nombres ?? "");
            $("#persona_apellidos").val(data.persona?.apellidos ?? "");
            $('[name="cantidad_usos"]').val(data.cantidad_usos);
            $('[name="fecha_maxima"]').val(data.fecha_maxima);
            $('[name="monto_efectivo"]').val(data.monto_efectivo);
            $('[name="porcentaje"]').val(data.porcentaje);
            $('[name="activo"]').prop("checked", data.activo);
            $("#modalTitulo").text("Editar Descuento");
            $("#modalDescuento").modal("show");
        });
    });

    $("#formDescuento").submit(function (e) {
        e.preventDefault();
        $.post(
            route("descuentos.guardar"),
            $(this).serialize(),
            function (res) {
                if (res.success) {
                    $("#modalDescuento").modal("hide");
                    tabla.ajax.reload();
                }
            }
        );
    });

    $("#tablaDescuentos").on("click", ".eliminar", function () {
        if (!confirm("¿Seguro que quieres eliminar este descuento?")) return;
        let id = $(this).data("id");
        $.ajax({
            url: route("descuentos.eliminar", id),
            type: "DELETE",
            data: { _token: $('meta[name="csrf-token"]').attr("content") },
            success: function (res) {
                if (res.success) tabla.ajax.reload();
            },
        });
    });

    $("#documento").on("blur", function () {
        const tipoDocumento = $("#tipo_documento_id").val();
        const documento = $(this).val().trim();

        if (!tipoDocumento || !documento) return;

        $.getJSON(`/buscar/?tipo=${tipoDocumento}&documento=${documento}`)
            .done(function (data) {
                if (data.error) {
                    Swal.fire("Error", data.error, "error");
                    return;
                }

                if (tipoDocumento == "2") {
                    $("#nombres").val(data.razon_social || "");
                    $("#apellidos").val("");
                    $("#razon_social").val(data.razon_social || "");
                } else {
                    $("#nombres").val(`${data.nombres || ""}`.trim());
                    $("#apellidos").val(
                        `${data.apellido_paterno || ""} ${
                            data.apellido_materno || ""
                        }`.trim()
                    );
                    $("#razon_social").val(""); // limpiar
                }
            })
            .fail(function () {
                Swal.fire("Error", "Error al consultar la API.", "error");
            });
    });
});
