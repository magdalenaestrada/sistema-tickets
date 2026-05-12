<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pueblito extends Model
{
    protected $table = "pueblitos";

    protected $fillable = [
        'nombre',
        'distrito_id'
    ];

    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }
}
