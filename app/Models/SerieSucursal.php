<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerieSucursal extends Model
{
    protected $table = "series_sucursal";
    protected $fillable = [
        "descripcion",
        "codigo"
    ];
}
