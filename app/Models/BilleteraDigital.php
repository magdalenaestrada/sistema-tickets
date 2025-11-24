<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BilleteraDigital extends Model
{
    protected $table = "billeteras_digitales";

    protected $fillable = [
        "descripcion",
    ];
}
