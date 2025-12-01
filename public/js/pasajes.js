document.addEventListener("DOMContentLoaded", function () {
    const horarioCards = document.querySelectorAll(".horario-card");
    const svgContainer = document.getElementById("svg-container");
    const sellButton = document.getElementById("sell-button");
    const editButton = document.getElementById("edit-button");

    let selectedSeats = [];
    let currentHorarioId = null;
    let selectedReservedPasajeId = null; // ID del pasaje reservado seleccionado

    horarioCards.forEach((card) => {
        card.addEventListener("click", function () {
            const horarioId = this.dataset.horarioId;
            currentHorarioId = horarioId;
            window.selectedHorario = horarioId;

            // Remover clase active de todas las tarjetas
            horarioCards.forEach((c) => c.classList.remove("active"));

            // Agregar clase active a la tarjeta seleccionada
            this.classList.add("active");

            // Resetear selecciones
            selectedSeats = [];
            selectedReservedPasajeId = null;
            updateSellButton();
            updateEditButton();

            fetch(`/pasajes/horario/${horarioId}/asientos`)
                .then((res) => res.json())
                .then((data) => {
                    svgContainer.innerHTML = data.svg;

                    const svgEl = svgContainer.querySelector("svg");
                    if (!svgEl) {
                        console.error("❌ SVG no encontrado");
                        return;
                    }

                    Object.keys(data.asientos).forEach((numero) => {
                        const estado = data.asientos[numero];
                        const g = svgEl.querySelector(`#seat-${numero}`);

                        if (!g) return;

                        // Limpiar clases previas
                        g.classList.remove(
                            "ocupado",
                            "reservado",
                            "libre",
                            "selected-seat"
                        );

                        g.classList.add(estado);

                        g.dataset.estado = estado;
                        g.dataset.numero = numero;

                        if (estado === "libre") {
                            g.style.cursor = "pointer";
                            g.style.opacity = "1";

                            g.onclick = function (e) {
                                e.stopPropagation();
                                toggleSeatSelection(g, numero, "libre");
                            };
                        } else if (estado === "reservado") {
                            g.style.cursor = "pointer";
                            g.style.opacity = "1";

                            g.onclick = function (e) {
                                e.stopPropagation();
                                // Obtener el ID del pasaje reservado
                                obtenerPasajeReservado(horarioId, numero);
                            };
                        } else {
                            g.style.cursor = "not-allowed";
                            g.style.opacity = "0.6";

                            g.onclick = function (e) {
                                e.stopPropagation();
                                alert("Este asiento está vendido");
                            };
                        }
                    });
                })
                .catch((err) => {
                    console.error("❌ Error:", err);
                });
        });
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

            // Limpiar selección de reservado
            selectedReservedPasajeId = null;
        }

        updateSellButton();
        updateEditButton();
    }

    async function obtenerPasajeReservado(horarioId, numeroAsiento) {
        try {
            // Buscar el pasaje reservado en ese horario y asiento
            const response = await fetch(
                `/pasajes/horario/${horarioId}/asientos`
            );
            const data = await response.json();

            // Necesitamos obtener el ID del pasaje - haremos una búsqueda adicional
            const pasajeResponse = await fetch(
                `/pasajes/buscar?horario_id=${horarioId}&asiento=${numeroAsiento}`
            );
            const pasajeData = await pasajeResponse.json();

            if (pasajeData.success && pasajeData.pasaje_id) {
                selectedReservedPasajeId = pasajeData.pasaje_id;

                // Limpiar selección de asientos libres
                selectedSeats = [];
                document.querySelectorAll(".selected-seat").forEach((seat) => {
                    seat.classList.remove("selected-seat");
                    seat.classList.add("libre");
                });

                // Marcar visualmente el asiento reservado seleccionado
                const seatElement = document.querySelector(
                    `#seat-${numeroAsiento}`
                );
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
            const res = await fetch("/caja/verificar");
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

        // Redirigir a la página de edición usando Ziggy
        window.location.href = route("pasajes.editar", {
            pasaje: selectedReservedPasajeId,
        });
    });
});