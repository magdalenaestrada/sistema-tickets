<?php

namespace App\Http\Controllers;

use App\Models\BilleteraDigital;
use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\MetodoPago;
use App\Models\SubtipoMovimientoCaja;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($this->esAdmin($user)) {
            $query = Caja::with(['sucursal', 'usuario.persona']);

            if ($request->filled('sucursal_id')) {
                $query->where('sucursal_id', $request->sucursal_id);
            }

            if ($request->filled('estado')) {
                if ($request->estado === 'abierta') {
                    $query->whereIn('estado', ['A', 'abierta']);
                } elseif ($request->estado === 'cerrada') {
                    $query->whereIn('estado', ['C', 'cerrada']);
                }
            }

            $cajas = $query
                ->orderByDesc('fecha_creacion')
                ->paginate(15)
                ->appends($request->query());

            $totalEfectivo = (clone $query)->sum('monto_apertura');

            // sumar solo cajas abiertas:
            // $totalEfectivo = (clone $query)
            //     ->whereIn('estado', ['A', 'abierta'])
            //     ->sum('monto_apertura');

            $sucursales = Sucursal::orderBy('nombre_comercial')->get();

            return view('caja.index_admin', compact('cajas', 'sucursales', 'totalEfectivo'));
        }

        $cajaAbierta = Caja::with(['sucursal', 'usuario.persona'])
            ->where('usuario_id', $user->id)
            ->whereIn('estado', ['A', 'abierta'])
            ->orderByDesc('fecha_creacion')
            ->first();

        if ($cajaAbierta) {
            return redirect()->route('caja.show', $cajaAbierta->id);
        }

        $cajas = Caja::with(['sucursal', 'usuario.persona'])
            ->where('usuario_id', $user->id)
            ->orderByDesc('fecha_creacion')
            ->paginate(15);

        return view('caja.index_cajero', compact('cajas'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'monto_apertura' => 'required|numeric|min:0',
        ];

        if ($this->esAdmin($user)) {
            $rules['sucursal_id'] = 'required|exists:sucursales,id';
        }

        $request->validate($rules);

        $sucursalId = $this->esAdmin($user)
            ? (int) $request->sucursal_id
            : (int) $user->sucursal_id;

        if ($this->esAdmin($user)) {
            $cajaAbierta = Caja::where('sucursal_id', $sucursalId)
                ->whereIn('estado', ['A', 'abierta'])
                ->first();

            if ($cajaAbierta) {
                return back()
                    ->withInput()
                    ->with('error', 'Ya existe una caja abierta en esa sucursal.');
            }
        } else {
            $cajaAbierta = Caja::where('usuario_id', $user->id)
                ->whereIn('estado', ['A', 'abierta'])
                ->first();

            if ($cajaAbierta) {
                return redirect()
                    ->route('caja.show', $cajaAbierta->id)
                    ->with('error', 'Ya tienes una caja abierta.');
            }
        }

        DB::beginTransaction();

        try {
            $caja = Caja::create([
                'usuario_id'      => $user->id,
                'sucursal_id'     => $sucursalId,
                'monto_apertura'  => $request->monto_apertura,
                'estado'          => 'A',
                'fecha_creacion'  => now(),
            ]);

            CajaDetalle::create([
                'caja_id'                    => $caja->id,
                'subtipo_movimiento_caja_id' => 10,
                'metodo_pago_id'             => 1,
                'amount'                     => abs($request->monto_apertura),
                'description'                => 'Apertura de caja',
                'anulado'                    => false,
            ]);

            DB::commit();

            return redirect()
                ->route('caja.show', $caja->id)
                ->with('success', 'Caja abierta correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'No se pudo abrir la caja.');
        }
    }

    public function show(Caja $caja)
    {
        $this->autorizarCaja($caja);

        $caja->load([
            'usuario',
            'sucursal',
            'detalles' => function ($q) {
                $q->with(['subtipo.tipo_movimiento', 'metodoPago'])
                    ->orderByDesc('created_at');
            }
        ]);

        $subtiposIngreso = SubtipoMovimientoCaja::whereHas('tipo_movimiento', function ($q) {
            $q->where('id', 1);
        })->get();

        $subtiposSalida = SubtipoMovimientoCaja::whereHas('tipo_movimiento', function ($q) {
            $q->where('id', 2);
        })->get();

        $metodosPago = MetodoPago::all();

        $billeterasDigitales = BilleteraDigital::all();

        return view('caja.show', compact(
            'caja',
            'subtiposIngreso',
            'subtiposSalida',
            'metodosPago',
            'billeterasDigitales'
        ));
    }

    public function registrarIngreso(Caja $caja, Request $request)
    {
        $this->autorizarCaja($caja);
        $this->validarCajaAbierta($caja);

        $request->validate([
            'subtipo_movimiento_caja_id' => 'required|exists:subtipo_movimiento_caja,id',
            'metodo_pago_id'             => 'required|exists:metodo_pago,id',
            'amount'                     => 'nullable|numeric|min:0.01',
            'monto_efectivo'             => 'nullable|numeric|min:0.01',
            'monto_digital'              => 'nullable|numeric|min:0.01',
            'description'                => 'nullable|string|max:500',
            'table_name'                 => 'nullable|string|max:255',
            'table_id'                   => 'nullable|integer',
            'billetera_digital_id'       => 'nullable|integer',
        ]);

        try {
            DB::transaction(function () use ($request, $caja) {
                if ($request->metodo_pago_id != '3') {
                    CajaDetalle::create([
                        'caja_id'                    => $caja->id,
                        'subtipo_movimiento_caja_id' => $request->subtipo_movimiento_caja_id,
                        'metodo_pago_id'             => $request->metodo_pago_id,
                        'table_name'                 => $request->table_name,
                        'table_id'                   => $request->table_id,
                        'billetera_digital_id'       => $request->billetera_digital_id,
                        'amount'                     => abs($request->amount),
                        'description'                => $request->description,
                        'anulado'                    => false,
                    ]);
                } else {
                    if ((float) $request->monto_efectivo > 0) {
                        CajaDetalle::create([
                            'caja_id'                    => $caja->id,
                            'subtipo_movimiento_caja_id' => $request->subtipo_movimiento_caja_id,
                            'metodo_pago_id'             => 1, // efectivo
                            'table_name'                 => $request->table_name,
                            'table_id'                   => $request->table_id,
                            'billetera_digital_id'       => null,
                            'amount'                     => abs($request->monto_efectivo),
                            'description'                => $request->description,
                            'anulado'                    => false,
                        ]);
                    }

                    if ((float) $request->monto_digital > 0) {
                        CajaDetalle::create([
                            'caja_id'                    => $caja->id,
                            'subtipo_movimiento_caja_id' => $request->subtipo_movimiento_caja_id,
                            'metodo_pago_id'             => 2, // digital o el id que corresponda
                            'table_name'                 => $request->table_name,
                            'table_id'                   => $request->table_id,
                            'billetera_digital_id'       => $request->billetera_digital_id,
                            'amount'                     => abs($request->monto_digital),
                            'description'                => $request->description,
                            'anulado'                    => false,
                        ]);
                    }
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                $caja->load([
                    'detalles.subtipo',
                    'detalles.metodoPago'
                ]);

                $tabla = view('caja.partials.tabla_movimientos', compact('caja'))->render();

                return response()->json([
                    'success' => true,
                    'message' => 'Ingreso registrado correctamente.',
                    'tabla'   => $tabla,
                ]);
            }

            return redirect()->back()->with('success', 'Ingreso registrado correctamente.');
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function registrarSalida(Caja $caja, Request $request)
    {
        $this->autorizarCaja($caja);
        $this->validarCajaAbierta($caja);

        $request->validate([
            'subtipo_movimiento_caja_id' => 'required|exists:subtipo_movimiento_caja,id',
            'metodo_pago_id'             => 'required|exists:metodo_pago,id',
            'amount'                     => 'nullable|numeric|min:0.01',
            'monto_efectivo'             => 'nullable|numeric|min:0.01',
            'monto_digital'              => 'nullable|numeric|min:0.01',
            'description'                => 'nullable|string|max:500',
            'table_name'                 => 'nullable|string|max:255',
            'table_id'                   => 'nullable|integer',
            'billetera_digital_id'       => 'nullable|integer',
        ]);

        try {
            DB::transaction(function () use ($request, $caja) {
                if ($request->metodo_pago_id != '3') {
                    CajaDetalle::create([
                        'caja_id'                    => $caja->id,
                        'subtipo_movimiento_caja_id' => $request->subtipo_movimiento_caja_id,
                        'metodo_pago_id'             => $request->metodo_pago_id,
                        'table_name'                 => $request->table_name,
                        'table_id'                   => $request->table_id,
                        'billetera_digital_id'       => $request->billetera_digital_id,
                        'amount'                     => -abs($request->amount),
                        'description'                => $request->description,
                        'anulado'                    => false,
                    ]);
                } else {
                    if ((float) $request->monto_efectivo > 0) {
                        CajaDetalle::create([
                            'caja_id'                    => $caja->id,
                            'subtipo_movimiento_caja_id' => $request->subtipo_movimiento_caja_id,
                            'metodo_pago_id'             => 1, // efectivo
                            'table_name'                 => $request->table_name,
                            'table_id'                   => $request->table_id,
                            'billetera_digital_id'       => null,
                            'amount'                     => -abs($request->monto_efectivo),
                            'description'                => $request->description,
                            'anulado'                    => false,
                        ]);
                    }

                    if ((float) $request->monto_digital > 0) {
                        CajaDetalle::create([
                            'caja_id'                    => $caja->id,
                            'subtipo_movimiento_caja_id' => $request->subtipo_movimiento_caja_id,
                            'metodo_pago_id'             => 2, // digital o el id que corresponda
                            'table_name'                 => $request->table_name,
                            'table_id'                   => $request->table_id,
                            'billetera_digital_id'       => $request->billetera_digital_id,
                            'amount'                     => -abs($request->monto_digital),
                            'description'                => $request->description,
                            'anulado'                    => false,
                        ]);
                    }
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                $caja->load([
                    'detalles.subtipo',
                    'detalles.metodoPago'
                ]);

                $tabla = view('caja.partials.tabla_movimientos', compact('caja'))->render();

                return response()->json([
                    'success' => true,
                    'message' => 'Ingreso registrado correctamente.',
                    'tabla'   => $tabla,
                ]);
            }

            return redirect()->back()->with('success', 'Ingreso registrado correctamente.');
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }



    public function cerrar(Caja $caja)
    {
        $this->autorizarCaja($caja);

        if ($this->cajaCerrada($caja)) {
            return back()->with('error', 'La caja ya está cerrada.');
        }

        $caja->refresh();

        $caja->update([
            'monto_cierre' => $caja->efectivo_esperado,
            'estado'       => 'C',
            'fecha_cierre' => now(),
        ]);

        return redirect()
            ->route('caja.show', $caja->id)
            ->with('success', 'Caja cerrada correctamente.');
    }

    public function print_corte(int $cajaId)
    {
        $usuario = auth()->user();

        $caja = Caja::with([
            'usuario',
            'sucursal.empresa',
            'detalles' => function ($q) {
                $q->with(['subtipo.tipo_movimiento', 'metodoPago'])
                    ->orderBy('created_at');
            }
        ])->findOrFail($cajaId);

        if (!$this->esAdmin($usuario) && $caja->usuario_id !== $usuario->id) {
            abort(403, 'No tienes permiso para imprimir este corte.');
        }

        return view('caja.corte_ticket', compact('caja', 'usuario'));
    }

    public function reimprimir(CajaDetalle $detalle)
    {
        $detalle->load([
            'caja.usuario.sucursal.empresa',
            'subtipo.tipo_movimiento',
            'metodoPago'
        ]);

        $user = auth()->user();

        if (!$this->esAdmin($user) && $detalle->caja->usuario_id !== $user->id) {
            abort(403, 'No tienes permiso para reimprimir este ticket.');
        }

        return view('caja.ticket', compact('detalle'));
    }

    public function anular(CajaDetalle $detalle)
    {
        $detalle->load('caja');

        $this->autorizarCaja($detalle->caja);
        $this->validarCajaAbierta($detalle->caja);

        if ($detalle->anulado) {
            return back()->with('error', 'El ticket ya está anulado.');
        }

        $detalle->update([
            'anulado' => true,
        ]);

        return back()->with('success', 'Ticket anulado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos privados
    |--------------------------------------------------------------------------
    */

    private function esAdmin($user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Super Administrador']);
    }

    private function autorizarCaja(Caja $caja): void
    {
        $user = auth()->user();

        if ($this->esAdmin($user)) {
            return;
        }

        if ((int) $caja->usuario_id !== (int) $user->id) {
            abort(403, 'No tienes permiso para acceder a esta caja.');
        }
    }

    private function validarCajaAbierta(Caja $caja): void
    {
        if ($this->cajaCerrada($caja)) {
            abort(422, 'La caja está cerrada y no admite más movimientos.');
        }
    }

    private function cajaCerrada(Caja $caja): bool
    {
        return in_array($caja->estado, ['C', 'cerrada']);
    }
}
