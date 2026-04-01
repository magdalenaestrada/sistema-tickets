document.addEventListener("DOMContentLoaded", function () {
    const svgContainer = document.getElementById("svg-container");
    const sellButton = document.getElementById("sell-button");
    const editButton = document.getElementById("edit-button");
    const resultadosInfo = document.getElementById("resultados-info");

    let selectedSeats = [];
    let currentSalidaId = null;
    let selectedReservedPasajeId = null;

    actualizarContador(-1);
    attachRowEvents();

    function attachRowEvents() {
        document.querySelectorAll(".horario-row").forEach((row) => {
            row.addEventListener("click", function () {
                const salidaId = this.dataset.salidaId;
                const tipoViajeId = parseInt(this.dataset.tipoViajeId);

                currentSalidaId = salidaId;

                document
                    .querySelectorAll(".horario-row")
                    .forEach((r) => r.classList.remove("active"));

                this.classList.add("active");
                resetSeleccion();

                if (tipoViajeId === 2) {
                    manejarViajePorTramo(salidaId);
                } else {
                    let puntos = [];

                    try {
                        puntos = JSON.parse(this.dataset.puntos || "[]");
                    } catch (e) {
                        puntos = [];
                    }

                    const primero = puntos[0];
                    const ultimo = puntos[puntos.length - 1];

                    cargarAsientos(
                        salidaId,
                        primero?.sucursal_id || null,
                        ultimo?.sucursal_id || null,
                    );
                }
            });
        });
    }

    function manejarViajePorTramo(salidaId) {
        const origenId = document.getElementById("filtro_origen").value;
        const destinoId = document.getElementById("filtro_destino").value;

        if (!origenId || !destinoId) {
            svgContainer.innerHTML = `
                <div class="no-results">
                    Selecciona origen y destino para ver asientos
                </div>
            `;
            return;
        }

        cargarAsientos(salidaId, origenId, destinoId);
    }

    function cargarAsientos(salidaId, origenId, destinoId) {
        if (!salidaId) return;

        svgContainer.innerHTML = `
            <div style="text-align:center;padding:30px;">
                <div class="spinner-border spinner-border-sm text-primary"></div>
            </div>
        `;

        let url = route("pasajes.asientos", { salida: salidaId });

        if (origenId && destinoId) {
            url += `?origen_id=${origenId}&destino_id=${destinoId}`;
        }

        fetch(url)
            .then((res) => res.json())
            .then((data) => renderizarAsientos(data, salidaId))
            .catch(() => {
                svgContainer.innerHTML = `<div class="no-results">No se pudieron cargar los asientos</div>`;
            });
    }

    function renderizarAsientos(data, salidaId) {
        svgContainer.innerHTML = data.svg;

        const svgEl = svgContainer.querySelector("svg");
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
            );
            seat.classList.add("seat");
            seat.classList.add(estado);
            seat.dataset.estado = estado;
            seat.dataset.numero = numero;

            if (estado === "libre") {
                seat.style.cursor = "pointer";
                seat.style.opacity = "1";
                seat.onclick = (e) => {
                    e.stopPropagation();
                    toggleSeatSelection(seat, numero);
                };
            } else if (estado === "reservado" || estado === "ocupado") {
                seat.style.cursor = "pointer";
                seat.style.opacity = "1";
                seat.onclick = (e) => {
                    e.stopPropagation();
                    obtenerPasajeAsiento(salidaId, numero);
                };
            } else {
                seat.style.cursor = "pointer";
                seat.style.opacity = "1";
                seat.onclick = (e) => {
                    e.stopPropagation();
                    obtenerPasajeAsiento(salidaId, numero);
                };
            }
        });
    }

    function toggleSeatSelection(seatElement, numero) {
        const seatNum = parseInt(numero);

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

    async function obtenerPasajeAsiento(salidaId, numeroAsiento) {
        try {
            const seatElement = document.querySelector(
                `#seat-${numeroAsiento}`,
            );

            if (seatElement?.classList.contains("selected-seat")) {
                seatElement.classList.remove("selected-seat");
                selectedReservedPasajeId = null;
                actualizarBotones();
                return;
            }

            const res = await fetch(
                route("pasajes.buscar") +
                    `?salida_id=${salidaId}&asiento=${numeroAsiento}`,
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
            console.error(err);
            Swal.fire(
                "Error",
                "No se pudo obtener el pasaje del asiento",
                "error",
            );
        }
    }

    async function obtenerPasajeReservado(salidaId, numeroAsiento) {
        try {
            const seatElement = document.querySelector(
                `#seat-${numeroAsiento}`,
            );

            if (seatElement?.classList.contains("selected-seat")) {
                seatElement.classList.remove("selected-seat");
                selectedReservedPasajeId = null;
                actualizarBotones();
                return;
            }

            const res = await fetch(
                route("pasajes.buscar") +
                    `?salida_id=${salidaId}&asiento=${numeroAsiento}`,
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
            console.error(err);
        }
    }

    function actualizarBotones() {
        if (selectedSeats.length > 0) {
            sellButton.style.display = "block";
            sellButton.textContent = `Vender ${selectedSeats.length} asiento${selectedSeats.length > 1 ? "s" : ""}`;
        } else {
            sellButton.style.display = "none";
        }

        if (selectedReservedPasajeId) {
            editButton.style.display = "block";
            editButton.textContent = "Editar pasaje";
        } else {
            editButton.style.display = "none";
        }
    }

    function resetSeleccion() {
        selectedSeats = [];
        selectedReservedPasajeId = null;
        actualizarBotones();
    }

    sellButton.addEventListener("click", function () {
        if (!selectedSeats.length || !currentSalidaId) return;

        const seats = selectedSeats.sort((a, b) => a - b).join(",");
        const origenId = document.getElementById("filtro_origen").value;
        const destinoId = document.getElementById("filtro_destino").value;

        window.location.href = route("pasajes.vender", {
            salida: currentSalidaId,
            asientos: seats,
            origen_id: origenId,
            destino_id: destinoId,
        });
    });

    editButton.addEventListener("click", function () {
        if (!selectedReservedPasajeId) return;

        window.location.href = route("pasajes.editar", {
            pasaje: selectedReservedPasajeId,
        });
    });

    function filtrarSalidas() {
        const fecha = document.getElementById("filtro_fecha").value;
        const origen = document.getElementById("filtro_origen").value;
        const destino = document.getElementById("filtro_destino").value;

        const selectOrigen = document.getElementById("filtro_origen");
        const selectDestino = document.getElementById("filtro_destino");

        const nombreOrigen =
            selectOrigen.options[selectOrigen.selectedIndex]?.text?.trim() ||
            "";
        const nombreDestino =
            selectDestino.options[selectDestino.selectedIndex]?.text?.trim() ||
            "";

        if (!fecha || !origen || !destino) {
            document.querySelectorAll(".horario-row").forEach((row) => {
                row.style.display = "none";

                const label = row.querySelector(".hr-route-label");
                const origenOriginal = row.dataset.origenNombre || "";
                const destinoOriginal = row.dataset.destinoNombre || "";

                if (label && origenOriginal && destinoOriginal) {
                    label.textContent = `${origenOriginal} → ${destinoOriginal}`;
                }
            });

            document.getElementById("estado-inicial").style.display = "block";
            document.getElementById("resultados-info").textContent = "";
            return;
        }

        const estadoInicial = document.getElementById("estado-inicial");
        const rows = document.querySelectorAll(".horario-row");

        let visibles = 0;

        rows.forEach((row) => {
            const rowFecha = row.dataset.fecha || "";
            const tipoViajeId = parseInt(row.dataset.tipoViajeId || "0");

            let puntos = [];
            try {
                puntos = JSON.parse(row.dataset.puntos || "[]");
            } catch (e) {
                puntos = [];
            }

            const puntoOrigen = puntos.find(
                (p) => String(p.sucursal_id) === String(origen),
            );

            const puntoDestino = puntos.find(
                (p) => String(p.sucursal_id) === String(destino),
            );

            const matchFecha = rowFecha === fecha;
            const matchOrigen = !!puntoOrigen;
            const matchDestino = !!puntoDestino;

            let matchOrden = false;
            if (puntoOrigen && puntoDestino) {
                matchOrden =
                    Number(puntoOrigen.orden) < Number(puntoDestino.orden);
            }

            let visible =
                matchFecha && matchOrigen && matchDestino && matchOrden;

            if (visible && tipoViajeId !== 2) {
                const primero = puntos[0];
                const ultimo = puntos[puntos.length - 1];

                const coincideExacto =
                    primero &&
                    ultimo &&
                    String(primero.sucursal_id) === String(origen) &&
                    String(ultimo.sucursal_id) === String(destino);

                visible = coincideExacto;
            }

            const label = row.querySelector(".hr-route-label");
            const origenOriginal = row.dataset.origenNombre || "";
            const destinoOriginal = row.dataset.destinoNombre || "";

            if (visible) {
                row.style.display = "flex";
                visibles++;

                if (label) {
                    label.textContent = `${nombreOrigen} → ${nombreDestino}`;
                }
            } else {
                row.style.display = "none";

                if (label && origenOriginal && destinoOriginal) {
                    label.textContent = `${origenOriginal} → ${destinoOriginal}`;
                }
            }
        });

        actualizarContador(visibles);

        if (estadoInicial) {
            estadoInicial.style.display = visibles === 0 ? "block" : "none";
            if (visibles === 0) {
                estadoInicial.innerHTML =
                    "No hay salidas disponibles con esos filtros";
            }
        }

        if (visibles === 0) {
            svgContainer.innerHTML = `<div class="no-results">No hay salidas disponibles</div>`;
        }
    }

    function actualizarContador(total) {
        if (total < 0) {
            resultadosInfo.innerHTML = "";
            return;
        }

        resultadosInfo.innerHTML = `Mostrando <strong>${total}</strong> salida${total !== 1 ? "s" : ""}`;
    }

    ["filtro_fecha", "filtro_origen", "filtro_destino"].forEach((id) => {
        document.getElementById(id)?.addEventListener("change", filtrarSalidas);
    });
});
