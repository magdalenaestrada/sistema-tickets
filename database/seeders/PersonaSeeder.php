<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        $tipoDocumentoId = DB::table('tipo_documento_personas')->value('id');
        $distritoId = DB::table('distritos')->value('id');

        if (!$tipoDocumentoId || !$distritoId) {
            throw new \Exception("Debe haber registros en tipo_documento_personas y distritos antes de ejecutar este seeder.");
        }

        DB::table('personas')->insert([
            'tipo_documento_id' => 1,
            'distrito_id'       => $distritoId,
            'documento'         => '65784889',
            'nombres'           => 'GRETHEL MARITZA',
            'apellidos'         => 'GUERREROS ANAMPA',
            'razon_social'      => null,
            'telefono'          => '979005457',
            'correo'            => '200gmgrethel@gmail.com',
            'direccion'         => 'Av. Arequipa Mz 10 Lt. 6',
            'fecha_nacimiento'  => '2000-04-30',
            'estado'            => 'A',
            'fecha_creacion'    => now(),
            'fecha_inactivacion' => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
