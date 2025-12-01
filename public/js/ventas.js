$(function () {
    const costoTotalInput = $("#costo_total");
    const metodoPagoSelect = $("#metodo_pago_id");
    const pagoEfectivoInput = $("#pago_efectivo");
    const pagoBilleteraInput = $("#pago_billetera");
    const billeteraSelect = $("#billetera_id");
    const grupoCostoTotal = $(".grupo_costo_total");

    const csrf_token = $('meta[name="csrf-token"]').attr("content");

    const params = new URLSearchParams(window.location.search);
    let selectedSeatNumbers =
        params
            .get("asientos")
            ?.split(",")
            .map((n) => parseInt(n)) || [];
    const horarioId = params.get("horario");

    let seatPrices = {};
    let precioTotal = 0;

    function actualizarCostoTotal() {
        precioTotal = selectedSeatNumbers.reduce(
            (sum, num) => sum + parseFloat(seatPrices[num] || 0),
            0
        );
        costoTotalInput.val(precioTotal.toFixed(2));
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
        let pagoE = pagoEfectivoInput.val();
        let pagoB = pagoBilleteraInput.val();

        pagoE = pagoE === "" ? 0 : parseFloat(pagoE) || 0;
        pagoB = pagoB === "" ? 0 : parseFloat(pagoB) || 0;

        if (document.activeElement === pagoEfectivoInput[0]) {
            pagoB = total - pagoE;
            if (pagoB < 0) pagoB = 0;
        } else if (document.activeElement === pagoBilleteraInput[0]) {
            pagoE = total - pagoB;
            if (pagoE < 0) pagoE = 0;
        }

        if (document.activeElement !== pagoEfectivoInput[0])
            pagoEfectivoInput.val(pagoE.toFixed(2));
        if (document.activeElement !== pagoBilleteraInput[0])
            pagoBilleteraInput.val(pagoB.toFixed(2));
    }

    pagoEfectivoInput.on("blur", function () {
        let val = parseFloat(this.value) || 0;
        if (val > parseFloat(costoTotalInput.val()))
            val = parseFloat(costoTotalInput.val());
        this.value = val.toFixed(2);
        actualizarPagosCombinados();
    });

    pagoBilleteraInput.on("blur", function () {
        let val = parseFloat(this.value) || 0;
        if (val > parseFloat(costoTotalInput.val()))
            val = parseFloat(costoTotalInput.val());
        this.value = val.toFixed(2);
        actualizarPagosCombinados();
    });

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

    function marcarAsientoOcupado(asientoNum) {
        let seat = document.getElementById(`seat-${asientoNum}`);
        if (seat) seat.querySelector(".seat-body").setAttribute("fill", "red");
    }

    function marcarAsientoReservado(asientoNum) {
        let seat = document.getElementById(`seat-${asientoNum}`);
        if (seat)
            seat.querySelector(".seat-body").setAttribute("fill", "orange");
    }

    if (horarioId) {
        $.getJSON(
            route("pasajes.horario.asientos", horarioId),
            function (data) {
                seatPrices = data.precios || {};
                actualizarCostoTotal();
            }
        );
    }

    $("#btnReservar").on("click", function (e) {
        e.preventDefault();

        if (selectedSeatNumbers.length === 0) {
            Swal.fire("Atención", "No hay asientos seleccionados", "warning");
            return;
        }

        const formData = new FormData(document.getElementById("formVenta"));
        let reservasExitosas = 0;
        let reservasFallidas = 0;
        let promesas = [];

        selectedSeatNumbers.forEach((asientoNum, index) => {
            const datosReserva = {
                _token: csrf_token,
                horario_id: horarioId,
                asiento_numero: asientoNum,
                persona: {
                    tipo_documento_id: formData.getAll("tipo_documento_id[]")[
                        index
                    ],
                    documento: formData.getAll("documento[]")[index],
                    nombres: formData.getAll("nombres[]")[index],
                    apellidos: formData.getAll("apellidos[]")[index],
                    telefono: formData.getAll("telefono[]")[index],
                    celular: formData.getAll("celular[]")[index],
                    correo: formData.getAll("direccion[]")[index],
                },
            };

            const promesa = $.post(route("pasajes.reservar"), datosReserva)
                .done((res) => {
                    if (res.success) {
                        marcarAsientoReservado(res.asiento_numero);
                        reservasExitosas++;
                    }
                })
                .fail(() => {
                    reservasFallidas++;
                    Swal.fire(
                        "Error",
                        `Error en asiento ${asientoNum}`,
                        "error"
                    );
                });

            promesas.push(promesa);
        });

        Promise.all(promesas).then(() => {
            Swal.fire(
                "Resultado",
                `Reservas: ${reservasExitosas} exitosas, ${reservasFallidas} fallidas`,
                "info"
            );
        });
    });

    $("#btnTerminarVenta").on("click", function (e) {
        e.preventDefault();

        let form = document.getElementById("formVenta");
        let formData = new FormData(form);
        formData.append("accion", "terminar");

        $('input[type="file"]').each(function () {
            if (this.files.length > 0)
                formData.append(this.name, this.files[0]);
        });

        $.ajax({
            url: route("pasajes.guardar"),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: { "X-CSRF-TOKEN": csrf_token },
            success: function (res) {
                if (res.success) {
                    selectedSeatNumbers.forEach((num) =>
                        marcarAsientoOcupado(num)
                    );
                    Swal.fire(
                        "Éxito",
                        "Venta realizada correctamente",
                        "success"
                    ).then(() => {
                        window.location.href = res.redirect;
                    });
                }
            },
            error: function () {
                Swal.fire("Error", "Error al procesar la venta", "error");
            },
        });
    });

    refrescarPagos();

    // Buscar documento automáticamente cuando el usuario sale del input
    $("[id^='documento_']").on("blur", function () {
        const input = $(this);
        const documento = input.val().trim();

        if (!documento) return;

        // obtener el index desde el id (documento_0, documento_1, etc.)
        const index = input.attr("id").split("_")[1];

        // loader mientras consulta
        input.prop("disabled", true);
        input.addClass("loading-input");

        $.getJSON(route("buscar.buscar") + `?documento=${documento}`)
            .done((data) => {
                if (data.error) {
                    Swal.fire(
                        "No encontrado",
                        "No se encontró información: " + data.error,
                        "warning"
                    );
                    return;
                }

                if (data.razon_social) {
                    // Empresa
                    $(`#nombres_${index}`).val(data.razon_social);
                    $(`#apellidos_${index}`).val("");
                    $(`#correo_${index}`).val(data.direccion || "");
                } else {
                    // Persona
                    $(`#nombres_${index}`).val(data.nombres || "");
                    $(`#apellidos_${index}`).val(
                        `${data.apellido_paterno || ""} ${
                            data.apellido_materno || ""
                        }`.trim()
                    );
                    $(`#correo_${index}`).val(data.direccion || "");
                }
            })
            .fail(() => {
                Swal.fire("Error", "No se pudo conectar con la API.", "error");
            })
            .always(() => {
                input.prop("disabled", false);
                input.removeClass("loading-input");
            });
    });
});
