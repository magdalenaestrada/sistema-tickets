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

    const todasLasRows = Array.from(
        document.querySelectorAll(".horario-row"),
    ).map((row) => ({
        element: row,
        fecha: row.dataset.fecha,
        origen: row.dataset.origen,
        destino: row.dataset.destino,
        origenId: row.dataset.origenId,
        destinoId: row.dataset.destinoId,
        horarioId: row.dataset.horarioId,
        tipoViajeId: row.dataset.tipoViajeId,
        disponibles: parseInt(row.dataset.disponibles),
    }));

    cargarPuntosHorarios();
    actualizarContador(-1);
    attachRowEvents();

    async function cargarPuntosHorarios() {
        const horariosIds = todasLasRows.map((r) => r.horarioId);

        const res = await fetch(route("horarios.puntos.lote"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify({ horarios: horariosIds }),
        });

        puntosHorarios = await res.json();
    }

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
        const puntos = puntosHorarios[horarioId];

        const origenSelect = document.getElementById("filtro_origen");
        const destinoSelect = document.getElementById("filtro_destino");

        origenSelect.innerHTML = '<option value="">Seleccionar origen</option>';
        destinoSelect.innerHTML =
            '<option value="">Seleccionar destino</option>';

        if (!puntos || puntos.length === 0) return;

        puntos.forEach((punto) => {
            origenSelect.innerHTML += `
            <option value="${punto.id}">${punto.nombre}</option>
        `;
        });
    }

    function buscarIdsDeTramo(horarioId, textoOrigen, textoDestino) {
        fetch(route("horario.puntos.index", horarioId))
            .then((res) => res.json())
            .then((puntos) => {
                const pOrigen = puntos.find((p) =>
                    p.sucursal.nombre_comercial
                        .toLowerCase()
                        .includes(textoOrigen),
                );
                const pDestino = puntos.find((p) =>
                    p.sucursal.nombre_comercial
                        .toLowerCase()
                        .includes(textoDestino),
                );

                if (!pOrigen || !pDestino) {
                    svgContainer.innerHTML = `
                        <div class="alert alert-warning" style="font-size:.83rem;">
                            No se encontró el tramo <strong>${textoOrigen}</strong> →
                            <strong>${textoDestino}</strong> en este horario.
                        </div>`;
                    return;
                }

                cargarAsientos(
                    horarioId,
                    pOrigen.sucursal_id,
                    pDestino.sucursal_id,
                );
            })
            .catch((err) => console.error("Error buscando tramo:", err));
    }

    /* ────────────────────────────────────────────────────────
     *  CARGA DE ASIENTOS
     * ──────────────────────────────────────────────────────── */
    function cargarAsientos(horarioId, origenId, destinoId) {
        svgContainer.innerHTML = `<div style="text-align:center;padding:30px;">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>`;

        let url = route("pasajes.asientos", { horario: horarioId });
        if (origenId && destinoId) {
            url += `?origen_id=${origenId}&destino_id=${destinoId}`;
        }

        fetch(url)
            .then((res) => res.json())
            .then((data) => {
                if (data.error) {
                    svgContainer.innerHTML = `<div class="alert alert-danger" style="font-size:.83rem;">${data.error}</div>`;
                    return;
                }
                renderizarAsientos(data, horarioId);
            })
            .catch((err) => console.error("Error cargando asientos:", err));
    }

    /* ────────────────────────────────────────────────────────
     *  RENDER DE ASIENTOS
     * ──────────────────────────────────────────────────────── */
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
        .addEventListener("change", function () {
            const origenId = parseInt(this.value);
            const puntos = puntosHorarios[currentHorarioId];

            const destinoSelect = document.getElementById("filtro_destino");

            destinoSelect.innerHTML =
                '<option value="">Seleccionar destino</option>';

            if (!origenId) return;

            const indexOrigen = puntos.findIndex((p) => p.id == origenId);

            puntos.forEach((p, index) => {
                if (index > indexOrigen) {
                    destinoSelect.innerHTML += `
                <option value="${p.id}">${p.nombre}</option>
            `;
                }
            });
        });

    document
        .getElementById("filtro_destino")
        .addEventListener("change", function () {
            const origenId = document.getElementById("filtro_origen").value;
            const destinoId = this.value;

            if (!origenId || !destinoId) return;

            cargarAsientos(currentHorarioId, origenId, destinoId);
        });

    async function filtrarHorarios() {
        const fecha = document.getElementById("filtro_fecha").value;
        const origen = document
            .getElementById("filtro_origen")
            .value.toLowerCase()
            .trim();
        const destino = document
            .getElementById("filtro_destino")
            .value.toLowerCase()
            .trim();

        if (!fecha && !origen && !destino) {
            todasLasRows.forEach((row) => (row.element.style.display = "none"));
            document.getElementById("estado-inicial").style.display = "";
            resetSeleccion();
            svgContainer.innerHTML = `<div class="no-results"><i class="bi bi-cursor"></i>Selecciona un horario para ver los asientos</div>`;
            currentHorarioId = null;
            actualizarContador(-1);
            return;
        }

        document.getElementById("estado-inicial").style.display = "none";
        resetSeleccion();

        let visibles = 0;

        for (const row of todasLasRows) {
            const okFecha = !fecha || row.fecha === fecha;

            const okTramo = validarTramoLocal(row, origen, destino);

            const mostrar = okFecha && okTramo;

            row.element.style.display = mostrar ? "" : "none";

            if (mostrar) visibles++;
        }

        if (visibles === 0) {
            if (!document.getElementById("sin-resultados")) {
                const msg = document.createElement("div");
                msg.id = "sin-resultados";
                msg.className = "no-results";
                msg.innerHTML = `<i class="bi bi-search"></i>No hay horarios que coincidan`;
                document.getElementById("horarios-list").appendChild(msg);
            }
        } else {
            document.getElementById("sin-resultados")?.remove();
        }

        attachRowEvents();
        actualizarContador(visibles);
    }

    function validarTramoLocal(row, origen, destino) {
        if (!origen || !destino) return true;

        const puntos = puntosHorarios[row.horarioId];

        if (!puntos) return false;

        const indexOrigen = puntos.indexOf(origen);
        const indexDestino = puntos.indexOf(destino);

        return indexOrigen >= 0 && indexDestino > indexOrigen;
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
        .addEventListener("blur", filtrarHorarios);
    document
        .getElementById("filtro_destino")
        .addEventListener("blur", filtrarHorarios);

    ["filtro_origen", "filtro_destino"].forEach((id) => {
        document.getElementById(id).addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                filtrarHorarios();
                e.target.blur();
            }
        });
    });
});
