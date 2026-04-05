@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Gestión de Cajas</h2>
                <p class="text-muted mb-0">Vista general de todas las cajas</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if ($cajas->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Cajero</th>
                                    <th>Sucursal</th>
                                    <th>Apertura</th>
                                    <th>Cierre</th>
                                    <th>Monto apertura</th>
                                    <th>Monto cierre</th>
                                    <th>Estado</th>
                                    <th width="140">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cajas as $caja)
                                    <tr>
                                        <td>{{ $caja->id }}</td>
                                        <td>{{ $caja->usuario->persona->nombre_completo ?? '---' }}</td>
                                        <td>{{ $caja->sucursal->nombre_comercial ?? '---' }}</td>
                                        <td>{{ optional($caja->fecha_creacion)->format('d/m/Y h:i A') }}</td>
                                        <td>{{ optional($caja->fecha_cierre)->format('d/m/Y h:i A') ?? '---' }}</td>
                                        <td>S/ {{ number_format($caja->monto_apertura, 2) }}</td>
                                        <td>
                                            {{ $caja->monto_cierre !== null ? 'S/ ' . number_format($caja->monto_cierre, 2) : '---' }}
                                        </td>
                                        <td>
                                            @if (in_array($caja->estado, ['C', 'cerrada']))
                                                <span class="badge bg-danger">Cerrada</span>
                                            @else
                                                <span class="badge bg-success">Abierta</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('caja.show', $caja->id) }}" class="btn btn-sm btn-primary">
                                                Ver
                                            </a>
                                            <a href="{{ route('caja.print_corte', $caja->id) }}" target="_blank"
                                                class="btn btn-sm btn-dark">
                                                Corte
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $cajas->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="mb-0 text-muted">No hay cajas registradas.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
