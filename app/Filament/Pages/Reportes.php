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

    /**
     * Determina quien puede ver esta pagina en el menu y acceder a ella
     * 
     * Ahorita en el server Cloudways, esta configurado para que cualquier usuario pueda acceder
     * Para el server de la asamblea, se puede modificar para que solo ciertos roles puedan acceder
     */
    
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    /**
     * Hook de inicializacion 
     * Se ejecuta al montar la pagina
     * Verifica si la configuracion de Power BI esta presente
     * Si no, muestra una notificacion al usuario
     * Ademas, registra en el log el acceso del usuario a los reportes para auditoria futura
     * Los logs se guardan en storage/logs/laravel.log
     */
    
    public function mount(): void
    {
        //Verifica si la URL de embed de Power BI esta configurada en el .env, y muestra notificacion
        if (!config('services.powerbi.embed_url')) {
            Notification::make()
                ->warning()
                ->title('Power BI no configurado')
                ->body('Contacta al administrador para configurar el acceso a reportes.')
                ->persistent()
                ->send();
        }
        //Logs de acceso a reportes Power BI, registra cada acceso con informacion relevante, si es necesario
        //Se puede agregar mas datos como 'departamental' => auth()->user()->department ?? 'N/A',
        \Log::info('Acceso a reportes Power BI', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'user_email' => auth()->user()->email,
            'ip' => request()->ip(),
            'timestamp' => now(),
        ]);
    }

    // No se necesita mas codigo por ahora, la vista Blade se encarga de cargar el iframe con Power BI
    //Aqui se podrian agregar metodos adicionales si se requiere mas funcionalidad en el futuro
}