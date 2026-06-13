let UBIGEO = null;

function initUbigeosReceptor() {
    const $dep = $("#departamento_id");
    const $prov = $("#provincia_id");
    const $dist = $("#distrito_id");
    const $ubigeo = $("#receptor_ubigeo");

    if (!$dep.length || !$prov.length || !$dist.length || !$ubigeo.length)
        return;

    const depInicial = $dep.val();
    const provInicial = $prov.val();
    const distInicial = $dist.val();

    if (!depInicial) {
        $dep.html('<option value="">Seleccione</option>');

        UBIGEO.forEach((d) => {
            $dep.append(`<option value="${d.id}">${d.nombre}</option>`);
        });
    }

    $dep.on("change", function () {
        const depId = this.value;

        $prov.html('<option value="">Seleccione</option>');
        $dist.html('<option value="">Seleccione</option>');
        $ubigeo.val("");

        if (!depId) return;

        const dep = UBIGEO.find((d) => d.id == depId);
        if (!dep) return;

        dep.provincias.forEach((p) => {
            $prov.append(`<option value="${p.id}">${p.nombre}</option>`);
        });
    });

    $prov.on("change", function () {
        const depId = $dep.val();
        const provId = this.value;

        $dist.html('<option value="">Seleccione</option>');
        $ubigeo.val("");

        if (!depId || !provId) return;

        const dep = UBIGEO.find((d) => d.id == depId);
        const prov = dep?.provincias.find((p) => p.id == provId);
        if (!prov) return;

        prov.distritos.forEach((d) => {
            $dist.append(
                `<option value="${d.id}" data-ubigeo="${d.ubigeo}">${d.nombre}</option>`,
            );
        });
    });

    $dist.on("change", function () {
        const ubigeo = $dist.find("option:selected").data("ubigeo");
        $ubigeo.val(ubigeo || "");
    });

    if (depInicial && provInicial) {
        const dep = UBIGEO.find((d) => d.id == depInicial);
        if (!dep) return;

        $prov.html('<option value="">Seleccione</option>');
        dep.provincias.forEach((p) => {
            $prov.append(`<option value="${p.id}">${p.nombre}</option>`);
        });

        $prov.val(provInicial).trigger("change");

        if (distInicial) {
            $dist.val(distInicial).trigger("change");
        }
    }
}

