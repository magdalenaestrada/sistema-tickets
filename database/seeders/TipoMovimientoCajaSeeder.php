<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TipoMovimientoCajaSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $tipos = [
            [
                'id' => 1,
                'descripcion' => 'INGRESO',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'descripcion' => 'EGRESO',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'descripcion' => 'TRANSFERENCIA',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'descripcion' => 'AJUSTE',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('tipo_movimiento_caja')->insert($tipos);

        $subtipos = [
            // INGRESOS (tipo_movimiento_caja_id = 1)
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Venta en efectivo',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Cobro de facturas',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Cobro de servicios',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Devolución de compras',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Aporte de capital',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Préstamo recibido',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Intereses ganados',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Venta de activos',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Anticipo de clientes',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Apertura de caja',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 1,
                'descripcion' => 'Otros ingresos',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // EGRESOS (tipo_movimiento_caja_id = 2)
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Compra en efectivo',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Pago a proveedores',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Pago de salarios',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Pago de servicios básicos',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Pago de alquiler',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Pago de impuestos',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Devolución a clientes',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Pago de préstamo',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Retiro de socios',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Gastos administrativos',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Gastos de mantenimiento',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Gastos de transporte',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Gastos de publicidad',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Compra de activos',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Anticipo a proveedores',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 2,
                'descripcion' => 'Otros egresos',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // TRANSFERENCIAS (tipo_movimiento_caja_id = 3)
            [
                'tipo_movimiento_caja_id' => 3,
                'descripcion' => 'Transferencia entre cajas',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 3,
                'descripcion' => 'Transferencia a banco',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 3,
                'descripcion' => 'Transferencia desde banco',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // AJUSTES (tipo_movimiento_caja_id = 4)
            [
                'tipo_movimiento_caja_id' => 4,
                'descripcion' => 'Ajuste por diferencia de caja',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 4,
                'descripcion' => 'Ajuste por error de registro',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 4,
                'descripcion' => 'Ajuste por arqueo',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tipo_movimiento_caja_id' => 4,
                'descripcion' => 'Ajuste por redondeo',
                'estado' => 'A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('subtipo_movimiento_caja')->insert($subtipos);
    }
}
