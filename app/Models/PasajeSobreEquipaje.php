<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasajeSobreEquipaje extends Model
{
    protected $table = 'pasaje_sobre_equipajes';

    protected $fillable = [
        'pasaje_id',
        'encomienda_id',
    ];

    public function pasaje()
    {
        return $this->belongsTo(Pasaje::class);
    }

    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class);
    }
}
