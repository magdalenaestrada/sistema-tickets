<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class CalculosHelper
{
    public static function calcular_mediana(Collection $data): ?float
    {
        $ordenado = $data->sortBy("valor");
        $total = $ordenado->count();
        if ($total === 0) {
            return null;
        }

        $mitad = floor($total / 2);
        if ($total % 2 === 0) {
            return ($ordenado->get($mitad - 1)["valor"] + $ordenado->get($mitad)["valor"]) / 2;
        } else {
            return $ordenado->get($mitad)["valor"];
        }
    }
}
