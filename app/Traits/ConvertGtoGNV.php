<?php

namespace App\Traits;

trait ConvertGtoGNV
{
    public array $array_ids_GNV = [6,7];
    private float $factor_conversion_galones = 3.3444;
    public function convertGtoGNV(float $m3Gnv): float
    {
        return round($m3Gnv / $this->factor_conversion_galones, 4);
    }
}
