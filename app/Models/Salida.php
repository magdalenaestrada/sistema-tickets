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

    public function obtenerTramosDeViaje($origenPueblitoId, $destinoPueblitoId)
    {
        $ruta = $this->horario?->ruta;

        if (!$ruta) {
            return collect();
        }

        $puntos = $ruta->puntos()
            ->orderBy('orden')
            ->get();

        $puntoOrigen = $puntos->firstWhere(
            'pueblito_id',
            $origenPueblitoId
        );

        $puntoDestino = $puntos->firstWhere(
            'pueblito_id',
            $destinoPueblitoId
        );

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

    public function asientosDisponibles($origenPueblitoId, $destinoPueblitoId)
    {
        $tramos = $this->obtenerTramosDeViaje($origenPueblitoId, $destinoPueblitoId);
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

    public function calcularCostoPorTramos($origenPueblitoId, $destinoPueblitoId)
    {
        return $this->obtenerTramosDeViaje($origenPueblitoId, $destinoPueblitoId)->sum('costo_tramo');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function conductorPrincipal()
    {
        return $this->belongsTo(Empleado::class, 'conductor_principal_id');
    }

    public function conductorSecundario()
    {
        return $this->belongsTo(Empleado::class, 'conductor_secundario_id');
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

    public function puedeTransportarEncomienda($origenPueblitoId, $destinoPueblitoId)
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
            return (int) $primerPunto?->pueblito_id === (int) $origenPueblitoId
                && (int) $ultimoPunto?->pueblito_id === (int) $destinoPueblitoId;
        }

        return $this->obtenerTramosDeViaje($origenPueblitoId, $destinoPueblitoId)->isNotEmpty();
    }

    public function getEsVencidaAttribute()
    {
        return $this->fecha_salida < now()->toDateString()
            || ($this->fecha_salida == now()->toDateString()
                && $this->hora_salida < now()->format('H:i:s'));
    }

    public function puntoActual($sucursalId)
    {
        return $this->horario
            ->ruta
            ->puntos
            ->firstWhere('sucursal_id', $sucursalId);
    }

    public function siguientePunto($sucursalId)
    {
        $actual = $this->puntoActual($sucursalId);

        if (!$actual) {
            return null;
        }

        return $this->horario
            ->ruta
            ->puntos
            ->firstWhere('orden', $actual->orden + 1);
    }

    public function pasajerosEnTramo($sucursalId)
    {
        $ruta = $this->horario->ruta;

        $puntos = $ruta->puntos;

        $actual = $puntos->firstWhere('sucursal_id', $sucursalId);

        if (!$actual) {
            return collect();
        }

        return $this->pasajes
            ->whereIn('estado', ['V', 'F'])
            ->filter(function ($pasaje) use ($puntos, $actual) {

                $origen = $puntos->firstWhere(
                    'pueblito_id',
                    $pasaje->origen_id
                );

                $destino = $puntos->firstWhere(
                    'pueblito_id',
                    $pasaje->destino_id
                );

                if (!$origen || !$destino) {
                    return false;
                }

                return $origen->orden <= $actual->orden
                    && $destino->orden > $actual->orden;
            })
            ->sortBy('asiento_numero')
            ->values();
    }

    public function datosManifiesto($sucursalId, bool $origenPrimeraRuta = false)
    {
        $puntos = $this->horario->ruta->puntos->sortBy('orden')->values();

        $actual = $puntos->firstWhere('sucursal_id', $sucursalId);

        if (!$actual) {
            return null;
        }

        $primerPunto = $puntos->first();
        $ultimoPunto = $puntos->last();

        return [
            'origen' => $origenPrimeraRuta
                ? ($primerPunto->pueblito?->descripcion ?? '-')
                : ($actual->pueblito?->descripcion ?? '-'),

            'destino' => $ultimoPunto->pueblito?->descripcion ?? '-',

            'orden' => $actual->orden,
        ];
    }

    public function sucursalesRuta()
    {
        return $this->horario->ruta->puntos
            ->whereNotNull('sucursal_id')
            ->sortBy('orden')
            ->pluck('sucursal')
            ->filter()
            ->unique('id')
            ->values();
    }
}
