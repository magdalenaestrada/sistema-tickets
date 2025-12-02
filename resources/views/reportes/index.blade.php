@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">Reportes</div>
        <div class="card-body">
            <form action="{{ route('reportes.generar') }}" method="POST" target="_blank">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Tipo de reporte</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="ventas">Ventas</option>
                            <option value="pasajeros">Pasajeros</option>
                            <option value="cupones">Uso de Cupones</option>
                            <option value="equipaje">Equipaje</option>
                            <option value="viajes">Viajes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Fecha inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Fecha fin</label>
                        <input type="date" name="fecha_fin" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">Generar PDF</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
