<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VentasPorRutaExport implements
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
            'Origen',
            'Destino',
            'Asiento',
            'Estado',
            'Precio',
        ];
    }

    public function map($pasaje): array
    {
        $precio = $pasaje->precio_cobrado
            ?? $pasaje->precio_pasaje
            ?? 0;

        $ruta = $pasaje->salida?->horario?->ruta;

        return [
            optional($pasaje->venta)->created_at
                ? $pasaje->venta->created_at->format('d/m/Y H:i')
                : '',

            $pasaje->usuario?->name ?? '',

            $ruta?->descripcion ?? '',

            $pasaje->persona
                ? trim(
                    ($pasaje->persona->nombres ?? '') . ' ' .
                    ($pasaje->persona->apellido_paterno ?? '') . ' ' .
                    ($pasaje->persona->apellido_materno ?? '')
                )
                : '',

            $pasaje->origen?->nombre ?? '',

            $pasaje->destino?->nombre ?? '',

            $pasaje->asiento_numero,

            $pasaje->estado,

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