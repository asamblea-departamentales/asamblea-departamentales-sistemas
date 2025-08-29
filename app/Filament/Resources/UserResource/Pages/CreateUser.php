<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Settings\MailSettings;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Antes de crear:
     * - Si quien crea NO es rol central (TI/GOL), forzamos su mismo departamental.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $auth = Filament::auth()->user();

        /** @var \App\Models\User|null $auth */ // <- ayuda al IDE
        if (
            $auth
            && ! ($auth->hasAnyRole(['ti', 'gol']) || $auth->hasRole(config('filament-shield.super_admin.name')))
        ) {
            $data['departamental_id'] = $auth->departamental_id;
        }

        return $data;
    }

    /**
     * Después de crear:
     * - Si el toggle "mark_email_verified" viene activo, marcamos email como verificado y NO enviamos correo.
     * - Si NO viene activo, enviamos el correo de verificación (si SMTP está configurado).
     */
    protected function afterCreate(): void
    {
        $user = $this->record;
        $state = $this->form->getState();       // aquí está 'mark_email_verified'
        $settings = app(MailSettings::class);

        // 1) Verificar directamente (sin correo)
        if (! empty($state['mark_email_verified'])) {
            if (is_null($user->email_verified_at)) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            Notification::make()
                ->title(__('resource.user.notifications.verify_sent.title') ?: 'Usuario verificado')
                ->success()
                ->send();

            return; // saltamos el envío de email
        }

        // 2) Enviar correo de verificación
        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;
            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        if ($settings->isMailSettingsConfigured()) {
            $notification = new VerifyEmail;
            $notification->url = Filament::getVerifyEmailUrl($user);

            $settings->loadMailSettingsToConfig();
            $user->notify($notification);

            Notification::make()
                ->title(__('resource.user.notifications.verify_sent.title') ?: 'Se envió el correo de verificación')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('resource.user.notifications.verify_warning.title') ?: 'Configura el correo')
                ->body(__('resource.user.notifications.verify_warning.description') ?: 'No hay SMTP configurado para enviar la verificación.')
                ->warning()
                ->send();
        }
    }
}
