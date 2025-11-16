<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Widgets\AccountWidget;
use Stephenjude\FilamentBlog\BlogPlugin;
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
            ->login()
            ->brandName('Marathon Indonesia')
            ->brandLogo(asset('logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => '#BEF200', // Hijau Volt (Secondary)
            ])
            ->assets([
                Css::make('buttons', asset('css/filament/buttons.css')),
            ])
            ->plugins([
                BlogPlugin::make(),
                FilamentShieldPlugin::make()
                    ->navigationGroup('Pengaturan Akses') // Prefix "~" untuk muncul di bawah
                    ->navigationSort(2), // Roles setelah Users
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Manajemen Event',
                'Direktori & Listing',
                'Monetisasi & Iklan',
                'Blog',
                'Konten',
                'Pengaturan Akses',
            ])
            ->spa();
    }
}