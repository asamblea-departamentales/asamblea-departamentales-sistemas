<?php 

namespace App\Filament\Resources\CierreMensualResource\Pages;

use App\Filament\Resources\ActividadResource;
use App\Filament\Resources\CierreMensualResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListCierreMensuals extends ListRecords
{
    protected static string $resource = CierreMensualResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('generar')
                ->label('Generar Cierre Mensual')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->action(function () {
                    $user = auth()->user();

                    app(\App\Services\CierreMensualService::class)->generarCierre(
                        $user->departamental_id,
                        now()->month,
                        now()->year,
                        $user->id
                    );

                    Notification::make()
                        ->title('Cierre generado correctamente')
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()->hasAnyRole(['super_admin','coordinador','gol'])),

            Actions\Action::make('generar_cierre')
                ->label('Ir a Actividades')
                ->icon('heroicon-o-plus-circle')
                ->color('gray')
                ->url(fn () => ActividadResource::getUrl('index'))
                ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'coordinador', 'gol'])),     
        ];
    }
}
