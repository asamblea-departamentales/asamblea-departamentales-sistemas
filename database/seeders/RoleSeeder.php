<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        // 1) Roles
        foreach (['super_admin', 'ti', 'gol', 'coordinador', 'asistente_tecnico', 'auditoria'] as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => $guard]);
        }

        // 2) Helpers
        $grant = function (string $role, array $perms) use ($guard) {
            $r = Role::where('name', $role)->firstOrFail();
            foreach ($perms as $p) {
                Permission::findOrCreate($p, $guard);
            }
            $r->syncPermissions(array_unique($perms)); // asegura set limpio
        };
        $crud = fn (string $r) => ["view_any_$r", "view_$r", "create_$r", "update_$r", "delete_$r"];
        $read = fn (string $r) => ["view_any_$r", "view_$r"];

        // 3) Slugs de tus módulos (ajusta si cambian)
        $mods = ['actividad', 'ejecucion', 'documento', 'bitacora', 'cierre_mensual'];

        // 4) TI (central, todo + admin)
        $ti = ['access_admin'];
        foreach ($mods as $m) {
            $ti = array_merge($ti, $crud($m));
        }
        $ti = array_merge($ti, [
            // usuarios / config
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user',
            'view_any_setting', 'view_setting', 'update_setting',
            // catálogos (si los usas con este slug)
            'view_any_catalogo', 'view_catalogo', 'create_catalogo', 'update_catalogo', 'delete_catalogo',
            // utilidades
            'access_log_viewer',
            'export_actividad', 'export_ejecucion', 'export_cierre_mensual',
        ]);
        $grant('ti', $ti);

        // 5) GOL (central, solo lectura + export)
        $gol = ['access_admin'];
        foreach ($mods as $m) {
            $gol = array_merge($gol, $read($m));
        }
        $gol = array_merge($gol, ['export_actividad', 'export_ejecucion', 'export_cierre_mensual']);
        $grant('gol', $gol);

        // 6) Coordinador (scoped a su departamental; puede generar cierre)
        $coord = ['access_admin'];
        foreach (['actividad', 'ejecucion', 'documento', 'bitacora'] as $m) {
            $coord = array_merge($coord, $crud($m));
        }
        $coord = array_merge($coord, ['view_any_cierre_mensual', 'view_cierre_mensual', 'create_cierre_mensual', 'export_cierre_mensual']);
        $grant('coordinador', $coord);

        // 7) Asistente Técnico (scoped, sin delete ni cierre)
        $asis = ['access_admin',
            'view_any_actividad', 'view_actividad', 'create_actividad', 'update_actividad',
            'view_any_ejecucion', 'view_ejecucion', 'create_ejecucion', 'update_ejecucion',
            'view_any_documento', 'view_documento', 'create_documento',
            'view_any_bitacora', 'view_bitacora', 'create_bitacora',
            'view_any_cierre_mensual', 'view_cierre_mensual',
        ];
        $grant('asistente_tecnico', $asis);

        // 8) Auditoría (scoped, lectura + logs + export)
        $aud = ['access_admin'];
        foreach ($mods as $m) {
            $aud = array_merge($aud, $read($m));
        }
        $aud = array_merge($aud, ['access_log_viewer', 'export_cierre_mensual']);
        $grant('auditoria', $aud);

        // super_admin no necesita permisos: Shield lo sobrepone.
    }
}
