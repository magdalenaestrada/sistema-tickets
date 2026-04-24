@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
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

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body py-3">
                        <form id="form-filtros-caja" method="GET" action="{{ route('caja.index') }}"
                            class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label for="filtro_sucursal" class="form-label mb-1">Sucursal</label>
                                <select name="sucursal_id" id="filtro_sucursal" class="form-select form-select-sm">
                                    <option value="">Todas las sucursales</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}"
                                            {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_comercial }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filtro_estado" class="form-label mb-1">Estado</label>
                                <select name="estado" id="filtro_estado" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                    <option value="abierta" {{ request('estado') == 'abierta' ? 'selected' : '' }}>Abiertas
                                    </option>
                                    <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerradas
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4 d-flex gap-2 align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    Filtrar
                                </button>
                                <button type="button" id="btn-limpiar-filtros"
                                    class="btn btn-outline-secondary btn-sm w-100">
                                    Limpiar
                                </button>
                                <button type="button" class="btn btn-success btn-sm w-100" data-bs-toggle="modal"
                                    data-bs-target="#modalAbrirCaja">
                                    Abrir caja
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-2">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body py-3 px-3 d-flex flex-column justify-content-center">
                        <span class="text-muted small">Total efectivo</span>
                        <h5 class="mb-0 fw-bold">S/ {{ number_format($totalEfectivo ?? 0, 2) }}</h5>
                        <small class="text-muted">Según filtros aplicados</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
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
                                    <th>Apertura</th>
                                    <th> cierre</th>
                                    <th>Estado</th>
                                    <th width="140">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cajas as $caja)
                                    <tr>
                                        <td>Caja {{ $caja->numero_visual }}</td>
                                        <td>{{ $caja->usuario->persona->nombre_completo ?? ($caja->usuario->name ?? '---') }}
                                        </td>
                                        <td>{{ $caja->sucursal->nombre_comercial ?? '---' }}</td>
                                        <td>{{ optional($caja->fecha_creacion)->format('d/m/Y h:i A') }}</td>
                                        <td>
                                            {{ $caja->fecha_cierre ? optional($caja->fecha_cierre)->format('d/m/Y h:i A') : '---' }}
                                        </td>
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
    @include('caja.modals.abrir_caja_admin')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnLimpiar = document.getElementById('btn-limpiar-filtros');

            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', function() {
                    window.location.href = "{{ route('caja.index') }}";
                });
            }
        });
    </script>
@endsection
