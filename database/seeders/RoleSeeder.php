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

        $roles = ['super_admin', 'ti', 'gol', 'coordinador', 'asistente_tecnico', 'auditoria'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => $guard]);
        }

        $grant = function (string $role, array $perms) use ($guard) {
            $r = Role::where('name', $role)->firstOrFail();
            foreach ($perms as $p) {
                Permission::findOrCreate($p, $guard);
            }
            $r->syncPermissions(array_unique($perms));
        };

        $crud = fn (string $r) => ["view_any_$r", "view_$r", "create_$r", "update_$r", "delete_$r"];
        $read = fn (string $r) => ["view_any_$r", "view_$r"];

        $resources = ['actividad', 'ejecucion', 'documento', 'bitacora', 'cierre_mensual'];

        // TI: central, todo + admin
        $ti = ['access_admin'];
        foreach ($resources as $m) {
            $ti = array_merge($ti, $crud($m));
        }
        $ti = array_merge($ti, [
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user',
            'view_any_departamental', 'view_departamental', 'create_departamental', 'update_departamental', 'delete_departamental',
            'view_any_ticket', 'view_ticket', 'create_ticket', 'update_ticket', 'delete_ticket',
            'view_any_requisicion', 'view_requisicion', 'create_requisicion', 'update_requisicion', 'delete_requisicion',
            'view_any_contrato', 'view_contrato', 'create_contrato', 'update_contrato', 'delete_contrato',
            'view_any_setting', 'view_setting', 'update_setting',
            'view_any_catalogo', 'view_catalogo', 'create_catalogo', 'update_catalogo', 'delete_catalogo',
            'access_log_viewer',
            'export_actividad', 'export_ejecucion', 'export_cierre_mensual',
        ]);
        $grant('ti', $ti);

        // GOL: central, solo lectura + export
        $gol = ['access_admin'];
        foreach ($resources as $m) {
            $gol = array_merge($gol, $read($m));
        }
        $gol = array_merge($gol, [
            'view_any_departamental', 'view_departamental',
            'view_any_ticket', 'view_ticket',
            'view_any_requisicion', 'view_requisicion',
            'view_any_contrato', 'view_contrato',
            'export_actividad', 'export_ejecucion', 'export_cierre_mensual',
        ]);
        $grant('gol', $gol);

        // Coordinador: scoped a su departamental; puede generar cierre
        $coord = ['access_admin'];
        foreach (['actividad', 'ejecucion', 'documento', 'bitacora'] as $m) {
            $coord = array_merge($coord, $crud($m));
        }
        $coord = array_merge($coord, [
            'view_any_cierre_mensual', 'view_cierre_mensual', 'create_cierre_mensual', 'export_cierre_mensual',
            'view_any_departamental', 'view_departamental',
            'view_any_ticket', 'view_ticket',
            'view_any_requisicion', 'view_requisicion',
            'view_any_contrato', 'view_contrato',
        ]);
        $grant('coordinador', $coord);

        // Asistente Tecnico: scoped, sin delete ni cierre
        $asis = [
            'access_admin',
            'view_any_actividad', 'view_actividad', 'create_actividad', 'update_actividad',
            'view_any_ejecucion', 'view_ejecucion', 'create_ejecucion', 'update_ejecucion',
            'view_any_documento', 'view_documento', 'create_documento',
            'view_any_bitacora', 'view_bitacora', 'create_bitacora',
            'view_any_cierre_mensual', 'view_cierre_mensual',
            'view_any_departamental', 'view_departamental',
            'view_any_ticket', 'view_ticket',
            'view_any_requisicion', 'view_requisicion',
            'view_any_contrato', 'view_contrato',
        ];
        $grant('asistente_tecnico', $asis);

        // Auditoria: scoped, lectura + logs + export
        $aud = ['access_admin'];
        foreach ($resources as $m) {
            $aud = array_merge($aud, $read($m));
        }
        $aud = array_merge($aud, [
            'access_log_viewer', 'export_cierre_mensual',
            'view_any_departamental', 'view_departamental',
            'view_any_ticket', 'view_ticket',
            'view_any_requisicion', 'view_requisicion',
            'view_any_contrato', 'view_contrato',
        ]);
        $grant('auditoria', $aud);

        // super_admin no necesita permisos explicitos: Shield lo sobrepone.
    }
}
