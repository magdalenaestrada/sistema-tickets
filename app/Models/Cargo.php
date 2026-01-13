<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function PHPUnit\Framework\returnArgument;

class Cargo extends Model
{
    protected $table = "cargos";
    protected $fillable = [
        "descripcion",
        "rol_id",
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function rol()
    {
        return $this->belongsTo(Role::class);
    }
}
