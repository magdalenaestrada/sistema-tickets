@extends('layouts.app')

@section('content')
    <div class="container">

        @include('reportes.partials.filtros')

        @include('reportes.partials.dashboard-ventas')

        @include('reportes.partials.dashboard-pasajeros')

        @include('reportes.partials.dashboard-viajes')

        @include('reportes.partials.dashboard-encomiendas')

        @include('reportes.partials.dashboard-cupones')

        @include('reportes.partials.dashboard-vehiculos')

    </div>
@endsection
@push('js')
    <script src="{{ asset('js/reportes.js') }}"></script>
@endpush
