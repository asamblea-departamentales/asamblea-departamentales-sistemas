<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Route;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    public function getTitle(): string
    {
        return 'Crear Rol';
    }

    public function getBreadcrumbs(): array
{
    return [
        route('filament.admin.pages.dashboard') => 'Dashboard',
        route('filament.admin.resources.roles.index') => 'Roles',
        '#' => 'Crear', // o directamente: '' => 'Crear',
    ];
}
}
