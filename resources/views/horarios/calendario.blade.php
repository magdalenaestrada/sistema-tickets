@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Calendario Semanal de Horarios</h5>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    @include('horarios.modals.ver') <!-- Modal separado en otro archivo -->
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script src="{{ asset('js/horarios.js') }}"></script>
@endpush
