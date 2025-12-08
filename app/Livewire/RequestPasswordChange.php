<?php

namespace App\Livewire;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Livewire\Component;

class RequestPasswordChange extends Component
{
    public $hasPendingRequest = false;
    public $pendingTicket = null;

    public function mount()
    {
        // Verificar si ya tiene un ticket pendiente de cambio de contraseña
        $this->checkPendingRequest();
    }

    public function checkPendingRequest()
    {
        $this->pendingTicket = Ticket::where('tipo_ticket', 'SOLICITUD')
            ->where('motivo', 'like', '%Solicitud de cambio de contraseña por parte del usuario ' . auth()->user()->name . '%')
            ->where('estado_interno', 'PENDIENTE')
            ->latest()
            ->first();

        $this->hasPendingRequest = $this->pendingTicket !== null;
    }

    public function request()
    {
        // Verificar nuevamente antes de crear
        $this->checkPendingRequest();

        if ($this->hasPendingRequest) {
            Notification::make()
                ->title('Solicitud Pendiente')
                ->warning()
                ->body('Ya tiene una solicitud de cambio de contraseña pendiente.')
                ->send();
            
            return;
        }

        try {
            Ticket::create([
                'tipo_ticket' => 'SOLICITUD',
                'motivo' => 'Solicitud de cambio de contraseña por parte del usuario ' . auth()->user()->name,
                'fecha_solicitud' => Carbon::now(),
                'estado_interno' => 'PENDIENTE',
                'departamental_id' => auth()->user()->departamental_id,
                'observaciones' => 'El usuario ' . auth()->user()->email . ' ha solicitado un cambio de contraseña.'
            ]);

            // Actualizar el estado
            $this->checkPendingRequest();

            Notification::make()
                ->title('Solicitud Enviada')
                ->success()
                ->body('Se ha creado un ticket para el cambio de contraseña.')
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body('No se pudo crear la solicitud. Por favor intente nuevamente.')
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.request-password-change');
    }
}