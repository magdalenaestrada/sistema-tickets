<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("areas")->insert([
            ['descripcion' => 'Gerencia General'],
            ['descripcion' => 'Administración'],
            ['descripcion' => 'Recursos Humanos'],
            ['descripcion' => 'Contabilidad y Finanzas'],
            ['descripcion' => 'Operaciones'],
            ['descripcion' => 'Logística y Almacén'],
            ['descripcion' => 'Mantenimiento Vehicular'],
            ['descripcion' => 'Taller Mecánico'],
            ['descripcion' => 'Control de Flota'],
            ['descripcion' => 'Despacho de Rutas'],
            ['descripcion' => 'Atención al Cliente'],
            ['descripcion' => 'Seguridad y Salud Ocupacional'],
            ['descripcion' => 'Sistemas e Informática'],
            ['descripcion' => 'Marketing y Comunicaciones'],
            ['descripcion' => 'Limpieza y Servicios Generales'],
        ]);
    }
}
