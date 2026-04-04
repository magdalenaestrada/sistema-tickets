const btnAbordo = document.getElementById("btnAbordo");
const btnNoAbordo = document.getElementById("btnNoAbordo");

$(function () {
    const csrf = $('meta[name="csrf-token"]').attr("content");

    $("#pasajero_menor").on("change", function () {
        if ($(this).is(":checked")) {
            $("#contenedorAutorizacion").slideDown();
        } else {
            $("#contenedorAutorizacion").slideUp();
        }
    });

    $("#btnGuardarDatosPasaje").on("click", function () {
        const form = document.getElementById("formEditarPasaje");
        const formData = new FormData(form);

        $.ajax({
            url: route("pasajes.actualizar", { pasaje: PASAJE_ID }),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": csrf,
                "X-HTTP-Method-Override": "PUT",
            },
            success: function (res) {
                Swal.fire("Correcto", res.message, "success");
            },
            error: function (xhr) {
                Swal.fire(
                    "Error",
                    xhr.responseJSON?.message ||
                        "No se pudo actualizar el pasaje",
                    "error",
                );
            },
        });
    });

    if (btnAbordo) {
        btnAbordo.addEventListener("click", function () {
            Swal.fire({
                title: "¿Marcar como abordó?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí",
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(route("pasajes.abordo", { pasaje: PASAJE_ID }), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                })
                    .then((r) => r.json())
                    .then((data) => {
                        Swal.fire("OK", data.message, "success").then(() =>
                            window.location.route("pasajes.listar"),
                        );
                    });
            });
        });
    }

    if (btnNoAbordo) {
        btnNoAbordo.addEventListener("click", function () {
            Swal.fire({
                title: "¿Marcar como no abordó?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí",
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(route("pasajes.noAbordo", { pasaje: PASAJE_ID }), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                })
                    .then((r) => r.json())
                    .then((data) => {
                        Swal.fire("OK", data.message, "success").then(() =>
                            location.reload(),
                        );
                    });
            });
        });
    }

    $("#btnCambiarViaje").on("click", function () {
        if (!$("#nueva_salida_id").val()) {
            Swal.fire("Atención", "Selecciona una salida", "warning");
            return;
        }

        if (!$("#nuevo_asiento_numero").val()) {
            Swal.fire(
                "Atención",
                "Selecciona un nuevo asiento en el mapa",
                "warning",
            );
            return;
        }

        $.ajax({
            url: route("pasajes.actualizar_horario", { pasaje: PASAJE_ID }),
            type: "POST",
            data: {
                _token: csrf,
                _method: "PUT",
                nueva_salida_id: $("#nueva_salida_id").val(),
                nuevo_asiento_numero: $("#nuevo_asiento_numero").val(),
                origen_id: $("#origen_id").val(),
                destino_id: $("#destino_id").val(),
                descuento_id: descuentoCambio.descuento_id,
                descuento_monto: descuentoCambio.monto,
            },
            success: function (res) {
                Swal.fire("Correcto", res.message, "success").then(() =>
                    window.location.reload(),
                );
            },
            error: function (xhr) {
                Swal.fire(
                    "Error",
                    xhr.responseJSON?.message || "No se pudo cambiar el viaje",
                    "error",
                );
            },
        });
    });

    $("#btnGuardarVenta").on("click", function () {
        $.ajax({
            url: route("pasajes.actualizar_venta", { pasaje: PASAJE_ID }),
            type: "POST",
            data: {
                _token: csrf,
                _method: "PUT",
                tipo_documento_factura_id: $(
                    "#formEditarVenta [name='tipo_documento_factura_id']",
                ).val(),
                numero_documento_id: $(
                    "#formEditarVenta [name='numero_documento_id']",
                ).val(),
                razon_social: $("#formEditarVenta [name='razon_social']").val(),
            },
            success: function (res) {
                Swal.fire("Correcto", res.message, "success");
            },
            error: function (xhr) {
                Swal.fire(
                    "Error",
                    xhr.responseJSON?.message ||
                        "No se pudo actualizar la venta",
                    "error",
                );
            },
        });
    });

    $("#btnCancelarPasaje").on("click", function () {
        Swal.fire({
            title: "¿Cancelar pasaje?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cancelar",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post(route("pasajes.cancelar", { pasaje: PASAJE_ID }), {
                _token: csrf,
            })
                .done(function (res) {
                    Swal.fire("Correcto", res.message, "success").then(() =>
                        window.location.reload(),
                    );
                })
                .fail(function (xhr) {
                    Swal.fire(
                        "Error",
                        xhr.responseJSON?.message || "No se pudo cancelar",
                        "error",
                    );
                });
        });
    });

    function cargarAsientosCambio() {
        const salidaId = $("#nueva_salida_id").val();
        const origenId = $("#origen_id").val();
        const destinoId = $("#destino_id").val();

        if (!salidaId || !origenId || !destinoId) {
            $("#svgContainerCambio").html(
                '<div class="text-muted">Selecciona salida, origen y destino</div>',
            );
            return;
        }

        $("#svgContainerCambio").html(`
        <div class="text-center p-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
        </div>
    `);

        let url =
            route("pasajes.asientos", { salida: salidaId }) +
            `?origen_id=${origenId}&destino_id=${destinoId}`;

        fetch(url)
            .then((res) => res.json())
            .then((data) => {
                $("#svgContainerCambio").html(data.svg);

                const svgEl = document.querySelector("#svgContainerCambio svg");
                if (!svgEl) return;

                Object.keys(data.asientos).forEach((numero) => {
                    const estado = data.asientos[numero];
                    const seat = svgEl.querySelector(`#seat-${numero}`);
                    if (!seat) return;

                    seat.classList.remove(
                        "ocupado",
                        "reservado",
                        "libre",
                        "selected-seat",
                        "seat-actual",
                    );
                    seat.classList.add("seat");

                    if (parseInt(numero) === parseInt(asientoActual)) {
                        seat.classList.add("seat-actual");
                        seat.style.cursor = "pointer";
                        seat.style.opacity = "1";
                    } else {
                        seat.classList.add(estado);
                        seat.style.cursor = "pointer";
                        seat.style.opacity = "1";
                    }

                    seat.onclick = (e) => {
                        e.stopPropagation();

                        document
                            .querySelectorAll(
                                "#svgContainerCambio .selected-seat",
                            )
                            .forEach((s) =>
                                s.classList.remove("selected-seat"),
                            );

                        seat.classList.add("selected-seat");
                        nuevoAsientoSeleccionado = numero;
                        $("#nuevo_asiento_numero").val(numero);
                        $("#nuevo_asiento_texto").val(numero);
                    };
                });
            })
            .catch(() => {
                $("#svgContainerCambio").html(
                    '<div class="text-danger">No se pudieron cargar los asientos</div>',
                );
            });
    }

    $("#nueva_salida_id, #origen_id, #destino_id").on("change", function () {
        cargarAsientosCambio();
    });

    enlazarBotonesAccion();
});
