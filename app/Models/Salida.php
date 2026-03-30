<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Salida extends Model
{
    protected $table = 'salidas';

    protected $fillable = [
        'horario_id',
        'fecha_salida',
        'estado',
    ];

    protected $casts = [
        'fecha_salida' => 'date:Y-m-d',
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function pasajes()
    {
        return $this->hasMany(Pasaje::class);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha_salida
            ? Carbon::parse($this->fecha_salida)->format('d/m/Y')
            : null;
    }

    public function getHoraSalidaAttribute()
    {
        return $this->horario?->hora_formateada;
    }

    public function getHoraLlegadaAttribute()
    {
        return $this->horario?->hora_llegada;
    }

    public function obtenerTramosDeViaje($origenSucursalId, $destinoSucursalId)
    {
        $ruta = $this->horario?->ruta;

        if (!$ruta) {
            return collect();
        }

        $puntos = $ruta->puntos()->orderBy('orden')->get();

        $puntoOrigen = $puntos->firstWhere('sucursal_id', $origenSucursalId);
        $puntoDestino = $puntos->firstWhere('sucursal_id', $destinoSucursalId);

        if (!$puntoOrigen || !$puntoDestino) {
            return collect();
        }

        if ($puntoOrigen->orden >= $puntoDestino->orden) {
            return collect();
        }

        return $ruta->tramos()
            ->with(['origen', 'destino'])
            ->get()
            ->filter(function ($tramo) use ($puntoOrigen, $puntoDestino) {
                if (!$tramo->origen || !$tramo->destino) {
                    return false;
                }

                return $tramo->origen->orden >= $puntoOrigen->orden
                    && $tramo->destino->orden <= $puntoDestino->orden;
            })
            ->sortBy(function ($tramo) {
                return $tramo->origen?->orden ?? 9999;
            })
            ->values();
    }

    public function asientosDisponibles($origenSucursalId, $destinoSucursalId)
    {
        $tramos = $this->obtenerTramosDeViaje($origenSucursalId, $destinoSucursalId);

        if ($tramos->isEmpty()) {
            return [];
        }

        $tramoIds = $tramos->pluck('id');

        $asientosOcupados = DB::table('pasajes')
            ->join('pasaje_tramos', 'pasajes.id', '=', 'pasaje_tramos.pasaje_id')
            ->where('pasajes.salida_id', $this->id)
            ->whereIn('pasaje_tramos.tramo_id', $tramoIds)
            ->whereIn('pasajes.estado', ['R', 'V'])
            ->pluck('pasajes.asiento_numero')
            ->unique()
            ->toArray();

        $totalAsientos = $this->horario?->tipo_vehiculo?->capacidad
            ?? $this->horario?->tipo_vehiculo?->asientos
            ?? 0;

        $asientos = [];

        for ($i = 1; $i <= $totalAsientos; $i++) {
            $asientos[$i] = in_array($i, $asientosOcupados) ? 'ocupado' : 'libre';
        }

        return $asientos;
    }

    public function calcularCostoPorTramos($origenSucursalId, $destinoSucursalId)
    {
        return $this->obtenerTramosDeViaje($origenSucursalId, $destinoSucursalId)
            ->sum('costo_tramo');
    }
}
