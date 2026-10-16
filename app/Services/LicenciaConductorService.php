<?php

namespace App\Services;

use App\Models\TipoVehiculo;

class LicenciaConductorService
{
    private const COMPATIBLES = [
        'A-I' => [
            'AI',
            'AIIa',
            'AIIb',
            'AIIIa',
            'AIIIb',
            'AIIIc',
        ],
        'A-IIa' => [
            'AIIa',
            'AIIb',
            'AIIIa',
            'AIIIb',
            'AIIIc',
        ],
        'A-IIb' => [
            'AIIb',
            'AIIIa',
            'AIIIc',
        ],
        'A-IIIa' => [
            'AIIIa',
            'AIIIc',
        ],
        'A-IIIb' => [
            'AIIIb',
            'AIIIc',
        ],
        'A-IIIc' => [
            'AIIIc',
        ],
    ];

    public function obtenerCompatibles(
        TipoVehiculo $tipoVehiculo
    ): array {
        return self::COMPATIBLES[$tipoVehiculo->licencia_requerida] ?? [];
    }
}
