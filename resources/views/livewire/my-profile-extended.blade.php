{{-- resources/views/livewire/my-profile-extended.blade.php --}}

<style>
    /* ❌ Ocultar el UpdatePassword DEFAULT de Breezy para NO-TI */
    @if(!auth()->user()->hasAnyRole('ti'))
        div[wire\:id*="FilamentBreezy.UpdatePassword"],
        div[wire\:id*="breezy.UpdatePassword"],
        .fi-ta-section:has([wire\:id*="UpdatePassword"]):not(.my-custom-section),
        h2:contains("Password"), h2:contains("Contraseña") ~ div[wire\:id*="UpdatePassword"] {
            display: none !important;
        }
    @endif
</style>

<x-filament-breezy::grid-section
    md="2"
    :title="__('filament-breezy::default.profile.personal_info.heading')"
    :description="__('filament-breezy::default.profile.personal_info.subheading')"
>
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">

            {{-- Renderiza el formulario definido en MyProfileExtended --}}
            {{ $this->form }}

            <div class="flex justify-end gap-2">
                {{-- Botón para guardar cambios normales --}}
                <x-filament::button type="submit">
                    Update
                </x-filament::button>

                {{-- Botón de solicitud de cambio de contraseña para NO-TI --}}
                @if(!$this->isUserTI())
                    <x-filament::button
                        type="button"
                        color="primary"
                        wire:click="requestPasswordChange"
                        icon="heroicon-o-key"
                    >
                        Solicitar Cambio de Contraseña
                    </x-filament::button>
                @endif
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>