$(async function () {
    if (!$("#formEncomienda").length) return;

    const csrf = $('meta[name="csrf-token"]').attr("content");
    let tiposEncomienda = [];

    function filtrarOrigenDestino() {
        const origen = $("#origen").val();
        const destino = $("#destino").val();

        $("#origen option, #destino option").show();

        if (origen) {
            $("#destino option[value='" + origen + "']").hide();
        }

        if (destino) {
            $("#origen option[value='" + destino + "']").hide();
        }

        if (origen && destino && origen === destino) {
            $("#destino").val("");
        }

        const ubigeo = $("#origen option:selected").data("ubigeo") || "";
        $("#emisor_ubigeo").val(ubigeo);
    }

    $("#origen").on("change", function () {
        filtrarOrigenDestino();
    });

    function actualizarResumen() {
        let totalPeso = 0;
        let totalBultos = 0;

        $("#tablaDetalles tbody tr").each(function () {
            const peso = parseFloat($(this).find(".peso").val()) || 0;
            if (peso > 0) totalBultos++;
            totalPeso += peso;
        });

        $("#peso_total").val(totalPeso.toFixed(2));
        $("#cantidad_bultos").val(totalBultos);
    }

    function recalcularTotal() {
        let total = 0;

        $("#tablaDetalles tbody tr").each(function () {
            total += parseFloat($(this).find(".costo").val()) || 0;
        });

        $("#costo_total").val(total.toFixed(2));

        const metodo = parseInt($("#metodo_pago_id").val());

        if (metodo === 1) {
            $("#pago_efectivo").val(total.toFixed(2));
            $("#pago_billetera").val(0);
        } else if (metodo === 2) {
            $("#pago_billetera").val(total.toFixed(2));
            $("#pago_efectivo").val(0);
        } else if (metodo === 3) {
            let pagoE = parseFloat($("#pago_efectivo").val()) || 0;
            if (pagoE > total) pagoE = total;
            $("#pago_efectivo").val(pagoE.toFixed(2));
            $("#pago_billetera").val((total - pagoE).toFixed(2));
        }
    }

    function refrescarPagos() {
        const metodo = parseInt($("#metodo_pago_id").val());
        const total = parseFloat($("#costo_total").val()) || 0;
        const sinVenta = $("#tiene_venta").val() !== "1";

        $("#pago_efectivo").closest(".row").attr("hidden", true);
        $("#billetera_id").closest(".row").attr("hidden", true);
        $("#pago_billetera").closest(".row").attr("hidden", true);
        $("#pago_efectivo").prop("readonly", false);
        $("#pago_billetera").prop("readonly", false);

        if (metodo === 1) {
            $("#pago_efectivo").closest(".row").removeAttr("hidden");
            $("#pago_efectivo").val(total.toFixed(2)).prop("readonly", true);
            $("#pago_billetera").val("0");
            sinVenta
                ? $(".grupo_costo_total").removeAttr("hidden")
                : $(".grupo_costo_total").attr("hidden", true);
        } else if (metodo === 2) {
            $("#billetera_id").closest(".row").removeAttr("hidden");
            $("#pago_billetera").closest(".row").removeAttr("hidden");
            $("#pago_billetera").val(total.toFixed(2)).prop("readonly", true);
            $("#pago_efectivo").val("0");
            sinVenta
                ? $(".grupo_costo_total").removeAttr("hidden")
                : $(".grupo_costo_total").attr("hidden", true);
        } else if (metodo === 3) {
            $("#pago_efectivo").closest(".row").removeAttr("hidden");
            $("#billetera_id").closest(".row").removeAttr("hidden");
            $("#pago_billetera").closest(".row").removeAttr("hidden");
            $(".grupo_costo_total").removeAttr("hidden");

            if (!window.IS_EDIT) {
                $("#pago_efectivo").val(total.toFixed(2));
                $("#pago_billetera").val("0.00");
            } else {
                let pagoE = parseFloat($("#pago_efectivo").val()) || 0;
                if (pagoE > total) pagoE = total;
                $("#pago_efectivo").val(pagoE.toFixed(2));
                $("#pago_billetera").val((total - pagoE).toFixed(2));
            }
        }
    }

    function agregarFilaDetalle() {
        const fila = $("<tr>");
        const tipoSelect = $('<select class="form-select tipo"></select>');

        tipoSelect.append(
            '<option value="" disabled selected>Selecciona un tipo</option>',
        );

        tiposEncomienda.forEach((t) => {
            tipoSelect.append(
                `<option value="${t.id}" data-precio="${t.precio_base}" data-peso-limite="${t.peso_limite}" data-costo-extra="${t.costo_kilo_extra}">
                    ${t.descripcion}
                </option>`,
            );
        });

        fila.append($("<td>").append(tipoSelect));
        fila.append(
            $("<td>").append('<input type="text" class="form-control desc">'),
        );
        fila.append(
            $("<td>").append(
                '<input type="number" class="form-control peso" step="0.01">',
            ),
        );
        fila.append(
            $("<td>").append(
                '<input type="number" class="form-control costo" step="0.01">',
            ),
        );
        fila.append(
            $("<td>").append(
                '<button type="button" class="btn btn-danger btn-sm btnQuitar">Eliminar</button>',
            ),
        );

        $("#tablaDetalles tbody").append(fila);

        actualizarResumen();
        recalcularTotal();
    }

    function calcularCostoFila(tr, tipo) {
        const peso = parseFloat(tr.find(".peso").val()) || 0;
        const precioBase = parseFloat(tipo.precio_base) || 0;
        const costoKiloExtra = parseFloat(tipo.costo_kilo_extra) || 0;
        const pesoLimite = parseFloat(tipo.peso_limite) || 0;

        let costo = precioBase;

        if (pesoLimite && peso > pesoLimite && costoKiloExtra) {
            costo += (peso - pesoLimite) * costoKiloExtra;
        }

        tr.find(".costo").val(costo.toFixed(2));
        recalcularTotal();
        actualizarResumen();
        $("#metodo_pago_id").trigger("change");
    }

    function debounce(fn, delay) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function updateRazonSocial() {
        const tipo = $("#tipo_documento_id").val();
        if (tipo == "1") {
            $("#razon_social").val(
                ($("#emisor_nombres").val() || "") +
                    " " +
                    ($("#emisor_apellidos").val() || ""),
            );
        }
    }

    function buscarPersona(tipo, campoDocumento = null) {
        const doc = campoDocumento
            ? $(campoDocumento).val()
            : $(`#${tipo}_documento`).val();
        if (!doc) return;

        $.get(route("buscar.buscar") + `?documento=${doc}`, function (res) {
            if (res.error) {
                Swal.fire("Aviso", res.error, "warning");
                return;
            }

            if (res.tipo === "DNI") {
                $(`#${tipo}_nombres`).val(res.nombres);
                $(`#${tipo}_apellidos`).val(
                    `${res.apellido_paterno || ""} ${res.apellido_materno || ""}`.trim(),
                );
            } else if (res.tipo === "RUC") {
                $(`#${tipo}_nombres`).val(res.razon_social);
                $(`#${tipo}_apellidos`).val("");
                $(`#${tipo}_direccion`).val(res.direccion || "");
            }

            if (tipo === "emisor") {
                updateRazonSocial();
                sincronizarFacturacionDesdeEmisor();
            }

            if (campoDocumento) {
                $("#numero_documento_id").val(doc);
            }
        }).fail(function (err) {
            Swal.fire(
                "Error",
                err.responseJSON?.error || "Error al buscar documento",
                "error",
            );
        });
    }

    function sinDocumento() {
        const receptor_tipo = $("#receptor_tipo_documento_id").val();

        if (receptor_tipo == "6") {
            $("#receptor_documento").prop("disabled", true).val("");
        } else {
            $("#receptor_documento").prop("disabled", false);
        }
    }

    UBIGEO = await $.get(route("ubigeos.todo"));
    initUbigeosReceptor();

    filtrarOrigenDestino();
    actualizarResumen();
    recalcularTotal();
    sinDocumento();

    $.get(route("tipo-encomienda.listar-todos"), function (res) {
        tiposEncomienda = res;

        if (!window.IS_EDIT) {
            agregarFilaDetalle();
        } else {
            recalcularTotal();
            refrescarPagos();
        }
    });

    $("#btnAgregarDetalle").on("click", agregarFilaDetalle);

    $("#origen").on("change", function () {
        filtrarOrigenDestino();
        actualizarResumen();
    });

    $("#destino").on("change", function () {
        filtrarOrigenDestino();
        actualizarResumen();
    });

    $(document).on("input", ".peso, .costo", function () {
        recalcularTotal();
        actualizarResumen();
    });

    $(document).on("change", ".tipo", function () {
        const tr = $(this).closest("tr");
        const tipoId = $(this).val();
        const tipo = tiposEncomienda.find((t) => t.id == tipoId);
        if (!tipo) return;

        const pesoInput = tr.find(".peso");
        if (!pesoInput.val()) {
            pesoInput.val(tipo.peso_limite || 1);
        }

        calcularCostoFila(tr, tipo);
    });

    $(document).on("input", ".peso", function () {
        const tr = $(this).closest("tr");
        const tipoId = tr.find(".tipo").val();
        const tipo = tiposEncomienda.find((t) => t.id == tipoId);
        if (!tipo) return;

        calcularCostoFila(tr, tipo);
    });

    $(document).on("click", ".btnQuitar", function () {
        $(this).closest("tr").remove();
        actualizarResumen();
        recalcularTotal();
    });

    $("#metodo_pago_id").on("change", refrescarPagos);

    $("#pago_efectivo").on("input", function () {
        if ($("#metodo_pago_id").val() != "3") return;
        const total = parseFloat($("#costo_total").val()) || 0;
        let efectivo = parseFloat($(this).val()) || 0;
        if (efectivo > total) efectivo = total;
        $("#pago_billetera").val((total - efectivo).toFixed(2));
    });

    $("#pago_billetera").on("input", function () {
        if ($("#metodo_pago_id").val() != "3") return;
        const total = parseFloat($("#costo_total").val()) || 0;
        let digital = parseFloat($(this).val()) || 0;
        if (digital > total) digital = total;
        $("#pago_efectivo").val((total - digital).toFixed(2));
    });

    $("#receptor_tipo_documento_id").on("change", sinDocumento);
    $("#tipo_documento_id").on("change", updateRazonSocial);

    $("#emisor_documento").on(
        "blur",
        debounce(() => buscarPersona("emisor"), 300),
    );
    $("#receptor_documento").on(
        "blur",
        debounce(() => buscarPersona("receptor"), 300),
    );

    $("#numero_documento_id").on("blur", function () {
        const numero = ($(this).val() || "").trim();

        if (!numero) {
            $("#razon_social").val("");
            return;
        }

        $.get(route("buscar.buscar") + `?documento=${numero}`, function (res) {
            if (res.error) {
                Swal.fire("Aviso", res.error, "warning");
                return;
            }

            if (numero.length === 8) {
                const nombreCompleto = [
                    res.nombres || "",
                    res.apellido_paterno || "",
                    res.apellido_materno || "",
                ]
                    .join(" ")
                    .replace(/\s+/g, " ")
                    .trim();

                $("#razon_social").val(nombreCompleto);
            } else if (numero.length === 11) {
                $("#razon_social").val((res.razon_social || "").trim());
            } else {
                $("#razon_social").val("");
            }
        }).fail(function (err) {
            Swal.fire(
                "Error",
                err.responseJSON?.error || "Error al buscar documento",
                "error",
            );
        });
    });

    const $container = $("#container_pago");
    const $metodo = $("#metodo_pago_id");

    $container.prop("hidden", true);

    $metodo.on("change", function () {
        if ($(this).val()) {
            $container.prop("hidden", false);
            refrescarPagos();
        } else {
            $container.prop("hidden", true);
        }
    });
    function ocultarPago() {
        $container.prop("hidden", true);
        $container.find("input, select").prop("disabled", true).val("");
    }

    function mostrarPago() {
        $container.prop("hidden", false);
        $container.find("input, select").prop("disabled", false);
    }

    const encomiendaId = $("#encomienda_id").val();
    const url = encomiendaId
        ? route("encomiendas.actualizar", { encomienda: encomiendaId })
        : route("encomiendas.guardar");
    const method = encomiendaId ? "PUT" : "POST";

    $("#formEncomienda").on("submit", function (e) {
        e.preventDefault();

        if ($("#tablaDetalles tbody tr").length === 0) {
            Swal.fire("Aviso", "Debes agregar al menos un detalle.", "warning");
            return;
        }

        let detalleInvalido = false;

        $("#tablaDetalles tbody tr").each(function () {
            const tipo = $(this).find(".tipo").val();
            if (!tipo) detalleInvalido = true;
        });

        if (detalleInvalido) {
            Swal.fire(
                "Aviso",
                "Todos los detalles deben tener un tipo seleccionado.",
                "warning",
            );
            return;
        }

        const detalles = [];
        $("#tablaDetalles tbody tr").each(function () {
            detalles.push({
                tipo_encomienda_id: $(this).find(".tipo").val(),
                tipo_encomienda_nombre: $(this)
                    .find(".tipo option:selected")
                    .text(),
                peso: $(this).find(".peso").val(),
                costo: $(this).find(".costo").val(),
                descripcion: $(this).find(".desc").val() || "Sin descripción",
            });
        });

        const pagos = [];

        if ($("#pago_instantaneo").is(":checked")) {
            const metodo = parseInt($("#metodo_pago_id").val());
            const total = parseFloat($("#costo_total").val()) || 0;

            if (metodo === 1) {
                pagos.push({
                    metodo_pago_id: 1,
                    total: total,
                });
            } else if (metodo === 2) {
                pagos.push({
                    metodo_pago_id: 2,
                    billetera_id: $("#billetera_id").val(),
                    total: total,
                });
            } else if (metodo === 3) {
                const efectivo = parseFloat($("#pago_efectivo").val()) || 0;
                const billetera = parseFloat($("#pago_billetera").val()) || 0;

                if (efectivo > 0) {
                    pagos.push({
                        metodo_pago_id: 1,
                        total: efectivo,
                    });
                }

                if (billetera > 0) {
                    pagos.push({
                        metodo_pago_id: 2,
                        billetera_id: $("#billetera_id").val(),
                        total: billetera,
                    });
                }
            }
        }

        const data = {
            _token: $("input[name=_token]").val(),
            emisor: {
                documento: $("#emisor_documento").val(),
                tipo_documento_id: $("#emisor_tipo_documento_id").val(),
                nombres: $("#emisor_nombres").val(),
                apellidos: $("#emisor_apellidos").val(),
                celular: $("#emisor_celular").val(),
                telefono: $("#emisor_telefono").val(),
                direccion: $("#emisor_direccion").val(),
            },
            receptor: {
                documento: $("#receptor_documento").val(),
                tipo_documento_id: $("#receptor_tipo_documento_id").val(),
                nombres: $("#receptor_nombres").val(),
                apellidos: $("#receptor_apellidos").val(),
                celular: $("#receptor_celular").val(),
                telefono: $("#receptor_telefono").val(),
                direccion: $("#receptor_direccion").val(),
            },
            origen: $("#origen").val(),
            numero_documento_id: $("#numero_documento_id").val(),
            razon_social: $("#razon_social").val(),
            pago_instantaneo: $("#pago_instantaneo").is(":checked") ? 1 : 0,
            distrito_id: $("#distrito_id").val(),
            destino: $("#destino").val(),
            tipo_documento_factura_id: $("#tipo_documento_factura_id").val(),
            total: parseFloat($("#costo_total").val()) || 0,
            detalles: detalles,
            tipo_servicio_id: 2,
            sucursal_id: $("#sucursal_id").val(),
            serie: null,
            numero: null,
            pagos: pagos,
        };

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: "success",
                        title: encomiendaId
                            ? "Encomienda actualizada"
                            : "Encomienda creada",
                        timer: 1200,
                        showConfirmButton: false,
                    });

                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 1200);
                } else {
                    Swal.fire(
                        "Error",
                        res.message || "Ocurrió un error",
                        "error",
                    );
                }
            },
            error: function (xhr) {
                Swal.fire(
                    "Error",
                    xhr.responseJSON?.message ||
                        "No se pudo guardar la encomienda",
                    "error",
                );
            },
        });
    });

    function sincronizarFacturacionDesdeEmisor() {
        const docEmisor = ($("#emisor_documento").val() || "").trim();
        const nombresEmisor = ($("#emisor_nombres").val() || "").trim();
        const apellidosEmisor = ($("#emisor_apellidos").val() || "").trim();

        const docFact = ($("#numero_documento_id").val() || "").trim();
        const razonFact = ($("#razon_social").val() || "").trim();

        if (!docFact && docEmisor) {
            $("#numero_documento_id").val(docEmisor);
        }

        if (!razonFact) {
            if (docEmisor.length === 8) {
                $("#razon_social").val(
                    [nombresEmisor, apellidosEmisor]
                        .join(" ")
                        .replace(/\s+/g, " ")
                        .trim(),
                );
            } else if (docEmisor.length === 11) {
                $("#razon_social").val(nombresEmisor);
            }
        }
    }

    // ===============================
    // FACTURACION SUNAT
    // ===============================

    function obtenerCodigoSucursal() {
        const option = $("#caja_id option:selected");
        return String(option.data("serie") || "").trim();
    }

    function generarSeriePorTipo(tipo) {
        const codigo = obtenerCodigoSucursal();

        if (!codigo || isNaN(Number(codigo))) {
            return "Seleccione una sucursal";
        }

        const numero = Number(codigo);

        if (tipo === "boleta") return `BBB${numero}`;
        if (tipo === "factura") return `FFF${numero}`;

        return `NNN${numero}`;
    }

    function limpiarClienteFacturacion() {
        $("#doc_cliente").val("").prop("readonly", false);
        $("#razon_social").val("").prop("readonly", false);
        $("#direccion").val("-");
    }

    function ponerClienteVariosNotaVenta() {
        $("#doc_cliente").val("00000000").prop("readonly", true);
        $("#razon_social").val("CLIENTE VARIOS").prop("readonly", true);
        $("#direccion").val("-");
    }

    function buscarCliente() {
        let documento = $("#doc_cliente").val().trim();

        if (!documento) return;

        $("#btnBuscarCliente").prop("disabled", true);

        $.getJSON(route("buscar.buscar") + "?documento=" + documento)
            .done(function (data) {
                if (data.error) {
                    Swal.fire("Aviso", data.error, "warning");
                    return;
                }

                if (data.razon_social) {
                    $("#razon_social").val(data.razon_social);
                    $("#direccion").val(data.direccion || "-");
                } else {
                    let nombreCompleto = (
                        (data.nombres || "") +
                        " " +
                        (data.apellido_paterno || "") +
                        " " +
                        (data.apellido_materno || "")
                    ).trim();

                    $("#razon_social").val(nombreCompleto);
                    $("#direccion").val("-");
                }
            })
            .fail(function () {
                Swal.fire("Error", "No se encontró el documento", "error");
            })
            .always(function () {
                $("#btnBuscarCliente").prop("disabled", false);
            });
    }

    function marcarTipoDocumento(tipo) {
        $(".doc-btn")
            .removeClass("active btn-primary btn-success btn-warning")
            .addClass("btn-outline-secondary");

        const serie = generarSeriePorTipo(tipo);

        if (tipo === "boleta") {
            $("#tipo_doc_sunat").val("boleta");
            $("#serie_doc").text(serie);

            $("#doc_cliente").attr("maxlength", 11);

            limpiarClienteFacturacion();

            $("#btn_boleta")
                .removeClass("btn-outline-secondary")
                .addClass("active btn-primary");
        } else if (tipo === "factura") {
            $("#tipo_doc_sunat").val("factura");
            $("#serie_doc").text(serie);

            $("#doc_cliente").attr("maxlength", 11);

            limpiarClienteFacturacion();

            $("#btn_factura")
                .removeClass("btn-outline-secondary")
                .addClass("active btn-success");
        } else {
            $("#tipo_doc_sunat").val("4");
            $("#serie_doc").text(serie);

            $("#doc_cliente").attr("maxlength", 8);

            ponerClienteVariosNotaVenta();

            $("#btn_nota_venta")
                .removeClass("btn-outline-secondary")
                .addClass("active btn-warning");
        }
    }

    function actualizarEstadoSunat() {
        const sunatActivo = $("#emitir_sunat").is(":checked");

        $("#emitir_sunat_estado").val(sunatActivo ? "1" : "0");

        if (sunatActivo) {
            $("#btn_boleta").prop("disabled", false);
            $("#btn_factura").prop("disabled", false);

            $("#btn_nota_venta")
                .prop("disabled", true)
                .removeClass("active btn-warning")
                .addClass("btn-outline-secondary");

            const actual = $("#tipo_doc_sunat").val();

            if (actual === "factura") {
                marcarTipoDocumento("factura");
            } else {
                marcarTipoDocumento("boleta");
            }
        } else {
            $("#btn_boleta")
                .prop("disabled", true)
                .removeClass("active btn-primary")
                .addClass("btn-outline-secondary");

            $("#btn_factura")
                .prop("disabled", true)
                .removeClass("active btn-success")
                .addClass("btn-outline-secondary");

            $("#btn_nota_venta").prop("disabled", false);

            marcarTipoDocumento("nota_venta");
        }
    }

    function validarClienteFacturacion() {
        const tipo = $("#tipo_doc_sunat").val();

        const doc = $("#doc_cliente").val().trim();

        const razon = $("#razon_social").val().trim();

        if (tipo === "4") {
            return true;
        }

        if (!doc || !razon) {
            Swal.fire(
                "Atención",
                "Debe buscar el cliente para emitir el comprobante.",
                "warning",
            );

            return false;
        }

        if (tipo === "factura" && doc.length !== 11) {
            Swal.fire(
                "Atención",
                "La factura requiere RUC de 11 dígitos.",
                "warning",
            );

            return false;
        }

        if (tipo === "boleta" && doc.length !== 8 && doc.length !== 11) {
            Swal.fire(
                "Atención",
                "La boleta requiere DNI (8) o RUC (11).",
                "warning",
            );

            return false;
        }

        return true;
    }
});
