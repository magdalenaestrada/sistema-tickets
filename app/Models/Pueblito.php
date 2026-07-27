<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pueblito extends Model
{
    protected $table = "pueblitos";

    protected $fillable = [
        'descripcion',
        'distrito_id',
        'sucursal_id',
    ];

    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
