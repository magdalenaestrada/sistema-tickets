<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class VentasPorUsuarioExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithCustomStartCell,
    ShouldAutoSize,
    WithColumnFormatting
{
    protected Collection $pasajes;
    protected string $desde;
    protected string $hasta;

    public function __construct(Collection $pasajes, string $desde, string $hasta)
    {
        $this->pasajes = $pasajes;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function collection()
    {
        return $this->pasajes;
    }

    /**
     * La tabla comenzará en la fila 4 para dejar espacio al título y fechas.
     */
    public function startCell(): string
    {
        return 'A4';
    }

    public function headings(): array
    {
        return [
            'Usuario / Cajero',
            'Fecha Venta',
            'Pasajero',
            'Origen',
            'Destino',
            'Ruta',
            'Asiento',
            'Estado',
            'Precio',
        ];
    }

    public function map($pasaje): array
    {
        $persona = $pasaje->persona;
        $nombreCompleto = $persona 
            ? trim("{$persona->nombres} {$persona->apellido_paterno} {$persona->apellido_materno}") 
            : 'Sin registrar';

        $origen = $pasaje->origen?->nombre ?? 'N/A';
        $destino = $pasaje->destino?->nombre ?? 'N/A';

        return [
            $pasaje->usuario?->name ?? 'Sin usuario',
            optional($pasaje->venta)->created_at ? $pasaje->venta->created_at->format('d/m/Y H:i') : '-',
            $nombreCompleto,
            $origen,
            $destino,
            "{$origen} - {$destino}",
            $pasaje->asiento_numero ?? '-',
            ucfirst(mb_strtolower($pasaje->estado ?? 'Desconocido')),
            (float) ($pasaje->precio_cobrado ?? $pasaje->precio_pasaje ?? 0),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => '"S/"#,##0.00', // Formato de moneda para la columna Precio
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalFilas = $this->pasajes->count() + 4; // Fila inicial (4) + filas de datos

        // 1. Título principal del reporte
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'REPORTE DE VENTAS POR USUARIO');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => '1F4E79'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // 2. Subtítulo con rango de fechas
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', "Periodo: {$this->desde} al {$this->hasta}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 11,
                'color' => ['argb' => '595959'],
            ],
        ]);

        // 3. Estilo del encabezado de la tabla (Fila 4)
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '1F4E79'], // Azul corporativo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(25);

        // 4. Estilos para los datos y bordes
        $sheet->getStyle("A4:I{$totalFilas}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'D9D9D9'],
                ],
            ],
        ]);

        // 5. Alineación de columnas específicas
        $sheet->getStyle("B5:B{$totalFilas}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Fecha
        $sheet->getStyle("G5:H{$totalFilas}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Asiento y Estado
        $sheet->getStyle("I5:I{$totalFilas}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);  // Precio

        return [];
    }
}