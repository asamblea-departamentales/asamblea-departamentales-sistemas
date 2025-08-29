<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignShieldPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Asegura que existan los roles
        $roles = [
            'ti',
            'gol',
            'coordinador',
            'asistente_tecnico',
            'auditoria',
        ];
        $roleModels = Role::whereIn('name', $roles)->get()->keyBy('name');

        // 2) Asegura que todos los roles puedan entrar al panel
        $accessAdmin = Permission::where('name', 'access_admin')->first();
        foreach ($roleModels as $role) {
            if ($accessAdmin) {
                $role->givePermissionTo($accessAdmin);
            }
        }

        // 3) Slugs de recursos (ajusta a los que tengas creados)
        $catalogos = ['programa', 'macroactividad'];
        $operacion = [
            'actividad_proyectada',
            'actividad_ejecutada',
            'atestado',
            'requisicion',
            'ticket',
            'contrato',
            'reporte',
        ];
        $admin = ['user', 'departamental', 'parametro_sistema', 'log_auditoria', 'cierre_mensual'];

        // Helper para construir nombres y filtrar solo los que existen
        $make = fn (string $action, string $slug) => "{$action}_{$slug}";
        $exists = fn (array $names) => Permission::whereIn('name', $names)->pluck('name')->all();

        // 4) TI = todo
        if ($ti = $roleModels->get('ti')) {
            $ti->syncPermissions(Permission::pluck('name')->all());
        }

        // 5) GOL = read-only global (incluye logs, reportes, cierres)
        if ($gol = $roleModels->get('gol')) {
            $want = [];
            foreach (array_merge($catalogos, $operacion, $admin) as $slug) {
                $want[] = $make('view_any', $slug);
                $want[] = $make('view', $slug);
            }
            $gol->syncPermissions(array_values(array_unique(array_merge(
                $gol->permissions->pluck('name')->all(),
                $exists($want),
                $accessAdmin ? ['access_admin'] : []
            ))));
        }

        // 6) Coordinador = CRUD en operación + cierre; catálogos solo ver
        if ($coord = $roleModels->get('coordinador')) {
            $want = [];

            // Ver catálogos
            foreach ($catalogos as $slug) {
                $want[] = $make('view_any', $slug);
                $want[] = $make('view', $slug);
            }

            // Operación CRUD
            foreach ($operacion as $slug) {
                foreach (['view_any', 'view', 'create', 'update'] as $a) {
                    $want[] = $make($a, $slug);
                }
                // si quieres permitir borrar en su oficina, agrega 'delete'
                // $want[] = $make('delete', $slug);
            }

            // Cierre mensual: crear/actualizar/ver
            foreach (['view_any', 'view', 'create', 'update'] as $a) {
                $want[] = $make($a, 'cierre_mensual');
            }

            $coord->syncPermissions(array_values(array_unique(array_merge(
                $coord->permissions->pluck('name')->all(),
                $exists($want),
                $accessAdmin ? ['access_admin'] : []
            ))));
        }

        // 7) Asistente Técnico = crear/editar en operación (sin cierre, sin delete)
        if ($asis = $roleModels->get('asistente_tecnico')) {
            $want = [];

            // Ver catálogos
            foreach ($catalogos as $slug) {
                $want[] = $make('view_any', $slug);
                $want[] = $make('view', $slug);
            }

            // Operación: ver/crear/editar (sin delete)
            foreach ($operacion as $slug) {
                foreach (['view_any', 'view', 'create', 'update'] as $a) {
                    $want[] = $make($a, $slug);
                }
            }

            // Cierre: solo ver
            foreach (['view_any', 'view'] as $a) {
                $want[] = $make($a, 'cierre_mensual');
            }

            $asis->syncPermissions(array_values(array_unique(array_merge(
                $asis->permissions->pluck('name')->all(),
                $exists($want),
                $accessAdmin ? ['access_admin'] : []
            ))));
        }

        // 8) Auditoría = read-only global (incluye logs)
        if ($aud = $roleModels->get('auditoria')) {
            $want = [];
            foreach (array_merge($catalogos, $operacion, $admin) as $slug) {
                $want[] = $make('view_any', $slug);
                $want[] = $make('view', $slug);
            }
            $aud->syncPermissions(array_values(array_unique(array_merge(
                $aud->permissions->pluck('name')->all(),
                $exists($want),
                $accessAdmin ? ['access_admin'] : []
            ))));
        }
    }
}
