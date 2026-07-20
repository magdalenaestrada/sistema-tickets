@extends('layouts.app')

@section('title', 'Solicitud de Anulación')

@section('content')

    <div class="container-fluid">

        <div class="card shadow">

            <div class="card-header">
                <h5>Solicitud de Anulación</h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6">
                        <label>Documento</label>
                        <input class="form-control" value="{{ $solicitud->venta->serie }}-{{ $solicitud->venta->numero }}"
                            readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Cliente</label>
                        <input class="form-control" value="{{ $solicitud->venta->persona?->nombre_facturacion }}" readonly>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Solicitado por</label>
                        <input class="form-control" value="{{ $solicitud->solicitante->persona?->nombre_completo }}"
                            readonly>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Fecha</label>
                        <input class="form-control" value="{{ $solicitud->fecha_solicitud->format('d/m/Y H:i') }}" readonly>
                    </div>

                    <div class="col-12 mt-3">
                        <label>Motivo</label>
                        <textarea class="form-control" rows="5" readonly>{{ $solicitud->motivo }}</textarea>
                    </div>

                </div>

            </div>

            @if ($solicitud->estado == 'Pendiente')
                <div class="card-footer text-end">

                    <form action="{{ route('solicitudes.aprobar', $solicitud) }}" method="POST" class="d-inline">

                        @csrf

                        <button class="btn btn-success">
                            Aprobar y Anular
                        </button>
                    </form>

                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                        Rechazar
                    </button>

                </div>
            @endif

        </div>

    </div>

@endsection
