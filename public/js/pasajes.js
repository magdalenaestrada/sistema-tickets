document.addEventListener("DOMContentLoaded", function () {
    const svgContainer = document.getElementById("svg-container");
    const sellButton = document.getElementById("sell-button");
    const editButton = document.getElementById("edit-button");
    const resultadosInfo = document.getElementById("resultados-info");
    const estadoInicial = document.getElementById("estado-inicial");

    const pueblitoDefaultId = window.VENTA_CONFIG?.pueblitoSucursalId || null;

    let selectedSeats = [];
    let currentSalidaId = null;
    let selectedReservedPasajeId = null;
    let currentOrigenId = null;
    let currentDestinoId = null;

    actualizarContador(-1);
    attachRowEvents();
    aplicarEstadoPorDefecto();

    function aplicarEstadoPorDefecto() {
        document.getElementById("filtro_fecha").value = "";
        document.getElementById("filtro_destino").value = "";

        document.querySelectorAll(".horario-row").forEach((row) => {
            row.classList.remove("active");
        });

        sellButton.style.display = "none";
        editButton.style.display = "none";
        svgContainer.innerHTML = `
            <div class="no-results">
                Selecciona una salida para ver los asientos
            </div>
        `;
        resetSeleccion();

        if (pueblitoDefaultId) {
            document.getElementById("filtro_origen").value = pueblitoDefaultId;
            estadoInicial.style.display = "none";
            filtrarSalidas();
        } else {
            document.getElementById("filtro_origen").value = "";
            estadoInicial.style.display = "block";
            mostrarPrimeras10Salidas();
        }
    }

    // Helper único: decide si un punto de la ruta es un origen válido
    // para vender. Debe existir Y no estar bloqueado (check ya registrado).
    function esOrigenValido(punto) {
        return !!punto && punto.origen_permitido !== false;
    }

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

                const origenSelect = document.getElementById("filtro_origen");
                const destinoSelect = document.getElementById("filtro_destino");

                let puntos = [];
                try {
                    puntos = JSON.parse(this.dataset.puntos || "[]");
                } catch (e) {}

                const primero = puntos[0];
                const ultimo = puntos[puntos.length - 1];

                const primerOrigenValido = puntos.find(
                    (p) => p.origen_permitido !== false,
                );

                let origenId = origenSelect.value;
                let destinoId = destinoSelect.value;

                if (!origenId && !destinoId) {
                    if (primerOrigenValido && ultimo) {
                        cargarAsientos(
                            salidaId,
                            primerOrigenValido.pueblito_id,
                            ultimo.pueblito_id,
                        );
                    } else {
                        svgContainer.innerHTML = `
                            <div class="no-results">
                                Ya no quedan sucursales habilitadas para vender en esta salida
                            </div>
                        `;
                    }
                    return;
                }

                if (origenId && !destinoId) {
                    destinoId = ultimo?.pueblito_id;
                }

                if (!origenId && destinoId) {
                    // 👇 mismo criterio aquí
                    origenId = primerOrigenValido?.pueblito_id;
                }

                if (tipoViajeId === 2) {
                    manejarViajePorTramo(salidaId, this, origenId, destinoId);
                } else {
                    cargarAsientos(salidaId, origenId, destinoId);
                }
            });
        });
    }

    function manejarViajePorTramo(salidaId, row, origenId, destinoId) {
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

    const btnLimpiar = document.getElementById("btn-limpiar-filtros");

    btnLimpiar?.addEventListener("click", () => {
        aplicarEstadoPorDefecto();
    });

    function cargarAsientos(salidaId, origenId, destinoId) {
        if (!salidaId) return;
        currentOrigenId = origenId;
        currentDestinoId = destinoId;
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

    const origen = document.getElementById("filtro_origen");
    const destino = document.getElementById("filtro_destino");

    const opcionesOrigenOriginal = origen.innerHTML;
    const opcionesDestinoOriginal = destino.innerHTML;

    origen.addEventListener("change", function () {
        const valorOrigen = this.value;
        const valorDestinoActual = destino.value;

        destino.innerHTML = opcionesDestinoOriginal;

        if (valorOrigen) {
            for (let option of [...destino.options]) {
                if (option.value === valorOrigen) {
                    option.remove();
                }
            }
        }

        if (
            [...destino.options].some(
                (option) => option.value === valorDestinoActual,
            )
        ) {
            destino.value = valorDestinoActual;
        } else {
            destino.value = "";
        }
    });

    destino.addEventListener("change", function () {
        const valorDestino = this.value;
        const valorOrigenActual = origen.value;

        origen.innerHTML = opcionesOrigenOriginal;

        if (valorDestino) {
            for (let option of [...origen.options]) {
                if (option.value === valorDestino) {
                    option.remove();
                }
            }
        }

        if (
            [...origen.options].some(
                (option) => option.value === valorOrigenActual,
            )
        ) {
            origen.value = valorOrigenActual;
        } else {
            origen.value = "";
        }
    });

    sellButton.addEventListener("click", function (e) {
        if (!window.VENTA_CONFIG.esAdmin && !window.VENTA_CONFIG.cajaAbierta) {
            e.preventDefault();

            Swal.fire({
                icon: "warning",
                title: "Caja cerrada",
                text: "Debe abrir una caja antes de vender un pasaje.",
                confirmButtonText: "Ir a abrir caja",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = window.VENTA_CONFIG.rutaCaja;
                }
            });

            return;
        }

        if (!selectedSeats.length || !currentSalidaId) return;

        const seats = selectedSeats.sort((a, b) => a - b).join(",");

        if (!currentOrigenId || !currentDestinoId) {
            Swal.fire(
                "Atención",
                "Selecciona origen y destino antes de vender.",
                "warning",
            );
            return;
        }

        window.location.href = route("pasajes.vender", {
            salida: currentSalidaId,
            asientos: seats,
            origen_id: currentOrigenId,
            destino_id: currentDestinoId,
        });
    });

    editButton.addEventListener("click", function () {
        if (!selectedReservedPasajeId) return;

        window.location.href = route("pasajes.editar", {
            pasaje: selectedReservedPasajeId,
        });
    });

    function obtenerFechasSemana() {
        const hoy = new Date();
        const fechas = [];
        for (let i = 0; i < 7; i++) {
            const d = new Date(hoy);
            d.setDate(hoy.getDate() + i);
            fechas.push(d.toISOString().split("T")[0]);
        }
        return fechas;
    }

    function mostrarSalidasSemanaPorOrigen(origen) {
        const estadoInicial = document.getElementById("estado-inicial");
        const rows = document.querySelectorAll(".horario-row");
        const fechasSemana = obtenerFechasSemana();

        const selectOrigen = document.getElementById("filtro_origen");
        const nombreOrigen =
            selectOrigen.options[selectOrigen.selectedIndex]?.text?.trim() ||
            "";

        let visibles = 0;

        rows.forEach((row) => {
            const rowFecha = row.dataset.fecha || "";

            let puntos = [];
            try {
                puntos = JSON.parse(row.dataset.puntos || "[]");
            } catch (e) {
                puntos = [];
            }

            const puntoOrigen = puntos.find(
                (p) => String(p.pueblito_id) === String(origen),
            );

            // 👇 FIX: ya no basta con que exista el punto, tiene que
            // seguir habilitado para vender (el bus no debe haber pasado).
            const matchOrigen = esOrigenValido(puntoOrigen);
            const matchSemana = fechasSemana.includes(rowFecha);

            const ordenMax = puntos.length
                ? Math.max(...puntos.map((p) => Number(p.orden)))
                : null;
            const esUltimoPunto =
                puntoOrigen && Number(puntoOrigen.orden) === ordenMax;

            const visible = matchOrigen && matchSemana && !esUltimoPunto;

            const label = row.querySelector(".hr-route-label");

            if (visible) {
                row.style.display = "flex";
                visibles++;

                const puntoFinal = puntos[puntos.length - 1];
                const nombreDestinoReal =
                    puntoFinal?.nombre ||
                    puntoFinal?.pueblito_nombre ||
                    row.dataset.destinoNombre ||
                    "";

                if (label) {
                    label.textContent = `${nombreOrigen} → ${nombreDestinoReal}`;
                }

                const horaEl = row.querySelector(".hr-date-time");
                if (horaEl && puntoOrigen?.hora) {
                    horaEl.textContent = puntoOrigen.hora;
                }
            } else {
                row.style.display = "none";
            }
        });

        actualizarContador(visibles);

        if (estadoInicial) {
            estadoInicial.style.display = visibles === 0 ? "block" : "none";
            if (visibles === 0) {
                estadoInicial.innerHTML = `No hay salidas esta semana desde ${nombreOrigen || "ese origen"}`;
            }
        }

        if (visibles === 0) {
            svgContainer.innerHTML = `<div class="no-results">No hay salidas disponibles</div>`;
        }
    }

    function mostrarSalidasSemanaPorOrigenDestino(origen, destino) {
        const estadoInicial = document.getElementById("estado-inicial");
        const rows = document.querySelectorAll(".horario-row");
        const fechasSemana = obtenerFechasSemana();

        const selectOrigen = document.getElementById("filtro_origen");
        const selectDestino = document.getElementById("filtro_destino");
        const nombreOrigen =
            selectOrigen.options[selectOrigen.selectedIndex]?.text?.trim() ||
            "";
        const nombreDestino =
            selectDestino.options[selectDestino.selectedIndex]?.text?.trim() ||
            "";

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
                (p) => String(p.pueblito_id) === String(origen),
            );
            const puntoDestino = puntos.find(
                (p) => String(p.pueblito_id) === String(destino),
            );

            const matchSemana = fechasSemana.includes(rowFecha);
            // 👇 FIX: mismo criterio, el origen debe seguir habilitado
            const matchOrigen = esOrigenValido(puntoOrigen);
            const matchDestino = !!puntoDestino;

            let matchOrden = false;
            if (puntoOrigen && puntoDestino) {
                matchOrden =
                    Number(puntoOrigen.orden) < Number(puntoDestino.orden);
            }

            let visible =
                matchSemana && matchOrigen && matchDestino && matchOrden;

            if (visible && tipoViajeId !== 2) {
                const primero = puntos[0];
                const ultimo = puntos[puntos.length - 1];

                const coincideExacto =
                    primero &&
                    ultimo &&
                    String(primero.pueblito_id) === String(origen) &&
                    String(ultimo.pueblito_id) === String(destino);

                visible = coincideExacto;
            }

            const label = row.querySelector(".hr-route-label");

            if (visible) {
                row.style.display = "flex";
                visibles++;

                if (label) {
                    label.textContent = `${nombreOrigen} → ${nombreDestino}`;
                }

                const horaEl = row.querySelector(".hr-date-time");
                if (horaEl && puntoOrigen?.hora) {
                    horaEl.textContent = puntoOrigen.hora;
                }
            } else {
                row.style.display = "none";
            }
        });

        actualizarContador(visibles);

        if (estadoInicial) {
            estadoInicial.style.display = visibles === 0 ? "block" : "none";
            if (visibles === 0) {
                estadoInicial.innerHTML = `No hay salidas esta semana de ${nombreOrigen} a ${nombreDestino}`;
            }
        }

        if (visibles === 0) {
            svgContainer.innerHTML = `<div class="no-results">No hay salidas disponibles</div>`;
        }
    }

    function filtrarSalidas() {
        const fecha = document.getElementById("filtro_fecha").value;
        const origen = document.getElementById("filtro_origen").value;
        const destino = document.getElementById("filtro_destino").value;

        if (!origen) {
            actualizarContador(0);
            document
                .querySelectorAll(".horario-row")
                .forEach((r) => (r.style.display = "none"));

            estadoInicial.style.display = "block";
            estadoInicial.innerHTML = "Seleccione un origen";
            return;
        }

        if (!destino && !fecha) {
            mostrarSalidasSemanaPorOrigen(origen);
            return;
        }

        if (destino && !fecha) {
            mostrarSalidasSemanaPorOrigenDestino(origen, destino);
            return;
        }

        if (!destino && fecha) {
            mostrarSalidasPorOrigenFecha(origen, fecha);
            return;
        }

        mostrarSalidasPorOrigenDestinoFecha(origen, destino, fecha);
    }

    function mostrarSalidasPorOrigenFecha(origen, fecha) {
        const estadoInicial = document.getElementById("estado-inicial");
        const rows = document.querySelectorAll(".horario-row");

        const selectOrigen = document.getElementById("filtro_origen");
        const nombreOrigen =
            selectOrigen.options[selectOrigen.selectedIndex]?.text?.trim() ||
            "";

        let visibles = 0;

        rows.forEach((row) => {
            const rowFecha = row.dataset.fecha || "";

            let puntos = [];
            try {
                puntos = JSON.parse(row.dataset.puntos || "[]");
            } catch (e) {
                puntos = [];
            }

            const puntoOrigen = puntos.find(
                (p) => String(p.pueblito_id) === String(origen),
            );

            // 👇 FIX
            const matchOrigen = esOrigenValido(puntoOrigen);
            const matchFecha = rowFecha === fecha;

            const ordenMax = puntos.length
                ? Math.max(...puntos.map((p) => Number(p.orden)))
                : null;
            const esUltimoPunto =
                puntoOrigen && Number(puntoOrigen.orden) === ordenMax;

            const visible = matchOrigen && matchFecha && !esUltimoPunto;

            const label = row.querySelector(".hr-route-label");

            if (visible) {
                row.style.display = "flex";
                visibles++;

                const puntoFinal = puntos[puntos.length - 1];
                const nombreDestinoReal =
                    puntoFinal?.nombre ||
                    puntoFinal?.pueblito_nombre ||
                    row.dataset.destinoNombre ||
                    "";

                if (label) {
                    label.textContent = `${nombreOrigen} → ${nombreDestinoReal}`;
                }

                const horaEl = row.querySelector(".hr-date-time");
                if (horaEl && puntoOrigen?.hora) {
                    horaEl.textContent = puntoOrigen.hora;
                }
            } else {
                row.style.display = "none";
            }
        });

        actualizarContador(visibles);

        if (estadoInicial) {
            estadoInicial.style.display = visibles === 0 ? "block" : "none";
            if (visibles === 0) {
                estadoInicial.innerHTML = `No hay salidas el ${fecha} desde ${nombreOrigen || "ese origen"}`;
            }
        }

        if (visibles === 0) {
            svgContainer.innerHTML = `<div class="no-results">No hay salidas disponibles</div>`;
        }
    }

    function mostrarSalidasPorOrigenDestinoFecha(origen, destino, fecha) {
        const estadoInicial = document.getElementById("estado-inicial");
        const rows = document.querySelectorAll(".horario-row");

        const selectOrigen = document.getElementById("filtro_origen");
        const selectDestino = document.getElementById("filtro_destino");
        const nombreOrigen =
            selectOrigen.options[selectOrigen.selectedIndex]?.text?.trim() ||
            "";
        const nombreDestino =
            selectDestino.options[selectDestino.selectedIndex]?.text?.trim() ||
            "";

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
                (p) => String(p.pueblito_id) === String(origen),
            );
            const puntoDestino = puntos.find(
                (p) => String(p.pueblito_id) === String(destino),
            );

            const matchFecha = rowFecha === fecha;
            // 👇 FIX
            const matchOrigen = esOrigenValido(puntoOrigen);
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
                    String(primero.pueblito_id) === String(origen) &&
                    String(ultimo.pueblito_id) === String(destino);

                visible = coincideExacto;
            }

            const label = row.querySelector(".hr-route-label");

            if (visible) {
                row.style.display = "flex";
                visibles++;

                if (label) {
                    label.textContent = `${nombreOrigen} → ${nombreDestino}`;
                }

                const horaEl = row.querySelector(".hr-date-time");
                if (horaEl && puntoOrigen?.hora) {
                    horaEl.textContent = puntoOrigen.hora;
                }
            } else {
                row.style.display = "none";
            }
        });

        actualizarContador(visibles);

        if (estadoInicial) {
            estadoInicial.style.display = visibles === 0 ? "block" : "none";
            if (visibles === 0) {
                estadoInicial.innerHTML = `No hay salidas el ${fecha} de ${nombreOrigen} a ${nombreDestino}`;
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

    function precargarDesdeUrl() {
        const params = new URLSearchParams(window.location.search);

        const salidaId = params.get("salida_id");
        const origenId = params.get("origen_id");
        const destinoId = params.get("destino_id");

        if (!salidaId || !origenId || !destinoId) return;

        document.getElementById("filtro_origen").value = origenId;
        document.getElementById("filtro_destino").value = destinoId;

        filtrarSalidas();

        const row = document.querySelector(
            `.horario-row[data-salida-id="${salidaId}"]`,
        );

        if (row) {
            row.click();
        }
    }
    precargarDesdeUrl();

    // 👇 FIX: el listado inicial (sin filtro de origen, típicamente admin)
    // ya no debe listar una salida donde TODOS los orígenes están bloqueados.
    function mostrarPrimeras10Salidas() {
        const rows = Array.from(
            document.querySelectorAll(".horario-row"),
        ).filter((row) => {
            let puntos = [];
            try {
                puntos = JSON.parse(row.dataset.puntos || "[]");
            } catch (e) {
                puntos = [];
            }
            return puntos.some((p) => p.origen_permitido !== false);
        });

        let visibles = 0;

        document.querySelectorAll(".horario-row").forEach((row) => {
            row.style.display = "none";
        });

        rows.forEach((row) => {
            if (visibles < 10) {
                row.style.display = "flex";
                visibles++;
            }
        });

        actualizarContador(visibles);

        const estadoInicial = document.getElementById("estado-inicial");
        if (estadoInicial) {
            estadoInicial.style.display = visibles ? "none" : "block";
        }
    }
});