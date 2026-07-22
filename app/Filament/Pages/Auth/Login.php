<?php

// app/Filament/Pages/Auth/Login.php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class Login extends BaseLogin
{
    protected int $maxAttempts = 5;
    protected int $decayMinutes = 1;

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

    protected function throttleKey(): string
    {
        return strtolower($this->data['username'] ?? '') . '|' . request()->ip();
    }

    public function authenticate(): ?LoginResponse
    {
        $throttleKey = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'data.username' => 'Demasiados intentos. Intente de nuevo en ' . $seconds . ' segundos.',
            ]);
        }

        $data = $this->form->getState();
        $username = $data['username'];
        $password = $data['password'];
        $remember = $data['remember'] ?? false;

        $localUser = User::where('username', $username)->first();
        $ldapEnabled = env('LDAP_ENABLED', true);

        if (! $ldapEnabled) {
            if (! $localUser || ! $localUser->password || ! Hash::check($password, $localUser->password)) {
                RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
                $this->throwFailureValidationException();
            }
            if ($localUser->activo === false) {
                $this->throwFailureValidationException();
            }
            Auth::login($localUser, $remember);
        } elseif ($localUser && $this->isSuperAdmin($localUser)) {
            if (! $localUser->password || ! Hash::check($password, $localUser->password)) {
                RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
                $this->throwFailureValidationException();
            }
            if ($localUser->activo === false) {
                $this->throwFailureValidationException();
            }
            Auth::login($localUser, $remember);
        } else {
            $credentials = ['samaccountname' => $username, 'password' => $password];

            if ($localUser) {
                $credentials['fallback'] = ['samaccountname' => $username, 'password' => $password];
            }

            try {
                if (! Auth::attempt($credentials, $remember)) {
                    RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
                    $this->throwFailureValidationException();
                }
            } catch (QueryException $e) {
                RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
                Notification::make()
                    ->title('Aún no tenés acceso al sistema')
                    ->body('Tu usuario no está registrado. Pedile al administrador que te cree un usuario.')
                    ->danger()
                    ->send();
                $this->throwFailureValidationException();
            }

            $authenticatedUser = Auth::user();
            if ($authenticatedUser && $authenticatedUser->activo === false) {
                Auth::logout();
                $this->throwFailureValidationException();
            }
        }

        RateLimiter::clear($throttleKey);

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
