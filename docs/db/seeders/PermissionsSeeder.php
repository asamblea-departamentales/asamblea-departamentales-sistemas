<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos específicos para el sistema
        $permissions = [
            // Permisos generales
            'view_any_actividad',
            'view_actividad',
            'create_actividad',
            'update_actividad',
            'delete_actividad',
            
            // Permisos de usuarios
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            
            // Permisos de roles
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
            
            // Otros permisos del sistema
            'access_log_viewer',
            'view_any_contact_us',
            'view_contact_us',
            'create_contact_us',
            'update_contact_us',
            'delete_contact_us',
            
            'view_any_post',
            'view_post',
            'create_post',
            'update_post',
            'delete_post',
            
            'view_any_menu',
            'view_menu',
            'create_menu',
            'update_menu',
            'delete_menu',
            
            'view_any_category',
            'view_category',
            'create_category',
            'update_category',
            'delete_category',
            
            'view_any_content',
            'view_content',
            'create_content',
            'update_content',
            'delete_content',
        ];

        // Crear permisos si no existen
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $analystRole = Role::firstOrCreate(['name' => 'Analyst']);
        $viewerRole = Role::firstOrCreate(['name' => 'Viewer']);

        // Configurar permisos para Admin (puede hacer todo, incluyendo eliminar)
        $adminPermissions = Permission::all();
        $adminRole->syncPermissions($adminPermissions);

        // Configurar permisos para Analyst (puede ver, crear y editar, pero NO eliminar)
        $analystPermissions = Permission::where('name', 'not like', '%delete%')->get();
        $analystRole->syncPermissions($analystPermissions);

        // Configurar permisos para Viewer (solo lectura)
        $viewerPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', 'view%')
                  ->orWhere('name', '=', 'access_log_viewer');
        })->get();
        $viewerRole->syncPermissions($viewerPermissions);

        // Mantener compatibilidad con roles existentes si es necesario
        $superAdminRoleName = config('filament-shield.super_admin.name', 'super_admin');
        $superAdmin = Role::where('name', $superAdminRoleName)->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        $this->command->info('Roles y permisos configurados correctamente:');
        $this->command->info('- Admin: Todos los permisos (incluyendo eliminar)');
        $this->command->info('- Analyst: Ver, crear y editar (sin eliminar)');
        $this->command->info('- Viewer: Solo lectura');
    }
}