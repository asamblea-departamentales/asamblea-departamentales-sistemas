<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EmailVerification;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Resources\ActividadResource;
use App\Filament\Resources\ContratoResource;
use App\Filament\Resources\DepartamentalResource;
use App\Filament\Resources\RequisicionResource;
use App\Filament\Resources\Shield\RoleResource;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\ActividadChart;
use App\Filament\Widgets\ActividadOverview;
use App\Filament\Widgets\CalendarioWidget;
use App\Http\Middleware\FilamentRobotsMiddleware;
use App\Settings\GeneralSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')

            // Auth
            ->login(action: \App\Filament\Pages\Auth\Login::class)
            ->passwordReset(RequestPasswordReset::class)
            ->emailVerification(EmailVerification::class)

            // Branding desde settings
            ->favicon(fn (GeneralSettings $s) => $s->site_favicon ? Storage::url($s->site_favicon) : asset('images/logo-asamblea1.png'))            ->brandName(fn (GeneralSettings $s) => $s->brand_name ?: 'Asamblea Legislativa')
            ->brandLogo(fn (GeneralSettings $s) => $s->brand_logo ? asset($s->brand_logo) : asset('images/logo-azul-fondo-transparente (002).png'))
            ->brandLogoHeight(fn (GeneralSettings $s) => $s->brand_logoHeight ?: '15rem')
            ->colors(fn (GeneralSettings $s) => $s->site_theme ?: [
                'primary' => \Filament\Support\Colors\Color::hex('#0A2C65'),
            ])

            // 🔔 NOTIFICACIONES
            ->databaseNotifications()              // Habilita notificaciones en BD
            ->databaseNotificationsPolling('30s')  // Polling cada 30 segundos
            // NO existe ->broadcastNotifications() en tu versión
            // El broadcasting funciona automáticamente si está configurado
            
            // UX
            ->spa()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->darkMode(false)
            ->viteTheme('resources/css/filament/admin/theme.css')

            // Resources
            ->resources([
                UserResource::class,
                DepartamentalResource::class,
                ActividadResource::class,
                RoleResource::class,
                RequisicionResource::class,
                TicketResource::class,
                ContratoResource::class,
            ])

            // Pages
            ->pages([
                Pages\Dashboard::class,
            ])

            // Widgets
            ->widgets([
                ActividadOverview::class,
                ActividadChart::class,
                CalendarioWidget::class,
            ])

            // Plugins
            ->plugins([
                FilamentFullCalendarPlugin::make()
                    ->selectable(true)
                    ->editable(true),
                FilamentMediaManagerPlugin::make()
                    ->allowSubFolders(),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->gridColumns(['default' => 2, 'sm' => 1])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->resourceCheckboxListColumns(['default' => 1, 'sm' => 2]),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        navigationGroup: 'Settings',
                        hasAvatars: true,
                        slug: 'my-profile'
                    )
                    ->myProfileComponents([
                        'personal_info' => \App\Livewire\MyProfileExtended::class,
                    ]),
            ])

            // Boton para salir del impersonate (NUEVO)
            ->userMenuItems([
                \Filament\Navigation\UserMenuItem::make()
                ->label('Salir de la departamental')
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->url(fn () => route('impersonate.leave'))
                ->visible(fn () => app(\Lab404\Impersonate\Services\ImpersonateManager::class)->isImpersonating()),
            ])

            // Middlewares
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                FilamentRobotsMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}