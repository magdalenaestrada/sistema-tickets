<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PasajerosPorRutaExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles
{
    protected $pasajes;

    public function __construct($pasajes)
    {
        $this->pasajes = $pasajes;
    }

    public function collection()
    {
        return $this->pasajes;
    }

    public function headings(): array
    {
        return [
            'Fecha Venta',
            'Usuario',
            'Ruta',
            'Pasajero',
            'Documento',
            'Origen',
            'Destino',
            'Asiento',
            'Precio',
        ];
    }

    public function map($pasaje): array
    {
        $persona = $pasaje->persona;

        $nombre = $persona
            ? trim(
                ($persona->nombres ?? '') . ' ' .
                ($persona->apellido_paterno ?? '') . ' ' .
                ($persona->apellido_materno ?? '')
            )
            : '';

        $ruta = $pasaje->salida?->horario?->ruta;

        $precio = $pasaje->precio_cobrado
            ?? $pasaje->precio_pasaje
            ?? 0;

        return [
            optional($pasaje->venta)->created_at
                ? $pasaje->venta->created_at->format('d/m/Y H:i')
                : '',

            $pasaje->usuario?->name ?? '',

            $ruta?->descripcion ?? '',

            $nombre,

            $persona?->numero_documento ?? '',

            $pasaje->origen?->descripcion ?? '',

            $pasaje->destino?->descripcion ?? '',

            $pasaje->asiento_numero,

            number_format($precio, 2, '.', ''),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    
}