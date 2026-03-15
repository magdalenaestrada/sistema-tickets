<?php

namespace Database\Seeders;

use App\Models\RazonMantenimiento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class RazonesMantenimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RazonMantenimiento::insert([
            ['descripcion' => 'Motor'],
            ['descripcion' => 'Mantenimiento general'],
            ['descripcion' => 'Llanta pinchada'],
            ['descripcion' => 'Cambio de llantas'],
            ['descripcion' => 'Cambio de aceite'],
            ['descripcion' => 'Sistema eléctrico'],
            ['descripcion' => 'Frenos'],
            ['descripcion' => 'Suspensión'],
            ['descripcion' => 'Batería'],
            ['descripcion' => 'Fuga de aceite'],
            ['descripcion' => 'Fuga de refrigerante'],
            ['descripcion' => 'Limpieza interior'],
            ['descripcion' => 'Limpieza exterior'],
            ['descripcion' => 'Lavado general'],
            ['descripcion' => 'Accidente'],
            ['descripcion' => 'Daño en carrocería'],
            ['descripcion' => 'Vidrio roto'],
            ['descripcion' => 'Espejo roto'],
            ['descripcion' => 'Faro dañado'],
            ['descripcion' => 'Revisión técnica'],
            ['descripcion' => 'Inspección de seguridad'],
            ['descripcion' => 'Diagnóstico mecánico'],
            ['descripcion' => 'Otro']
        ]);
    }
}
