<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            // Apartamentos
            'apartamentos.ver',
            'apartamentos.crear',
            'apartamentos.editar',
            'apartamentos.eliminar',

            // Residentes
            'residentes.ver',
            'residentes.crear',
            'residentes.editar',
            'residentes.eliminar',

            // Áreas comunes
            'areas.ver',
            'areas.crear',
            'areas.editar',
            'areas.eliminar',

            // Reservas
            'reservas.ver',
            'reservas.crear',
            'reservas.editar',
            'reservas.eliminar',
            'reservas.gestionar',

            // Cuotas
            'cuotas.ver',
            'cuotas.crear',
            'cuotas.editar',
            'cuotas.eliminar',

            // Pagos
            'pagos.ver',
            'pagos.registrar',
            'pagos.editar',
            'pagos.eliminar'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $administrador = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $administracion = Role::firstOrCreate([
            'name' => 'Administración',
            'guard_name' => 'web',
        ]);

        $residente = Role::firstOrCreate([
            'name' => 'Residente',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Administrador
        |--------------------------------------------------------------------------
        */

        $administrador->syncPermissions(
            Permission::all()
        );

        /*
        |--------------------------------------------------------------------------
        | Administración
        |--------------------------------------------------------------------------
        */

        $administracion->syncPermissions([
            'apartamentos.ver',
            'apartamentos.crear',
            'apartamentos.editar',

            'residentes.ver',
            'residentes.crear',
            'residentes.editar',

            'areas.ver',

            'reservas.ver',
            'reservas.crear',
            'reservas.editar',
            'reservas.gestionar',

            'cuotas.ver',
            'cuotas.crear',
            'cuotas.editar',

            'pagos.ver',
            'pagos.registrar',
            'pagos.editar',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Residente
        |--------------------------------------------------------------------------
        */

        $residente->syncPermissions([
            'areas.ver',

            'reservas.ver',
            'reservas.crear',

            'cuotas.ver',

            'pagos.ver',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}