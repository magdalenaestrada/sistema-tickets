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

    window.abrirCambioHorario = function (index, asiento, horarioId) {
        pasajeCambioIndex = index;
        asientoNuevo = null;
        horarioNuevoId = null;

        $("#listaHorariosCambio").html("");
        $("#contenedorAsientosCambio").addClass("d-none");
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

                res.forEach((h) => {
                    const capacidad = h.tipo_vehiculo.capacidad;
                    const vendidos = h.pasajes_count;
                    const disponibles = capacidad - vendidos;

                    html += `
<div class="col-md-6 mb-4">
    <!-- TARJETA HORARIO -->
    <div class="card horario-card mb-2"
         onclick="seleccionarHorarioCambio(${h.id})"
         style="cursor:pointer">
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

    <!-- TARJETA SVG -->
    <div class="card d-none" id="svg-card-${h.id}">
        <div class="card-body p-2">
            <div id="svg-bus-${h.id}" class="text-center"></div>
        </div>
    </div>
</div>`;
                });

                $("#listaHorariosCambio").html(html);
            }
        ).fail(function () {
            Swal.fire("Error", "No se pudieron buscar horarios", "error");
        });
    };

    $("#btnBuscarCambio").on("click", function () {
        buscarHorariosCambio();
    });

    window.seleccionarHorarioCambio = function (horarioId) {
        horarioNuevoId = horarioId;
        asientoNuevo = null;
        $("[id^='svg-card-']").addClass("d-none");
        $.get(route("pasajes.horario.asientos", horarioId), function (res) {
            const contenedor = $(`#svg-bus-${horarioId}`);
            contenedor.html(res.svg);

            $(`#svg-card-${horarioId}`).removeClass("d-none");

            setTimeout(() => {
                pintarAsientos(res.asientos || []);

                agregarEventListenersAsientos();

                $("#leyendaAsientos").slideDown();
            }, 100);
        }).fail(function () {
            Swal.fire("Error", "No se pudieron cargar los asientos", "error");
        });
    };

    function pintarAsientos(asientos) {
        Object.entries(asientos).forEach(([numero, estado]) => {
            const seat = document.getElementById(`seat-${numero}`);
            if (!seat) {
                console.warn(`Asiento seat-${numero} no encontrado en el SVG`);
                return;
            }

            const shape = seat.querySelector("path, rect, polygon, circle");

            if (!shape) {
                console.warn(`Shape no encontrado para asiento ${numero}`);
                return;
            }

            seat.setAttribute("data-estado", estado);

            if (estado === "ocupado") {
                shape.setAttribute("fill", "red");
                seat.style.cursor = "not-allowed";
            } else if (estado === "reservado") {
                shape.setAttribute("fill", "orange");
                seat.style.cursor = "not-allowed";
            } else {
                shape.setAttribute("fill", "#d3d3d3");
                seat.style.cursor = "pointer";
            }
        });
    }

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

        console.log(
            `Event listeners agregados a ${seats.length} asientos (sin clonar)`
        );
    }

    window.seleccionarAsientoCambio = function (asiento) {
        const seat = document.getElementById(`seat-${asiento}`);
        if (!seat) {
            console.error(`Asiento seat-${asiento} no encontrado`);
            return;
        }

        const shape = seat.querySelector("path, rect, polygon, circle");
        if (!shape) {
            console.error(`Shape no encontrado para asiento ${asiento}`);
            return;
        }

        const color = shape.getAttribute("fill");

        if (color === "red" || color === "orange") {
            Swal.fire({
                icon: "warning",
                title: "Asiento no disponible",
                text: "Este asiento ya está ocupado o reservado",
                timer: 2000,
                showConfirmButton: false,
            });
            return;
        }

        document.querySelectorAll(".seat").forEach((s) => {
            s.classList.remove("selected");
            const prevShape = s.querySelector("path, rect, polygon, circle");
            if (prevShape && prevShape.getAttribute("fill") === "#0d6efd") {
                prevShape.setAttribute("fill", "#d3d3d3");
            }
        });

        asientoNuevo = asiento;
        seat.classList.add("selected");
        shape.setAttribute("fill", "#0d6efd");

        console.log(`Asiento ${asiento} seleccionado`);

        Swal.fire({
            icon: "success",
            title: `Asiento ${asiento} seleccionado`,
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
                    precioOriginal - descuentoAplicado
                );

                seatPrices[asientoNumero] = nuevoPrecio;

                actualizarCostoTotal();

                Swal.fire(
                    "Descuento aplicado",
                    `Descuento aplicado al asiento ${asientoNumero}`,
                    "success"
                );
            })
            .fail(() => {
                Swal.fire(
                    "Error",
                    "No se pudo conectar con el servidor",
                    "error"
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
