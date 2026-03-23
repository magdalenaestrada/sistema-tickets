@extends('layouts.app')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">CALENDARIO DE CUMPLEAÑOS</h5>
            <div>
                <button class="btn btn-xs btn-outline-primary" id="btnHoy">Hoy</button>
                <button class="btn btn-xs btn-outline-secondary" id="btnMes">Mes</button>
                <button class="btn btn-xs btn-outline-secondary" id="btnSemana">Semana</button>
            </div>
        </div>
        <div class="card-body p-3">
            <div id="calendar"></div>
        </div>
    </div>

    @include('horarios.modals.ver')
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">

    <style>
        #calendar {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            min-height: 600px;
        }

        .fc-event {
            border: none !important;
            border-radius: 6px !important;
            background-color: #ffaae4 !important;
            color: white;
            padding: 4px 6px;
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
            const calendarEl = document.getElementById("calendar");
            setTimeout(() => {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: "dayGridMonth",
                    locale: "es",
                    height: "auto",
                    events: eventosLaravel,

                    eventContent(info) {
                        const tipo = info.event.extendedProps.tipo ?? info.event.title;
                        const persona = info.event.extendedProps.persona ?? '';
                        const edad = info.event.extendedProps.edad ?? null;

                        return {
                            html: `
                        <div>${tipo}</div>
                        <div class="fc-event-title">${persona}</div>
                        ${edad ? `<div style="font-size:13px;font-weight:bold;opacity:.8">${edad} años</div>` : ""}
                    `
                        };
                    },

                    eventClick(info) {
                        $("#ModalVerEvento").modal("show");
                        $("#verTitulo").text(info.event.title);
                        const edad = info.event.extendedProps.edad ?? null;
                        $("#verEdad").text(edad ? edad + " años" : "Sin edad");
                    }
                });

                calendar.render();
            }, 50);
        });
    </script>
@endpush  
