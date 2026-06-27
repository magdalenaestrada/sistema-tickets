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
            { data: "DT_RowIndex" },
            { data: "tipo_cupon" },
            { data: "codigo" },
            { data: "cantidad_usos" },
            { data: "fecha_maxima" },
            { data: "descuento" },
            { data: "asignado_a" },
            { data: "activo" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        order: [[0, "desc"]],

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
        document.getElementById("contenedor_reglas").innerHTML = "";
        $("#modalTitulo").text("Registrar Descuento");
        $("#modalDescuento").modal("show");
    });

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
        tabla.column(6).search(this.value, false, true).draw();
    });

    $("#tablaDescuentos").on("click", ".editar", function () {
        let id = $(this).data("id");

        document.getElementById("contenedor_reglas").innerHTML = "";

        $.get(route("descuentos.mostrar", id), function (data) {
            $("#descuento_id").val(data.id);
            $("#tipo_cupon_id").val(data.tipo_cupon_id);
            $("#codigo").val(data.codigo);
            $("#cantidad_usos").val(data.cantidad_usos);
            $("#fecha_maxima").val(data.fecha_maxima);
            $("#monto_efectivo").val(data.monto_efectivo);
            $("#porcentaje").val(data.porcentaje);

            $("#tipo_descuento_id")
                .val(data.tipo_descuento_id)
                .trigger("change");

            if (data.reglas && data.reglas.length > 0) {
                data.reglas.forEach((regla) => {
                    agregarRegla(regla.tipo);

                    const contenedor =
                        document.getElementById("contenedor_reglas");
                    const reglaEl = contenedor.lastElementChild;

                    if (regla.tipo === "G" && regla.cargos) {
                        const select =
                            reglaEl.querySelector(".select-cargos")?.tomselect;
                        regla.cargos.forEach((id) => select?.addItem(id));
                    }

                    if (regla.tipo === "P" && regla.personas) {
                        const select =
                            reglaEl.querySelector(
                                ".select-clientes",
                            )?.tomselect;

                        regla.personas.forEach((id) => select?.addItem(id));
                    }

                    if (regla.tipo === "E" && regla.personas) {
                        const select =
                            reglaEl.querySelector(
                                ".select-empleados",
                            )?.tomselect;

                        regla.personas.forEach((id) => select?.addItem(id));
                    }
                });
            }

            $("#modalTitulo").text("Editar Descuento");
            $("#modalDescuento").modal("show");
        });
    });

    $("#formDescuento").submit(function (e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop("disabled", true);
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
                    $btn.prop("disabled", false);

                    tabla.ajax.reload();
                }
            },
        );
    });

    $("#btnLimpiarFiltros").click(function () {
        $("#filtroCodigo").val("");
        $("#filtroTipoCupon")[0].tomselect.clear();
        $("#filtroPersona")[0].tomselect.clear();
        $("#filtroCargo")[0].tomselect.clear();
        tabla.ajax.reload();
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

    function inicializarRegla(reglaEl) {
        const selectTipo = reglaEl.querySelector(".select-tipo-regla");

        function actualizarDetalle() {
            if (!selectTipo) return;

            const tipo = selectTipo.value;

            reglaEl
                .querySelectorAll(
                    ".detalle-T, .detalle-G, .detalle-P, .detalle-C, .detalle-E",
                )
                .forEach((d) => {
                    d.style.display = "none";
                });

            const target = reglaEl.querySelector(".detalle-" + tipo);

            if (target) {
                target.style.display = "";
            }
        }

        selectTipo?.addEventListener("change", actualizarDetalle);
        actualizarDetalle();

        reglaEl
            .querySelector(".btnEliminarRegla")
            ?.addEventListener("click", function () {
                reglaEl.remove();
            });

        const selectCargos = reglaEl.querySelector(".select-cargos");
        if (selectCargos) {
            new TomSelect(selectCargos, {
                plugins: ["remove_button"],
                maxItems: null,
                closeAfterSelect: false,
            });
        }

        const selectClientes = reglaEl.querySelector(".select-clientes");

        if (selectClientes) {
            new TomSelect(selectClientes, {
                plugins: ["remove_button"],
                maxItems: null,
                closeAfterSelect: false,
            });
        }

        const selectEmpleados = reglaEl.querySelector(".select-empleados");

        if (selectEmpleados) {
            new TomSelect(selectEmpleados, {
                plugins: ["remove_button"],
                maxItems: null,
                closeAfterSelect: false,
            });
        }
    }

    function agregarRegla(tipoInicial) {
        const template = document.getElementById("templateRegla");
        const clone = template.content.cloneNode(true);
        const reglaEl = clone.querySelector(".regla-item");

        document.getElementById("contenedor_reglas").appendChild(reglaEl);

        inicializarRegla(reglaEl);

        if (tipoInicial) {
            const sel = reglaEl.querySelector(".select-tipo-regla");
            sel.value = tipoInicial;
            sel.dispatchEvent(new Event("change"));
        }
    }

    $("#btnAgregarRegla").on("click", function () {
        agregarRegla();
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
