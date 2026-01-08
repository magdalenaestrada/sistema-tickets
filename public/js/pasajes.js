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
                    if (!svgEl) {
                        console.error("SVG no encontrado");
                        return;
                    }

                    Object.keys(data.asientos).forEach((numero) => {
                        const estado = data.asientos[numero];
                        const g = svgEl.querySelector(`#seat-${numero}`);

                        if (!g) return;

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
                    console.error("Error:", err);
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

            selectedReservedPasajeId = null;
        }

        updateSellButton();
        updateEditButton();
    }

    async function obtenerPasajeReservado(horarioId, numeroAsiento) {
        try {
            const response = await fetch(
                `/pasajes/horario/${horarioId}/asientos`
            );
            const data = await response.json();

            const pasajeResponse = await fetch(
                `/pasajes/buscar?horario_id=${horarioId}&asiento=${numeroAsiento}`
            );
            const pasajeData = await pasajeResponse.json();

            if (pasajeData.success && pasajeData.pasaje_id) {
                selectedReservedPasajeId = pasajeData.pasaje_id;

                selectedSeats = [];
                document.querySelectorAll(".selected-seat").forEach((seat) => {
                    seat.classList.remove("selected-seat");
                    seat.classList.add("libre");
                });

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

        window.location.href = route("pasajes.editar", {
            pasaje: selectedReservedPasajeId,
        });
    });

    $(document).ready(function () {
        function cargarHorarios() {
            let fecha = $("#filtro_fecha").val();
            let origen = $("#filtro_origen").val();
            let destino = $("#filtro_destino").val();

            $.ajax({
                url: "/pasajes/filtrar",
                method: "GET",
                data: {
                    fecha: fecha,
                    origen: origen,
                    destino: destino,
                },
                success: function (res) {
                    $(".row .col-md-6.mb-3").remove(); // limpiar horarios

                    let contenedor = $(".row").first(); // donde están los horarios

                    if (res.horarios.length === 0) {
                        contenedor.append(`
                        <div class="col-md-12">
                            <p class="text-center text-muted">No hay horarios disponibles.</p>
                        </div>
                    `);
                        return;
                    }

                    res.horarios.forEach((h) => {
                        let capacidad = h.tipo_vehiculo.capacidad;
                        let vendidos = h.pasajes_count;
                        let disponibles = capacidad - vendidos;

                        contenedor.append(`
                        <div class="col-md-6 mb-3">
                            <div class="card horario-card" data-horario-id="${h.id}">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        ${h.tipo_vehiculo.descripcion} – ${disponibles} asientos disponibles
                                    </h5>

                                    <p class="card-text">
                                        ${h.punto_origen.nombre_comercial} → ${h.punto_destino.nombre_comercial} <br>
                                        ${h.fecha_salida} - ${h.hora_embarque}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `);
                    });
                },
            });
        }

        // FILTRO AUTO
        $("#filtro_fecha, #filtro_origen, #filtro_destino").on(
            "change",
            function () {
                cargarHorarios();
            }
        );

        // OCULTAR DESTINOS QUE NO CORRESPONDEN
        $("#filtro_origen").on("change", function () {
            let origen = $(this).val();

            $("#filtro_destino option").show();

            if (origen) {
                $("#filtro_destino option").each(function () {
                    if ($(this).val() == origen) {
                        $(this).hide();
                    }
                });
            }

            $("#filtro_destino").val("");
            cargarHorarios();
        });
    });
});














// ✅ REEMPLAZA LAS FUNCIONES DEL MODAL EN TU CÓDIGO
// Este código sigue el MISMO patrón que pasajes.js

let asientoNuevo = null;
let horarioNuevoId = null;
let pasajeCambioIndex = null;

window.abrirCambioHorario = function (index, asiento, horarioId) {
    pasajeCambioIndex = index;
    asientoNuevo = null;
    horarioNuevoId = null;

    $("#listaHorariosCambio").html("");
    
    // Resetear cualquier selección visual anterior
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
                html = '<div class="col-12"><p class="text-center text-muted">No hay horarios disponibles.</p></div>';
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

            // ⭐ AGREGAR EVENT LISTENERS A LAS TARJETAS (como en pasajes.js)
            agregarEventListenersHorarios();
        }
    ).fail(function () {
        Swal.fire("Error", "No se pudieron buscar horarios", "error");
    });
};

