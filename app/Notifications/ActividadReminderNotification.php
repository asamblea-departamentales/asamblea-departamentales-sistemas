<?php

namespace App\Notifications;

use App\Models\Actividad;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ActividadReminderNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $actividad;

    public function __construct(Actividad $actividad)
    {
        $this->actividad = $actividad;
        $this->onQueue('notifications');
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        $timeRemaining = $this->calculateTimeRemaining();

        return [
            'format'=>'filament',
            'title' => '📅 Recordatorio de Actividad',
            'body' => "La actividad '{$this->actividad->macroactividad}' comienza pronto. {$timeRemaining}",
            'icon' => 'heroicon-o-clock',
            'iconColor' => $this->getNotificationColor(),
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Ver Actividad',
                    'url' => \App\Filament\Resources\ActividadResource::getUrl('view', ['record' => $this->actividad]),
                    'color' => 'primary',
                ],
                [
                    'name' => 'edit',
                    'label' => 'Editar',
                    'url' => \App\Filament\Resources\ActividadResource::getUrl('edit', ['record' => $this->actividad]),
                    'color' => 'gray',
                ],
            ],
            'data' => [
                'actividad_id' => $this->actividad->id,
                'macroactividad' => $this->actividad->macroactividad,
                'estado' => $this->actividad->estado,
                'star_date' => (string) $this->actividad->star_date,
                'reminder_at' => (string) $this->actividad->reminder_at,
                'color' => $this->getNotificationColor(),
                'type' => 'actividad_reminder',
            ],
        ];
    }

    public function toBroadcast($notifiable)
    {
        \Log::error('=== BROADCAST DEBUG: Método ejecutándose ===');
        
        \Log::info('📡 Broadcasting notification', [
            'notifiable_id' => $notifiable->id,
            'canal' => 'notifications.' . $this->actividad->user->id
        ]);
    
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastOn()
{
    \Log::error('=== CANAL DEBUG ===', ['canal' => 'App.Models.User' . $this->actividad->user_id]);
    return new PrivateChannel('App.Models.User' . $this->actividad->user_id);
}

    public function broadcastAs()
    {
        return 'notification';
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }

    private function calculateTimeRemaining(): string
    {
        if (!$this->actividad->star_date) return '';

        $now = now();
        $fechaInicio = Carbon::parse($this->actividad->star_date);

        if ($fechaInicio->isPast()) {
            $diff = $now->diff($fechaInicio);
            return "⚠️  Debería haber comenzado hace {$diff->h}h {$diff->i}m";
        }

        $diff = $now->diff($fechaInicio);
        
        if ($diff->days > 0) return "⏳ Comienza en {$diff->days}d {$diff->h}h";
        if ($diff->h > 0) return "⏳ Comienza en {$diff->h}h {$diff->i}m";

        return "⏳ Comienza en {$diff->i} minutos";
    }

    private function getNotificationColor(): string
    {
        if (!$this->actividad->star_date) return 'gray';

        $now = now();
        $fechaInicio = Carbon::parse($this->actividad->star_date);
        $hoursUntilStart = $now->diffInHours($fechaInicio, false);

        if ($hoursUntilStart < 0) return 'danger';
        if ($hoursUntilStart <= 1) return 'warning';
        if ($hoursUntilStart <= 24) return 'info';

        return 'primary';
    }

    public function delay($notifiable)
    {
        // 👈 CAMBIAR: Para debugging, enviar inmediatamente
        return now();
        
        // Comentar temporalmente para debugging:
        // if ($this->actividad->reminder_at && $this->actividad->reminder_at->isFuture()) {
        //     return $this->actividad->reminder_at;
        // }
        // return now();
    }
}