document.addEventListener("DOMContentLoaded", function () {
    /* ─── Referencias DOM ─────────────────────────────────── */
    const svgContainer = document.getElementById("svg-container");
    const sellButton = document.getElementById("sell-button");
    const editButton = document.getElementById("edit-button");
    const resultadosInfo = document.getElementById("resultados-info");

    /* ─── Estado ──────────────────────────────────────────── */
    let selectedSeats = [];
    let currentHorarioId = null;
    let selectedReservedPasajeId = null;
    let puntosHorarios = {};

    actualizarContador(-1);
    attachRowEvents();

    function attachRowEvents() {
        document.querySelectorAll(".horario-row").forEach((row) => {
            row.addEventListener("click", function () {
                const horarioId = this.dataset.horarioId;
                const tipoViajeId = parseInt(this.dataset.tipoViajeId);
                currentHorarioId = horarioId;
                window.selectedHorario = horarioId;

                document
                    .querySelectorAll(".horario-row")
                    .forEach((r) => r.classList.remove("active"));
                this.classList.add("active");

                resetSeleccion();

                if (tipoViajeId === 2) {
                    manejarViajePorTramo(horarioId);
                } else {
                    document.getElementById("tramo_selector").style.display =
                        "none";
                    cargarAsientos(horarioId, null, null);
                }
            });
        });
    }

    function manejarViajePorTramo(horarioId) {
        const origenId = document.getElementById("filtro_origen").value;
        const destinoId = document.getElementById("filtro_destino").value;

        if (!origenId || !destinoId) {
            svgContainer.innerHTML = `
            <div class="no-results">
                <i class="bi bi-arrow-left-right"></i>
                Selecciona origen y destino
            </div>`;
            return;
        }

        cargarAsientos(horarioId, origenId, destinoId);
    }

    function cargarAsientos(horarioId, origenId, destinoId) {
        if (!horarioId) {
            console.warn("Horario no definido");
            return;
        }

        svgContainer.innerHTML = `<div style="text-align:center;padding:30px;">
        <div class="spinner-border spinner-border-sm text-primary"></div>
    </div>`;

        let url = route("pasajes.asientos", { horario: horarioId });

        if (origenId && destinoId) {
            url += `?origen_id=${origenId}&destino_id=${destinoId}`;
        }

        fetch(url)
            .then((res) => res.json())
            .then((data) => renderizarAsientos(data, horarioId));
    }

    function actualizarBadgeTramo(data) {
        const rowActiva = document.querySelector(
            `.horario-row[data-horario-id="${currentHorarioId}"]`,
        );
        if (!rowActiva) return;
        const badge = rowActiva.querySelector(
            ".seats-disponibles[data-pendiente]",
        );
        if (!badge) return;

        const libres = Object.values(data.asientos).filter(
            (e) => e === "libre",
        ).length;
        badge.textContent = `${libres} libre${libres !== 1 ? "s" : ""}`;
        badge.className = `seats-badge seats-disponibles ${libres > 5 ? "ok" : libres > 0 ? "low" : "full"}`;
        delete badge.dataset.pendiente;
    }

    function renderizarAsientos(data, horarioId) {
        actualizarBadgeTramo(data);
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
                    toggleSeatSelection(g, numero);
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

    /* ────────────────────────────────────────────────────────
     *  SELECCIÓN DE ASIENTOS
     * ──────────────────────────────────────────────────────── */
    function toggleSeatSelection(seatElement, numero) {
        const seatNum = parseInt(numero);

        // Si había un reservado seleccionado, deseleccionarlo
        if (selectedReservedPasajeId) {
            document
                .querySelectorAll(".selected-seat")
                .forEach((s) => s.classList.remove("selected-seat"));
            selectedReservedPasajeId = null;
        }

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

        actualizarBotones();
    }

    async function obtenerPasajeReservado(horarioId, numeroAsiento) {
        try {
            const seatElement = document.querySelector(
                `#seat-${numeroAsiento}`,
            );

            // Toggle: si ya estaba seleccionado, deseleccionar
            if (seatElement?.classList.contains("selected-seat")) {
                seatElement.classList.remove("selected-seat");
                selectedReservedPasajeId = null;
                actualizarBotones();
                return;
            }

            const res = await fetch(
                route("pasajes.buscar") +
                    `?horario_id=${horarioId}&asiento=${numeroAsiento}`,
            );
            const data = await res.json();

            if (data.success && data.pasaje_id) {
                selectedReservedPasajeId = data.pasaje_id;
                selectedSeats = [];

                document
                    .querySelectorAll(".selected-seat")
                    .forEach((s) => s.classList.remove("selected-seat"));
                seatElement?.classList.add("selected-seat");

                actualizarBotones();
            }
        } catch (err) {
            console.error("Error al obtener pasaje reservado:", err);
        }
    }

    function actualizarBotones() {
        if (selectedSeats.length > 0) {
            sellButton.style.display = "block";
            sellButton.textContent = `Vender ${selectedSeats.length} asiento${selectedSeats.length > 1 ? "s" : ""}`;
        } else {
            sellButton.style.display = "none";
        }

        editButton.style.display = selectedReservedPasajeId ? "block" : "none";
    }

    function resetSeleccion() {
        selectedSeats = [];
        selectedReservedPasajeId = null;
        actualizarBotones();
    }

    sellButton.addEventListener("click", async function () {
        if (!selectedSeats.length || !currentHorarioId) return;

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
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo verificar el estado de la caja.",
            });
        }
    });

    editButton.addEventListener("click", function () {
        if (!selectedReservedPasajeId) return;
        window.location.href = route("pasajes.editar", {
            pasaje: selectedReservedPasajeId,
        });
    });

    document
        .getElementById("filtro_origen")
        .addEventListener("change", filtrarHorarios);

    document
        .getElementById("filtro_destino")
        .addEventListener("change", filtrarHorarios);

    async function filtrarHorarios() {
        const fecha = document.getElementById("filtro_fecha").value;
        const origen = document.getElementById("filtro_origen").value;
        const destino = document.getElementById("filtro_destino").value;

        // Ocultar estado inicial
        const estadoInicial = document.getElementById("estado-inicial");
        if (estadoInicial) estadoInicial.style.display = "none";

        const rows = document.querySelectorAll(".horario-row");
        let visibles = 0;

        rows.forEach((row) => {
            const rowFecha = row.dataset.fecha;
            const rowOrigenId = row.dataset.origenId;
            const rowDestinoId = row.dataset.destinoId;

            const matchFecha = !fecha || rowFecha === fecha;
            const matchOrigen = !origen || rowOrigenId === origen;
            const matchDestino = !destino || rowDestinoId === destino;

            if (matchFecha && matchOrigen && matchDestino) {
                row.style.display = "flex";
                visibles++;
            } else {
                row.style.display = "none";
            }
        });

        actualizarContador(visibles);

        if (visibles === 0) {
            if (!document.getElementById("no-results-msg")) {
                const msg = document.createElement("div");
                msg.id = "no-results-msg";
                msg.className = "no-results";
                msg.innerHTML = `<i class="bi bi-search"></i> No hay horarios disponibles`;
                document.getElementById("horarios-list").appendChild(msg);
            }
        } else {
            const msg = document.getElementById("no-results-msg");
            if (msg) msg.remove();
        }
    }

    function renderizarHorarios(horarios) {
        const container = document.getElementById("horarios-list");
        container.innerHTML = "";

        if (!horarios.length) {
            container.innerHTML = `
        <div class="no-results">
            <i class="bi bi-search"></i>
            No hay horarios disponibles
        </div>`;
            return;
        }

        horarios.forEach((h) => {
            const html = `
        <div class="horario-row"
     data-horario-id="${h.id}"
     data-tipo-viaje-id="${h.tipo_viaje_id}"
     data-origen="${h.origen_id}"
     data-destino="${h.destino_id}">
     
            <div>${h.hora_salida}</div>
            <div>${h.tipo_vehiculo.nombre}</div>

        </div>`;

            container.innerHTML += html;
        });

        attachRowEvents();
    }

    function actualizarContador(total) {
        if (total < 0) {
            resultadosInfo.innerHTML = "";
            return;
        }
        resultadosInfo.innerHTML = `Mostrando <strong>${total}</strong> horario${total !== 1 ? "s" : ""}`;
    }

    // Listeners de filtros
    document
        .getElementById("filtro_fecha")
        .addEventListener("change", filtrarHorarios);
    document
        .getElementById("filtro_origen")
        .addEventListener("change", filtrarHorarios);

    document
        .getElementById("filtro_destino")
        .addEventListener("change", filtrarHorarios);

    ["filtro_origen", "filtro_destino"].forEach((id) => {
        document.getElementById(id).addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                filtrarHorarios();
                e.target.blur();
            }
        });
    });
});
