<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Impuesto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'descripcion',
        'codigo_sunat',
        'valor',
        'valor_2',
        'estado',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
