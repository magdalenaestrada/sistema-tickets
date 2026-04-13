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

    document.addEventListener("change", function (e) {
        if (e.target.matches("[id^='tipo_documento_id_']")) {
            const index = e.target.id.split("_").pop();
            const input = document.querySelector(`#documento_${index}`);
            input.value = "";
            if (!input) return;

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

    function actualizarCostoTotal() {
        const total = selectedSeatNumbers.reduce((sum, num) => {
            return sum + (parseFloat(seatPrices[num]) || 0);
        }, 0);

        costoTotalInput.val(total.toFixed(2));
        refrescarPagos();

        selectedSeatNumbers.forEach((num, i) => {
            const precioFinal = seatPrices[num] || 0;
            const descuento = descuentosAplicados[num]?.monto || 0;

            $(`#precio_asiento_${i}`).text(`S/ ${precioFinal.toFixed(2)}`);
            $(`#descuento_asiento_${i}`).text(`S/ ${descuento.toFixed(2)}`);
        });
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

    $("#precio_manual").on("input", function () {
        let nuevoPrecio = parseFloat($(this).val());

        if (isNaN(nuevoPrecio) || nuevoPrecio < 0) return;

        selectedSeatNumbers.forEach((num) => {
            seatPrices[num] = nuevoPrecio;
        });

        actualizarCostoTotal();
    });

    function obtenerSeriesSucursal() {
        const option = $("#sucursal_venta_id option:selected");

        return {
            boleta: option.data("serie-boleta") || "B001",
            factura: option.data("serie-factura") || "F001",
            nota_venta: option.data("serie-nota") || "N001",
        };
    }

    function marcarTipoDocumento(tipo) {
        $(".doc-btn")
            .removeClass("active btn-primary btn-success btn-dark")
            .addClass("btn-outline-secondary");

        const serie = generarSeriePorTipo(tipo);

        if (tipo === "boleta") {
            $("#tipo_doc_sunat").val("boleta");
            $("#serie_doc").text(serie);
            $("#doc_cliente").attr("maxlength", 11).val("");
            $("#btn_boleta")
                .removeClass("btn-outline-secondary")
                .addClass("btn-primary active");
        } else if (tipo === "factura") {
            $("#tipo_doc_sunat").val("factura");
            $("#serie_doc").text(serie);
            $("#doc_cliente").attr("maxlength", 11).val("");
            $("#btn_factura")
                .removeClass("btn-outline-secondary")
                .addClass("btn-success active");
        } else {
            $("#tipo_doc_sunat").val("nota_venta");
            $("#serie_doc").text(serie);
            $("#doc_cliente").attr("maxlength", 11).val("");
            $("#btn_nota_venta")
                .removeClass("btn-outline-secondary")
                .addClass("btn-success active");
        }
    }

    $("#doc_cliente").on("blur", function () {
        const valor = $(this).val().trim();
        const tipo = $("#tipo_doc_sunat").val();

        if (!valor) return;

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
                    $("#doc_cliente").val(documento);

                    const nombres = ($(`#nombres_${index}`).val() || "").trim();
                    const apellidos = (
                        $(`#apellidos_${index}`).val() || ""
                    ).trim();

                    if (!$("#razon_social").val().trim()) {
                        $("#razon_social").val(
                            `${nombres} ${apellidos}`.trim(),
                        );
                    }
                }
            })
            .always(() => {
                input.prop("disabled", false);
            });
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
