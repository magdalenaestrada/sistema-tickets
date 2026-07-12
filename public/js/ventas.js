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
    const seriesSucursal = config.seriesSucursal || [];
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

    function bloquearDatosPersonales() {
        selectedSeatNumbers.forEach((_, i) => {
            $(`#documento_${i}`).prop("readonly", true).addClass("bg-light");

            $(`#nombres_${i}`).prop("readonly", true).addClass("bg-light");

            $(`#apellidos_${i}`).prop("readonly", true).addClass("bg-light");

            $(`#celular_${i}`).prop("readonly", true).addClass("bg-light");

            $(`#telefono_${i}`).prop("readonly", true).addClass("bg-light");

            $(`#correo_${i}`).prop("readonly", true).addClass("bg-light");

            $(`#tipo_documento_id_${i}`).prop("disabled", true);

            $(`#pasajero_menor_${i}`).prop("disabled", true);

            $(`.btn-buscar-documento[data-index="${i}"]`).prop(
                "disabled",
                true,
            );
        });
    }

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

    function actualizarResumenTotales() {
        let subtotalOriginal = 0;
        let totalDescuento = 0;
        let totalPagar = 0;

        selectedSeatNumbers.forEach((num, i) => {
            const precioFinal = parseFloat(seatPrices[num]) || 0;
            const descuento = parseFloat(descuentosAplicados[num]?.monto || 0);

            // sobre equipaje
            let totalSobre = 0;
            $(`#tablaSobreEquipaje_${i} tbody tr`).each(function () {
                totalSobre +=
                    parseFloat($(this).find(".sobre-costo").val()) || 0;
            });

            subtotalOriginal += precioBase + totalSobre;
            totalDescuento += descuento;
            totalPagar += precioFinal + totalSobre;

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
        $("#direccion_cliente").val("");
    }

    function ponerClienteVariosNotaVenta() {
        $("#doc_cliente").val("00000000").prop("readonly", true);
        $("#razon_social").val("CLIENTE VARIOS").prop("readonly", true);
        $("#direccion_cliente").val("-");
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

    function buscarCliente() {
        let documento = $("#doc_cliente").val().trim();

        $("#btnBuscarCliente").prop("disabled", true);

        $.getJSON(route("buscar.buscar") + "?documento=" + documento)
            .done(function (data) {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // RUC
                if (data.razon_social) {
                    $("#razon_social").val(data.razon_social);
                    $("#direccion").val(data.direccion || "-");
                }
                // DNI
                else {
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
                alert("No se encontró el documento.");
            })
            .always(function () {
                $("#btnBuscarCliente").prop("disabled", false);
            });
    }

    $(document).on("click", "#btnBuscarCliente", function () {
        buscarCliente();
    });

    $(document).on("keypress", "#doc_cliente", function (e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarCliente();
        }
    });

    function actualizarCostoTotal() {
        actualizarResumenTotales();
    }

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
        const total = parseFloat($("#costo_total").val()) || 0;
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

    function buscarDocumento(index) {
        const input = $(`#documento_${index}`);
        const documento = input.val().trim();
        limpiarCupones(index);

        input.prop("disabled", true);

        $.getJSON(route("buscar.buscar") + `?documento=${documento}`)
            .done((data) => {
                if (data.error) {
                    alert(data.error);
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

                cargarCuponesPersona(index, documento);
            })
            .always(() => {
                input.prop("disabled", false);
            });
    }

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

    function buscarYLimpiar(index) {
        limpiarCupones(index);
        buscarDocumento(index);
    }

    $(document).on("blur", ".documento-input", function () {
        buscarYLimpiar($(this).data("index"));
    });

    $(document).on("click", ".btn-buscar-documento", function () {
        buscarYLimpiar($(this).data("index"));
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
            descuentosAplicados[asiento] = {
                descuento_id: null,
                codigo: null,
                tipo: null,
                valor: 0,
                monto: 0,
            };

            seatPrices[asiento] = precioBase;
            msg.text("");

            actualizarCostoTotal();
            return;
        }

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

            console.log(res);

            if (res.error) {
                Swal.fire("Atención", res.error, "warning");
                input.val("");
                return;
            }

            let descuentoAplicado = 0;
            const descuentoId =
                res.descuento_id ?? res.id ?? res.data?.id ?? null;

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
                // Promo = 100% del precio
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

            console.log("Asiento:", asiento);
            console.log("Descuento:", desc);

            formData.append(
                "descuento_ids[]",
                desc.descuento_id !== null && desc.descuento_id !== undefined
                    ? desc.descuento_id
                    : "",
            );

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
        if (!datosPasajerosCompletos()) return;
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

                efectivo.val(Math.max(0, total - nuevosOtros).toFixed(2));
            } else {
                efectivo.val(Math.max(0, total - otros).toFixed(2));
            }

            validarSumaPagos();
        },
    );

    $(document).on(
        "input",
        "#modal_pago_tarjeta, #modal_pago_plin, #modal_pago_transferencia",
        function () {
            if (parseInt($("#modal_metodo_pago").val()) !== 2) {
                return;
            }

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
        const btn = $(this);
        if (btn.prop("disabled")) return;
        btn.prop("disabled", true).text("Procesando...");

        if (!datosPasajerosCompletos()) {
            btn.prop("disabled", false).text("Terminar Venta");
            return;
        }

        if (!validarClienteFacturacion()) {
            btn.prop("disabled", false).text("Terminar Venta");
            return;
        }

        if (!validarMenores()) {
            Swal.fire("Atención", "Adjunta autorización PDF.", "warning");
            btn.prop("disabled", false).text("Terminar Venta");
            return;
        }

        if (!validarSumaPagos()) {
            Swal.fire("Atención", "La suma de pagos no coincide.", "warning");
            btn.prop("disabled", false).text("Terminar Venta");
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

        const pagos = [];

        if (pagoEfectivo > 0) {
            pagos.push({
                metodo_pago_id: 1,
                total: pagoEfectivo,
            });
        }

        if (pagoYape > 0) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: 1,
                total: pagoYape,
            });
        }

        if (pagoPlin > 0) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: 2,
                total: pagoPlin,
            });
        }

        if (pagoTarjeta > 0) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: 3,
                total: pagoTarjeta,
            });
        }

        if (pagoTransferencia > 0) {
            pagos.push({
                metodo_pago_id: 2,
                billetera_id: 4,
                total: pagoTransferencia,
            });
        }

        pagos.forEach((pago, i) => {
            formData.append(`pagos[${i}][metodo_pago_id]`, pago.metodo_pago_id);
            formData.append(
                `pagos[${i}][billetera_id]`,
                pago.billetera_id ?? "",
            );
            formData.append(`pagos[${i}][total]`, pago.total);
        });

        $.ajax({
            url: route("pasajes.store"),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: { "X-CSRF-TOKEN": csrfToken },
            success: function (res) {
                if (!res.success) {
                    $("#btnConfirmarVenta")
                        .prop("disabled", false)
                        .text("Terminar Venta");
                    return;
                }
                const win = window.open(
                    res.ticket_url,
                    "_blank",
                    "width=420,height=700",
                );

                setTimeout(() => {
                    if (win && !win.closed) {
                        try {
                            win.focus();
                            win.print();
                        } catch (e) {}
                    }
                    window.location.href = res.redirect;
                }, 1200);
            },
            error: function (xhr) {
                $("#btnConfirmarVenta")
                    .prop("disabled", false)
                    .text("Terminar Venta");

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
        const precioBase = parseFloat(option.data("precio")) || 0;
        const pesoLimite = parseFloat(option.data("peso-limite")) || 0;
        const costoExtra = parseFloat(option.data("costo-extra")) || 0;

        let costo = precioBase;

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
});
