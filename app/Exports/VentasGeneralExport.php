<?php

namespace App\Exports;

use BackedEnum;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Reporte General de Ventas: una fila por comprobante.
 * IMPORTANTE: temporalmente el campo 'inafecta' del servicio contiene
 * las operaciones EXONERADAS en este sistema. Cuando se normalice,
 * sustituir por 'exonerada' tanto aqui como en el PDF.
 */
class VentasGeneralExport extends DefaultValueBinder implements
    FromArray,
    WithCustomValueBinder,
    WithStrictNullComparison,
    WithEvents,
    WithTitle
{
    private array $rows = [];
    private int $headerRow = 3;
    private int $lastDataRow = 3;
    private int $totalRow = 4;
    public function __construct(array $data)
    {
        $this->rows = [
            ['REPORTE GENERAL DE VENTAS'],

            [
                'Periodo: ' . $data['desde']->format('d/m/Y') .
                    ' al ' . $data['hasta']->format('d/m/Y')
            ],

            [
                'Fecha',
                'Comprobante',
                'Serie',
                'Numero',
                'Documento',
                'Cliente',
                'Detalle',
                'Base Imponible',
                'IGV Gravada',
                'Op. Exonerada',
                'Total',
                'Estado',
                'Sucursal',
            ],
        ];
        $sumas = ['base' => 0.0, 'igv' => 0.0, 'exonerada' => 0.0, 'total' => 0.0];

        foreach ($data['detalleGeneral'] ?? [] as $fila) {
            $estado = $fila['estado'] ?? '';
            $estado = $estado instanceof BackedEnum ? $estado->value : (string) $estado;

            $base = (float) ($fila['subtotal'] ?? 0);
            $igv = (float) ($fila['igv'] ?? 0);
            // Compatibilidad TEMPORAL: inafecta = exonerada en la BD actual.
            $exonerada = (float) ($fila['exonerada'] ?? 0);
            $total = (float) ($fila['total'] ?? 0);

            $this->rows[] = [
                self::asText($fila['fecha'] ?? ''),
                self::asText($fila['comprobante'] ?? ''),
                self::asText($fila['serie'] ?? ''),
                self::asText($fila['numero'] ?? ''),
                self::asText($fila['documento'] ?? ''),
                self::asText($fila['cliente'] ?? ''),
                self::asText($fila['detalle_servicio'] ?? ''),
                $base,
                $igv,
                $exonerada,
                $total,
                self::asText($estado),
                self::asText($fila['sucursal'] ?? ''),
            ];

            if (strtoupper(trim($estado)) === 'EMITIDO') {
                $sumas['base'] += $base;
                $sumas['igv'] += $igv;
                $sumas['exonerada'] += $exonerada;
                $sumas['total'] += $total;
            }
        }

        $this->lastDataRow = count($this->rows);
        $this->rows[] = [
            'TOTAL EMITIDAS',
            null,
            null,
            null,
            null,
            null,
            null,
            round($sumas['base'], 2),
            round($sumas['igv'], 2),
            round($sumas['exonerada'], 2),
            round($sumas['total'], 2),
            null,
            null,
        ];
        $this->totalRow = count($this->rows);
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Ventas general';
    }

    private static function asText(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }
        return (string) ($value ?? '');
    }

    // Mantiene ceros iniciales y evita la ejecucion de formulas en texto.
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => [$this, 'afterSheet']];
    }

    public function afterSheet(AfterSheet $event): void
    {
        $sheet = $event->sheet->getDelegate();
        $last = $this->totalRow;
        $header = $this->headerRow;

        $sheet->getStyle("A1:M{$last}")->applyFromArray([
            'font' => ['name' => 'Calibri', 'size' => 10],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:M2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(34);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->getStyle("A{$header}:M{$header}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension($header)->setRowHeight(32);
        $sheet->getStyle("A{$header}:M{$last}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        for ($r = $header + 1; $r < $last; $r++) {
            if (($r - $header) % 2 === 0) {
                $sheet->getStyle("A{$r}:M{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
            }
        }

        $sheet->mergeCells("A{$last}:G{$last}");
        $sheet->getStyle("A{$last}:M{$last}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '1E3A8A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
        ]);
        $sheet->getRowDimension($last)->setRowHeight(26);

        $sheet->getStyle("H4:K{$last}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->getStyle("H4:K{$last}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("G4:G{$last}")
            ->getAlignment()
            ->setWrapText(true);
        foreach (
            [
                'A' => 20,
                'B' => 23,
                'C' => 14,
                'D' => 16,
                'E' => 19,
                'F' => 35,
                'G' => 62,
                'H' => 20,
                'I' => 16,
                'J' => 20,
                'K' => 20,
                'L' => 17,
                'M' => 28,
            ] as $column => $width
        ) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        if ($this->lastDataRow > $header) {
            $sheet->setAutoFilter("A{$header}:M{$this->lastDataRow}");
        }
        $sheet->freezePane('A4');
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A3)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setPrintArea("A1:M{$last}");
    }
}
