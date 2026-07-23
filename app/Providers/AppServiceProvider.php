<?php

namespace App\Providers;

use App\Models\Blog\Post;
use App\Observers\PostObserver;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->validateLdapConfig();

        Post::observe(PostObserver::class);

        Table::configureUsing(function (Table $table): void {
            $table
                ->emptyStateHeading('No data yet')
                ->defaultPaginationPageOption(10)
                ->paginated([10, 25, 50, 100])
                ->extremePaginationLinks()
                ->defaultSort('created_at', 'desc');
        });

        // # \Opcodes\LogViewer
        LogViewer::auth(function ($request) {
            $user = auth()->user();
            $role = $user?->roles?->first()?->name;

            return $role == config('filament-shield.super_admin.name');
        });

        // # Filament Hooks
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): View => view('filament.components.panel-footer'),
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn () => view('filament.components.impersonate-banner')
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_AFTER,
            fn (): View => view('filament.components.manual-button'),
        );
    }

    protected function validateLdapConfig(): void
    {
        if (! env('LDAP_ENABLED', true)) {
            return;
        }

        $missing = array_filter([
            'LDAP_HOSTS' => env('LDAP_HOSTS'),
            'LDAP_BASE_DN' => env('LDAP_BASE_DN'),
        ], fn ($v) => empty($v));

        if ($missing) {
            Log::warning('LDAP habilitado pero variables faltantes: ' . implode(', ', array_keys($missing)));
        }
    }
}