// ⭐ NUEVA FUNCIÓN: Event listeners para las tarjetas de horarios
function agregarEventListenersHorarios() {
    const horarioCards = document.querySelectorAll("#listaHorariosCambio .horario-card");
    
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

    // Marcar tarjeta activa (como en pasajes.js)
    document.querySelectorAll("#listaHorariosCambio .horario-card").forEach((c) => {
        c.classList.remove("active");
    });
    
    const tarjetaActiva = document.querySelector(`[data-horario-id="${horarioId}"]`);
    if (tarjetaActiva) {
        tarjetaActiva.classList.add("active");
    }

    // Ocultar todos los SVG
    $("[id^='svg-card-']").addClass("d-none");

    // Cargar SVG del horario seleccionado
    $.get(route("pasajes.horario.asientos", horarioId), function (res) {
        const contenedor = $(`#svg-bus-${horarioId}`);
        contenedor.html(res.svg);

        // Mostrar la tarjeta SVG
        $(`#svg-card-${horarioId}`).removeClass("d-none");

        // ⭐ APLICAR EL MISMO PATRÓN QUE pasajes.js
        const svgEl = contenedor[0].querySelector("svg");
        if (!svgEl) {
            console.error("SVG no encontrado");
            return;
        }

        // Procesar cada asiento (EXACTAMENTE como en pasajes.js)
        Object.keys(res.asientos).forEach((numero) => {
            const estado = res.asientos[numero];
            const g = svgEl.querySelector(`#seat-${numero}`);

            if (!g) return;

            // Limpiar clases anteriores
            g.classList.remove("ocupado", "reservado", "libre", "selected-seat");

            // Agregar clase según estado
            g.classList.add(estado);

            // Guardar datos
            g.dataset.estado = estado;
            g.dataset.numero = numero;

            // ⭐ LÓGICA SEGÚN ESTADO (como pasajes.js)
            if (estado === "libre") {
                g.style.cursor = "pointer";
                g.style.opacity = "1";

                // Evento onclick directo (sin event listeners complejos)
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
                        showConfirmButton: false
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
                        showConfirmButton: false
                    });
                };
            }
        });

        // Mostrar leyenda si existe
        $("#leyendaAsientos").slideDown();

    }).fail(function () {
        Swal.fire("Error", "No se pudieron cargar los asientos", "error");
    });
};

