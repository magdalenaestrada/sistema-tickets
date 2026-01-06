document.addEventListener("DOMContentLoaded", function () {
    const horarioCards = document.querySelectorAll(".horario-card");
    const svgContainer = document.getElementById("svg-container");
    const sellButton = document.getElementById("sell-button");
    const editButton = document.getElementById("edit-button");

    let selectedSeats = [];mira
    let currentHorarioId = null;
    let selectedReservedPasajeId = null;

    let modalHorarioId = null;
    let modalAsientoSeleccionado = null;

    horarioCards.forEach((card) => {
        card.addEventListener("click", function () {
            const horarioId = this.dataset.horarioId;
            currentHorarioId = horarioId;
            window.selectedHorario = horarioId;

            horarioCards.forEach((c) => c.classList.remove("active"));
            this.classList.add("active");

            selectedSeats = [];
            selectedReservedPasajeId = null;
            updateSellButton();
            updateEditButton();

            fetch(`/pasajes/horario/${horarioId}/asientos`)
                .then((res) => res.json())
                .then((data) => {
                    svgContainer.innerHTML = data.svg;

                    const svgEl = svgContainer.querySelector("svg");
                    if (!svgEl) return;

                    Object.keys(data.asientos).forEach((numero) => {
                        const estado = data.asientos[numero];
                        const g = svgEl.querySelector(`#seat-${numero}`);
                        if (!g) return;

                        g.className.baseVal = "";
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
                            g.onclick = () => {
                                alert("Este asiento está vendido");
                            };
                        }
                    });
                });
        });
    });

    function seleccionarAsientoModal(g, numero) {
        document.querySelectorAll(".selected-seat").forEach((s) => {
            s.classList.remove("selected-seat");
            s.classList.add("libre");
        });

        g.classList.remove("libre");
        g.classList.add("selected-seat");

        modalAsientoSeleccionado = numero;
        document.getElementById("btnGuardarCambioHorario").disabled = false;
    }

    document
        .getElementById("btnGuardarCambioHorario")
        ?.addEventListener("click", async () => {
            if (!modalHorarioId || !modalAsientoSeleccionado) return;

            const pasajeId = PASAJE_ID; // ← define esto en blade

            const res = await fetch(`/pasajes/${pasajeId}/cambiar-horario`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
                body: JSON.stringify({
                    horario_id: modalHorarioId,
                    asiento_numero: modalAsientoSeleccionado,
                }),
            });

            const data = await res.json();

            if (data.success) {
                location.reload();
            } else {
                Swal.fire("Error", data.message, "error");
            }
        });

    function toggleSeatSelection(seatElement, numero, tipo) {
        const seatNum = parseInt(numero);

        if (tipo === "libre") {
            if (selectedSeats.includes(seatNum)) {
                selectedSeats = selectedSeats.filter((s) => s !== seatNum);
                seatElement.classList.remove("selected-seat");
                seatElement.classList.add("libre");
            } else {
                selectedSeats.push(seatNum);
                seatElement.classList.remove("libre");
                seatElement.classList.add("selected-seat");
            }

            selectedReservedPasajeId = null;
        }

        updateSellButton();
        updateEditButton();
    }

    async function obtenerPasajeReservado(horarioId, numeroAsiento) {
        try {
            const pasajeResponse = await fetch(
                `/pasajes/buscar?horario_id=${horarioId}&asiento=${numeroAsiento}`
            );
            const pasajeData = await pasajeResponse.json();

            if (pasajeData.success) {
                selectedReservedPasajeId = pasajeData.pasaje_id;

                selectedSeats = [];
                document.querySelectorAll(".selected-seat").forEach((seat) => {
                    seat.classList.remove("selected-seat");
                    seat.classList.add("libre");
                });

                document
                    .querySelector(`#seat-${numeroAsiento}`)
                    ?.classList.add("selected-seat");

                updateSellButton();
                updateEditButton();
            }
        } catch (e) {
            console.error(e);
        }
    }

    function updateSellButton() {
        sellButton.style.display = selectedSeats.length > 0 ? "block" : "none";
        sellButton.textContent = `Vender ${selectedSeats.length} asiento(s)`;
    }

    function updateEditButton() {
        editButton.style.display = selectedReservedPasajeId ? "block" : "none";
    }

    sellButton.addEventListener("click", async () => {
        if (!selectedSeats.length || !currentHorarioId) return;

        const res = await fetch("/caja/verificar");
        const data = await res.json();
        if (!data.abierta) {
            Swal.fire("Caja cerrada", "Abre caja primero", "warning");
            return;
        }

        window.location.href = route("pasajes.vender", {
            asientos: selectedSeats.join(","),
            horario: currentHorarioId,
        });
    });

    editButton.addEventListener("click", () => {
        if (!selectedReservedPasajeId) return;
        window.location.href = route("pasajes.editar", {
            pasaje: selectedReservedPasajeId,
        });
    });

    $(document).ready(function () {
        function cargarHorarios() {
            $.get("/pasajes/filtrar", {
                fecha: $("#filtro_fecha").val(),
                origen: $("#filtro_origen").val(),
                destino: $("#filtro_destino").val(),
            }).done(() => location.reload());
        }

        $("#filtro_fecha, #filtro_origen, #filtro_destino").on(
            "change",
            cargarHorarios
        );
    });
});
