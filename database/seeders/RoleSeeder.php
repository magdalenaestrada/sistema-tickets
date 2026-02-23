<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::firstOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'web']
        );

        $cajero = Role::firstOrCreate(
            ['name' => 'Cajero', 'guard_name' => 'web']
        );

        $permisos = [
            'gestionar empresa',
            'gestionar areas',
            'gestionar asignaciones encomiendas',
            'gestionar caja',
            'gestionar cargos',
            'gestionar asignar horarios',
            'gestionar clientes',
            'gestionar descuentos',
            'ver descuentos',
            'gestionar empleados',
            'gestionar encomiendas',
            'eliminar encomiendas',
            'gestionar horarios',
            'ver horarios',
            'gestionar pasajes',
            'gestionar reportes',
            'gestionar tipo cupones',
            'gestionar tipo encomiendas',
            'gestionar usuarios',
            'gestionar vehiculos',
            'ver vehiculos'
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web'
            ]);
        }

        $admin->givePermissionTo(Permission::all());

        $cajero->givePermissionTo([
            'gestionar asignaciones encomiendas',
            'gestionar caja',
            'gestionar asignar horarios',
            'gestionar clientes',
            'ver descuentos',
            'gestionar encomiendas',
            'gestionar pasajes',
            'ver horarios',
        ]);
    }
}
