<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener IDs válidos de tablas relacionadas
        $tipoDocumentoId = DB::table('tipo_documento_personas')->value('id');
        $distritoId = DB::table('distritos')->value('id');

        if (!$tipoDocumentoId || !$distritoId) {
            throw new \Exception("Debe haber registros en tipo_documento_personas y distritos antes de ejecutar este seeder.");
        }

        DB::table('personas')->insert([
            'tipo_documento_id' => $tipoDocumentoId,
            'distrito_id'       => $distritoId,
            'documento'         => '12345678',
            'nombres'           => 'Magdalena Adali',
            'apellidos'         => 'Cabezudo Estrada',
            'razon_social'      => null,
            'telefono'          => '922318036',
            'celular'           => '922318036',
            'correo'            => 'juan@example.com',
            'direccion'         => 'Av. Principal 123',
            'fecha_nacimiento'  => '1995-05-20',
            'estado'            => 'A',
            'fecha_creacion'    => now(),
            'fecha_inactivacion' => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
