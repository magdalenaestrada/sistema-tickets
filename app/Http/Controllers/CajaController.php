<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\MetodoPago;
use App\Models\SubtipoMovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($this->esAdmin($user)) {
            $cajas = Caja::with(['sucursal', 'usuario'])
                ->orderByDesc('fecha_creacion')
                ->paginate(15);

            return view('caja.index_admin', compact('cajas'));
        }

        $cajaAbierta = Caja::with(['sucursal', 'usuario'])
            ->where('usuario_id', $user->id)
            ->whereIn('estado', ['A', 'abierta'])
            ->orderByDesc('fecha_creacion')
            ->first();

        if ($cajaAbierta) {
            return redirect()->route('caja.show', $cajaAbierta->id);
        }

        $cajas = Caja::with(['sucursal', 'usuario'])
            ->where('usuario_id', $user->id)
            ->orderByDesc('fecha_creacion')
            ->paginate(15);

        return view('caja.index_cajero', compact('cajas'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        $cajaAbierta = Caja::where('usuario_id', $user->id)
            ->whereIn('estado', ['A', 'abierta'])
            ->first();

        if ($cajaAbierta) {
            return redirect()
                ->route('caja.show', $cajaAbierta->id)
                ->with('error', 'Ya tienes una caja abierta.');
        }

        $caja = Caja::create([
            'usuario_id'      => $user->id,
            'sucursal_id'     => $user->sucursal_id,
            'monto_apertura'  => $request->monto_apertura,
            'estado'          => 'A',
            'fecha_creacion'  => now(),
        ]);

        return redirect()
            ->route('caja.show', $caja->id)
            ->with('success', 'Caja abierta correctamente.');
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

        return view('caja.show', compact(
            'caja',
            'subtiposIngreso',
            'subtiposSalida',
            'metodosPago'
        ));
    }

    public function registrarIngreso(Caja $caja, Request $request)
    {
        $this->autorizarCaja($caja);
        $this->validarCajaAbierta($caja);

        $request->validate([
            'subtipo_movimiento_caja_id' => 'required|exists:subtipo_movimiento_caja,id',
            'metodo_pago_id'             => 'required|exists:metodo_pago,id',
            'amount'                     => 'required|numeric|min:0.01',
            'description'                => 'nullable|string|max:500',
            'table_name'                 => 'nullable|string|max:255',
            'table_id'                   => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $caja) {
            CajaDetalle::create([
                'caja_id'                     => $caja->id,
                'subtipo_movimiento_caja_id'  => $request->subtipo_movimiento_caja_id,
                'metodo_pago_id'              => $request->metodo_pago_id,
                'table_name'                  => $request->table_name,
                'table_id'                    => $request->table_id,
                'amount'                      => abs($request->amount),
                'description'                 => $request->description,
                'anulado'                     => false,
            ]);
        });

        return back()->with('success', 'Ingreso registrado correctamente.');
    }

    public function registrarSalida(Caja $caja, Request $request)
    {
        $this->autorizarCaja($caja);
        $this->validarCajaAbierta($caja);

        $request->validate([
            'subtipo_movimiento_caja_id' => 'required|exists:subtipo_movimiento_caja,id',
            'metodo_pago_id'             => 'required|exists:metodo_pago,id',
            'amount'                     => 'required|numeric|min:0.01',
            'description'                => 'nullable|string|max:500',
            'table_name'                 => 'nullable|string|max:255',
            'table_id'                   => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $caja) {
            CajaDetalle::create([
                'caja_id'                     => $caja->id,
                'subtipo_movimiento_caja_id'  => $request->subtipo_movimiento_caja_id,
                'metodo_pago_id'              => $request->metodo_pago_id,
                'table_name'                  => $request->table_name,
                'table_id'                    => $request->table_id,
                'amount'                      => -1 * abs($request->amount),
                'description'                 => $request->description,
                'anulado'                     => false,
            ]);
        });

        return back()->with('success', 'Salida registrada correctamente.');
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
