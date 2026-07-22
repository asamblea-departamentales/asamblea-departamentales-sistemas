<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MapaDeActores extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $title = 'Mapa de Actores';

    protected static string $view = 'filament.pages.mapa-de-actores';

    public function getViewData(): array
    {
        return [
            'roles' => [
                [
                    'name' => 'Super Admin',
                    'icon' => 'heroicon-o-shield-check',
                    'description' => 'Acceso total al sistema. Administra usuarios, roles, permisos y configuraciones.',
                ],
                [
                    'name' => 'Tecnologia de Informacion',
                    'icon' => 'heroicon-o-wrench-screwdriver',
                    'description' => 'Soporte tecnico, gestion de infraestructura y asistencia tecnica.',
                ],
                [
                    'name' => 'Gestion de Oficina Local',
                    'icon' => 'heroicon-o-chart-bar',
                    'description' => 'Consulta indicadores, dashboards y estadísticas territoriales.',
                ],
                [
                    'name' => 'Coordinador',
                    'icon' => 'heroicon-o-briefcase',
                    'description' => 'Gestiona actividades, atestados y cierres mensuales en su oficina.',
                ],
                [
                    'name' => 'Asistente Tecnico',
                    'icon' => 'heroicon-o-pencil-square',
                    'description' => 'Apoya la carga operativa: ejecucion, atestados, bitacoras.',
                ],
                [
                    'name' => 'Auditoria',
                    'icon' => 'heroicon-o-eye',
                    'description' => 'Acceso de solo lectura a reportes, bitacoras y registros cerrados.',
                ],
            ],
        ];
    }
}
