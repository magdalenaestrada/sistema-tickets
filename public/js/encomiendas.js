$(function () {
    // === Buscar persona por documento ===
    function buscarPersona(tipo) {
        // 'emisor' o 'receptor'
        let doc = $(`#${tipo}_documento`).val();
        if (!doc) return;

        $.get(`/buscar?documento=${doc}`, function (res) {
            if (res.error) {
                alert(res.error);
                return;
            }

            if (res.tipo === "DNI") {
                $(`#${tipo}_nombres`).val(res.nombres);
                $(`#${tipo}_apellidos`).val(
                    res.apellido_paterno + " " + res.apellido_materno
                );
            } else if (res.tipo === "RUC") {
                $(`#${tipo}_nombres`).val(res.razon_social);
                $(`#${tipo}_apellidos`).val("");
                $(`#${tipo}_direccion`).val(res.direccion || "");
            }
        }).fail(function (err) {
            alert(err.responseJSON?.error || "Error al buscar documento");
        });
    }

    $("#emisor_documento").on("blur", () => buscarPersona("emisor"));
    $("#receptor_documento").on("blur", () => buscarPersona("receptor"));

    // === Evitar sucursal repetida en origen/destino ===
    $("#origen").on("change", function () {
        let origen = $(this).val();
        $("#destino option").show();
        if (origen) $('#destino option[value="' + origen + '"]').hide();
    });

    $("#destino").on("change", function () {
        let destino = $(this).val();
        $("#origen option").show();
        if (destino) $('#origen option[value="' + destino + '"]').hide();
    });

    let tabla = $("#tablaEncomiendas").DataTable({
        ajax: "/encomiendas/datatable",
        columns: [
            { data: "id" },
            { data: "emisor" },
            { data: "receptor" },
            { data: "total" },
            { data: "estado" },
            { data: "acciones", orderable: false, searchable: false },
        ],
    });

    $("#btnNueva").click(() => {
        window.location.href = "encomiendas/crear-encomienda";
    });

    $("#formEncomienda").submit(function (e) {
        e.preventDefault();

        let detalles = [];
        $("#tablaDetalles tbody tr").each(function () {
            detalles.push({
                tipo_equipaje: $(this).find(".tipo").val(),
                descripcion: $(this).find(".desc").val(),
                peso: $(this).find(".peso").val(),
                costo: $(this).find(".costo").val(),
            });
        });

        $.ajax({
            url: "/encomiendas/guardar",
            method: "POST",
            data: {
                _token: $("input[name=_token]").val(),
                emisor: {
                    documento: $("#emisor_documento").val(),
                    nombres: $("#emisor_nombres").val(),
                    apellidos: $("#emisor_apellidos").val(),
                    celular: $("#emisor_celular").val(),
                    direccion: $("#emisor_direccion").val(),
                },
                receptor: {
                    documento: $("#receptor_documento").val(),
                    nombres: $("#receptor_nombres").val(),
                    apellidos: $("#receptor_apellidos").val(),
                    celular: $("#receptor_celular").val(),
                    direccion: $("#receptor_direccion").val(),
                },
                origen: $("#origen").val(),
                destino: $("#destino").val(),
                total: $("#total").val(),
                detalles: detalles,
            },
            success: function (res) {
                if (res.success) {
                    $("#modalEncomienda").modal("hide");
                    tabla.ajax.reload();
                }
            },
        });
    });

    $(function () {
        let tiposEncomienda = [];

        $.get("/tipo-encomienda/listar-todos", function (res) {
            tiposEncomienda = res;
            agregarFilaDetalle();
        });

        $("#btnAgregarDetalle").click(() => agregarFilaDetalle());

        function agregarFilaDetalle() {
            let fila = $("<tr>");
            let tipoSelect = $('<select class="form-select tipo"></select>');
            tipoSelect.append(
                '<option value="" disabled selected>Selecciona un tipo</option>'
            );

            tiposEncomienda.forEach((t) => {
                tipoSelect.append(
                    `<option value="${t.id}" data-precio="${t.precio_base}" data-peso-limite="${t.peso_limite}" data-costo-extra="${t.costo_kilo_extra}">${t.descripcion}</option>`
                );
            });

            fila.append($("<td>").append(tipoSelect));
            fila.append(
                $("<td>").append(
                    '<input type="text" class="form-control desc">'
                )
            );
            fila.append(
                $("<td>").append(
                    '<input type="number" class="form-control peso" step="0.01">'
                )
            );
            fila.append(
                $("<td>").append(
                    '<input type="number" class="form-control costo" step="0.01">'
                )
            );
            fila.append(
                $("<td>").append(
                    '<button type="button" class="btn btn-danger btn-sm btnQuitar">Eliminar</button>'
                )
            );

            $("#tablaDetalles tbody").append(fila);

            actualizarResumen();
            recalcularTotal();
        }

        $(document).on("change", ".tipo", function () {
            let tr = $(this).closest("tr");
            let tipoId = $(this).val();
            let tipo = tiposEncomienda.find((t) => t.id == tipoId);
            if (!tipo) return;

            let pesoInput = tr.find(".peso");

            if (!pesoInput.val()) {
                pesoInput.val(tipo.peso_limite || 1);
            }

            calcularCostoFila(tr, tipo);
        });

        $(document).on("input", ".peso", function () {
            let tr = $(this).closest("tr");
            let tipoId = tr.find(".tipo").val();
            let tipo = tiposEncomienda.find((t) => t.id == tipoId);
            if (!tipo) return;

            calcularCostoFila(tr, tipo);
        });

        function recalcularTotal() {
            let total = 0;

            $("#tablaDetalles tbody tr").each(function () {
                let costo = parseFloat($(this).find(".costo").val()) || 0;
                total += costo;
            });

            $("#total").val(total.toFixed(2));
            $("#costo_total").val(total.toFixed(2));
        }

        function calcularCostoFila(tr, tipo) {
            let peso = parseFloat(tr.find(".peso").val()) || 0;

            let precioBase = parseFloat(tipo.precio_base) || 0;
            let costoKiloExtra = parseFloat(tipo.costo_kilo_extra) || 0;
            let pesoLimite = parseFloat(tipo.peso_limite) || 0;

            let costo = precioBase;

            if (pesoLimite && peso > pesoLimite && costoKiloExtra) {
                costo += (peso - pesoLimite) * costoKiloExtra;
            }

            tr.find(".costo").val(costo.toFixed(2));

            recalcularTotal();
        }

        $(document).on("click", ".btnQuitar", function () {
            $(this).closest("tr").remove();
            actualizarResumen();
            recalcularTotal();
        });

        function actualizarResumen() {
            let totalPeso = 0;
            let totalBultos = 0;

            $("#tablaDetalles tbody tr").each(function () {
                let peso = parseFloat($(this).find(".peso").val()) || 0;
                if (peso > 0) totalBultos++;
                totalPeso += peso;
            });

            $("#peso_total").val(totalPeso.toFixed(2));
            $("#cantidad_bultos").val(totalBultos);

            let origenText = $("#origen option:selected").text() || "";
            let destinoText = $("#destino option:selected").text() || "";
            $("#origen").val(origenText);
            $("#destino").val(destinoText);
        }

        $(document).on(
            "input change",
            ".peso, #origen, #destino",
            actualizarResumen
        );
        $(document).on("change", ".tipo", actualizarResumen);

        $("#tipo_documento_id").on("change", function () {
            let tipo = $(this).val();

            if (tipo !== "1") {
                // asumiendo 1 = Boleta
                $("#numero_documento_id").val("");
                $("#razon_social").val("");
                $("#numero_serie").val("");
            } else {
                // Por defecto boleta a nombre del emisor
                $("#razon_social").val(
                    $("#emisor_nombres").val() +
                        " " +
                        $("#emisor_apellidos").val()
                );
            }
        });

        function updateRazonSocial() {
            let tipo = $("#tipo_documento_id").val();
            if (tipo == "1") {
                // Boleta
                $("#razon_social").val(
                    $("#emisor_nombres").val() +
                        " " +
                        $("#emisor_apellidos").val()
                );
            }
        }

        // Llamar al completar búsqueda del emisor
        function buscarPersona(tipo) {
            let doc = $(`#${tipo}_documento`).val();
            if (!doc) return;

            $.get(`/buscar?documento=${doc}`, function (res) {
                if (res.error) {
                    alert(res.error);
                    return;
                }

                if (res.tipo === "DNI") {
                    $(`#${tipo}_nombres`).val(res.nombres);
                    $(`#${tipo}_apellidos`).val(
                        res.apellido_paterno + " " + res.apellido_materno
                    );
                } else if (res.tipo === "RUC") {
                    $(`#${tipo}_nombres`).val(res.razon_social);
                    $(`#${tipo}_apellidos`).val("");
                    $(`#${tipo}_direccion`).val(res.direccion || "");
                }

                if (tipo === "emisor") updateRazonSocial(); // actualizar nombre en boleta
            }).fail(function (err) {
                alert(err.responseJSON?.error || "Error al buscar documento");
            });
        }

        // también cuando cambie tipo de documento
        $("#tipo_documento_id").on("change", updateRazonSocial);

        $("#numero_documento_id").on("blur", function () {
            let numero = $(this).val();
            if (!numero) return;

            $.get(`/buscar?documento=${numero}`, function (res) {
                if (res.error) {
                    alert(res.error);
                    return;
                }
                if (res.tipo === "DNI" || res.tipo === "RUC") {
                    $("#razon_social").val(
                        res.razon_social ||
                            res.nombres +
                                " " +
                                res.apellido_paterno +
                                " " +
                                res.apellido_materno
                    );
                }
            }).fail(function (err) {
                alert(err.responseJSON?.error || "Error al buscar documento");
            });
        });

        function actualizarMetodosPago() {
            let metodoId = parseInt($("#metodo_pago_id").val());

            if (metodoId === 1) {
                $("#pago_efectivo").closest(".row").show();
                $("#billetera_id").closest(".row").hide();
                $("#pago_billetera").closest(".row").hide();
            }

            if (metodoId === 2) {
                $("#pago_efectivo").closest(".row").hide();
                $("#billetera_id").closest(".row").show();
                $("#pago_billetera").closest(".row").show();
            }

            if (metodoId === 3) {
                $("#pago_efectivo").closest(".row").show();
                $("#billetera_id").closest(".row").show();
                $("#pago_billetera").closest(".row").show();
            }
            calcularTotalPago();
        }

        function calcularTotalPago() {
            let total = parseFloat($("#total").val()) || 0;
            let efectivo = parseFloat($("#pago_efectivo").val()) || 0;
            let billetera = parseFloat($("#pago_billetera").val()) || 0;

            let suma = efectivo + billetera;

            if (suma > total) suma = total;

            $("#costo_total").val(total.toFixed(2));
        }
        $("#metodo_pago_id").on("change", function () {
            let metodo = $(this).val();

            $(".grupo-efectivo").hide();
            $(".grupo-digital").hide();
            $(".grupo-mixto").hide();

            $(".grupo-efectivo input").not("#total_venta").val("");
            $(".grupo-digital input").not("#total_venta").val("");
            $(".grupo-mixto input").not("#total_venta").val("");

            if (metodo == 1) {
                $(".grupo-efectivo").show();
            } else if (metodo == 2) {
                $(".grupo-digital").show();
            } else if (metodo == 3) {
                $(".grupo-mixto").show();

                let totalVenta = parseFloat($("#total_venta").val());
                $("#monto_mixto_1").val(totalVenta.toFixed(2));
            }
        });
    });
});
