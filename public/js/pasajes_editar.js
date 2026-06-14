/**
 * editar_pasaje.js
 * Solo permite: pagar, cancelar reserva, agregar sobre equipaje, cupones y cambiar precio.
 * Bloquea toda edición de datos personales del pasajero.
 */

$(function () {
    const config = window.VENTA_CONFIG || {};
    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const tiposEncomienda = config.tiposEncomienda || [];
    const salidaId = config.salidaId;
    const origenId = config.origenId;
    const destinoId = config.destinoId;
    const selectedSeatNumbers = config.asientos || [];
    let precioBase = parseFloat(config.precioUnitario || 0);
    const descuentoPromoId = config.descuentoPromoId || 1;

    const costoTotalInput = $("#costo_total");

    let seatPrices = {};
    let descuentosAplicados = {};

    selectedSeatNumbers.forEach((num) => {
        seatPrices[num] = precioBase;
        descuentosAplicados[num] = {
            descuento_id: null,
            codigo: null,
            monto: 0,
        };
    });

    // ─────────────────────────────────────────────────────────────
    // BLOQUEAR datos personales (solo lectura)
    // ─────────────────────────────────────────────────────────────
    function bloquearDatosPersonales() {
        selectedSeatNumbers.forEach((_, i) => {
            // Campos de texto / email
            $(`#documento_${i}`)
                .prop("readonly", true)
                .prop("disabled", false)
                .addClass("bg-light");
            $(`#nombres_${i}`).prop("readonly", true).addClass("bg-light");
            $(`#apellidos_${i}`).prop("readonly", true).addClass("bg-light");
            $(`#celular_${i}`).prop("readonly", true).addClass("bg-light");
            $(`#telefono_${i}`).prop("readonly", true).addClass("bg-light");
            $(`#correo_${i}`).prop("readonly", true).addClass("bg-light");

            // Select tipo documento
            $(`#tipo_documento_id_${i}`).prop("disabled", true);

            // Botón buscar documento
            $(`.btn-buscar-documento[data-index="${i}"]`).prop(
                "disabled",
                true,
            );

            // Checkbox menor de edad y su contenedor
            $(`#pasajero_menor_${i}`).prop("disabled", true);
        });
    }

    bloquearDatosPersonales();

    // ─────────────────────────────────────────────────────────────
    // PRECIO MANUAL
    // ─────────────────────────────────────────────────────────────
    $("#precio_manual").on("input", function () {
        const nuevoPrecio = parseFloat($(this).val());
        if (isNaN(nuevoPrecio) || nuevoPrecio < 0) return;

        precioBase = nuevoPrecio;

        selectedSeatNumbers.forEach((num) => {
            const desc = descuentosAplicados[num];

            if (!desc) {
                seatPrices[num] = nuevoPrecio;
                return;
            }

            if (desc.tipo === "porcentaje") {
                desc.monto = nuevoPrecio * (desc.valor / 100);
            } else if (desc.tipo === "monto_fijo") {
                desc.monto = desc.valor;
            } else {
                desc.monto = 0;
            }

            seatPrices[num] = Math.max(0, nuevoPrecio - desc.monto);
        });

        actualizarCostoTotal();
    });

    // ─────────────────────────────────────────────────────────────
    // TOTALES
    // ─────────────────────────────────────────────────────────────
    function actualizarResumenTotales() {
        let subtotalOriginal = 0;
        let totalDescuento = 0;
        let totalPagar = 0;

        selectedSeatNumbers.forEach((num, i) => {
            const precioFinal = parseFloat(seatPrices[num]) || 0;
            const descuento = parseFloat(descuentosAplicados[num]?.monto || 0);

            let totalSobre = 0;
            $(`#tablaSobreEquipaje_${i} tbody tr`).each(function () {
                totalSobre +=
                    parseFloat($(this).find(".sobre-costo").val()) || 0;
            });

            subtotalOriginal += precioBase + totalSobre;
            totalDescuento += descuento;
            totalPagar += precioFinal + totalSobre;

            $(`#precio_asiento_${i}`).text(`S/ ${precioFinal.toFixed(2)}`);
        });

        $("#subtotal").text(subtotalOriginal.toFixed(2));
        $("#total_descuento").text(totalDescuento.toFixed(2));
        $("#total_pagar").text(totalPagar.toFixed(2));
        $("#modal_total_pagar").text(totalPagar.toFixed(2));
        costoTotalInput.val(totalPagar.toFixed(2));
    }

    function actualizarCostoTotal() {
        actualizarResumenTotales();
    }

    // ─────────────────────────────────────────────────────────────
    // CUPONES
    // ─────────────────────────────────────────────────────────────
    function limpiarCupones(index) {
        const asiento = selectedSeatNumbers[index];

        $(`#descuento_${index}`)
            .empty()
            .append('<option value="">Sin cupón</option>');

        $(`#descuento_msg_${index}`).text("");

        descuentosAplicados[asiento] = {
            descuento_id: null,
            codigo: null,
            monto: 0,
        };

        seatPrices[asiento] = precioBase;
        actualizarCostoTotal();
    }

    // Carga cupones al iniciar (ya hay DNI guardado en el input)
    function cargarCuponesPersona(index, documento) {
        const select = $(`#descuento_${index}`);
        select.html(`<option value="">Cargando cupones...</option>`);

        $.getJSON(route("descuentos.persona", { documento }))
            .done((cupones) => {
                select.html(`<option value="">Seleccionar</option>`);

                cupones.forEach((cupon) => {
                    let texto = cupon.codigo;
                    if (cupon.monto_efectivo) {
                        texto += ` - S/ ${parseFloat(cupon.monto_efectivo).toFixed(2)}`;
                    }
                    if (cupon.porcentaje) {
                        texto += ` - ${cupon.porcentaje}%`;
                    }
                    select.append(
                        `<option value="${cupon.codigo}">${texto}</option>`,
                    );
                });
            })
            .fail(() => {
                select.html(`<option value="">Sin cupón</option>`);
            });
    }

    // Cargar cupones automáticamente al iniciar con los DNI ya cargados
    selectedSeatNumbers.forEach((_, i) => {
        const documento = $(`#documento_${i}`).val().trim();
        if (documento) {
            cargarCuponesPersona(i, documento);
        }
    });

    async function verificarPromo10(index, codigo, dni) {
        const asiento = selectedSeatNumbers[index];

        const res = await $.ajax({
            url: route("pasajes.verificar_promocion"),
            method: "POST",
            data: {
                _token: csrfToken,
                salida_id: salidaId,
                origen_id: origenId,
                destino_id: destinoId,
                dni: dni,
                codigo: codigo,
            },
        });

        return { asiento, ...res };
    }

    $(".descuento-input").on("change", async function () {
        const input = $(this);
        const index = parseInt(input.data("index"));
        const codigo = input.val().trim();
        const dni = $(`#documento_${index}`).val().trim();
        const asiento = selectedSeatNumbers[index];
        const msg = $(`#descuento_msg_${index}`);

        msg.text("");

        if (!codigo) {
            delete descuentosAplicados[asiento];
            seatPrices[asiento] = precioBase;
            actualizarCostoTotal();
            return;
        }

        if (!dni) {
            Swal.fire(
                "Atención",
                "No se encontró el DNI del pasajero.",
                "warning",
            );
            input.val("");
            return;
        }

        input.prop("disabled", true);

        try {
            const res = await $.getJSON(
                route("descuentos.buscar") + `?codigo=${codigo}`,
            );

            if (res.error) {
                Swal.fire("Atención", res.error, "warning");
                input.val("");
                return;
            }

            let descuentoId = res.id || null;

            if (parseInt(descuentoId) === parseInt(descuentoPromoId)) {
                const promo = await verificarPromo10(index, codigo, dni);
                if (!promo.valido) {
                    Swal.fire(
                        "Atención",
                        promo.message || "La promoción no aplica.",
                        "warning",
                    );
                    input.val("");
                    return;
                }
                descuentosAplicados[asiento] = {
                    descuento_id: descuentoId,
                    codigo: codigo,
                    tipo: "porcentaje",
                    valor: 100,
                    monto: precioBase,
                };
                msg.text(`Promo aplicada: ${promo.message}`);
            } else {
                if (res.monto_efectivo) {
                    descuentosAplicados[asiento] = {
                        descuento_id: descuentoId,
                        codigo: codigo,
                        tipo: "monto_fijo",
                        valor: parseFloat(res.monto_efectivo),
                        monto: parseFloat(res.monto_efectivo),
                    };
                } else if (res.porcentaje) {
                    descuentosAplicados[asiento] = {
                        descuento_id: descuentoId,
                        codigo: codigo,
                        tipo: "porcentaje",
                        valor: parseFloat(res.porcentaje),
                        monto: precioBase * (parseFloat(res.porcentaje) / 100),
                    };
                }
            }

            const nuevoPrecio = Math.max(
                0,
                precioBase - descuentosAplicados[asiento].monto,
            );
            seatPrices[asiento] = nuevoPrecio;
            actualizarCostoTotal();

            Swal.fire(
                "Correcto",
                `Descuento aplicado al asiento ${asiento}`,
                "success",
            );
        } catch (e) {
            Swal.fire("Error", "No se pudo validar el descuento", "error");
            input.val("");
        } finally {
            input.prop("disabled", false);
        }
    });

    // ─────────────────────────────────────────────────────────────
    // SOBRE EQUIPAJE
    // ─────────────────────────────────────────────────────────────
    function agregarFilaSobreEquipaje(index) {
        const fila = $("<tr>");
        const tipoSelect = $(
            '<select class="form-select form-select-sm sobre-tipo"></select>',
        );

        tipoSelect.append('<option value="" disabled selected>Tipo</option>');

        tiposEncomienda.forEach((t) => {
            tipoSelect.append(`
                <option value="${t.id}"
                    data-precio="${t.precio_base}"
                    data-peso-limite="${t.peso_limite}"
                    data-costo-extra="${t.costo_kilo_extra}">
                    ${t.descripcion}
                </option>
            `);
        });

        fila.append($("<td>").append(tipoSelect));
        fila.append(
            $("<td>").append(
                '<input type="text" class="form-control form-control-sm sobre-desc">',
            ),
        );
        fila.append(
            $("<td>").append(
                '<input type="number" step="0.01" class="form-control form-control-sm sobre-peso">',
            ),
        );
        fila.append(
            $("<td>").append(
                '<input type="number" step="0.01" class="form-control form-control-sm sobre-costo">',
            ),
        );
        fila.append(
            $("<td>").append(
                '<button type="button" class="btn btn-danger btn-sm btnQuitarSobre">X</button>',
            ),
        );

        $(`#tablaSobreEquipaje_${index} tbody`).append(fila);
    }

    $(document).on("change", ".toggle-sobre-equipaje", function () {
        const index = $(this).data("index");
        $(`#card_sobre_equipaje_${index}`).toggle(this.checked);

        if (
            this.checked &&
            $(`#tablaSobreEquipaje_${index} tbody tr`).length === 0
        ) {
            agregarFilaSobreEquipaje(index);
        }
    });

    $(document).on("click", ".btn-agregar-sobre", function () {
        const index = $(this).data("index");
        agregarFilaSobreEquipaje(index);
    });

    function calcularCostoFila(tr) {
        const option = tr.find(".sobre-tipo option:selected");
        const peso = parseFloat(tr.find(".sobre-peso").val()) || 0;
        const precioBaseFila = parseFloat(option.data("precio")) || 0;
        const pesoLimite = parseFloat(option.data("peso-limite")) || 0;
        const costoExtra = parseFloat(option.data("costo-extra")) || 0;

        let costo = precioBaseFila;
        if (pesoLimite && peso > pesoLimite && costoExtra) {
            costo += (peso - pesoLimite) * costoExtra;
        }

        tr.find(".sobre-costo").val(costo.toFixed(2));
    }

    $(document).on("change", ".sobre-tipo", function () {
        calcularCostoFila($(this).closest("tr"));
    });

    $(document).on("input", ".sobre-peso", function () {
        calcularCostoFila($(this).closest("tr"));
    });

    $(document).on("click", ".btnQuitarSobre", function () {
        const tr = $(this).closest("tr");
        const table = tr.closest("table");
        tr.remove();
        recalcularTotalSobre(table);
    });

    function recalcularTotalSobre(table) {
        let total = 0;
        table.find("tbody tr").each(function () {
            total += parseFloat($(this).find(".sobre-costo").val()) || 0;
        });

        const index = table.data("index");
        $(`#total_sobre_equipaje_${index}`).text(total.toFixed(2));
        actualizarCostoTotal();
    }

    $(document).on("input", ".sobre-costo", function () {
        const table = $(this).closest("table");
        recalcularTotalSobre(table);
    });

    // ─────────────────────────────────────────────────────────────
    // MODAL DE PAGO
    // ─────────────────────────────────────────────────────────────
    function limpiarPagosModal() {
        $("#modal_pago_efectivo").val("0");
        $("#modal_pago_tarjeta").val("0");
        $("#modal_pago_yape").val("0");
        $("#modal_pago_plin").val("0");
        $("#modal_pago_transferencia").val("0");
        $("#alerta_pago").addClass("d-none");
    }

    function distribuirPagosPorMetodo() {
        const metodo = parseInt($("#modal_metodo_pago").val() || 1);
        const total = parseFloat($("#costo_total").val()) || 0;

        const efectivo = $("#modal_pago_efectivo");
        const tarjeta = $("#modal_pago_tarjeta");
        const yape = $("#modal_pago_yape");
        const plin = $("#modal_pago_plin");
        const transferencia = $("#modal_pago_transferencia");

        [efectivo, tarjeta, yape, plin, transferencia].forEach((input) => {
            input.prop("disabled", true).val("0.00");
        });

        switch (metodo) {
            case 1:
                efectivo.prop("disabled", false).val(total.toFixed(2));
                break;
            case 2:
                yape.prop("disabled", false);
                plin.prop("disabled", false);
                transferencia.prop("disabled", false);
                tarjeta.prop("disabled", false);
                yape.val(total.toFixed(2));
                break;
            case 3:
                efectivo.prop("disabled", false);
                tarjeta.prop("disabled", false);
                yape.prop("disabled", false);
                plin.prop("disabled", false);
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
        const total = parseFloat($("#costo_total").val()) || 0;
        const totalPagado = sumarPagosModal();

        if (Math.abs(totalPagado - total) > 0.01) {
            $("#alerta_pago").removeClass("d-none");
            return false;
        }

        $("#alerta_pago").addClass("d-none");
        return true;
    }

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

            const total = parseFloat($("#costo_total").val()) || 0;
            const campoEditado = $(this).attr("id");

            if (campoEditado === "modal_pago_efectivo") {
                validarSumaPagos();
                return;
            }

            const vTarjeta = parseFloat($("#modal_pago_tarjeta").val()) || 0;
            const vYape = parseFloat($("#modal_pago_yape").val()) || 0;
            const vPlin = parseFloat($("#modal_pago_plin").val()) || 0;
            const vTransferencia =
                parseFloat($("#modal_pago_transferencia").val()) || 0;

            const otros = vTarjeta + vYape + vPlin + vTransferencia;

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

                $("#modal_pago_efectivo").val(
                    Math.max(0, total - nuevosOtros).toFixed(2),
                );
            } else {
                $("#modal_pago_efectivo").val(
                    Math.max(0, total - otros).toFixed(2),
                );
            }

            validarSumaPagos();
        },
    );

    $(document).on(
        "input",
        "#modal_pago_tarjeta, #modal_pago_plin, #modal_pago_transferencia",
        function () {
            if (parseInt($("#modal_metodo_pago").val()) !== 2) return;

            const total = parseFloat($("#costo_total").val()) || 0;
            const tarjeta = parseFloat($("#modal_pago_tarjeta").val()) || 0;
            const plin = parseFloat($("#modal_pago_plin").val()) || 0;
            const transferencia =
                parseFloat($("#modal_pago_transferencia").val()) || 0;

            const sumaOtros = tarjeta + plin + transferencia;
            $("#modal_pago_yape").val(
                Math.max(0, total - sumaOtros).toFixed(2),
            );
            validarSumaPagos();
        },
    );

    // ─────────────────────────────────────────────────────────────
    // SUNAT / SERIE (solo visual, igual que ventas.js)
    // ─────────────────────────────────────────────────────────────
    function obtenerCodigoSucursal() {
        const option = $("#caja_id option:selected");
        return String(option.data("serie") || "").trim();
    }

    function generarSeriePorTipo(tipo) {
        const codigo = obtenerCodigoSucursal();
        if (!codigo || isNaN(Number(codigo))) return "Seleccione una sucursal";
        const numero = Number(codigo);
        if (tipo === "boleta") return `BBB${numero}`;
        if (tipo === "factura") return `FFF${numero}`;
        return `NNN${numero}`;
    }

    function limpiarClienteFacturacion() {
        $("#doc_cliente").val("").prop("readonly", false);
        $("#razon_social").val("").prop("readonly", false);
    }

    function ponerClienteVariosNotaVenta() {
        $("#doc_cliente").val("00000000").prop("readonly", true);
        $("#razon_social").val("CLIENTE VARIOS").prop("readonly", true);
    }

    function marcarTipoDocumento(tipo) {
        $(".doc-btn")
            .removeClass("active btn-primary btn-success btn-warning")
            .addClass("btn-outline-secondary");

        const serie = generarSeriePorTipo(tipo);

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
                .removeClass("active btn-success btn-dark")
                .addClass("btn-outline-secondary");

            const actual = $("#tipo_doc_sunat").val();
            marcarTipoDocumento(actual === "1" ? "factura" : "boleta");
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

    $("#emitir_sunat").on("change", function () {
        actualizarEstadoSunat();
    });

    $("#caja_id").on("change", function () {
        const tipoActual = $("#tipo_doc_sunat").val() || "nota_venta";
        marcarTipoDocumento(tipoActual);
    });

    $(".doc-btn").on("click", function () {
        if ($(this).prop("disabled")) return;
        marcarTipoDocumento($(this).data("doc"));
    });

    function buscarCliente() {
        const documento = $("#doc_cliente").val().trim();
        $("#btnBuscarCliente").prop("disabled", true);

        $.getJSON(route("buscar.buscar") + "?documento=" + documento)
            .done(function (data) {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                if (data.razon_social) {
                    $("#razon_social").val(data.razon_social);
                    $("#direccion").val(data.direccion || "-");
                } else {
                    let nombre =
                        `${data.nombres || ""} ${data.apellido_paterno || ""} ${data.apellido_materno || ""}`.trim();
                    $("#razon_social").val(nombre);
                    $("#direccion").val("-");
                }
            })
            .fail(() => alert("No se encontró el documento."))
            .always(() => $("#btnBuscarCliente").prop("disabled", false));
    }

    $(document).on("click", "#btnBuscarCliente", buscarCliente);
    $(document).on("keypress", "#doc_cliente", function (e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarCliente();
        }
    });

    // ─────────────────────────────────────────────────────────────
    // CONSTRUIR PAYLOAD
    // ─────────────────────────────────────────────────────────────
    function construirPayload(accion) {
        const form = document.getElementById("formVenta");

        // Habilitar temporalmente los selects deshabilitados para que entren al FormData
        const selectsDeshabilitados = $("select[disabled]");
        selectsDeshabilitados.prop("disabled", false);

        const formData = new FormData(form);

        // Volver a deshabilitar
        selectsDeshabilitados.prop("disabled", true);

        formData.append("accion", accion);

        selectedSeatNumbers.forEach((asiento) => {
            const desc = descuentosAplicados[asiento] || {};
            formData.append("descuento_ids[]", desc.descuento_id || "");
            formData.append("descuento_montos[]", desc.monto || 0);
            formData.append(
                "precios_finales[]",
                seatPrices[asiento] || precioBase,
            );
        });

        $(".tabla-sobre-equipaje").each(function () {
            const index = $(this).data("index");
            $(this)
                .find("tbody tr")
                .each(function (i) {
                    formData.append(
                        `sobre_equipaje_detalles[${index}][${i}][tipo_encomienda_id]`,
                        $(this).find(".sobre-tipo").val(),
                    );
                    formData.append(
                        `sobre_equipaje_detalles[${index}][${i}][descripcion]`,
                        $(this).find(".sobre-desc").val(),
                    );
                    formData.append(
                        `sobre_equipaje_detalles[${index}][${i}][peso]`,
                        $(this).find(".sobre-peso").val(),
                    );
                    formData.append(
                        `sobre_equipaje_detalles[${index}][${i}][costo]`,
                        $(this).find(".sobre-costo").val(),
                    );
                });
        });

        return formData;
    }

    // ─────────────────────────────────────────────────────────────
    // VALIDACIÓN FACTURACIÓN
    // ─────────────────────────────────────────────────────────────
    function validarClienteFacturacion() {
        const tipo = $("#tipo_doc_sunat").val();
        if (tipo === "4") return true; // nota de venta

        const doc = $("#doc_cliente").val().trim();
        const razon = $("#razon_social").val().trim();

        if (!doc || !razon) {
            Swal.fire(
                "Atención",
                "Ingresa y busca el cliente a quien se va a boletear o facturar.",
                "warning",
            );
            return false;
        }
        return true;
    }

    // ─────────────────────────────────────────────────────────────
    // BOTÓN: ABRIR PAGO
    // ─────────────────────────────────────────────────────────────
    $("#btnAbrirPago").on("click", function (e) {
        e.preventDefault();

        actualizarCostoTotal();
        limpiarPagosModal();
        distribuirPagosPorMetodo();

        const modalEl = document.getElementById("modalPago");
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    // ─────────────────────────────────────────────────────────────
    // BOTÓN: CONFIRMAR VENTA
    // ─────────────────────────────────────────────────────────────
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

        const pagoEfectivo = parseFloat($("#modal_pago_efectivo").val()) || 0;
        const pagoTarjeta = parseFloat($("#modal_pago_tarjeta").val()) || 0;
        const pagoYape = parseFloat($("#modal_pago_yape").val()) || 0;
        const pagoPlin = parseFloat($("#modal_pago_plin").val()) || 0;
        const pagoTransferencia =
            parseFloat($("#modal_pago_transferencia").val()) || 0;

        actualizarCostoTotal();

        const totalActual = parseFloat(costoTotalInput.val()) || 0;
        const sumaPagos =
            pagoEfectivo +
            pagoTarjeta +
            pagoYape +
            pagoPlin +
            pagoTransferencia;

        if (Math.abs(sumaPagos - totalActual) > 0.01) {
            Swal.fire(
                "Atención",
                `La suma de pagos (S/ ${sumaPagos.toFixed(2)}) no coincide con el total (S/ ${totalActual.toFixed(2)}).`,
                "warning",
            );
            return;
        }

        $("#metodo_pago_id_hidden").val($("#modal_metodo_pago").val());
        $("#pago_efectivo_hidden").val(pagoEfectivo.toFixed(2));
        $("#pago_tarjeta_hidden").val(pagoTarjeta.toFixed(2));
        $("#pago_yape_hidden").val(pagoYape.toFixed(2));
        $("#pago_plin_hidden").val(pagoPlin.toFixed(2));
        $("#pago_transferencia_hidden").val(pagoTransferencia.toFixed(2));

        const formData = construirPayload("vender");

        $.ajax({
            url: route("pasajes.store"),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: { "X-CSRF-TOKEN": csrfToken },
            success: function (res) {
                if (res.success) {
                    Swal.fire(
                        "Éxito",
                        res.message || "Venta realizada correctamente",
                        "success",
                    ).then(() => {
                        window.location.href = res.redirect;
                    });
                }
            },
            error: function (xhr) {
                Swal.fire(
                    "Error",
                    xhr.responseJSON?.message || "Error al procesar la venta",
                    "error",
                );
            },
        });
    });

    // ─────────────────────────────────────────────────────────────
    // BOTÓN: CANCELAR RESERVA
    // ─────────────────────────────────────────────────────────────
    const volverAsientosUrl = route("pasajes.index", {
        salida_id: salidaId,
        origen_id: origenId,
        destino_id: destinoId,
    });

    $("#btnCancelarVenta").on("click", function () {
        Swal.fire({
            title: "Cancelar reserva",
            text: "Se liberarán los asientos y se cancelará la reserva.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cancelar",
            cancelButtonText: "No",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = volverAsientosUrl;
            }
        });
    });

    // Ocultar botón "Reservar" en el modo edición (ya es una reserva)
    $("#btnReservar").hide();

    // ─────────────────────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────────────────────
    actualizarCostoTotal();
    actualizarEstadoSunat();
});
