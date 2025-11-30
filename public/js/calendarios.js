$(document).ready(function () {
    if (typeof FullCalendar === "undefined") {
        console.error("⚠️ FullCalendar no está cargado.");
        return;
    }

    const calendarEl = document.getElementById("calendar");
    if (!calendarEl) return;

    const modalCalendarioEl = document.getElementById("modalVerHorarios");
    const modalCalendario = modalCalendarioEl
        ? new bootstrap.Modal(modalCalendarioEl)
        : null;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        locale: "es",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "",
        },

        // ⬇️ Ziggy aquí
        events: route("horarios.calendario.eventos"),

        eventDisplay: "block",
        eventClick: function (info) {
            const props = info.event.extendedProps;
            const tbody = $("#tablaHorariosDia tbody");
            tbody.empty();

            tbody.append(`
                <tr>
                    <td>${props.hora || "—"}</td>
                    <td>${info.event.title}</td>
                    <td>${props.tipo_viaje || "—"}</td>
                    <td>${props.vehiculo || "—"}</td>
                    <td>${props.costo || "—"}</td>
                </tr>
            `);

            if (modalCalendario) modalCalendario.show();
        },
    });

    calendar.render();

    // 🔹 Controles personalizados
    $("#btnHoy").click(() => calendar.today());
    $("#btnMes").click(() => calendar.changeView("dayGridMonth"));
    $("#btnSemana").click(() => calendar.changeView("timeGridWeek"));
});
