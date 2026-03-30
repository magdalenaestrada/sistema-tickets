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
    const metodoPagoSelect = $("#metodo_pago_id");
    const pagoEfectivoInput = $("#pago_efectivo");
    const pagoBilleteraInput = $("#pago_billetera");
    const billeteraSelect = $("#billetera_id");
    const grupoCostoTotal = $(".grupo_costo_total");

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

    function actualizarCostoTotal() {
        const total = selectedSeatNumbers.reduce((sum, num) => {
            return sum + (parseFloat(seatPrices[num]) || 0);
        }, 0);

        costoTotalInput.val(total.toFixed(2));
        refrescarPagos();
    }

    function refrescarPagos() {
        const metodo = parseInt(metodoPagoSelect.val());
        const total = parseFloat(costoTotalInput.val()) || 0;

        pagoEfectivoInput.closest(".mb-3").hide();
        pagoBilleteraInput.closest(".mb-3").hide();
        billeteraSelect.closest(".mb-3").hide();
        grupoCostoTotal.attr("hidden", true);

        pagoEfectivoInput.prop("readonly", false);
        pagoBilleteraInput.prop("readonly", false);

        if (metodo === 1) {
            pagoEfectivoInput.closest(".mb-3").show();
            pagoEfectivoInput.val(total.toFixed(2)).prop("readonly", true);
        } else if (metodo === 2) {
            pagoBilleteraInput.closest(".mb-3").show();
            billeteraSelect.closest(".mb-3").show();
            pagoBilleteraInput.val(total.toFixed(2)).prop("readonly", true);
        } else if (metodo === 3) {
            pagoEfectivoInput.closest(".mb-3").show();
            pagoBilleteraInput.closest(".mb-3").show();
            billeteraSelect.closest(".mb-3").show();
            grupoCostoTotal.removeAttr("hidden");

            let pagoE = parseFloat(pagoEfectivoInput.val()) || 0;
            let pagoB = parseFloat(pagoBilleteraInput.val()) || 0;

            if (pagoE === 0 && pagoB === 0) {
                let mitad = total / 2;
                pagoEfectivoInput.val(mitad.toFixed(2));
                pagoBilleteraInput.val((total - mitad).toFixed(2));
            }
        }
    }

    function actualizarPagosCombinados() {
        const total = parseFloat(costoTotalInput.val()) || 0;
        let pagoE =
            pagoEfectivoInput.val() === ""
                ? 0
                : parseFloat(pagoEfectivoInput.val()) || 0;
        let pagoB =
            pagoBilleteraInput.val() === ""
                ? 0
                : parseFloat(pagoBilleteraInput.val()) || 0;

        if (document.activeElement === pagoEfectivoInput[0]) {
            pagoB = total - pagoE;
            if (pagoB < 0) pagoB = 0;
        } else if (document.activeElement === pagoBilleteraInput[0]) {
            pagoE = total - pagoB;
            if (pagoE < 0) pagoE = 0;
        }

        if (document.activeElement !== pagoEfectivoInput[0]) {
            pagoEfectivoInput.val(pagoE.toFixed(2));
        }

        if (document.activeElement !== pagoBilleteraInput[0]) {
            pagoBilleteraInput.val(pagoB.toFixed(2));
        }
    }

    metodoPagoSelect.on("change", refrescarPagos);

    pagoEfectivoInput.on("input", function () {
        if (parseInt(metodoPagoSelect.val()) === 3) {
            actualizarPagosCombinados();
        }
    });

    pagoBilleteraInput.on("input", function () {
        if (parseInt(metodoPagoSelect.val()) === 3) {
            actualizarPagosCombinados();
        }
    });

    pagoEfectivoInput.on("blur", function () {
        let val = parseFloat(this.value) || 0;
        let total = parseFloat(costoTotalInput.val()) || 0;
        if (val > total) val = total;
        this.value = val.toFixed(2);
        actualizarPagosCombinados();
    });

    pagoBilleteraInput.on("blur", function () {
        let val = parseFloat(this.value) || 0;
        let total = parseFloat(costoTotalInput.val()) || 0;
        if (val > total) val = total;
        this.value = val.toFixed(2);
        actualizarPagosCombinados();
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
                if (data.error) {
                    return;
                }

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

                if (parseInt(index) === 0) {
                    $("#numero_documento_id").val(documento);
                    const nombres = $(`#nombres_${index}`).val();
                    const apellidos = $(`#apellidos_${index}`).val();
                    $("#razon_social").val(`${nombres} ${apellidos}`.trim());
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

    $("#btnTerminarVenta").on("click", function (e) {
        e.preventDefault();

        if (!validarMenores()) {
            Swal.fire(
                "Atención",
                "Los pasajeros menores deben adjuntar autorización PDF.",
                "warning",
            );
            return;
        }

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
    refrescarPagos();
});
