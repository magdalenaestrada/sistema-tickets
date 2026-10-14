<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class VentaSobreequipajeExport extends DefaultValueBinder implements
    FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomValueBinder
{
    public function __construct(private Collection $filas) {}

    public function collection(): Collection
    {
        return $this->filas;
    }

    public function headings(): array
    {
        return [
            'Fecha de venta', 'Código', 'Pasajero', 'DNI', 'Ruta',
            'Origen', 'Destino', 'Bultos', 'Peso', 'Total (S/)',
        ];
    }

    public function map($fila): array
    {
        return [
            $fila['fecha'], $fila['codigo'], $fila['pasajero'], $fila['dni'],
            $fila['ruta'], $fila['origen'], $fila['destino'],
            $fila['bultos'], $fila['peso'], $fila['total'],
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($cell->getRow() > 1 && $cell->getColumn() === 'D') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
