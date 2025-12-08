{{-- resources/views/livewire/request-password-change.blade.php --}}
<div class="space-y-4">
    @if($hasPendingRequest && $pendingTicket)
        <x-filament::section>
            <x-slot name="heading">
                Solicitud Pendiente
            </x-slot>
            <x-slot name="description">
                Ya tiene una solicitud de cambio de contraseña en proceso (Ticket #{{ $pendingTicket->id }}).
            </x-slot>
            <p class="text-sm text-gray-600">
                Solicitado el: {{ $pendingTicket->fecha_solicitud->format('d/m/Y H:i') }}
            </p>
        </x-filament::section>
    @else
        <x-filament::section>
            <x-slot name="heading">
                Cambio de Contraseña
            </x-slot>
            <x-slot name="description">
                Para cambiar su contraseña, debe crear una solicitud que será procesada por el departamento de TI.
            </x-slot>
            
            <x-filament::button 
                wire:click="request" 
                color="primary"
                icon="heroicon-o-key"
            >
                Solicitar Cambio de Contraseña
            </x-filament::button>
        </x-filament::section>
    @endif
</div>