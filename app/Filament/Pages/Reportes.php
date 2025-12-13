<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;

class Reportes extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static string $view = 'filament.pages.reportes';
    
    protected static ?string $navigationLabel = 'Reportes';
    
    protected static ?string $title = 'Dashboard de Reportes';
    
    protected static ?string $navigationGroup = 'Reportes y Cierres';
    
    protected static ?int $navigationSort = 10;
    
    public static function canAccess(): bool
    {
        return auth()->check();
    }
    
    public function mount(): void
    {
        if (!config('services.powerbi.embed_url')) {
            Notification::make()
                ->warning()
                ->title('Power BI no configurado')
                ->body('Contacta al administrador para configurar el acceso a reportes.')
                ->persistent()
                ->send();
        }
        
        \Log::info('Acceso a reportes Power BI', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'user_email' => auth()->user()->email,
            'ip' => request()->ip(),
            'timestamp' => now(),
        ]);
    }
}