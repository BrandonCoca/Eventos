<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->font('Montserrat')
            ->brandName(
                request()->is('admin/')
                    ? null
                    : 'Facultad Nacional de Ingeniería'
            )
            ->brandLogo(
                request()->is('admin/login')
                    ? asset('/images/facu.png')
                    : null
            )
            ->brandLogoHeight('150px')
            ->favicon(asset('/images/facu.ico'))
            ->login()
            ->renderHook(
                'panels::head.end',
                fn (): string => '<meta name="turbo-cache-control" content="no-preview" />'
            )
            ->maxContentWidth('full')
            ->colors([
                'primary' => Color::hex('#0D47A1'),
                'danger'  => Color::hex('#E53935'),
                'success' => Color::hex('#2E7D32'),
                'warning' => Color::hex('#FFC107'),
                'info'    => Color::hex('#1E88E5'),
                'gray'    => Color::Zinc,
            ])
            ->navigationGroups([
                'Manejo del Personal',
                'Manejo del evento',
                'Manejo de los implicados',
                'Informes'
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                //Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('sm')
            ->databaseNotificationsPolling('60s')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa();
    }
}
