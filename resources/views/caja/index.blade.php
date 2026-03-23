@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Gestión de caja</h5>
            <div class="d-flex gap-2">
                <button id="btnAbrirCaja" class="btn btn-primary">
                    Abrir Caja
                </button>
            </div>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Sucursal</th>
                        <th>Monto Apertura</th>
                        <th>Monto Actual</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cajas as $caja)
                        <tr>
                            <td>{{ $caja->id }}</td>
                            <td>{{ $caja->usuario->persona->nombres }} {{ $caja->usuario->persona->apellidos }}</td>
                            <td>{{ $caja->sucursal->nombre_comercial }}</td>
                            <td>{{ number_format($caja->monto_apertura, 2) }}</td>
                            <td>{{ number_format($caja->monto_actual, 2) }}</td>
                            <td>{{ $caja->estado }}</td>
                            <td>{{ $caja->fecha_creacion->format('Y-m-d') }}</td>
                            <td>
                                @if ($caja->estado === 'A')
                                    <a href="{{ route('caja.show', $caja) }}" class="btn btn-xs" title="Ver">
                                        <i data-lucide="info"></i>
                                    </a>

                                    <form action="{{ route('caja.cerrar', $caja) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-danger" title="Cerrar caja"
                                            onclick="return confirm('¿Seguro que deseas cerrar la caja?')">
                                            <i data-lucide="lock"></i>
                                        </button>
                                    </form>

                                    <a href="{{ route('caja.corte.imprimir', $caja->id) }}" target="_blank"
                                        class="btn btn-xs btn-primary" title="Imprimir corte">
                                        <i data-lucide="printer"></i>
                                    </a>
                                @endif
                                @if ($caja->estado === 'C')
                                    <a href="{{ route('caja.corte.imprimir', $caja->id) }}" target="_blank"
                                        class="btn btn-primary">
                                        Imprimir Corte de Caja
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $cajas->links() }}
    </div>
    @include('caja.modals.create')
@endsection
@push('scripts')
    <script src="{{ asset('js/caja.js') }}"></script>
@endpush
