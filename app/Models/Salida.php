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
        'vehiculo_id',
        'conductor_principal_id',
        'conductor_secundario_id',
        'fecha_cambio_estado',
        'hora_cambio_estado',
        'motivo_cambio_estado',
        'usuario_cambio_estado_id'
    ];

    protected $casts = [
        'fecha_salida' => 'date',
        'fecha_cambio_estado' => 'date',
        'hora_cambio_estado' => 'datetime:H:i',
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

        $tramoIds = $tramos->pluck('id')->toArray();

        $pasajes = DB::table('pasajes')
            ->join('pasaje_tramos', 'pasajes.id', '=', 'pasaje_tramos.pasaje_id')
            ->where('pasajes.salida_id', $this->id)
            ->whereIn('pasaje_tramos.tramo_id', $tramoIds)
            ->whereIn('pasajes.estado', ['R', 'V'])
            ->select('pasajes.asiento_numero', 'pasajes.estado')
            ->distinct()
            ->get()
            ->groupBy('asiento_numero');

        $totalAsientos = $this->horario?->tipo_vehiculo?->capacidad
            ?? $this->horario?->tipo_vehiculo?->asientos
            ?? 0;

        $asientos = [];

        for ($i = 1; $i <= $totalAsientos; $i++) {
            if (!isset($pasajes[$i])) {
                $asientos[$i] = 'libre';
                continue;
            }

            $estados = $pasajes[$i]->pluck('estado')->unique();

            if ($estados->contains('V')) {
                $asientos[$i] = 'ocupado';
            } elseif ($estados->contains('R')) {
                $asientos[$i] = 'reservado';
            } else {
                $asientos[$i] = 'libre';
            }
        }

        return $asientos;
    }

    public function calcularCostoPorTramos($origenSucursalId, $destinoSucursalId)
    {
        return $this->obtenerTramosDeViaje($origenSucursalId, $destinoSucursalId)
            ->sum('costo_tramo');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function conductorPrincipal()
    {
        return $this->belongsTo(Persona::class, 'conductor_principal_id');
    }

    public function conductorSecundario()
    {
        return $this->belongsTo(Persona::class, 'conductor_secundario_id');
    }

    public function asignacionesEncomienda()
    {
        return $this->hasMany(EncomiendaSalida::class, 'salida_id');
    }

    public function encomiendas()
    {
        return $this->belongsToMany(Encomienda::class, 'encomienda_salida')
            ->withPivot(['usuario_id', 'fecha_asignacion', 'fecha_llegada', 'estado'])
            ->withTimestamps();
    }

    public function puedeTransportarEncomienda($origenSucursalId, $destinoSucursalId)
    {
        $ruta = $this->horario?->ruta;

        if (!$ruta) {
            return false;
        }

        $puntos = $ruta->puntos()->orderBy('orden')->get();

        if ($puntos->isEmpty()) {
            return false;
        }

        $primerPunto = $puntos->first();
        $ultimoPunto = $puntos->last();

        $tipoViajeId = (int) ($this->horario?->tipo_viaje_id ?? 0);

        if ($tipoViajeId === 1) {
            return (int) $primerPunto?->sucursal_id === (int) $origenSucursalId
                && (int) $ultimoPunto?->sucursal_id === (int) $destinoSucursalId;
        }

        return $this->obtenerTramosDeViaje($origenSucursalId, $destinoSucursalId)->isNotEmpty();
    }
}
