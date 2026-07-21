<?php

// app/Filament/Pages/Auth/Login.php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

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
            ->prefixIcon('heroicon-o-lock-closed')
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
        $data = $this->form->getState();
        $username = $data['username'];
        $password = $data['password'];
        $remember = $data['remember'] ?? false;

        // Check if user exists locally to determine auth strategy
        $localUser = User::where('username', $username)->first();

        if ($localUser && $this->isSuperAdmin($localUser)) {
            // SuperAdmin: authenticate against local DB
            if (! $localUser->password || ! Hash::check($password, $localUser->password)) {
                $this->throwFailureValidationException();
            }
            Auth::login($localUser, $remember);
        } else {
            // All other users: authenticate via LDAP (with local fallback if needed)
            $credentials = ['username' => $username, 'password' => $password];

            // If user exists locally, add fallback for offline scenarios
            if ($localUser) {
                $credentials['fallback'] = ['username' => $username, 'password' => $password];
            }

            if (! Auth::attempt($credentials, $remember)) {
                $this->throwFailureValidationException();
            }
        }

        return app(LoginResponse::class);
    }

    protected function isSuperAdmin(User $user): bool
    {
        return $user->hasRole(config('filament-shield.super_admin.name'));
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
            'remember' => $data['remember'] ?? false,
        ];
    }
}
