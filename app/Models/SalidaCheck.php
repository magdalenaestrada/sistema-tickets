<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalidaCheck extends Model
{
    protected $fillable = ['salida_id', 'punto_id', 'usuario_id', 'registrado_en'];

    protected $casts = [
        'registrado_en' => 'datetime',
    ];

    public function salida()
    {
        return $this->belongsTo(Salida::class);
    }

    public function punto()
    {
        return $this->belongsTo(RutaPunto::class, 'punto_id'); // ajusta al nombre real del modelo de puntos
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }
}
