<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEvento extends Model
{
    protected $table = 'tipo_evento';
    protected $fillable = ['descripcion'];

    public function eventos()
    {
        return $this->hasMany(Evento::class, 'tipo_evento_id');
    }
}
