<?php

namespace App\Exports;

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

class VentaPasajesExport extends DefaultValueBinder implements
    FromArray,
    WithCustomValueBinder,
    WithStrictNullComparison,
    WithEvents,
    WithTitle
{
    private array $rows;
    private int $lastDataRow;
    private int $totalRow;
    private bool $hasData;

    public function __construct(array $data)
    {
        $this->rows = [
            ['VENTA DE PASAJES'],
            ['Período: ' . $data['desde']->format('d/m/Y')
                . ' al ' . $data['hasta']->format('d/m/Y')],
            [$data['dni'] !== null ? 'DNI del pasajero: ' . $data['dni'] : 'Pasajeros: Todos'],
            ['Cantidad de pasajes: ' . $data['cantidadPasajes']],
            [],
            ['Fecha venta', 'Comprobante', 'Pasajero', 'Documento', 'Ruta',
                'Origen', 'Destino', 'Asiento', 'Vendedor', 'Estado', 'Precio (S/)'],
        ];

        $this->hasData = $data['filas']->isNotEmpty();

        foreach ($data['filas'] as $fila) {
            $this->rows[] = [
                $fila['fecha'], $fila['comprobante'], $fila['pasajero'],
                $fila['documento'], $fila['ruta'], $fila['origen'],
                $fila['destino'], $fila['asiento'], $fila['vendedor'],
                $fila['estado'], (float) $fila['precio'],
            ];
        }

        if (!$this->hasData) {
            $this->rows[] = [$data['dni'] !== null
                ? 'No se encontraron pasajes para el DNI y período seleccionados.'
                : 'No se encontraron pasajes para el período seleccionado.'];
        }

        $this->lastDataRow = count($this->rows);
        $total = array_fill(0, 11, null);
        $total[0] = 'TOTAL DE IMPORTES DE PASAJES';
        $total[10] = (float) $data['totalImporte'];
        $this->rows[] = $total;
        $this->totalRow = count($this->rows);
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Venta de pasajes';
    }

    public function bindValue(Cell $cell, $value): bool
    {
        // Mantiene los ceros iniciales del DNI y todo el texto literal.
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
        $end = $this->totalRow;

        $sheet->getStyle("A1:K{$end}")->applyFromArray([
            'font' => ['name' => 'Calibri', 'size' => 11],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
        ]);

        for ($row = 1; $row <= 4; $row++) {
            $sheet->mergeCells("A{$row}:K{$row}");
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getStyle('A6:K6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(30);
        $sheet->getStyle("A6:K{$end}")->applyFromArray([
            'borders' => ['allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'D9E2F3'],
            ]],
        ]);
        $sheet->mergeCells("A{$end}:J{$end}");
        $sheet->getStyle("A{$end}:K{$end}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
        ]);
        $sheet->getStyle("K7:K{$end}")->getNumberFormat()->setFormatCode('#,##0.00');

        if ($this->hasData) {
            $sheet->setAutoFilter('A6:K' . $this->lastDataRow);
        } else {
            $sheet->mergeCells('A7:K7');
        }

        foreach (['A' => 22, 'B' => 20, 'C' => 36, 'D' => 18, 'E' => 28,
            'F' => 24, 'G' => 24, 'H' => 12, 'I' => 30, 'J' => 18, 'K' => 18]
            as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('A7');
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setPrintArea("A1:K{$end}");
    }
}
