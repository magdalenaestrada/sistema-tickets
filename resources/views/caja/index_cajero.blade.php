@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <strong>MIS CAJAS</strong>
            </div>

            <div class="row justify-content-center">


                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card-body">


                    <div class="card shadow-sm border-0 border-start border-4 border-success mb-4 bg-light">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                                    style="width: 45px; height: 45px;">
                                    <i class="bi bi-cash-stack fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 fw-bold">Abrir Nueva Caja</h5>
                                    <small class="text-muted">Ingresa el monto inicial en efectivo para iniciar
                                        turno</small>
                                </div>
                            </div>

                            <form action="{{ route('caja.store') }}" method="POST">
                                @csrf
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-7 col-lg-8">
                                        <label class="form-label fw-semibold text-secondary">Monto de apertura</label>
                                        <div class="input-group input-group-lg">
                                            <span
                                                class="input-group-text bg-white border-end-0 fw-bold text-success">S/</span>
                                            <input type="number" step="0.01" min="0" name="monto_apertura"
                                                class="form-control border-start-0 ps-0 fw-bold fs-4" placeholder="0.00"
                                                required autofocus>
                                        </div>
                                    </div>

                                    <div class="col-md-5 col-lg-4">
                                        <button type="submit"
                                            class="btn btn-success btn-lg w-100 shadow-sm py-2 fw-semibold">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Abrir Caja
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">

                <div class="card-body">
                    @if ($cajas->count())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
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
                                                <a href="{{ route('caja.show', $caja->id) }}"
                                                    class="btn btn-sm btn-primary">
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
                            <p class="mb-0 text-muted">Aún no tienes cajas registradas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