// ⭐ SELECCIONAR ASIENTO (patrón simplificado como pasajes.js)
window.seleccionarAsientoCambio = function (numero, seatElement) {
    // Limpiar selección anterior
    document.querySelectorAll(".selected-seat").forEach((seat) => {
        seat.classList.remove("selected-seat");
        
        // Restaurar clase original según su estado
        const estadoOriginal = seat.dataset.estado;
        if (estadoOriginal === "libre") {
            seat.classList.add("libre");
        }
    });

    // Marcar nuevo asiento como seleccionado
    asientoNuevo = numero;
    seatElement.classList.remove("libre");
    seatElement.classList.add("selected-seat");

    console.log(`Asiento ${numero} seleccionado para cambio`);

    // Feedback visual
    Swal.fire({
        icon: "success",
        title: `Asiento ${numero} seleccionado`,
        timer: 1500,
        showConfirmButton: false,
        toast: true,
        position: "top-end"
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

    // Actualizar los inputs ocultos del formulario
    document.querySelectorAll('input[name="asientos[]"]')[pasajeCambioIndex].value = asientoNuevo;
    document.querySelectorAll('input[name="horario_id[]"]')[pasajeCambioIndex].value = horarioNuevoId;

    // Cerrar modal
    const modalElement = document.getElementById("modalCambioHorario");
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    modalInstance.hide();

    Swal.fire({
        icon: "success",
        title: "Cambio registrado",
        text: `Nuevo asiento: ${asientoNuevo}`,
        timer: 2000
    });
};

// Event listener para el botón de buscar
$("#btnBuscarCambio").on("click", function () {
    buscarHorariosCambio();
});





/* ✅ CSS PARA EL MODAL DE CAMBIO DE HORARIO */
/* Sigue el mismo patrón que pasajes.blade.php */

/* Tarjetas de horarios */
.horario-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 3px solid transparent;
}

.horario-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.horario-card.active {
    border: 3px solid #3498db;
    background-color: #e8f4f8;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

/* SVG Container */
.svg-bus-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
}

.svg-bus-container svg {
    width: 100%;
    max-width: 380px;
    height: auto;
}

/* Estados de asientos - IGUAL que en pasajes.js */
.seat.libre .seat-body,
.seat.libre .seat-base,
[id^='seat-'].libre .seat-body,
[id^='seat-'].libre .seat-base {
    fill: #d3d3d3;
}

.seat.ocupado .seat-body,
.seat.ocupado .seat-base,
[id^='seat-'].ocupado .seat-body,
[id^='seat-'].ocupado .seat-base {
    fill: #dc3545; /* rojo */
}

.seat.reservado .seat-body,
.seat.reservado .seat-base,
[id^='seat-'].reservado .seat-body,
[id^='seat-'].reservado .seat-base {
    fill: #ffc107; /* naranja/amarillo */
}

/* Asiento seleccionado - IGUAL que en pasajes.blade.php */
.seat.selected-seat .seat-body,
.seat.selected-seat .seat-base,
[id^='seat-'].selected-seat .seat-body,
[id^='seat-'].selected-seat .seat-base {
    fill: #1e90ff !important; /* azul */
}

/* Hover suave solo para asientos libres */
.seat.libre:hover .seat-body,
.seat.libre:hover .seat-base,
[id^='seat-'].libre:hover .seat-body,
[id^='seat-'].libre:hover .seat-base {
    fill: #b0b0b0; /* gris más oscuro */
    transition: fill 0.2s ease;
}

/* NO aplicar hover a ocupados/reservados */
.seat.ocupado:hover,
.seat.reservado:hover,
[id^='seat-'].ocupado:hover,
[id^='seat-'].reservado:hover {
    transform: none;
}

/* Leyenda de colores */
.leyenda-asientos {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 15px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.leyenda-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
}

.leyenda-color {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    border: 1px solid #ccc;
}

/* Asegurar que los elementos SVG sean clickeables */
.seat,
[id^='seat-'] {
    pointer-events: all;
}

.seat *,
[id^='seat-'] * {
    pointer-events: none; /* Evitar que los hijos capturen el click */
}

/* Transiciones suaves */
.seat .seat-body,
.seat .seat-base,
[id^='seat-'] .seat-body,
[id^='seat-'] .seat-base {
    transition: fill 0.2s ease;
}


<!-- ✅ REEMPLAZA LA SECCIÓN DEL MODAL-BODY -->

<div class="modal-body">
    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label">Fecha</label>
                    <input type="date" id="filtroFechaCambio" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Origen</label>
                    <select id="filtroOrigenCambio" class="form-select">
                        <option value="">-- Origen --</option>
                        @foreach ($puntos_origen as $origen)
                            <option value="{{ $origen->id }}">
                                {{ $origen->nombre_comercial }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Destino</label>
                    <select id="filtroDestinoCambio" class="form-select">
                        <option value="">-- Destino --</option>
                        @foreach ($puntos_destino as $destino)
                            <option value="{{ $destino->id }}">
                                {{ $destino->nombre_comercial }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo de viaje</label>
                    <select id="filtroTipoViajeCambio" class="form-select">
                        <option value="">-- Todos --</option>
                        @foreach ($tipos_viaje as $tv)
                            <option value="{{ $tv->id }}">
                                {{ $tv->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo de vehículo</label>
                    <select id="filtroTipoVehiculoCambio" class="form-select">
                        <option value="">-- Todos --</option>
                        @foreach ($tipos_vehiculos as $tv)
                            <option value="{{ $tv->id }}">
                                {{ $tv->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12 d-flex align-items-end">
                    <button id="btnBuscarCambio" class="btn btn-primary w-100">
                        <i class="link-icon" data-lucide="search"></i>
                        Buscar horarios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de horarios con SVG -->
    <div id="listaHorariosCambio" class="row g-3">
        <div class="col-12">
            <p class="text-center text-muted">
                Use los filtros y haga clic en "Buscar horarios" para comenzar
            </p>
        </div>
    </div>

    <!-- ⭐ LEYENDA DE COLORES -->
    <div class="leyenda-asientos mt-3" style="display: none;" id="leyendaAsientos">
        <div class="leyenda-item">
            <div class="leyenda-color" style="background-color: #d3d3d3;"></div>
            <span>Disponible</span>
        </div>
        <div class="leyenda-item">
            <div class="leyenda-color" style="background-color: #1e90ff;"></div>
            <span>Seleccionado</span>
        </div>
        <div class="leyenda-item">
            <div class="leyenda-color" style="background-color: #ffc107;"></div>
            <span>Reservado</span>
        </div>
        <div class="leyenda-item">
            <div class="leyenda-color" style="background-color: #dc3545;"></div>
            <span>Vendido</span>
        </div>
    </div>
</div>


