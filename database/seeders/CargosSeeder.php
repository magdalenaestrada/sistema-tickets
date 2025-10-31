<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cargos')->insert([
            
            ['descripcion' => 'Gerente General'],
            ['descripcion' => 'Asistente de Gerencia'],
            ['descripcion' => 'Coordinador General'],

            ['descripcion' => 'Administrador'],
            ['descripcion' => 'Asistente Administrativo'],
            ['descripcion' => 'Secretaria'],

            ['descripcion' => 'Jefe de Recursos Humanos'],
            ['descripcion' => 'Asistente de RRHH'],
            ['descripcion' => 'Reclutador de Personal'],

            ['descripcion' => 'Contador General'],
            ['descripcion' => 'Asistente Contable'],
            ['descripcion' => 'Tesorero'],
            ['descripcion' => 'Analista Financiero'],

            ['descripcion' => 'Jefe de Operaciones'],
            ['descripcion' => 'Supervisor de Rutas'],
            ['descripcion' => 'Conductor'],
            ['descripcion' => 'Copiloto'],
            ['descripcion' => 'Despachador'],

            ['descripcion' => 'Jefe de Logística'],
            ['descripcion' => 'Almacenero'],
            ['descripcion' => 'Encargado de Compras'],

            ['descripcion' => 'Jefe de Mantenimiento'],
            ['descripcion' => 'Mecánico'],
            ['descripcion' => 'Electricista Automotriz'],

            ['descripcion' => 'Jefe de Taller'],
            ['descripcion' => 'Técnico Mecánico'],
            ['descripcion' => 'Ayudante de Taller'],

            ['descripcion' => 'Supervisor de Control de Flota'],
            ['descripcion' => 'Controlador de GPS'],
            ['descripcion' => 'Inspector de Unidades'],

            ['descripcion' => 'Despachador Principal'],
            ['descripcion' => 'Asistente de Despacho'],
            ['descripcion' => 'Jefe de Atención al Cliente'],
            ['descripcion' => 'Vendedor de Pasajes'],
            ['descripcion' => 'Anfitriona'],
            ['descripcion' => 'Call Center'],

            ['descripcion' => 'Jefe de Seguridad'],
            ['descripcion' => 'Vigilante'],
            ['descripcion' => 'Encargado de SST'],

            ['descripcion' => 'Jefe de Sistemas'],
            ['descripcion' => 'Soporte Técnico'],
            ['descripcion' => 'Desarrollador Web'],

            ['descripcion' => 'Jefe de Marketing'],
            ['descripcion' => 'Diseñador Gráfico'],
            ['descripcion' => 'Community Manager'],

            ['descripcion' => 'Encargado de Limpieza'],
            ['descripcion' => 'Auxiliar de Servicios'],
        ]);
    }
}
