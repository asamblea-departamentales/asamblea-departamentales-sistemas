<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class DepartamentalFaltanteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $departamental_id;
    public string $departamental_nombre;
    public int $mes;
    public int $anio;

    public function __construct(int $departamental_id, string $departamental_nombre, ?int $mes = null, ?int $anio = null)
    {
        $this->departamental_id = $departamental_id;
        $this->departamental_nombre = $departamental_nombre;
        $nextMonth = now()->addMonth();
        $this->mes = $mes ?? $nextMonth->month;
        $this->anio = $anio ?? $nextMonth->year;
        $this->onQueue('notifications');
    }

    /**
     * IMPORTANTE: Solo usar 'database' 
     * Filament maneja el broadcast automáticamente
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Formato de notificación para Filament
     */
    public function toDatabase($notifiable): array
    {
        $mesTexto = $this->obtenerNombreMes($this->mes);
        
        // Usar FilamentNotification para crear el formato correcto
        return FilamentNotification::make()
            ->warning()
            ->title('Departamental sin actividades')
            ->body("La departamental **{$this->departamental_nombre}** no ha ingresado actividades para **{$mesTexto} {$this->anio}**.")
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning')
            ->actions([
                Action::make('activities')
                    ->label('Ver Actividades')
                    ->url("/admin/actividades?tableFilters[departamental_id][value]={$this->departamental_id}")
                    ->button()
                    ->color('primary'),
            ])
            ->getDatabaseMessage(); // Esto retorna el array en formato Filament
    }

    /**
     * Obtener el nombre del mes en español
     */
    private function obtenerNombreMes(int $mes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return $meses[$mes] ?? 'Desconocido';
    }
}