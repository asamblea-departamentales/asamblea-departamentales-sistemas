<?php

// app/Filament/Pages/Auth/Login.php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use App\Services\LdapAuthenticator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Ldap;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class Login extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Nombre de Usuario')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
            ->placeholder('nombre de usuario')
            ->prefixIcon('heroicon-o-user')
            ->maxLength(255);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Contraseña')
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3" class="text-sm font-medium text-primary-600 hover:text-primary-500"> ¿Olvidaste tu contraseña? </x-filament::link>')) : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2])
            ->placeholder('Ingresa tu contraseña')
            ->prefixIcon('heroicon-o-user-lock')
            ->maxLength(255);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label('Recordarme');
    }

    public function getTitle(): string
    {
        return 'Acceso al Sistema - Asamblea Legislativa';
    }

    public function getHeading(): string
    {
        return 'Bienvenido';
    }

    public function getSubHeading(): string
    {
        return 'Sistema de Gestión Departamental';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction()
                ->label('Iniciar Sesión')
                ->color('primary'),
        ];
    }

    public function authenticate(): ?LoginResponse
{
    $credentials = $this->getCredentialsFromFormData($this->form->getState());
    $username = $credentials['username'];
    $password = $credentials['password'];

    // Buscar usuario local
    $user = User::where('username', $username)->first();

    if (!$user) {
        $this->throwFailureValidationException();
    }

    // Autenticar contra LDAP
    $ldapAuth = app(LdapAuthenticator::class);

    if (!$ldapAuth->authenticate($username, $password)) {
        $this->throwFailureValidationException();
    }

    // Login en Laravel
    Auth::login($user, $this->remember);

    return app(LoginResponse::class);
} 

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    // Método para personalizar mensajes de validación
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}
