<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    protected $table = 'eventos';
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'tipo_evento_id',
        "persona_id",
    ];

    public function tipo_evento()
    {
        return $this->belongsTo(TipoEvento::class, 'tipo_evento_id');
    }
    
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    use SoftDeletes;
}
