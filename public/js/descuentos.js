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
            { data: "cantidad_usos" },
            { data: "fecha_maxima" },
            { data: "descuento" },
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
        document.querySelector(".empleados_asignados")?.tomselect?.clear();
        document.querySelector(".cargos_asignados")?.tomselect?.clear();
        $("#modalTitulo").text("Registrar Descuento");
        $("#modalDescuento").modal("show");
    });

    $("#tipo_asignacion_id")
        .on("change", function () {
            let empleados = document.querySelector(
                ".empleados_asignados",
            )?.tomselect;
            let cargos = document.querySelector(".cargos_asignados")?.tomselect;

            if ($(this).val() === "P") {
                $(".empleados_asignados").closest(".col-md-9").show();
                $(".cargos_asignados").closest(".col-md-9").hide();

                cargos?.clear();
            } else if ($(this).val() === "G") {
                $(".empleados_asignados").closest(".col-md-9").hide();
                $(".cargos_asignados").closest(".col-md-9").show();

                empleados?.clear();
            } else {
                $(".empleados_asignados").closest(".col-md-9").hide();
                $(".cargos_asignados").closest(".col-md-9").hide();

                empleados?.clear();
                cargos?.clear();
            }
        })
        .trigger("change");

    $("#filtroCodigo").on("keyup change", function () {
        let valor = this.value;

        if (valor) {
            tabla
                .column(2)
                .search("^" + valor, true, false)
                .draw();
        } else {
            tabla.column(2).search("").draw();
        }
    });

    let tsTipo = new TomSelect("#filtroTipoCupon", {
        create: false,
    });

    let tsPersona = new TomSelect("#filtroPersona", {
        create: false,
    });

    let tsCargo = new TomSelect("#filtroCargo", {
        create: false,
    });

    function soloLetras(ts) {
        ts.control_input.addEventListener("input", function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
        });
    }

    soloLetras(tsTipo);
    soloLetras(tsPersona);
    soloLetras(tsCargo);

    $("#filtroTipoCupon").on("change", function () {
        tabla.column(1).search(this.value, false, true).draw();
    });

    $("#filtroPersona").on("change", function () {
        tabla.column(3).search(this.value, false, true).draw();
    });

    $("#tablaDescuentos").on("click", ".editar", function () {
        let id = $(this).data("id");

        $.get(route("descuentos.mostrar", id), function (data) {
            $("#descuento_id").val(data.id);
            $("#tipo_cupon_id").val(data.tipo_cupon_id);
            $("#codigo").val(data.codigo);
            $("#cantidad_usos").val(data.cantidad_usos);
            $("#fecha_maxima").val(data.fecha_maxima);
            $("#monto_efectivo").val(data.monto_efectivo);
            $("#porcentaje").val(data.porcentaje);
            $("#activo").prop("checked", data.activo);
            $("#tipo_asignacion_id")
                .val(data.tipo_asignacion_id)
                .trigger("change");

            $("#tipo_descuento_id")
                .val(data.tipo_descuento_id)
                .trigger("change");

            let empleadosTS = document.querySelector(
                ".empleados_asignados",
            )?.tomselect;
            empleadosTS?.clear();

            if (data.empleados) {
                data.empleados.forEach((p) => {
                    empleadosTS?.addItem(p.empleado_id);
                });
            }
            let cargosTS =
                document.querySelector(".cargos_asignados")?.tomselect;
            cargosTS?.clear();

            if (data.cargos) {
                data.cargos.forEach((c) => {
                    cargosTS?.addItem(c.cargo_id);
                });
            }

            $("#modalTitulo").text("Editar Descuento");
            $("#modalDescuento").modal("show");
        });
    });

    $("#formDescuento").submit(function (e) {
        e.preventDefault();
        console.log($(this).serialize());
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
            },
        );
    });

    document.querySelectorAll(".empleados_asignados").forEach((el) => {
        new TomSelect(el, {
            placeholder: "Selecciona empleados",
            plugins: ["remove_button"],
            maxItems: null,
            hidePlaceholder: true,
            closeAfterSelect: false,
            render: {
                option: function (data, escape) {
                    return `<div>
            👤 ${escape(data.text)}
        </div>`;
                },
                item: function (data, escape) {
                    return `<div>
            ${escape(data.text)}
        </div>`;
                },
            },
        });
    });

    document.querySelectorAll(".cargos_asignados").forEach((el) => {
        new TomSelect(el, {
            placeholder: "Selecciona los cargos",
            plugins: ["remove_button"],
            maxItems: null,
            hidePlaceholder: true,
            closeAfterSelect: false,
            render: {
                option: function (data, escape) {
                    return `<div>
            👤 ${escape(data.text)}
        </div>`;
                },
                item: function (data, escape) {
                    return `<div>
            ${escape(data.text)}
        </div>`;
                },
            },
        });
    });

    $("#tablaDescuentos").on("click", ".eliminar", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "¿Eliminar descuento?",
            text: "Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route("descuentos.eliminar", id),
                    type: "DELETE",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: "Eliminado",
                            text:
                                res.message ||
                                "Descuento eliminado correctamente",
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        tabla.ajax.reload(null, false);
                    },
                    error: function () {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo eliminar el descuento",
                        });
                    },
                });
            }
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
            }),
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
                        }`.trim(),
                    );
                    $("#razon_social").val(""); // limpiar
                }
            })
            .fail(function () {
                Swal.fire(
                    "Error",
                    "Ingrese un numero de documento válido.",
                    "error",
                );
            });
    });

    $("#documento").on("keypress", function (e) {
        if (e.which === 13) {
            $("#btnBuscarPersona").click();
        }
    });

    $("#tipo_descuento_id").on("change", function () {
        let contenedor_monto = document.getElementById("descuento_monto_fijo");
        let contenedor_porcentaje = document.getElementById(
            "descuento_porcentaje",
        );

        let monto = document.getElementById("monto_efectivo");
        let porcentaje = document.getElementById("porcentaje");

        if (this.value === "P") {
            contenedor_monto.hidden = true;
            monto.value = "";
            monto.required = false;
            porcentaje.required = true;
            contenedor_porcentaje.hidden = false;
        } else if (this.value === "M") {
            contenedor_monto.hidden = false;
            contenedor_porcentaje.hidden = true;
            monto.required = true;
            porcentaje.required = false;
            porcentaje.value = "";
        } else {
            contenedor_monto.hidden = true;
            monto.value = "";
            porcentaje.value = "";
            monto.required = false;
            porcentaje.required = false;
            contenedor_porcentaje.hidden = true;
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
