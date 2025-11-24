<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unidad_medida_id',
        'impuesto_id',
        'codigo',
        'descripcion',
        'total',
        'estado',
        'fecha_creacion',
        'fecha_inactivo',
    ];

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function impuesto()
    {
        return $this->belongsTo(Impuesto::class);
    }

    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }
}
