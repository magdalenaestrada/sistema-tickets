<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\MetodoPago;
use App\Models\SubtipoMovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CajaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->sucursal_id) {
            $cajas = Caja::with('sucursal', 'usuario')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return view('caja.index', compact('cajas'));
        }

        $caja = Caja::where('sucursal_id', $user->sucursal_id)
            ->where('estado', 'A')
            ->latest()
            ->first();

        if (!$caja) {
            return redirect()
                ->route('caja.index')
                ->with('warning', 'No hay una caja activa para tu sucursal.');
        }

        return redirect()->route('caja.show', $caja);
    }
    public function store(Request $request)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0'
        ]);
        $user = Auth::user();
        $caja = Caja::create([
            'usuario_id' => $user->id,
            'sucursal_id' => $user->sucursal_id,
            'monto_apertura' => $request->monto_apertura,
            'fecha_creacion' => now()
        ]);

        return redirect()->route('caja.index')->with('success', 'Caja creada correctamente');
    }

    public function show(Caja $caja)
    {
        $caja->load('detalles.subtipo', 'detalles.metodoPago');

        $subtiposIngreso = SubtipoMovimientoCaja::whereHas('tipo_movimiento', function ($q) {
            $q->where('id', '1');
        })->get();

        $subtiposSalida = SubtipoMovimientoCaja::whereHas('tipo_movimiento', function ($q) {
            $q->where('id', '2');
        })->get();

        $totalIngresos = $caja->detalles()
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalSalidas = $caja->detalles()
            ->where('amount', '<', 0)
            ->sum('amount');

        $montoActual = $caja->monto_apertura + $totalIngresos + $totalSalidas;


        $metodosPago = MetodoPago::all();

        return view('caja.show', compact(
            'caja',
            'subtiposIngreso',
            'subtiposSalida',
            'metodosPago',
            'totalIngresos',
            'totalSalidas',
            'montoActual'
        ));
    }


    public function cerrar(Caja $caja)
    {
        $caja->update([
            'monto_cierre' => $caja->monto_actual,
            'estado' => 'C',
            'fecha_cierre' => now()
        ]);
        return redirect()->route('caja.index')->with('success', 'Caja cerrada correctamente');
    }

    public function registrarIngreso(Caja $caja, Request $request)
    {
        $request->validate([
            'subtipo_movimiento_caja_id' => 'required|exists:subtipo_movimiento_caja,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'amount' => 'required|numeric|min:0'
        ]);

        CajaDetalle::create([
            'caja_id' => $caja->id,
            'subtipo_movimiento_caja_id' => $request->subtipo_movimiento_caja_id,
            'metodo_pago_id' => $request->metodo_pago_id,
            'amount' => $request->amount,
            'description' => $request->description
        ]);

        return redirect()->back()->with('success', 'Ingreso registrado');
    }

    public function registrarSalida(Caja $caja, Request $request)
    {
        $request->validate([
            'subtipo_movimiento_caja_id' => 'required|exists:subtipo_movimiento_caja,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'amount' => 'required|numeric|min:0'
        ]);

        CajaDetalle::create([
            'caja_id' => $caja->id,
            'subtipo_movimiento_caja_id' => $request->subtipo_movimiento_caja_id,
            'metodo_pago_id' => $request->metodo_pago_id,
            'amount' => -1 * $request->amount,
            'description' => $request->description
        ]);

        return redirect()->back()->with('success', 'Salida registrada');
    }

    public function reimprimir(CajaDetalle $detalle)
    {
        $detalle->load('caja.usuario.sucursal.empresa', 'subtipo.tipo_movimiento', 'metodoPago');

        return view('caja.ticket', compact('detalle'));
    }

    public function anular(CajaDetalle $detalle)
    {
        if ($detalle->anulado) {
            return back()->with('error', 'El ticket ya está anulado.');
        }

        $detalle->update([
            'anulado' => true,
        ]);

        return back()->with('success', 'Ticket anulado correctamente.');
    }

    public function print_corte(int $caja)
    {
        $usuario = Auth::user();
        $caja = Caja::findOrFail($caja);
        return view('caja.corte_ticket', compact('caja', 'usuario'));
    }
}
