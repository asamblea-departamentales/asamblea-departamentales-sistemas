<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Validation\ValidationException;

class OpsLogin extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.ops-login'; // Blade propio

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')
                ->label('Correo')
                ->email()
                ->required()
                ->autofocus()
                ->placeholder('tu@correo.com'),

            Forms\Components\TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->required(),

            Forms\Components\Checkbox::make('remember')
                ->label('Recordarme'),
        ]);
    }

    // (Opcional) Mensaje de error único
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'email' => 'Credenciales inválidas.',
        ]);
    }
}
