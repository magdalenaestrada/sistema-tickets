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
            background-color: #007bff;
            color: white;
            padding: 3px 6px;
            font-size: 13px;
        }

        .fc-event:hover {
            background-color: #0056b3;
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
    <script src="{{ asset('js/calendarios.js') }}"></script>
@endpush
