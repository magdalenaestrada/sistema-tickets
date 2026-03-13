@extends('layouts.app')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Calendario de Horarios</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="btnHoy">Hoy</button>
                <button class="btn btn-sm btn-outline-secondary" id="btnMes">Mes</button>
                <button class="btn btn-sm btn-outline-secondary" id="btnSemana">Semana</button>
            </div>
        </div>
        {{-- Filtros --}}
        <div class="row g-2 mb-3 px-3">
            <div class="col-md-3">
                <select id="filtroOrigen" class="form-select form-select-sm">
                    <option value="">Todos los orígenes</option>
                    @foreach ($sucursales as $s)
                        <option value="{{ $s->nombre_comercial }}">{{ $s->nombre_comercial }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filtroDestino" class="form-select form-select-sm">
                    <option value="">Todos los destinos</option>
                    @foreach ($sucursales as $s)
                        <option value="{{ $s->nombre_comercial }}">{{ $s->nombre_comercial }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select id="filtroTipoViaje" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    @foreach ($tiposViaje as $t)
                        <option value="{{ $t->id }}">{{ $t->descripcion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" id="filtroFecha" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-secondary w-100" id="btnLimpiarFiltros">Limpiar</button>
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
        /* 🎨 Estilo general tipo ClickUp */
        #calendar {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }

        .fc-button {
            border-radius: 8px !important;
            font-weight: 500;
        }

        .fc-daygrid-day {
            transition: background 0.2s ease;
        }

        .fc-daygrid-day:hover {
            background: #f8f9fa;
        }

        .fc-event {
            border: none;
            border-radius: 6px;
            color: white;
            padding: 3px 6px;
            font-size: 13px;
        }

        .fc-event:hover {
            filter: brightness(0.85);
        }

        .fc-col-header-cell-cushion {
            font-weight: 600;
            color: #555;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        const eventosLaravel = @json($eventos);

        document.addEventListener("DOMContentLoaded", function() {
            const calendarEl = document.getElementById("calendar");

            setTimeout(() => {
                function eventosFiltrados() {
                    const origen = $("#filtroOrigen").val();
                    const destino = $("#filtroDestino").val();
                    const tipoViaje = $("#filtroTipoViaje").val();
                    const fecha = $("#filtroFecha").val();

                    return eventosLaravel.filter(e => {
                        const p = e.extendedProps;
                        if (origen && p.origen !== origen) return false;
                        if (destino && p.destino !== destino) return false;
                        if (tipoViaje && String(p.tipo_viaje_id) !== tipoViaje) return false;
                        if (fecha && !e.start.startsWith(fecha)) return false;
                        return true;
                    });
                }

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: "dayGridMonth",
                    locale: "es",
                    height: "auto",
                    headerToolbar: {
                        left: "prev,next today",
                        center: "title",
                        right: ""
                    },

                    events: (info, success) => success(eventosFiltrados()),

                    eventContent(info) {
                        const p = info.event.extendedProps;
                        const color = info.event.backgroundColor || '#007bff';
                        return {
                            html: `
                        <div style="background:${color}; border-radius:6px; padding:3px 5px; color:white; font-size:12px; line-height:1.5;">
                            <div>📌 ${p.tipo_viaje ?? ''}</div>
                            <div>🕐 ${p.hora ?? ''}</div>
                            <div>📍 ${p.origen ?? ''}</div>
                            <div>🏁 ${p.destino ?? ''}</div>
                            <div>🚌 ${p.vehiculo ?? ''} — 💰 S/. ${p.costo ?? ''}</div>
                        </div>
                    `
                        };
                    },

                    eventDidMount(info) {
                        // Transparenta el wrapper para que no haya doble fondo
                        info.el.style.background = 'transparent';
                        info.el.style.border = 'none';
                        info.el.style.padding = '0';
                        info.el.style.marginBottom = '2px';
                    },

                    eventClick(info) {
                        $("#modalVerHorarios").modal("show");
                        $("#verTitulo").text(info.event.title);
                    }
                });

                calendar.render();

                $("#filtroOrigen, #filtroDestino, #filtroTipoViaje").on("change", () => calendar
                    .refetchEvents());
                $("#filtroFecha").on("change", function() {
                    if (this.value) calendar.gotoDate(this.value);
                    calendar.refetchEvents();
                });
                $("#btnLimpiarFiltros").on("click", function() {
                    $("#filtroOrigen, #filtroDestino, #filtroTipoViaje, #filtroFecha").val('');
                    calendar.refetchEvents();
                });

                document.getElementById("btnHoy").onclick = () => calendar.today();
                document.getElementById("btnSemana").onclick = () => calendar.changeView("timeGridWeek");
                document.getElementById("btnMes").onclick = () => calendar.changeView("dayGridMonth");

            }, 50);
        });
    </script>
@endpush
