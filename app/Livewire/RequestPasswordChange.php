<?php

namespace App\Livewire;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Livewire\Component;

class RequestPasswordChange extends Component
{
    //Funcion para avisar del cambio de contraseña solicitado
    public function request()
    {
        Ticket::create([
            'tipo_ticket' => 'SOLICITUD', //usamos la constante del modelo Ticket
            'motivo' => 'Solicitud de cambio de contraseña por parte del usuario ' . auth()->user()->name,
            'fecha_solicitud' => Carbon::now(),
            'estado_interno' => 'PENDIENTE', //se usa la constante del modelo Ticket
            'oficina' => auth()->user()->oficina ?? 'No especificada',
            'observaciones' => 'El usuario ' . auth()->user()->email . ' ha solicitado un cambio de contraseña.'
        ]);

        //Notificacion que se disparara
        Notification::make()
            ->title('Solicitud de cambio de contraseña enviada')
            ->success()
            ->body('Se ha creado un ticket para el cambio de contraseña.')            
            ->send();
    }
    public function render()
    {
        return view('livewire.request-password-change');
    }
}
