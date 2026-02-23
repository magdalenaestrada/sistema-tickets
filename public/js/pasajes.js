document.addEventListener("DOMContentLoaded", function () {
    const horarioCards = document.querySelectorAll(".horario-card");
    const svgContainer = document.getElementById("svg-container");
    const sellButton = document.getElementById("sell-button");
    const editButton = document.getElementById("edit-button");

    let selectedSeats = [];
    let currentHorarioId = null;
    let selectedReservedPasajeId = null;
    let todasLasCards = []; // Guardar todas las cards originales

    horarioCards.forEach((card) => {
        todasLasCards.push({
            element: card.parentElement.cloneNode(true),
            fecha: card.dataset.fecha,
            origen: card.dataset.origen,
            destino: card.dataset.destino,
            horarioId: card.dataset.horarioId,
            tipoViajeId: card.dataset.tipoViajeId, // ← agregar
        });
    });

    function attachCardEvents() {
        document.querySelectorAll(".horario-card").forEach((card) => {
            card.addEventListener("click", function () {
                console.log("tipoViajeId:", this.dataset.tipoViajeId);
                console.log("horarioId:", this.dataset.horarioId);
                const horarioId = this.dataset.horarioId;
                const tipoViajeId = this.dataset.tipoViajeId; // ← necesitas agregar esto al HTML
                currentHorarioId = horarioId;
                window.selectedHorario = horarioId;

                document
                    .querySelectorAll(".horario-card")
                    .forEach((c) => c.classList.remove("active"));
                this.classList.add("active");
                selectedSeats = [];
                selectedReservedPasajeId = null;
                updateSellButton();
                updateEditButton();

                if (parseInt(tipoViajeId) === 2) {
                    const origenId =
                        document.getElementById("sel_origen_tramo")?.value;
                    const destinoId =
                        document.getElementById("sel_destino_tramo")?.value;

                    if (!origenId || !destinoId) {
                        svgContainer.innerHTML = `
                <div class="alert alert-info">
                    Selecciona el tramo (origen y destino) para ver disponibilidad.
                </div>
            `;
                        document.getElementById(
                            "tramo_selector",
                        ).style.display = "block";
                        document.getElementById(
                            "tramo_selector",
                        ).dataset.horarioId = horarioId;

                        cargarAsientosConTramo(horarioId);
                    }
                } else {
                    document.getElementById("tramo_selector").style.display =
                        "none";
                    cargarAsientos(horarioId, null, null);
                }
            });
        });
    }

    attachCardEvents();

    function cargarAsientos(horarioId, origenId, destinoId) {
        let url = route("pasajes.asientos", { horario: horarioId });
        if (origenId && destinoId) {
            url += `?origen_id=${origenId}&destino_id=${destinoId}`;
        }

        console.log("Cargando asientos URL:", url); // ← ver qué URL se genera

        fetch(url)
            .then((res) => res.json())
            .then((data) => {
                if (data.error) {
                    svgContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    return;
                }
                renderizarAsientos(data, horarioId);
            })

            .catch((err) => console.error("Error:", err));
    }

    function cargarAsientosConTramo(horarioId, origenId, destinoId) {
        console.log("URL:", route("horario.puntos.index", horarioId));
        fetch(route("horario.puntos.index", horarioId))
            .then((res) => res.json())
            .then((puntos) => {
                console.log(puntos);
                const selOrigen = document.getElementById("sel_origen_tramo");
                const selDestino = document.getElementById("sel_destino_tramo");

                selOrigen.innerHTML = '<option value="">Origen</option>';
                selDestino.innerHTML = '<option value="">Destino</option>';

                puntos.forEach((p) => {
                    selOrigen.innerHTML += `<option value="${p.sucursal_id}">${p.sucursal.nombre_comercial}</option>`;
                    selDestino.innerHTML += `<option value="${p.sucursal_id}">${p.sucursal.nombre_comercial}</option>`;
                });

                document.getElementById("tramo_selector").style.display =
                    "block";
                document.getElementById("tramo_selector").dataset.horarioId =
                    horarioId;
            });
    }

    function renderizarAsientos(data, horarioId) {
        svgContainer.innerHTML = data.svg;

        const svgEl = svgContainer.querySelector("svg");
        if (!svgEl) return;

        Object.keys(data.asientos).forEach((numero) => {
            const estado = data.asientos[numero];
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
                g.onclick = (e) => {
                    e.stopPropagation();
                    toggleSeatSelection(g, numero, "libre");
                };
            } else if (estado === "reservado") {
                g.style.cursor = "pointer";
                g.style.opacity = "1";
                g.onclick = (e) => {
                    e.stopPropagation();
                    obtenerPasajeReservado(horarioId, numero);
                };
            } else {
                g.style.cursor = "not-allowed";
                g.style.opacity = "0.6";
                g.onclick = (e) => {
                    e.stopPropagation();
                    alert("Este asiento está vendido");
                };
            }
        });
    }

    document
        .getElementById("btn_cargar_tramo")
        ?.addEventListener("click", function () {
            const horarioId =
                document.getElementById("tramo_selector").dataset.horarioId;
            const origenId = document.getElementById("sel_origen_tramo").value;
            const destinoId =
                document.getElementById("sel_destino_tramo").value;

            console.log("horarioId:", horarioId);
            console.log("origenId:", origenId);
            console.log("destinoId:", destinoId);

            if (!origenId || !destinoId) {
                alert("Selecciona origen y destino del tramo");
                return;
            }

            if (origenId === destinoId) {
                alert("Origen y destino no pueden ser iguales");
                return;
            }

            cargarAsientos(horarioId, origenId, destinoId);
        });

    function toggleSeatSelection(seatElement, numero, tipo) {
        const seatNum = parseInt(numero);

        if (tipo === "libre") {
            const isSelected = selectedSeats.includes(seatNum);

            if (isSelected) {
                selectedSeats = selectedSeats.filter((s) => s !== seatNum);
                seatElement.classList.remove("selected-seat");
                seatElement.classList.add("libre");
            } else {
                selectedSeats.push(seatNum);
                seatElement.classList.remove("libre");
                seatElement.classList.add("selected-seat");
            }

            // Deseleccionar cualquier asiento reservado seleccionado
            if (selectedReservedPasajeId) {
                document
                    .querySelectorAll(".reservado.selected-seat")
                    .forEach((seat) => {
                        seat.classList.remove("selected-seat");
                    });
                selectedReservedPasajeId = null;
            }
        }

        updateSellButton();
        updateEditButton();
    }

    async function obtenerPasajeReservado(horarioId, numeroAsiento) {
        try {
            const seatElement = document.querySelector(
                `#seat-${numeroAsiento}`,
            );
            if (
                seatElement &&
                seatElement.classList.contains("selected-seat")
            ) {
                seatElement.classList.remove("selected-seat");
                selectedReservedPasajeId = null;
                updateSellButton();
                updateEditButton();
                return;
            }

            console.log("horarioId:", horarioId);

            const response = await fetch(
                route("pasajes.asientos", { horario: horarioId }),
            );
            const data = await response.json();

            const pasajeResponse = await fetch(
                route("pasajes.buscar") +
                    `?horario_id=${horarioId}&asiento=${numeroAsiento}`,
            );
            const pasajeData = await pasajeResponse.json();

            if (pasajeData.success && pasajeData.pasaje_id) {
                selectedReservedPasajeId = pasajeData.pasaje_id;

                // Deseleccionar todos los asientos libres
                selectedSeats = [];
                document.querySelectorAll(".selected-seat").forEach((seat) => {
                    seat.classList.remove("selected-seat");
                });

                // Seleccionar solo el asiento reservado actual
                if (seatElement) {
                    seatElement.classList.add("selected-seat");
                }

                updateSellButton();
                updateEditButton();
            }
        } catch (error) {
            console.error("Error al obtener pasaje reservado:", error);
        }
    }

    function updateSellButton() {
        if (selectedSeats.length > 0) {
            sellButton.style.display = "block";
            sellButton.textContent = `Vender ${selectedSeats.length} asiento(s)`;
        } else {
            sellButton.style.display = "none";
        }
    }

    function updateEditButton() {
        if (selectedReservedPasajeId) {
            editButton.style.display = "block";
            editButton.textContent = "Editar reserva";
        } else {
            editButton.style.display = "none";
        }
    }

    sellButton.addEventListener("click", async function () {
        if (selectedSeats.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No hay asientos seleccionados",
                text: "Selecciona al menos un asiento antes de continuar.",
                confirmButtonText: "Entendido",
            });
            return;
        }

        if (!currentHorarioId) {
            Swal.fire({
                icon: "warning",
                title: "No hay horario seleccionado",
                text: "Selecciona un horario antes de continuar.",
                confirmButtonText: "Entendido",
            });
            return;
        }

        try {
            const res = await fetch(route("caja.verificar"));
            const data = await res.json();

            if (!data.abierta) {
                Swal.fire({
                    icon: "warning",
                    title: "Caja no abierta",
                    text: "Aún no has abierto caja. No puedes vender pasajes.",
                    confirmButtonText: "Entendido",
                });
                return;
            }
            const seats = selectedSeats.sort((a, b) => a - b).join(",");
            window.location.href = route("pasajes.vender", {
                asientos: seats,
                horario: currentHorarioId,
            });
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo verificar el estado de la caja.",
                confirmButtonText: "Entendido",
            });
        }
    });

    editButton.addEventListener("click", function () {
        if (!selectedReservedPasajeId) {
            Swal.fire({
                icon: "warning",
                title: "No hay asiento seleccionado",
                text: "Selecciona un asiento reservado para editar.",
                confirmButtonText: "Entendido",
            });
            return;
        }

        window.location.href = route("pasajes.editar", {
            pasaje: selectedReservedPasajeId,
        });
    });

    function filtrarHorarios() {
        let fecha = $("#filtro_fecha").val();
        let origen = $("#filtro_origen").val().toLowerCase().trim();
        let destino = $("#filtro_destino").val().toLowerCase().trim();

        $(".row .col-md-6.mb-3").remove();
        $(".row .col-md-12").remove();

        svgContainer.innerHTML =
            "<p>Seleccione un horario para ver los asientos.</p>";
        selectedSeats = [];
        currentHorarioId = null;
        selectedReservedPasajeId = null;
        updateSellButton();
        updateEditButton();

        let contenedor = $(".col-md-8 .row").first();

        let cardsFiltradas = todasLasCards.filter((card) => {
            let coincideFecha = !fecha || card.fecha === fecha;
            let coincideOrigen = !origen || card.origen.includes(origen);
            let coincideDestino = !destino || card.destino.includes(destino);

            return coincideFecha && coincideOrigen && coincideDestino;
        });

        if (cardsFiltradas.length === 0) {
            contenedor.append(`
                <div class="col-md-12 mb-3">
                    <p class="text-center text-muted">No hay horarios disponibles.</p>
                </div>
            `);
        } else {
            cardsFiltradas.forEach((card) => {
                contenedor.append(card.element.cloneNode(true));
            });
            attachCardEvents();
        }
    }

    $("#filtro_fecha, #filtro_origen, #filtro_destino").on("blur", function () {
        filtrarHorarios();
    });

    $("#filtro_origen, #filtro_destino").on("keypress", function (e) {
        if (e.which === 13) {
            filtrarHorarios();
            $(this).blur();
        }
    });

    $("#filtro_fecha").on("change", function () {
        filtrarHorarios();
    });
});
