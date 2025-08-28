<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EmailVerification;
use Filament\Pages\Auth\OpsLogin;
use App\Filament\Pages\Auth\RequestPasswordReset;

// RESOURCES del sistema (solo los que quieres ver)
use App\Filament\Resources\UserResource;
use App\Filament\Resources\DepartamentalResource;
use App\Filament\Resources\ActividadResource;
use App\Filament\Resources\Shield\RoleResource; // visible según permisos Shield

// WIDGETS propios
use App\Filament\Widgets\ActividadChart;
use App\Filament\Widgets\ActividadOverview;
use App\Filament\Widgets\CalendarioWidget;

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
use App\Http\Middleware\FilamentRobotsMiddleware;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')

            // Auth
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->passwordReset(RequestPasswordReset::class)
            ->emailVerification(EmailVerification::class)            ->passwordReset(RequestPasswordReset::class)
            ->emailVerification(EmailVerification::class)

            // Branding desde settings
            ->favicon(fn (GeneralSettings $s) => $s->site_favicon ? Storage::url($s->site_favicon) : asset(''))
->brandName(fn (GeneralSettings $s) => $s->brand_name ?: 'Asamblea Legislativa')
->brandLogo(fn (GeneralSettings $s) => $s->brand_logo ? asset($s->brand_logo) : asset('images/logo-azul-fondo-transparente (002).png'))
->brandLogoHeight(fn (GeneralSettings $s) => $s->brand_logoHeight ?: '15rem')
->colors(fn (GeneralSettings $s) => $s->site_theme ?: [
    'primary' => \Filament\Support\Colors\Color::hex('#0A2C65'), // azul institucional
])


            // UX
            ->databaseNotifications()->databaseNotificationsPolling('30s')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->darkMode(false) // Desactiva el modo oscuro por defecto
            ->viteTheme('resources/css/filament/admin/theme.css')

            // 🚫 Sin auto-descubrimiento (mostramos SOLO lo registrado abajo)
            // ->discoverResources(...)
            // ->discoverPages(...)
            // ->discoverWidgets(...)
            // ->discoverClusters(...)

            // ✅ SOLO tus Resources
            ->resources([
                UserResource::class,
                DepartamentalResource::class,
                ActividadResource::class,
                RoleResource::class, // visible si el rol tiene 'view_any_role' (Shield)
                // Agrega aquí tus demás módulos del sistema:
                // ProgramaResource::class,
                // MacroactividadResource::class,
                // ActividadProyectadaResource::class,
                // ActividadEjecutadaResource::class,
                // AtestadoResource::class,
                // CierreMensualResource::class,
                // RequisicionResource::class,
                // TicketResource::class,
                // ContratoResource::class,
                // ReporteResource::class,
                // ParametroSistemaResource::class,
                // LogAuditoriaResource::class,
            ])

            // ✅ SOLO tus páginas
            ->pages([
                Pages\Dashboard::class,
            ])

            // ✅ SOLO tus widgets
            ->widgets([
                // Widgets\FilamentInfoWidget::class, // <- quítalo si no lo quieres
                ActividadOverview::class,
                ActividadChart::class,
                CalendarioWidget::class,
            ])

            // Plugins necesarios
            ->plugins([
                FilamentFullCalendarPlugin::make()->selectable(true)->editable(true),
                FilamentMediaManagerPlugin::make()->allowSubFolders(),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->gridColumns(['default' => 2, 'sm' => 1])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns(['default' => 1, 'sm' => 2, 'lg' => 3])
                    ->resourceCheckboxListColumns(['default' => 1, 'sm' => 2]),
                \Jeffgreco13\FilamentBreezy\BreezyCore::make()
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
