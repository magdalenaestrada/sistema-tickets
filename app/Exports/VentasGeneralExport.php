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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use UnitEnum;

/**
 * Guardar como app/Exports/VentasGeneralExport.php.
 * Recibe el array de obtenerResumenVentasGeneral($request), igual que el PDF.
 * No recibe una colección de pasajes ni una colección de ventas directamente.
 */
class VentasGeneralExport extends DefaultValueBinder implements
    FromArray,
    WithCustomValueBinder,
    WithStrictNullComparison,
    WithEvents,
    WithTitle
{
    private array $rows = [];
    private array $sections = [];
    private array $tables = [];
    private array $totals = [];
    private array $moneyRanges = [];

    public function __construct(array $data)
    {
        $this->rows = [
            ['REPORTE GENERAL DE VENTAS'],
            ['Período: ' . $data['desde']->format('d/m/Y')
                . ' al ' . $data['hasta']->format('d/m/Y')],
            [],
        ];

        $summary = [
            ['Total vendido', (float) $data['totalVendido']],
            ['Ventas emitidas', (int) $data['cantidadVentas']],
            ['Ventas anuladas', (int) $data['cantidadAnuladas']],
            ['Ticket promedio', (float) $data['ticketPromedio']],
            ['Pasajes', (int) $data['cantidadPasajes']],
            ['Encomiendas', (int) $data['cantidadEncomiendas']],
            ['Sobreequipajes', (int) $data['cantidadSobreEquipajes']],
            ['Total servicios', (int) $data['totalServicios']],
        ];

        $summaryHeader = $this->addTable('RESUMEN GENERAL', ['Concepto', 'Valor'], $summary);
        $this->moneyRanges[] = 'B' . ($summaryHeader + 1);
        $this->moneyRanges[] = 'B' . ($summaryHeader + 4);

        $groups = [
            ['VENTAS POR SUCURSAL', 'ventasPorSucursal', 'Sucursal', 'sucursal', 'ventas'],
            ['MÉTODOS DE PAGO', 'metodosPago', 'Método', 'nombre', 'operaciones'],
            ['VENTAS POR VENDEDOR', 'ventasPorVendedor', 'Vendedor', 'vendedor', 'ventas'],
        ];

        foreach ($groups as [$title, $key, $label, $nameKey, $countKey]) {
            $tableRows = [];
            $operations = 0;
            $total = 0.0;

            foreach ($data[$key] as $item) {
                $count = (int) $item[$countKey];
                $amount = (float) $item['total'];
                $tableRows[] = [self::text($item[$nameKey]), $count, $amount];
                $operations += $count;
                $total += $amount;
            }

            $header = $this->addTable($title, [$label, 'Operaciones', 'Total (S/)'], $tableRows);
            $totalRow = $this->addTotal(['TOTAL', $operations, $total]);
            $this->moneyRanges[] = 'C' . ($header + 1) . ':C' . $totalRow;
        }

        $details = [];

        // Todas las ventas del período: pasajes, encomiendas y sobreequipajes.
        // Las anuladas aparecen en el detalle; los resúmenes conservan las reglas del PDF.
        foreach ($data['ventas'] as $venta) {
            $comprobante = collect([$venta->serie, $venta->numero])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->map(fn ($value) => self::text($value))
                ->implode('-');

            $methods = $venta->pagos->map(function ($pago) {
                $method = $pago->metodoPago?->nombre
                    ?? $pago->metodoPago?->descripcion
                    ?? 'SIN MÉTODO';
                $wallet = $pago->billetera?->nombre
                    ?? $pago->billetera?->descripcion;

                $name = $wallet
                    ? 'BILLETERA DIGITAL - ' . self::text($wallet)
                    : self::text($method);

                return mb_strtoupper(str_replace('_', ' ', trim($name)));
            })->unique()->implode(', ');

            $details[] = [
                $venta->fecha_emision?->format('d/m/Y H:i') ?? '',
                $comprobante ?: '-',
                self::text($venta->sucursal?->nombre_comercial
                    ?? $venta->sucursal?->nombre ?? 'SIN SUCURSAL'),
                self::text($venta->persona?->nombre_completo ?? 'SIN CLIENTE'),
                self::text($venta->usuario?->persona?->nombre_completo
                    ?? $venta->usuario?->name ?? 'SIN USUARIO'),
                $venta->pasajes->count(),
                $venta->encomiendas->where('sobre_equipaje', false)->count(),
                $venta->encomiendas->where('sobre_equipaje', true)->count(),
                $methods ?: 'SIN MÉTODO',
                (float) $venta->subtotal,
                (float) $venta->impuesto,
                (float) $venta->total,
                self::text($venta->estado),
            ];
        }

        $header = $this->addTable('DETALLE DE TODAS LAS VENTAS', [
            'Fecha', 'Comprobante', 'Sucursal', 'Cliente', 'Vendedor',
            'Pasajes', 'Encomiendas', 'Sobreequipajes', 'Método de pago',
            'Subtotal (S/)', 'IGV (S/)', 'Total (S/)', 'Estado',
        ], $details);

        $totalRow = array_fill(0, 13, null);
        $totalRow[10] = 'TOTAL VENDIDO (EMITIDAS)';
        $totalRow[11] = (float) $data['totalVendido'];
        $lastRow = $this->addTotal($totalRow);
        $this->moneyRanges[] = 'J' . ($header + 1) . ':L' . ($lastRow - 1);
        $this->moneyRanges[] = 'L' . $lastRow;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Ventas general';
    }

    // Conserva texto e impide que nombres o comprobantes se interpreten como fórmulas.
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
        $lastRow = count($this->rows);
        $sheet->getStyle("A1:M{$lastRow}")->applyFromArray([
            'font' => ['name' => 'Calibri', 'size' => 11],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
        ]);

        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getRowDimension(1)->setRowHeight(28);

        foreach ($this->sections as $row) {
            $sheet->mergeCells("A{$row}:M{$row}");
            $sheet->getStyle("A{$row}:M{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(23);
        }

        foreach ($this->tables as [$header, $end, $column]) {
            $sheet->getStyle("A{$header}:{$column}{$end}")->applyFromArray([
                'borders' => ['allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9E2F3'],
                ]],
            ]);
            $sheet->getStyle("A{$header}:{$column}{$header}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E2F3']],
            ]);
        }

        foreach ($this->totals as [$row, $column]) {
            $sheet->getStyle("A{$row}:{$column}{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
            ]);
        }

        foreach ($this->moneyRanges as $range) {
            $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        foreach (['A' => 32, 'B' => 22, 'C' => 28, 'D' => 32, 'E' => 32,
            'F' => 12, 'G' => 15, 'H' => 18, 'I' => 36, 'J' => 18,
            'K' => 22, 'L' => 18, 'M' => 16] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('A3');
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setPrintArea("A1:M{$lastRow}");
    }

    private function addTable(string $title, array $columns, array $rows): int
    {
        $this->rows[] = [];
        $this->rows[] = [$title];
        $this->sections[] = count($this->rows);
        $this->rows[] = $columns;
        $header = count($this->rows);

        if ($rows === []) {
            $this->rows[] = ['Sin movimientos para el período seleccionado.'];
        } else {
            foreach ($rows as $row) {
                $this->rows[] = $row;
            }
        }

        $this->tables[] = [$header, count($this->rows), Coordinate::stringFromColumnIndex(count($columns))];
        return $header;
    }

    private function addTotal(array $values): int
    {
        $this->rows[] = $values;
        $row = count($this->rows);
        $this->totals[] = [$row, Coordinate::stringFromColumnIndex(count($values))];
        return $row;
    }

    private static function text(mixed $value): string
    {
        return match (true) {
            $value instanceof BackedEnum => (string) $value->value,
            $value instanceof UnitEnum => $value->name,
            default => (string) ($value ?? ''),
        };
    }
}
