<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\Actions\ImpersonatePageAction;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            ImpersonatePageAction::make()->record($this->record),
            
            Actions\ActionGroup::make([
                // ✅ Acción de Cambiar Contraseña (mejorada)
                Actions\Action::make('change_password')
                    ->label('Cambiar Contraseña')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('new_password')
                            ->label('Nueva Contraseña')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->revealable()
                            ->helperText('Mínimo 8 caracteres')
                            ->autocomplete('new-password'),
                        
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Confirmar Nueva Contraseña')
                            ->password()
                            ->required()
                            ->revealable()
                            ->same('new_password')
                            ->dehydrated(false)
                            ->autocomplete('new-password'),
                    ])
                    ->modalWidth(Support\Enums\MaxWidth::Medium)
                    ->modalHeading('Cambiar Contraseña de Usuario')
                    ->modalDescription(fn ($record) => "Usuario: {$record->name} ({$record->email})")
                    ->modalAlignment(Alignment::Center)
                    ->modalSubmitActionLabel('Cambiar Contraseña')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (array $data) {
                        $this->record->update([
                            'password' => Hash::make($data['new_password']),
                        ]);

                        Notification::make()
                            ->title('Contraseña Actualizada')
                            ->success()
                            ->body("La contraseña de {$this->record->name} ha sido actualizada correctamente.")
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-shield-exclamation')
                    ->modalIconColor('warning'),

                Actions\DeleteAction::make()
                    ->label('Eliminar Usuario')
                    ->extraAttributes(['class' => 'border-b']),

                Actions\Action::make('create_new')
                    ->label('Crear Nuevo Usuario')
                    ->icon('heroicon-o-user-plus')
                    ->url(fn (): string => static::$resource::getUrl('create'))
                    ->color('success'),
            ])
                ->icon('heroicon-m-ellipsis-horizontal')
                ->label('Más Acciones')
                ->button()
                ->tooltip('Más Acciones')
                ->color('gray'),
        ];

        return $actions;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Editar Usuario: ' . $this->record->name;
    }

    public function getHeading(): string|Htmlable
    {
        $title = $this->record->name;
        $badge = $this->getBadgeStatus();

        return new HtmlString("
            <div class='flex items-center space-x-2'>
                <div>$title</div>
                $badge
            </div>
        ");
    }

    public function getBadgeStatus(): string|Htmlable
    {
        if (empty($this->record->email_verified_at)) {
            $icon = Blade::render('<x-fluentui-error-circle-24 class="w-5 h-5 text-danger-600" title="No Verificado" />');
            $badge = "<span class='inline-flex items-center' title='No Verificado'>"
                .$icon.'</span>';
        } else {
            $icon = Blade::render('<x-fluentui-checkmark-starburst-24 class="w-5 h-5 text-success-600" title="Verificado" />');
            $badge = "<span class='inline-flex items-center' title='Verificado'>"
                .$icon.'</span>';
        }

        return new HtmlString($badge);
    }
}