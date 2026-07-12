let UBIGEO = null;
const config = window.VENTA_CONFIG || {};
const seriesSucursal = config.seriesSucursal || [];

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

$("#transbordo_incuyo").on("change", function () {
    if ($(this).is(":checked")) {
        $("#contenedor_observaciones").removeClass("d-none");
    } else {
        $("#contenedor_observaciones").addClass("d-none");

        $("#observaciones").val("");
    }
});

$(async function () {
    if (!$("#formEncomienda").length) return;

    const csrf = $('meta[name="csrf-token"]').attr("content");
    let tiposEncomienda = [];

    // ===============================
    // ORIGEN / DESTINO
    // ===============================

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

    // ===============================
    // RESUMEN DE DETALLES
    // ===============================

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

        const totalFinal = total.toFixed(2);

        // Actualizar panel de resumen (igual que ventas: subtotal, descuentos, total)
        $("#subtotal").text(totalFinal);
        $("#total_descuento").text("0.00");
        $("#total_pagar").text(totalFinal);
        $("#modal_total_pagar").text(totalFinal);

        // También actualizar input hidden de costo total si existe
        if ($("#costo_total").length) {
            $("#costo_total").val(totalFinal);
        }
    }

    // ===============================
    // MODAL DE PAGO (idéntico a ventas)
    // ===============================

    function limpiarPagosModal() {
        $("#modal_pago_efectivo").val("0");
        $("#modal_pago_tarjeta").val("0");
        $("#modal_efectivo_recibido").val("0");
        $("#modal_vuelto").val("0");
        $("#modal_pago_yape").val("0");
        $("#modal_pago_plin").val("0");
        $("#modal_pago_transferencia").val("0");
        $("#alerta_pago").addClass("d-none");
    }

    function distribuirPagosPorMetodo() {
        const metodo = parseInt($("#modal_metodo_pago").val() || 1);
        const total = parseFloat($("#total_pagar").text()) || 0;
        const efectivo = $("#modal_pago_efectivo");
        const tarjeta = $("#modal_pago_tarjeta");
        const yape = $("#modal_pago_yape");
        const plin = $("#modal_pago_plin");
        const transferencia = $("#modal_pago_transferencia");
        const efectivo_recibido = $("#modal_efectivo_recibido");
        const vuelto = $("#modal_vuelto");

        const div_efectivo = $("#modal_efectivo_div");
        const div_tarjeta = $("#modal_tarjeta_div");
        const div_yape = $("#modal_yape_div");
        const div_plin = $("#modal_plin_div");
        const div_transferencia = $("#modal_transferencia_div");

        const label_contado = $(".al_contado");

        // Reiniciar
        [
            efectivo,
            tarjeta,
            yape,
            plin,
            transferencia,
            efectivo_recibido,
            vuelto,
        ].forEach((input) => {
            input.prop("disabled", true);
            input.prop("readonly", false);
            input.val("0.00");
        });

        [
            div_efectivo,
            div_tarjeta,
            div_yape,
            div_plin,
            div_transferencia,
        ].forEach((div) => {
            div.prop("hidden", false);
        });

        switch (metodo) {
            case 1:
                efectivo.prop("disabled", false);
                vuelto.prop("disabled", false);
                efectivo_recibido.prop("disabled", false);
                efectivo.prop("readonly", true);
                vuelto.prop("readonly", true);
                efectivo.val(total.toFixed(2));

                div_yape.prop("hidden", true);
                label_contado.prop("hidden", true);
                div_plin.prop("hidden", true);
                div_transferencia.prop("hidden", true);
                div_tarjeta.prop("hidden", true);
                break;
            case 2:
                yape.prop("disabled", false);
                plin.prop("disabled", false);
                transferencia.prop("disabled", false);
                tarjeta.prop("disabled", false);
                yape.val(total.toFixed(2));
                div_efectivo.prop("hidden", true);
                break;

            case 3:
                efectivo.prop("disabled", false);
                tarjeta.prop("disabled", false);
                yape.prop("disabled", false);
                efectivo_recibido.prop("disabled", false);
                label_contado.prop("hidden", false);
                plin.prop("disabled", false);
                vuelto.prop("readonly", true);
                transferencia.prop("disabled", false);
                efectivo.val(total.toFixed(2));
                break;
        }

        validarSumaPagos();
    }

    function sumarPagosModal() {
        return (
            (parseFloat($("#modal_pago_efectivo").val()) || 0) +
            (parseFloat($("#modal_pago_tarjeta").val()) || 0) +
            (parseFloat($("#modal_pago_yape").val()) || 0) +
            (parseFloat($("#modal_pago_plin").val()) || 0) +
            (parseFloat($("#modal_pago_transferencia").val()) || 0)
        );
    }

    function validarSumaPagos() {
        const total = parseFloat($("#total_pagar").text()) || 0;
        const totalPagado = sumarPagosModal();

        if (Math.abs(totalPagado - total) > 0.01) {
            $("#alerta_pago").removeClass("d-none");
            return false;
        }

        $("#alerta_pago").addClass("d-none");
        return true;
    }

    function obtenerSeriePorTipo(tipo) {
        const sucursalId = $("#caja_id option:selected").data("sucursal");

        console.log("Sucursal seleccionada:", sucursalId);
        console.log("Tipo seleccionado:", tipo);

        let tipoDocumentoId = null;

        if (tipo === "factura") {
            tipoDocumentoId = 1;
        }

        if (tipo === "boleta") {
            tipoDocumentoId = 2;
        }

        if (tipo === "nota_venta") {
            tipoDocumentoId = 3;
        }

        console.log("Tipo documento ID:", tipoDocumentoId);

        const serie = config.seriesSucursal.find(
            (s) =>
                Number(s.sucursal_id) === Number(sucursalId) &&
                Number(s.tipo_documento_factura_id) === Number(tipoDocumentoId),
        );

        console.log("SERIE ENCONTRADA:", serie);

        return serie ? serie.serie : "SIN SERIE";
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
    function marcarTipoDocumento(tipo) {
        $(".doc-btn")
            .removeClass("active btn-primary btn-success btn-warning")
            .addClass("btn-outline-secondary");

        const serie = obtenerSeriePorTipo(tipo);

        if (tipo === "boleta") {
            $("#tipo_doc_sunat").val("2");
            $("#serie_doc").text(serie);
            $("#doc_cliente").attr("maxlength", 11);
            limpiarClienteFacturacion();

            $("#btn_boleta")
                .removeClass("btn-outline-secondary")
                .addClass("active btn-primary");
        } else if (tipo === "factura") {
            $("#tipo_doc_sunat").val("1");
            $("#serie_doc").text(serie);
            $("#doc_cliente").attr("maxlength", 11);
            limpiarClienteFacturacion();

            $("#btn_factura")
                .removeClass("btn-outline-secondary")
                .addClass("active btn-success");
        } else {
            $("#tipo_doc_sunat").val("3");
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

        if (tipo === "4") return true;

        if (!doc || !razon) {
            Swal.fire(
                "Atención",
                "Ingresa y busca el cliente a quien se va a boletear o facturar.",
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

    // ===============================
    // DETALLES DE ENCOMIENDA
    // ===============================

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

    // ===============================
    // BÚSQUEDA DE PERSONAS
    // ===============================

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

    function sincronizarFacturacionDesdeEmisor() {
        const docEmisor = ($("#emisor_documento").val() || "").trim();
        const nombresEmisor = ($("#emisor_nombres").val() || "").trim();
        const apellidosEmisor = ($("#emisor_apellidos").val() || "").trim();

        const docFact = ($("#doc_cliente").val() || "").trim();
        const razonFact = ($("#razon_social").val() || "").trim();

        if (!docFact && docEmisor) {
            $("#doc_cliente").val(docEmisor);
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

    function sinDocumento() {
        const receptor_tipo = $("#receptor_tipo_documento_id").val();

        if (receptor_tipo == "6") {
            $("#receptor_documento").prop("disabled", true).val("");
        } else {
            $("#receptor_documento").prop("disabled", false);
        }
    }

    // ===============================
    // INICIALIZACIÓN
    // ===============================

    UBIGEO = await $.get(route("ubigeos.todo"));
    initUbigeosReceptor();

    filtrarOrigenDestino();
    actualizarResumen();
    recalcularTotal();
    sinDocumento();

    // Estado inicial SUNAT (igual que ventas: arranca en nota de venta)
    actualizarEstadoSunat();

    $.get(route("tipo-encomienda.listar-todos"), function (res) {
        tiposEncomienda = res;

        if (!window.IS_EDIT) {
            agregarFilaDetalle();
        } else {
            recalcularTotal();
        }
    });

    // ===============================
    // EVENTOS DE DETALLES
    // ===============================

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

    // ===============================
    // EVENTOS DE FACTURACIÓN SUNAT (idéntico a ventas)
    // ===============================

    $("#emitir_sunat").on("change", function () {
        actualizarEstadoSunat();
    });

    $("#caja_id").on("change", function () {
        const tipoActual = $("#tipo_doc_sunat").val();

        if (tipoActual == "1") {
            marcarTipoDocumento("factura");
        } else if (tipoActual == "2") {
            marcarTipoDocumento("boleta");
        } else {
            marcarTipoDocumento("nota_venta");
        }
    });

    $(".doc-btn").on("click", function () {
        if ($(this).prop("disabled")) return;
        const tipo = $(this).data("doc");
        marcarTipoDocumento(tipo);
    });

    $("#doc_cliente").on("input", function () {
        const tipo = $("#tipo_doc_sunat").val();
        if (tipo !== "4") {
            $("#razon_social").val("");
        }
    });

    $("#doc_cliente").on("blur", function () {
        const valor = $(this).val().trim();
        const tipo = $("#tipo_doc_sunat").val();

        if (!valor || tipo === "4") return;

        if (tipo === "factura" && valor.length !== 11) {
            Swal.fire(
                "Atención",
                "Para factura el RUC debe tener 11 dígitos.",
                "warning",
            );
            return;
        }

        if (tipo === "boleta" && !(valor.length === 8 || valor.length === 11)) {
            Swal.fire(
                "Atención",
                "Para boleta usa DNI de 8 dígitos o RUC de 11.",
                "warning",
            );
            return;
        }
    });

    $(document).on("click", "#btnBuscarCliente", function () {
        buscarCliente();
    });

    $(document).on("keypress", "#doc_cliente", function (e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarCliente();
        }
    });

    // ===============================
    // EVENTOS DE PERSONAS (emisor/receptor)
    // ===============================

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

    // Botones de búsqueda por click
    $(document).on("click", ".btn-buscar-persona", function () {
        const tipo = $(this).data("tipo");
        buscarPersona(tipo);
    });

    // Enter en los campos de documento de emisor/receptor
    $(document).on(
        "keypress",
        "#emisor_documento, #receptor_documento",
        function (e) {
            if (e.which === 13) {
                e.preventDefault();
                const tipo = $(this).attr("id").replace("_documento", "");
                buscarPersona(tipo);
            }
        },
    );

    $(document).on("input change", ".tipo, .peso, .costo, .desc", function () {
        if ($(this).val()) {
            $(this).removeClass("is-invalid");
        }
    });

    $("#btnAbrirPago").on("click", function (e) {
        e.preventDefault();

        if ($("#tablaDetalles tbody tr").length === 0) {
            Swal.fire("Aviso", "Debes agregar al menos un detalle.", "warning");
            return;
        }

        let detalleInvalido = false;

        $("#tablaDetalles tbody tr").each(function () {
            const tipo = $(this).find(".tipo").val();
            const peso = $(this).find(".peso").val();
            const costo = $(this).find(".costo").val();
            const descripcion = $(this).find(".desc").val().trim();

            if (!tipo || !peso || !costo || !descripcion) {
                detalleInvalido = true;

                $(this)
                    .find(".tipo, .peso, .costo, .desc")
                    .each(function () {
                        if (!$(this).val()) {
                            $(this).addClass("is-invalid");
                        } else {
                            $(this).removeClass("is-invalid");
                        }
                    });
            }
        });

        if (detalleInvalido) {
            Swal.fire({
                icon: "warning",
                title: "Campos incompletos",
                text: "Por favor completa todos los campos de los detalles de la encomienda.",
            });

            return;
        }

        if (!validarClienteFacturacion()) return;

        recalcularTotal();
        limpiarPagosModal();
        distribuirPagosPorMetodo();

        const modalEl = document.getElementById("modalPago");
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });

    $("#modal_metodo_pago").on("change", function () {
        distribuirPagosPorMetodo();
    });

    $(document).on(
        "input",
        "#modal_pago_efectivo, #modal_pago_tarjeta, #modal_pago_yape, #modal_pago_plin, #modal_pago_transferencia",
        function () {
            const metodo = parseInt($("#modal_metodo_pago").val()) || 1;

            if (metodo !== 3) {
                validarSumaPagos();
                return;
            }

            const total = parseFloat($("#total_pagar").text()) || 0;

            const efectivo = $("#modal_pago_efectivo");
            const tarjeta = $("#modal_pago_tarjeta");
            const yape = $("#modal_pago_yape");
            const plin = $("#modal_pago_plin");
            const transferencia = $("#modal_pago_transferencia");

            const campoEditado = $(this).attr("id");

            if (campoEditado === "modal_pago_efectivo") {
                efectivo.val(
                    Math.min(parseFloat(efectivo.val()) || 0, total).toFixed(2),
                );

                validarSumaPagos();
                return;
            }

            const vTarjeta = parseFloat(tarjeta.val()) || 0;
            const vYape = parseFloat(yape.val()) || 0;
            const vPlin = parseFloat(plin.val()) || 0;
            const vTransferencia = parseFloat(transferencia.val()) || 0;
            const vEfectivo = parseFloat(efectivo.val()) || 0;

            const otros = vTarjeta + vYape + vPlin + vTransferencia + vEfectivo;

            if (otros > total) {
                const valorActual = parseFloat($(this).val()) || 0;
                const sumaSinActual = otros - valorActual;
                const maxPermitido = Math.max(0, total - sumaSinActual);

                $(this).val(maxPermitido.toFixed(2));

                const nuevosOtros =
                    campoEditado === "modal_pago_tarjeta"
                        ? maxPermitido + vYape + vPlin + vTransferencia
                        : campoEditado === "modal_pago_yape"
                          ? vTarjeta + maxPermitido + vPlin + vTransferencia
                          : campoEditado === "modal_pago_plin"
                            ? vTarjeta + vYape + maxPermitido + vTransferencia
                            : vTarjeta + vYape + vPlin + maxPermitido;

                efectivo.val(Math.max(0, total - nuevosOtros).toFixed(2));
            } else {
                efectivo.val(Math.max(0, total - otros).toFixed(2));
            }

            validarSumaPagos();
        },
    );

    $(document).on(
        "input",
        "#modal_pago_yape, #modal_pago_tarjeta, #modal_pago_plin, #modal_pago_transferencia, #modal_pago_efectivo",
        function () {
            if (parseInt($("#modal_metodo_pago").val()) !== 2) return;

            const total = parseFloat($("#total_pagar").text()) || 0;

            let valor = parseFloat($(this).val()) || 0;

            if (valor > total) {
                valor = total;
                $(this).val(total.toFixed(2));
            }

            const yape = $("#modal_pago_yape");
            const tarjeta = $("#modal_pago_tarjeta");
            const plin = $("#modal_pago_plin");
            const transferencia = $("#modal_pago_transferencia");
            const efectivo = $("#modal_pago_efectivo");

            const id = $(this).attr("id");

            let vYape = parseFloat(yape.val()) || 0;
            let vTarjeta = parseFloat(tarjeta.val()) || 0;
            let vPlin = parseFloat(plin.val()) || 0;
            let vTransferencia = parseFloat(transferencia.val()) || 0;
            let vEfectivo = parseFloat(efectivo.val()) || 0;

            if (id !== "modal_pago_yape") {
                const otros = vTarjeta + vPlin + vTransferencia + vEfectivo;

                if (otros > total) {
                    const actual = parseFloat($(this).val()) || 0;
                    const sinActual = otros - actual;
                    const maximo = Math.max(0, total - sinActual);

                    $(this).val(maximo.toFixed(2));

                    vTarjeta = parseFloat(tarjeta.val()) || 0;
                    vPlin = parseFloat(plin.val()) || 0;
                    vTransferencia = parseFloat(transferencia.val()) || 0;
                    vEfectivo = parseFloat(efectivo.val()) || 0;
                }

                yape.val(
                    Math.max(
                        0,
                        total - (vTarjeta + vPlin + vTransferencia + vEfectivo),
                    ).toFixed(2),
                );
            }

            validarSumaPagos();
        },
    );

    function calcularVuelto() {
        const contado = Number($("#modal_pago_efectivo").val()) || 0;
        const recibido = Number($("#modal_efectivo_recibido").val()) || 0;

        const vuelto = Math.max(0, recibido - contado);

        $("#modal_vuelto").val(vuelto.toFixed(2));
    }

    $("#modal_efectivo_recibido").on("input", calcularVuelto);
    $("#modal_pago_efectivo").on("input", calcularVuelto);

    $("#btnConfirmarVenta").on("click", function (e) {
        e.preventDefault();

        if (!validarClienteFacturacion()) return;

        if (!validarSumaPagos()) {
            Swal.fire(
                "Atención",
                "La suma de pagos no coincide con el total.",
                "warning",
            );
            return;
        }

        const total = parseFloat($("#total_pagar").text()) || 0;

        const pagoEfectivo = parseFloat($("#modal_pago_efectivo").val()) || 0;
        const pagoTarjeta = parseFloat($("#modal_pago_tarjeta").val()) || 0;
        const pagoYape = parseFloat($("#modal_pago_yape").val()) || 0;
        const pagoPlin = parseFloat($("#modal_pago_plin").val()) || 0;
        const pagoTransferencia =
            parseFloat($("#modal_pago_transferencia").val()) || 0;

        const sumaPagos =
            pagoEfectivo +
            pagoTarjeta +
            pagoYape +
            pagoPlin +
            pagoTransferencia;

        if (Math.abs(sumaPagos - total) > 0.01) {
            Swal.fire(
                "Atención",
                `La suma de pagos (S/ ${sumaPagos.toFixed(2)}) no coincide con el total (S/ ${total.toFixed(2)}).`,
                "warning",
            );
            return;
        }

        if ($("#tablaDetalles tbody tr").length === 0) {
            Swal.fire("Aviso", "Debes agregar al menos un detalle.", "warning");
            return;
        }

        let detalleInvalido = false;
        $("#tablaDetalles tbody tr").each(function () {
            if (!$(this).find(".tipo").val()) detalleInvalido = true;
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
                descripcion: $(this).find(".desc").val() || "",
            });
        });

        // Construir pagos
        const pagos = [];
        const metodo = parseInt($("#modal_metodo_pago").val()) || 1;

        if (pagoEfectivo > 0) {
            pagos.push({ metodo_pago_id: 1, total: pagoEfectivo });
        }

        if (pagoYape > 0) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: 1,
                tipo: "yape",
                total: pagoYape,
            });
        }

        if (pagoPlin > 0) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: 2,
                tipo: "plin",
                total: pagoPlin,
            });
        }

        if (pagoTarjeta > 0) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: 3,
                tipo: "tarjeta",
                total: pagoTarjeta,
            });
        }

        if (pagoTransferencia > 0) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: 4,
                tipo: "transferencia",
                total: pagoTransferencia,
            });
        }

        const encomiendaId = $("#encomienda_id").val();
        const url = encomiendaId
            ? route("encomiendas.actualizar", { encomienda: encomiendaId })
            : route("encomiendas.guardar");
        const method = encomiendaId ? "PUT" : "POST";

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
            origen_pueblito_id: $("#origen").val(),
            destino_pueblito_id: $("#destino").val(),
            distrito_id: $("#distrito_id").val(),
            transbordo_incuyo: $("#transbordo_incuyo").is(":checked") ? 1 : 0,

            // Facturación
            sobrequipaje: $("input[name='sobrequipaje']").val(),
            pasaje_id: $("input[name='pasaje_id']").val(),

            emitir_sunat_estado: $("#emitir_sunat_estado").val(),
            tipo_doc_sunat: $("#tipo_doc_sunat").val(),
            caja_id: $("#caja_id").val(),
            numero_documento_id: $("#doc_cliente").val(),
            razon_social: $("#razon_social").val(),
            direccion: $("#direccion").val(),
            // Totales y pagos
            total: total,
            detalles: detalles,
            tipo_servicio_id: 2,
            pagos: pagos,
        };

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function (res) {
                if (res.success) {
                    const modalEl = document.getElementById("modalPago");
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();

                    Swal.fire({
                        icon: "success",
                        title: encomiendaId
                            ? "Actualizada con éxito"
                            : "Creada con éxito",
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

    $("#formEncomienda").on("submit", function (e) {
        e.preventDefault();
        $("#btnAbrirPago").trigger("click");
    });
});
