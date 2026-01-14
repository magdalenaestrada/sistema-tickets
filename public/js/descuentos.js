$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(document).ready(function () {
    let tabla = $("#tablaDescuentos").DataTable({
        processing: true,
        serverSide: false,
        ajax: route("descuentos.datatable"),
        dom: "rtip",
        columns: [
            { data: "id" },
            { data: "tipo_cupon" },
            { data: "codigo" },
            { data: "persona" },
            { data: "cantidad_usos" },
            { data: "fecha_maxima" },
            { data: "monto_efectivo" },
            {
                data: "porcentaje",
                render: function (data) {
                    return data ? data + " %" : "";
                },
            },
            { data: "activo" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        drawCallback: function () {
            lucide.createIcons();
        },
    });

    $("#tablaDescuentos").on("click", ".desactivar", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "¿Desactivar descuento?",
            text: "Este descuento no podrá usarse mientras esté inactivo.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Sí, desactivar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(route("descuentos.desactivar", id), function (res) {
                    Swal.fire({
                        icon: "success",
                        title: "Desactivado",
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    tabla.ajax.reload(null, false);
                });
            }
        });
    });
    $("#tablaDescuentos").on("click", ".activar", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "¿Activar descuento?",
            text: "El descuento volverá a estar disponible.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            confirmButtonText: "Sí, activar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(route("descuentos.activar", id), function (res) {
                    Swal.fire({
                        icon: "success",
                        title: "Activado",
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    tabla.ajax.reload(null, false);
                });
            }
        });
    });

    $("#btnNuevoDescuento").click(function () {
        $("#formDescuento")[0].reset();
        $("#descuento_id").val("");
        $("#modalTitulo").text("Registrar Descuento");
        $("#modalDescuento").modal("show");
    });

    $("#filtroTipoCupon").on("keyup change", function () {
        tabla.column(1).search(this.value).draw();
    });
    $("#filtroCodigo").on("keyup change", function () {
        let valor = this.value;

        if (valor) {
            tabla
                .column(2)
                .search("^" + valor, true, false) // regex = true, smart = false
                .draw();
        } else {
            tabla.column(2).search("").draw();
        }
    });

    $("#filtroPersona").on("keyup change", function () {
        tabla.column(3).search(this.value).draw();
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
        let monto = parseFloat($("#monto_efectivo").val());
        let porcentaje = parseFloat($("#porcentaje").val());

        if ((!monto || monto <= 0) && (!porcentaje || porcentaje <= 0)) {
            Swal.fire(
                "Error",
                "Debes ingresar un Monto en fectivo o un porcentaje.",
                "error"
            );
            return;
        }
        if ((!monto && !porcentaje) || (monto && porcentaje)) {
            Swal.fire(
                "Error",
                "Debe ingresar solo un valor, monto efectivo o porcentaje.",
                "error"
            );
            return;
        }

        $.post(
            route("descuentos.guardar"),
            $(this).serialize(),
            function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Descuento guardado correctamente",
                        showConfirmButton: false,
                        timer: 1500,
                    });

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

    $("#btnBuscarPersona").on("click", function () {
        const tipoDocumento = $("#tipo_documento_id").val();
        const documento = $("#documento").val().trim();

        if (!tipoDocumento || !documento) return;

        $.getJSON(
            route("buscar.buscar", {
                tipo: tipoDocumento,
                documento: documento,
            })
        )
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
                Swal.fire(
                    "Error",
                    "Ingrese un numero de documento válido.",
                    "error"
                );
            });
    });

    $("#documento").on("keypress", function (e) {
        if (e.which === 13) {
            $("#btnBuscarPersona").click();
        }
    });

    function actualizarCamposSegunTipo() {
        const tipo = $("#tipo_documento_id").val();

        if (tipo == "2") {
            // RUC
            $("#nombres").closest(".col-md-4").hide();
            $("#apellidos").closest(".col-md-4").hide();

            $("#razon_social").closest(".col-md-8").show();
        } else {
            // DNI
            $("#nombres").closest(".col-md-4").show();
            $("#apellidos").closest(".col-md-4").show();

            $("#razon_social").closest(".col-md-8").hide();
        }
    }

    $("#tipo_documento_id").on("change", actualizarCamposSegunTipo);

    actualizarCamposSegunTipo();
});
