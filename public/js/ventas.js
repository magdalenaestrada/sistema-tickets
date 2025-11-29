$(function () {
    const params = new URLSearchParams(window.location.search);
    let selectedSeatNumbers =
        params
            .get("asientos")
            ?.split(",")
            .map((n) => parseInt(n)) || [];
    const horarioId = params.get("horario");

    let seatPrices = {};
    let precioTotal = 0;

    const costoTotalInput = $("#costo_total");
    const metodoPagoSelect = $("#metodo_pago_id");
    const pagoEfectivoInput = $("#pago_efectivo");
    const pagoBilleteraInput = $("#pago_billetera");
    const billeteraSelect = $("#billetera_id");
    const grupoCostoTotal = $(".grupo_costo_total");

    const csrf_token = $('meta[name="csrf-token"]').attr("content");

    if (horarioId) {
        $.getJSON(`/pasajes/horario/${horarioId}/asientos`, function (data) {
            seatPrices = data.precios || {};
            actualizarCostoTotal();
        });
    }

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

        pagoEfectivoInput.closest(".row").hide();
        pagoBilleteraInput.closest(".row").hide();
        billeteraSelect.closest(".row").hide();
        grupoCostoTotal.attr("hidden", true);

        pagoEfectivoInput.prop("readonly", false);
        pagoBilleteraInput.prop("readonly", false);

        if (metodo === 1) {
            pagoEfectivoInput.closest(".row").show();
            pagoEfectivoInput.val(total.toFixed(2)).prop("readonly", true);
        } else if (metodo === 2) {
            pagoBilleteraInput.closest(".row").show();
            billeteraSelect.closest(".row").show();
            pagoBilleteraInput.val(total.toFixed(2)).prop("readonly", true);
        } else if (metodo === 3) {
            pagoEfectivoInput.closest(".row").show();
            pagoBilleteraInput.closest(".row").show();
            billeteraSelect.closest(".row").show();
            grupoCostoTotal.removeAttr("hidden");

            let pagoE = parseFloat(pagoEfectivoInput.val()) || 0;
            if (pagoE > total) pagoE = total;

            pagoEfectivoInput.val(pagoE.toFixed(2));
            pagoBilleteraInput.val((total - pagoE).toFixed(2));
        }
    }

    metodoPagoSelect.on("change", refrescarPagos);

    function marcarAsientoOcupado(asientoNum) {
        let seat = document.getElementById(`seat-${asientoNum}`);
        if (seat) seat.querySelector(".seat-body").setAttribute("fill", "red");
    }

    function marcarAsientoReservado(asientoNum) {
        let seat = document.getElementById(`seat-${asientoNum}`);
        if (seat)
            seat.querySelector(".seat-body").setAttribute("fill", "orange");
    }

    $("#btnReservar").on("click", function (e) {
        e.preventDefault();

        selectedSeatNumbers.forEach((asientoNum) => {
            $.post("/pasajes/reservar", {
                _token: csrf_token,
                horario_id: horarioId,
                asiento_numero: asientoNum,
            })
                .done((res) => {
                    if (res.success) {
                        marcarAsientoReservado(res.asiento_numero);
                    }
                })
                .fail((err) => {
                    alert(
                        err.responseJSON?.message || "Error al reservar asiento"
                    );
                });
        });
    });

    $("#btnTerminarVenta").on("click", function (e) {
        e.preventDefault();

        const formData = new FormData();

        $("form").each(function () {
            $(this)
                .serializeArray()
                .forEach((f) => {
                    formData.append(f.name, f.value);
                });
        });

        formData.append("horario_id", horarioId);
        formData.append("asientos", selectedSeatNumbers.join(","));

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
            error: function (err) {
                alert(
                    err.responseJSON?.message || "Error al procesar la venta"
                );
            },
        });
    });
});
