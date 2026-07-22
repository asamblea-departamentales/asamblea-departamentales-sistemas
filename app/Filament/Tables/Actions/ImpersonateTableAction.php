<?php

namespace App\Filament\Tables\Actions;

use Filament\Facades\Filament;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Lab404\Impersonate\Services\ImpersonateManager;

class ImpersonateTableAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'impersonate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Ver como')
            ->icon('heroicon-o-user')
            ->color('warning')
            ->action(function (Model $record): void {
                if (! $this->canBeImpersonated($record)) {
                    return;
                }

                $backToKey = config('superduper.impersonate.back_to_session_key');
                $guardKey = config('laravel-impersonate.session_guard_using');
                $guard = Filament::getCurrentPanel()->getAuthGuard() ?? config('laravel-impersonate.default_impersonator_guard');

                session()->put([
                    $backToKey => request('fingerprint.path', request()->header('referer')) ?? Filament::getCurrentPanel()->getUrl(),
                    $guardKey => $guard,
                ]);

                app(ImpersonateManager::class)->take(
                    Filament::auth()->user(),
                    $record,
                    $guard
                );

                $redirectTo = config('laravel-impersonate.take_redirect_to', '/');
                $this->redirect($redirectTo);
            })
            ->visible(function (Model $record): bool {
                $user = Auth::user();
                $canImpersonate = config('superduper.impersonate.can_impersonate_method', 'canImpersonate');
                $canBeImpersonated = config('superduper.impersonate.can_be_impersonated_method', 'canBeImpersonated');
                $notSame = $user && isset($user->id, $record->id) && $user->id !== $record->id;

                return $user
                    && method_exists($user, $canImpersonate) && $user->$canImpersonate()
                    && $notSame
                    && ! app(ImpersonateManager::class)->isImpersonating()
                    && method_exists($record, $canBeImpersonated) && $record->$canBeImpersonated();
            })
            ->requiresConfirmation()
            ->modalHeading('Ver como este usuario')
            ->modalDescription(function (Model $record): string {
                return "¿Deseas ver el sistema como {$record->name}?";
            })
            ->modalSubmitActionLabel('Confirmar');
    }

    protected function canBeImpersonated(Model $target): bool
    {
        $current = Filament::auth()->user();
        $canImpersonate = config('superduper.impersonate.can_impersonate_method', 'canImpersonate');
        $canBeImpersonated = config('superduper.impersonate.can_be_impersonated_method', 'canBeImpersonated');

        $notSame = false;
        if ($current && method_exists($current, 'isNot')) {
            $notSame = $current->isNot($target);
        } elseif ($current && $target && isset($current->id, $target->id)) {
            $notSame = $current->id !== $target->id;
        }

        return $current && method_exists($current, $canImpersonate) && $current->$canImpersonate()
            && $notSame
            && ! app(ImpersonateManager::class)->isImpersonating()
            && method_exists($target, $canBeImpersonated) && $target->$canBeImpersonated();
    }
}
