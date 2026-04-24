$(function () {
    const config = window.VENTA_CONFIG || {};
    const csrfToken = $('meta[name="csrf-token"]').attr("content");

    const salidaId = config.salidaId;
    const origenId = config.origenId;
    const destinoId = config.destinoId;
    const selectedSeatNumbers = config.asientos || [];
    const precioBase = parseFloat(config.precioUnitario || 0);
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

    document.addEventListener("change", function (e) {
        if (e.target.matches("[id^='tipo_documento_id_']")) {
            const index = e.target.id.split("_").pop();
            const input = document.querySelector(`#documento_${index}`);
            if (!input) return;

            input.value = "";

            let max = 15;

            if (e.target.value == 1) max = 8;
            else if (e.target.value == 2) max = 11;
            else if (e.target.value == 3) max = 9;
            else if (e.target.value == 6) max = 0;

            input.setAttribute("maxlength", max);

            if (max === 0) {
                input.value = "";
                input.disabled = true;
            } else {
                input.disabled = false;
            }
        }
    });

    document.addEventListener("input", function (e) {
        if (e.target.matches(".solo-numeros")) {
            e.target.value = e.target.value.replace(/\D/g, "");
        }
    });

    document.addEventListener("input", function (e) {
        if (e.target.matches(".solo-letras")) {
            e.target.value = e.target.value.replace(
                /[^A-Za-zÁÉÍÓÚáéíóúÑñ ]/g,
                "",
            );
        }
    });

    function obtenerCodigoSucursal() {
        const option = $("#sucursal_venta_id option:selected");
        return option.data("codigo-sucursal") || "001";
    }

    function generarSeriePorTipo(tipo) {
        const codigoSucursal = String(obtenerCodigoSucursal()).padStart(3, "0");

        if (tipo === "boleta") return `B${codigoSucursal}`;
        if (tipo === "factura") return `F${codigoSucursal}`;
        return `N${codigoSucursal}`;
    }

    function actualizarResumenTotales() {
        let subtotalOriginal = 0;
        let totalDescuento = 0;
        let totalPagar = 0;

        selectedSeatNumbers.forEach((num, i) => {
            const precioFinal = parseFloat(seatPrices[num]) || 0;
            const descuento = parseFloat(descuentosAplicados[num]?.monto || 0);

            subtotalOriginal += precioBase;
            totalDescuento += descuento;
            totalPagar += precioFinal;

            $(`#precio_asiento_${i}`).text(`S/ ${precioFinal.toFixed(2)}`);

            if ($(`#descuento_asiento_${i}`).length) {
                $(`#descuento_asiento_${i}`).text(`S/ ${descuento.toFixed(2)}`);
            }
        });

        $("#subtotal").text(subtotalOriginal.toFixed(2));
        $("#total_descuento").text(totalDescuento.toFixed(2));
        $("#total_pagar").text(totalPagar.toFixed(2));
        $("#modal_total_pagar").text(totalPagar.toFixed(2));
        costoTotalInput.val(totalPagar.toFixed(2));
    }

    const volverAsientosUrl = route("pasajes.index", {
        salida_id: salidaId,
        origen_id: origenId,
        destino_id: destinoId,
    });

    function limpiarClienteFacturacion() {
        $("#doc_cliente").val("").prop("readonly", false);
        $("#razon_social").val("").prop("readonly", false);
    }

    function ponerClienteVariosNotaVenta() {
        $("#doc_cliente").val("00000000").prop("readonly", true);
        $("#razon_social").val("CLIENTE VARIOS").prop("readonly", true);
    }

    function datosPasajerosCompletos() {
        const form = document.getElementById("formVenta");

        for (let i = 0; i < selectedSeatNumbers.length; i++) {
            const documento = $(`#documento_${i}`).val().trim();
            const nombres = $(`#nombres_${i}`).val().trim();
            const apellidos = $(`#apellidos_${i}`).val().trim();

            if (!documento || !nombres || !apellidos) {
                form.reportValidity();
                Swal.fire(
                    "Atención",
                    `Completa los datos del pasajero del asiento ${selectedSeatNumbers[i]}.`,
                    "warning",
                );
                return false;
            }
        }

        return true;
    }

    function validarClienteFacturacion() {
        const tipo = $("#tipo_doc_sunat").val();
        const doc = $("#doc_cliente").val().trim();
        const razon = $("#razon_social").val().trim();

        if (tipo === "nota_venta") return true;

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

    function buscarClienteFacturacion() {
        const documento = $("#doc_cliente").val().trim();
        const tipo = $("#tipo_doc_sunat").val();

        if (!documento || tipo === "nota_venta") return;

        $.getJSON(route("buscar.buscar") + `?documento=${documento}`).done(
            (data) => {
                if (data.error) {
                    $("#razon_social").val("");
                    Swal.fire("Atención", data.error, "warning");
                    return;
                }

                if (data.razon_social) {
                    $("#razon_social").val(data.razon_social);
                } else {
                    const apellidos =
                        `${data.apellido_paterno || ""} ${data.apellido_materno || ""}`.trim();
                    $("#razon_social").val(
                        `${data.nombres || ""} ${apellidos}`.trim(),
                    );
                }
            },
        );
    }

    function actualizarCostoTotal() {
        actualizarResumenTotales();
    }

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

        efectivo.prop("readonly", false);
        tarjeta.prop("readonly", false);
        yape.prop("readonly", false);
        plin.prop("readonly", false);
        transferencia.prop("readonly", false);

        if (metodo === 1) {
            efectivo.val(total.toFixed(2)).prop("readonly", true);
            tarjeta.val("0");
            yape.val("0");
            plin.val("0");
            transferencia.val("0");
        } else if (metodo === 2) {
            efectivo.val("0");
            tarjeta.val("0");
            yape.val(total.toFixed(2));
            plin.val("0");
            transferencia.val("0");
        } else if (metodo === 3) {
            efectivo.val(total.toFixed(2));
            tarjeta.val("0");
            yape.val("0");
            plin.val("0");
            transferencia.val("0");
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

    function marcarTipoDocumento(tipo) {
        $(".doc-btn")
            .removeClass("active btn-primary btn-success btn-dark")
            .addClass("btn-outline-secondary");

        const serie = generarSeriePorTipo(tipo);

        if (tipo === "boleta") {
            $("#tipo_doc_sunat").val("boleta");
            $("#serie_doc").text(serie);
            $("#doc_cliente").attr("maxlength", 11);
            limpiarClienteFacturacion();
        } else if (tipo === "factura") {
            $("#tipo_doc_sunat").val("factura");
            $("#serie_doc").text(serie);
            $("#doc_cliente").attr("maxlength", 11);
            limpiarClienteFacturacion();
        } else {
            $("#tipo_doc_sunat").val("nota_venta");
            $("#serie_doc").text(serie);
            $("#doc_cliente").attr("maxlength", 8);
            ponerClienteVariosNotaVenta();
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

    $("#emitir_sunat").on("change", function () {
        actualizarEstadoSunat();
    });

    $("#sucursal_venta_id").on("change", function () {
        const tipoActual = $("#tipo_doc_sunat").val() || "nota_venta";
        marcarTipoDocumento(tipoActual);
    });

    $(".doc-btn").on("click", function () {
        if ($(this).prop("disabled")) return;
        const tipo = $(this).data("doc");
        marcarTipoDocumento(tipo);
    });

    $("#doc_cliente").on("input", function () {
        if ($("#tipo_doc_sunat").val() !== "nota_venta") {
            $("#razon_social").val("");
        }
    });

    $("#doc_cliente").on("blur", function () {
        const valor = $(this).val().trim();
        const tipo = $("#tipo_doc_sunat").val();

        if (!valor || tipo === "nota_venta") return;

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

        buscarClienteFacturacion();
    });

    $("#btnRegresarAsientos").on("click", function () {
        window.location.href = volverAsientosUrl;
    });

    $("#btnCancelarVenta").on("click", function () {
        Swal.fire({
            title: "Cancelar venta",
            text: "Se liberarán los asientos seleccionados y volverás a escoger asientos.",
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

    $("#precio_manual").on("input", function () {
        const nuevoPrecio = parseFloat($(this).val());
        if (isNaN(nuevoPrecio) || nuevoPrecio < 0) return;

        selectedSeatNumbers.forEach((num) => {
            seatPrices[num] = nuevoPrecio;
        });

        actualizarCostoTotal();
    });

    $(".pasajero-menor-check").on("change", function () {
        const index = $(this).data("index");
        const container = $(`#autorizacion_container_${index}`);
        const fileInput = $(`#autorizacion_pdf_${index}`);

        if (this.checked) {
            container.slideDown();
            fileInput.prop("required", true);
        } else {
            container.slideUp();
            fileInput.prop("required", false);
            fileInput.val("");
        }
    });

    $(".documento-input").on("blur", function () {
        const input = $(this);
        const index = input.data("index");
        const documento = input.val().trim();

        if (!documento) return;

        input.prop("disabled", true);

        $.getJSON(route("buscar.buscar") + `?documento=${documento}`)
            .done((data) => {
                if (data.error) return;

                if (data.razon_social) {
                    $(`#nombres_${index}`).val(data.razon_social);
                    $(`#apellidos_${index}`).val("");
                    $(`#correo_${index}`).val(data.direccion || "");
                } else {
                    $(`#nombres_${index}`).val(data.nombres || "");
                    const apellidos =
                        `${data.apellido_paterno || ""} ${data.apellido_materno || ""}`.trim();
                    $(`#apellidos_${index}`).val(apellidos);
                    $(`#correo_${index}`).val(data.direccion || "");
                }
            })
            .always(() => {
                input.prop("disabled", false);
            });
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

    $(".descuento-input").on("blur", async function () {
        const input = $(this);
        const index = parseInt(input.data("index"));
        const codigo = input.val().trim();
        const dni = $(`#documento_${index}`).val().trim();
        const asiento = selectedSeatNumbers[index];
        const msg = $(`#descuento_msg_${index}`);

        msg.text("");

        if (!codigo) return;

        if (!dni) {
            Swal.fire(
                "Atención",
                "Primero ingresa el DNI del pasajero.",
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

            let descuentoAplicado = 0;
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

                descuentoAplicado = precioBase;
                msg.text(`Promo aplicada: ${promo.message}`);
            } else {
                if (res.monto_efectivo) {
                    descuentoAplicado = parseFloat(res.monto_efectivo);
                } else if (res.porcentaje) {
                    descuentoAplicado =
                        precioBase * (parseFloat(res.porcentaje) / 100);
                }
            }

            const nuevoPrecio = Math.max(0, precioBase - descuentoAplicado);
            seatPrices[asiento] = nuevoPrecio;

            descuentosAplicados[asiento] = {
                descuento_id: descuentoId,
                codigo: codigo,
                monto: descuentoAplicado,
            };

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

    function validarMenores() {
        let valido = true;

        $(".pasajero-menor-check").each(function () {
            const index = $(this).data("index");

            if (this.checked) {
                const fileInput = document.getElementById(
                    `autorizacion_pdf_${index}`,
                );
                if (!fileInput || !fileInput.files.length) {
                    valido = false;
                }
            }
        });

        return valido;
    }

    function construirPayload(accion) {
        const form = document.getElementById("formVenta");
        const formData = new FormData(form);

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

        return formData;
    }

    $("#btnReservar").on("click", function (e) {
        e.preventDefault();
        if (!datosPasajerosCompletos()) return;
        if (!validarMenores()) {
            Swal.fire(
                "Atención",
                "Los pasajeros menores deben adjuntar autorización PDF.",
                "warning",
            );
            return;
        }

        const formData = construirPayload("reservar");

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
                        res.message || "Reserva realizada correctamente",
                        "success",
                    ).then(() => {
                        window.location.href = res.redirect;
                    });
                }
            },
            error: function (xhr) {
                Swal.fire(
                    "Error",
                    xhr.responseJSON?.message || "Error al reservar",
                    "error",
                );
            },
        });
    });

    $("#btnAbrirPago").on("click", function (e) {
        e.preventDefault();

        if (!validarMenores()) {
            Swal.fire(
                "Atención",
                "Los pasajeros menores deben adjuntar autorización PDF.",
                "warning",
            );
            return;
        }

        actualizarCostoTotal();
        limpiarPagosModal();
        distribuirPagosPorMetodo();

        const modalEl = document.getElementById("modalPago");
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });

    $("#modal_metodo_pago").on("change", function () {
        distribuirPagosPorMetodo();
    });

    $(
        "#modal_pago_efectivo, #modal_pago_tarjeta, #modal_pago_yape, #modal_pago_plin, #modal_pago_transferencia",
    ).on("input", function () {
        validarSumaPagos();
    });

    $("#btnConfirmarVenta").on("click", function (e) {
        e.preventDefault();
        if (!datosPasajerosCompletos()) return;
        if (!validarClienteFacturacion()) return;
        if (!validarMenores()) {
            Swal.fire(
                "Atención",
                "Los pasajeros menores deben adjuntar autorización PDF.",
                "warning",
            );
            return;
        }

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

    actualizarCostoTotal();
    actualizarEstadoSunat();
});
