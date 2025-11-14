@extends('layouts.app')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">CALENDARIO DE CUMPLEAÑOS</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="btnHoy">Hoy</button>
                <button class="btn btn-sm btn-outline-secondary" id="btnMes">Mes</button>
                <button class="btn btn-sm btn-outline-secondary" id="btnSemana">Semana</button>
            </div>
        </div>
        <div class="card-body p-3">
            <div id="calendar"></div>
        </div>
    </div>

    @include('horarios.modals.ver')
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        #calendar {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        .fc-event {
            border: none;
            border-radius: 6px;
            background-color: #ffaae4;
            color: white;
            padding: 3px 6px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        const eventosLaravel = @json($datos_eventos);

        document.addEventListener("DOMContentLoaded", function() {

            let calendarEl = document.getElementById("calendar");

            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                locale: "es",
                events: eventosLaravel,

                eventContent: function(info) {
                    let edad = info.event.extendedProps.edad ?? null;
                    let persona = info.event.extendedProps.persona ?? null;
                    let tipo = info.event.extendedProps.tipo ?? null;
                    let html = `
                <div>🎂 ${tipo}</div>
                <div class="fc-event-title">${persona}</div>
                <div style="font-size:14px; font-weight:bold; opacity:.8">${edad ? edad + " años" : ""}</div>
            `;

                    return {
                        html: html
                    };
                },

                eventClick: function(info) {
                    $("#ModalVerEvento").modal("show");

                    $("#verTitulo").text(info.event.title);
                    $("#verEdad").text(info.event.extendedProps.edad ?
                        info.event.extendedProps.edad + " años" :
                        "Sin edad");
                }
            });

            calendar.render();
        });
    </script>
@endpush
