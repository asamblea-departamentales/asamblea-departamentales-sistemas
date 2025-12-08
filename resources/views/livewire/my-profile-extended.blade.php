{{-- resources/views/livewire/my-profile-extended.blade.php --}}
<style>
    /* ❌ ESconde el UpdatePassword DEFAULT de Breezy para NO-TI */
    @if(!auth()->user()->hasAnyRole('ti'))
        /* Por ID del componente Breezy */
        div[wire\:id*="FilamentBreezy.UpdatePassword"],
        div[wire\:id*="breezy.UpdatePassword"],
        /* Por clase/título común de Breezy */
        .fi-ta-section:has([wire\:id*="UpdatePassword"]):not(.my-custom-section),
        /* Títulos duplicados */
        h2:contains("Password"), h2:contains("Contraseña") ~ div[wire\:id*="UpdatePassword"] {
            display: none !important;
        }
    @endif
</style>


<x-filament-breezy::grid-section md=2 :title="__('filament-breezy::default.profile.personal_info.heading')" :description="__('filament-breezy::default.profile.personal_info.subheading')">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    Update
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>
