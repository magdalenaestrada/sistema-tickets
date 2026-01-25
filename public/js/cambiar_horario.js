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
    let asientoNuevo = null;
    let horarioNuevoId = null;
    let pasajeCambioIndex = null;

    // NUEVO: Detectar si estamos en modo edición
    const modoEdicion = $('input[name="pasaje_id"]').length > 0;

    // NUEVO: Guardar valores iniciales para modo edición
    const valoresIniciales = {
        pagoEfectivo: parseFloat(pagoEfectivoInput.val()) || 0,
        pagoBilletera: parseFloat(pagoBilleteraInput.val()) || 0,
        costoTotal: parseFloat(costoTotalInput.val()) || 0,
    };

    window.abrirCambioHorario = function (index, asiento, horarioId) {
        pasajeCambioIndex = index;
        asientoNuevo = null;
        horarioNuevoId = null;

        $("#listaHorariosCambio").html("");

        document.querySelectorAll(".selected-seat").forEach((s) => {
            s.classList.remove("selected-seat");
        });
    };

    window.buscarHorariosCambio = function () {
        $.get(
            route("horarios.filtrar"),
            {
                fecha: $("#filtroFechaCambio").val(),
                origen_id: $("#filtroOrigenCambio").val(),
                destino_id: $("#filtroDestinoCambio").val(),
            },
            function (res) {
                let html = "";

                if (!res || res.length === 0) {
                    html =
                        '<div class="col-12"><p class="text-center text-muted">No hay horarios disponibles.</p></div>';
                    $("#listaHorariosCambio").html(html);
                    return;
                }

                res.forEach((h) => {
                    const capacidad = h.tipo_vehiculo.capacidad;
                    const vendidos = h.pasajes_count;
                    const disponibles = capacidad - vendidos;

                    html += `
<div class="col-md-6 mb-4">
    <!-- TARJETA HORARIO -->
    <div class="card horario-card mb-2" data-horario-id="${h.id}">
        <div class="card-body">
            <h6 class="mb-1">
                ${h.tipo_vehiculo.descripcion} –
                ${disponibles} asientos disponibles
            </h6>
            <small>
                ${h.punto_origen.nombre_comercial}
                →
                ${h.punto_destino.nombre_comercial}<br>
                ${h.fecha_salida} - ${h.hora_embarque}
            </small>
        </div>
    </div>

    <!-- TARJETA SVG (oculta inicialmente) -->
    <div class="card d-none" id="svg-card-${h.id}">
        <div class="card-body p-2">
            <div id="svg-bus-${h.id}" class="svg-bus-container"></div>
        </div>
    </div>
</div>`;
                });

                $("#listaHorariosCambio").html(html);

                agregarEventListenersHorarios();
            },
        ).fail(function () {
            Swal.fire("Error", "No se pudieron buscar horarios", "error");
        });
    };

    function agregarEventListenersHorarios() {
        const horarioCards = document.querySelectorAll(
            "#listaHorariosCambio .horario-card",
        );

        horarioCards.forEach((card) => {
            card.addEventListener("click", function () {
                const horarioId = this.dataset.horarioId;
                seleccionarHorarioCambio(horarioId);
            });
        });
    }

    window.seleccionarHorarioCambio = function (horarioId) {
        horarioNuevoId = horarioId;
        asientoNuevo = null;

        document
            .querySelectorAll("#listaHorariosCambio .horario-card")
            .forEach((c) => {
                c.classList.remove("active");
            });

        const tarjetaActiva = document.querySelector(
            `[data-horario-id="${horarioId}"]`,
        );
        if (tarjetaActiva) {
            tarjetaActiva.classList.add("active");
        }

        $("[id^='svg-card-']").addClass("d-none");

        $.get(route("pasajes.asientos", horarioId), function (res) {
            const contenedor = $(`#svg-bus-${horarioId}`);
            contenedor.html(res.svg);

            $(`#svg-card-${horarioId}`).removeClass("d-none");

            const svgEl = contenedor[0].querySelector("svg");
            if (!svgEl) {
                console.error("SVG no encontrado");
                return;
            }

            Object.keys(res.asientos).forEach((numero) => {
                const estado = res.asientos[numero];
                const g = svgEl.querySelector(`#seat-${numero}`);

                if (!g) return;

                g.classList.remove(
                    "ocupado",
                    "reservado",
                    "libre",
                    "selected-seat",
                );

                g.classList.add(estado);

                g.dataset.estado = estado;
                g.dataset.numero = numero;

                if (estado === "libre") {
                    g.style.cursor = "pointer";
                    g.style.opacity = "1";

                    g.onclick = function (e) {
                        e.stopPropagation();
                        seleccionarAsientoCambio(numero, g);
                    };
                } else if (estado === "ocupado") {
                    g.style.cursor = "not-allowed";
                    g.style.opacity = "0.6";

                    g.onclick = function (e) {
                        e.stopPropagation();
                        Swal.fire({
                            icon: "warning",
                            title: "Asiento no disponible",
                            text: "Este asiento ya está vendido",
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    };
                } else if (estado === "reservado") {
                    g.style.cursor = "not-allowed";
                    g.style.opacity = "0.6";

                    g.onclick = function (e) {
                        e.stopPropagation();
                        Swal.fire({
                            icon: "warning",
                            title: "Asiento no disponible",
                            text: "Este asiento está reservado",
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    };
                }
            });

            $("#leyendaAsientos").slideDown();
        }).fail(function () {
            Swal.fire("Error", "No se pudieron cargar los asientos", "error");
        });
    };

    window.seleccionarAsientoCambio = function (numero, seatElement) {
        document.querySelectorAll(".selected-seat").forEach((seat) => {
            seat.classList.remove("selected-seat");

            const estadoOriginal = seat.dataset.estado;
            if (estadoOriginal === "libre") {
                seat.classList.add("libre");
            }
        });

        asientoNuevo = numero;
        seatElement.classList.remove("libre");
        seatElement.classList.add("selected-seat");

        Swal.fire({
            icon: "success",
            title: `Asiento ${numero} seleccionado`,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: "top-end",
        });
    };

    window.confirmarCambioHorario = function () {
        if (!horarioNuevoId || !asientoNuevo) {
            Swal.fire({
                icon: "warning",
                title: "Datos incompletos",
                text: "Debe seleccionar un horario y un asiento",
            });
            return;
        }

        document.querySelectorAll('input[name="asientos[]"]')[
            pasajeCambioIndex
        ].value = asientoNuevo;
        document.querySelectorAll('input[name="horario_id[]"]')[
            pasajeCambioIndex
        ].value = horarioNuevoId;

        const modalElement = document.getElementById("modalCambioHorario");
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        modalInstance.hide();

        Swal.fire({
            icon: "success",
            title: "Cambio registrado",
            text: `Nuevo asiento: ${asientoNuevo}`,
            timer: 2000,
        });
    };

    $("#btnBuscarCambio").on("click", function () {
        buscarHorariosCambio();
    });

    function agregarEventListenersAsientos() {
        const seats = document.querySelectorAll('.seat, [id^="seat-"]');

        seats.forEach((seat) => {
            const asientoNum = seat.id ? seat.id.replace("seat-", "") : null;

            if (!asientoNum) return;
            const clickHandler = function (e) {
                e.preventDefault();
                e.stopPropagation();
                seleccionarAsientoCambio(asientoNum);
            };

            const mouseEnterHandler = function () {
                if (
                    this.dataset.estado === "ocupado" ||
                    this.dataset.estado === "reservado"
                ) {
                    return;
                }
                this.classList.add("seat-hover");
            };

            const mouseLeaveHandler = function () {
                this.classList.remove("seat-hover");
            };

            seat.removeEventListener("click", seat._clickHandler);
            seat.removeEventListener("mouseenter", seat._mouseEnterHandler);
            seat.removeEventListener("mouseleave", seat._mouseLeaveHandler);

            seat._clickHandler = clickHandler;
            seat._mouseEnterHandler = mouseEnterHandler;
            seat._mouseLeaveHandler = mouseLeaveHandler;

            seat.addEventListener("click", clickHandler);
            seat.addEventListener("mouseenter", mouseEnterHandler);
            seat.addEventListener("mouseleave", mouseLeaveHandler);
        });
    }

    function actualizarCostoTotal() {
        precioTotal = selectedSeatNumbers.reduce(
            (sum, num) => sum + parseFloat(seatPrices[num] || 0),
            0,
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

            // MODIFICADO: En modo edición, preservar el valor inicial
            if (modoEdicion && valoresIniciales.pagoEfectivo > 0) {
                pagoEfectivoInput.val(valoresIniciales.pagoEfectivo.toFixed(2));
            } else {
                pagoEfectivoInput.val(total.toFixed(2));
            }
            pagoEfectivoInput.prop("readonly", true);
        } else if (metodo === 2) {
            pagoBilleteraInput.closest(".mb-3").show();
            billeteraSelect.closest(".mb-3").show();

            // MODIFICADO: En modo edición, preservar el valor inicial
            if (modoEdicion && valoresIniciales.pagoBilletera > 0) {
                pagoBilleteraInput.val(
                    valoresIniciales.pagoBilletera.toFixed(2),
                );
            } else {
                pagoBilleteraInput.val(total.toFixed(2));
            }
            pagoBilleteraInput.prop("readonly", true);
        } else if (metodo === 3) {
            pagoEfectivoInput.closest(".mb-3").show();
            pagoBilleteraInput.closest(".mb-3").show();
            billeteraSelect.closest(".mb-3").show();
            grupoCostoTotal.removeAttr("hidden");

            let pagoE = parseFloat(pagoEfectivoInput.val()) || 0;
            let pagoB = parseFloat(pagoBilleteraInput.val()) || 0;

            // MODIFICADO: Si estamos en edición y hay valores guardados, usarlos
            if (
                modoEdicion &&
                (valoresIniciales.pagoEfectivo > 0 ||
                    valoresIniciales.pagoBilletera > 0)
            ) {
                pagoEfectivoInput.val(valoresIniciales.pagoEfectivo.toFixed(2));
                pagoBilleteraInput.val(
                    valoresIniciales.pagoBilletera.toFixed(2),
                );
            } else if (pagoE === 0 && pagoB === 0) {
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
        $.getJSON(route("pasajes.asientos", horarioId), function (data) {
            seatPrices = data.precios || {};
            actualizarCostoTotal();
        });
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
                        "error",
                    );
                });

            promesas.push(promesa);
        });

        Promise.all(promesas).then(() => {
            Swal.fire(
                "Resultado",
                `Reservas: ${reservasExitosas} exitosas, ${reservasFallidas} fallidas`,
                "info",
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
                        marcarAsientoOcupado(num),
                    );
                    Swal.fire(
                        "Éxito",
                        "Venta realizada correctamente",
                        "success",
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

    // MODIFICADO: Solo llamar a refrescarPagos() después de guardar los valores iniciales
    refrescarPagos();

    $("[id^='documento_']").on("blur", function () {
        const input = $(this);
        const documento = input.val().trim();

        if (!documento) return;

        const index = input.attr("id").split("_")[1];

        input.prop("disabled", true);
        input.addClass("loading-input");

        $.getJSON(route("buscar.buscar") + `?documento=${documento}`)
            .done((data) => {
                if (data.error) {
                    Swal.fire(
                        "No encontrado",
                        "No se encontró información: " + data.error,
                        "warning",
                    );
                    return;
                }

                if (data.razon_social) {
                    $(`#nombres_${index}`).val(data.razon_social);
                    $(`#apellidos_${index}`).val("");
                    $(`#correo_${index}`).val(data.direccion || "");
                } else {
                    $(`#nombres_${index}`).val(data.nombres || "");
                    $(`#apellidos_${index}`).val(
                        `${data.apellido_paterno || ""} ${
                            data.apellido_materno || ""
                        }`.trim(),
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

    $(".descuento-input").on("blur", function () {
        const input = $(this);
        const index = input.data("index");
        const codigo = input.val().trim();

        if (!codigo) return;

        input.prop("disabled", true);

        $.getJSON(route("descuentos.buscar") + `?codigo=${codigo}`)
            .done((res) => {
                if (res.error) {
                    Swal.fire("Atención", res.error, "warning");
                    input.val("");
                    return;
                }

                const asientoNumero = selectedSeatNumbers[index];
                const precioOriginal = parseFloat(seatPrices[asientoNumero]);

                let descuentoAplicado = 0;

                if (res.monto_efectivo) {
                    descuentoAplicado = res.monto_efectivo;
                } else if (res.porcentaje) {
                    descuentoAplicado = precioOriginal * (res.porcentaje / 100);
                }

                const nuevoPrecio = Math.max(
                    0,
                    precioOriginal - descuentoAplicado,
                );

                seatPrices[asientoNumero] = nuevoPrecio;

                actualizarCostoTotal();

                Swal.fire(
                    "Descuento aplicado",
                    `Descuento aplicado al asiento ${asientoNumero}`,
                    "success",
                );
            })
            .fail(() => {
                Swal.fire(
                    "Error",
                    "No se pudo conectar con el servidor",
                    "error",
                );
            })
            .always(() => {
                input.prop("disabled", false);
            });
    });

    $("#btnActualizarPasaje").on("click", function (e) {
        e.preventDefault();

        let form = document.getElementById("formVenta");
        let formData = new FormData(form);

        const pasajeId = $('input[name="pasaje_id"]').val();

        $('input[type="file"]').each(function () {
            if (this.files.length > 0)
                formData.append(this.name, this.files[0]);
        });

        $.ajax({
            url: route("pasajes.actualizar", pasajeId),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": csrf_token,
                "X-HTTP-Method-Override": "PUT",
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire("Éxito", res.message, "success").then(() => {
                        window.location.href = res.redirect;
                    });
                }
            },
            error: function () {
                Swal.fire("Error", "Error al actualizar el pasaje", "error");
            },
        });
    });
});
