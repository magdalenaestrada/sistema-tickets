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

        // Ocultar todos los campos inicialmente
        pagoEfectivoInput.closest(".mb-3").hide();
        pagoBilleteraInput.closest(".mb-3").hide();
        billeteraSelect.closest(".mb-3").hide();
        grupoCostoTotal.attr("hidden", true);

        // Limpiar readonly por defecto
        pagoEfectivoInput.prop("readonly", false);
        pagoBilleteraInput.prop("readonly", false);

        if (metodo === 1) {
            // Solo efectivo
            pagoEfectivoInput.closest(".mb-3").show();
            pagoEfectivoInput.val(total.toFixed(2)).prop("readonly", true);
        } else if (metodo === 2) {
            // Solo billetera digital
            pagoBilleteraInput.closest(".mb-3").show();
            billeteraSelect.closest(".mb-3").show();
            pagoBilleteraInput.val(total.toFixed(2)).prop("readonly", true);
        } else if (metodo === 3) {
            // Mixto: ambos editables
            pagoEfectivoInput.closest(".mb-3").show();
            pagoBilleteraInput.closest(".mb-3").show();
            billeteraSelect.closest(".mb-3").show();
            grupoCostoTotal.removeAttr("hidden");

            // Inicializa con reparto equitativo si ambos están en cero
            let pagoE = parseFloat(pagoEfectivoInput.val()) || 0;
            let pagoB = parseFloat(pagoBilleteraInput.val()) || 0;

            if (pagoE === 0 && pagoB === 0) {
                let mitad = total / 2;
                pagoEfectivoInput.val(mitad.toFixed(2));
                pagoBilleteraInput.val((total - mitad).toFixed(2));
            }
            // No llamamos a actualizarPagosCombinados aquí para no sobrescribir
        }
    }

    function actualizarPagosCombinados() {
        const total = parseFloat(costoTotalInput.val()) || 0;
        let pagoE = pagoEfectivoInput.val();
        let pagoB = pagoBilleteraInput.val();

        // Permitir temporalmente que quede vacío
        pagoE = pagoE === "" ? 0 : parseFloat(pagoE) || 0;
        pagoB = pagoB === "" ? 0 : parseFloat(pagoB) || 0;

        if (document.activeElement === pagoEfectivoInput[0]) {
            pagoB = total - pagoE;
            if (pagoB < 0) pagoB = 0;
        } else if (document.activeElement === pagoBilleteraInput[0]) {
            pagoE = total - pagoB;
            if (pagoE < 0) pagoE = 0;
        }

        // Solo actualizamos los valores si no está vacío
        if (document.activeElement !== pagoEfectivoInput[0])
            pagoEfectivoInput.val(pagoE.toFixed(2));
        if (document.activeElement !== pagoBilleteraInput[0])
            pagoBilleteraInput.val(pagoB.toFixed(2));
    }

    // Formatear al perder foco
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

    // Eventos
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

    // Eventos para pagos mixtos
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

    if (horarioId) {
        $.getJSON(`/pasajes/horario/${horarioId}/asientos`, function (data) {
            seatPrices = data.precios || {};
            actualizarCostoTotal();
        });
    }

    $("#btnReservar").on("click", function (e) {
        e.preventDefault();

        if (selectedSeatNumbers.length === 0) {
            alert("No hay asientos seleccionados");
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

            const promesa = $.post("/pasajes/reservar", datosReserva)
                .done((res) => {
                    if (res.success) {
                        marcarAsientoReservado(res.asiento_numero);
                        reservasExitosas++;
                    }
                })
                .fail((err) => {
                    reservasFallidas++;
                    alert(`Error en asiento ${asientoNum}`);
                });

            promesas.push(promesa);
        });

        Promise.all(promesas).then(() => {
            alert(
                `Reservas: ${reservasExitosas} exitosas, ${reservasFallidas} fallidas`
            );
        });
    });

    $("#btnTerminarVenta").on("click", function (e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append("accion", "terminar");
        formData.append(
            "tipo_documento_factura_id",
            $("#tipo_documento_factura_id").val()
        );

        selectedSeatNumbers.forEach((n) => formData.append("asientos[]", n));

        // Agregar todos los demás campos
        $("#formVenta")
            .serializeArray()
            .filter((f) => f.name !== "asientos[]")
            .forEach((f) => formData.append(f.name, f.value));

        // Archivos
        $('input[type="file"]').each(function () {
            if (this.files.length > 0)
                formData.append(this.name, this.files[0]);
        });

        $.ajax({
            url: "/pasajes/guardar",
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
                    alert("Venta realizada correctamente");
                    window.location.href = res.redirect;
                }
            },
            error: function () {
                alert("Error al procesar la venta");
            },
        });
    });

    refrescarPagos();
});
