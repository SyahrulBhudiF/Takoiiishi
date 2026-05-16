<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->brandName('Takoiiishi')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->globalSearch(false)
            ->userMenu(false)
            ->databaseNotifications()
            ->renderHook(PanelsRenderHook::SIDEBAR_FOOTER, fn (): string => view('filament.sidebar-user')->render())
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearch(false)
            ->maxContentWidth(Width::Full)
            ->colors([
                'primary' => [
                    50 => '250, 245, 255',
                    100 => '243, 232, 255',
                    200 => '233, 213, 255',
                    300 => '216, 180, 254',
                    400 => '192, 132, 252',
                    500 => '168, 85, 247',
                    600 => '147, 51, 234',
                    700 => '126, 34, 206',
                    800 => '107, 33, 168',
                    900 => '88, 28, 135',
                    950 => '59, 7, 100',
                ],
                'gray' => [
                    50 => '250, 245, 255',
                    100 => '243, 237, 250',
                    200 => '233, 225, 245',
                    300 => '216, 205, 235',
                    400 => '180, 165, 210',
                    500 => '140, 120, 175',
                    600 => '105, 85, 140',
                    700 => '80, 65, 115',
                    800 => '60, 48, 90',
                    900 => '45, 35, 70',
                    950 => '30, 22, 50',
                ],
                'danger' => [
                    50 => '254, 242, 242',
                    100 => '254, 226, 226',
                    200 => '254, 202, 202',
                    300 => '252, 165, 165',
                    400 => '248, 113, 113',
                    500 => '239, 68, 68',
                    600 => '220, 38, 38',
                    700 => '185, 28, 28',
                    800 => '153, 27, 27',
                    900 => '127, 29, 29',
                    950 => '69, 10, 10',
                ],
                'success' => [
                    50 => '240, 253, 250',
                    100 => '204, 251, 241',
                    200 => '153, 246, 228',
                    300 => '94, 234, 212',
                    400 => '45, 212, 191',
                    500 => '20, 184, 166',
                    600 => '13, 148, 136',
                    700 => '15, 118, 110',
                    800 => '17, 94, 89',
                    900 => '19, 78, 74',
                    950 => '4, 47, 46',
                ],
                'warning' => [
                    50 => '255, 251, 235',
                    100 => '254, 243, 199',
                    200 => '253, 230, 138',
                    300 => '252, 211, 77',
                    400 => '251, 191, 36',
                    500 => '245, 158, 11',
                    600 => '217, 119, 6',
                    700 => '180, 83, 9',
                    800 => '146, 64, 14',
                    900 => '120, 53, 15',
                    950 => '69, 26, 3',
                ],
                'info' => [
                    50 => '239, 246, 255',
                    100 => '219, 234, 254',
                    200 => '191, 219, 254',
                    300 => '147, 197, 253',
                    400 => '96, 165, 250',
                    500 => '59, 130, 246',
                    600 => '37, 99, 235',
                    700 => '29, 78, 216',
                    800 => '30, 64, 175',
                    900 => '30, 58, 138',
                    950 => '23, 37, 84',
                ],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
