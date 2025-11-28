@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Cajas</h1>

        <button id="btnAbrirCaja" class="btn btn-primary">
            Abrir Caja
        </button>

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
                                <a href="{{ route('caja.show', $caja) }}" class="btn btn-sm btn-info">Ver</a>

                                <form action="{{ route('caja.cerrar', $caja) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Cerrar</button>
                                </form>
                            @endif
                            @if ($caja->estado === 'C')
                                <a href="{{ route('caja.corte.imprimir') }}" target="_blank" class="btn btn-primary">
                                    Imprimir Corte de Caja
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $cajas->links() }}
    </div>
    @include('caja.modals.create')
@endsection
@push('scripts')
    <script src="{{ asset('js/caja.js') }}"></script>
@endpush
