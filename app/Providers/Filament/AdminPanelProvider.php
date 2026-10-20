<?php

namespace App\Providers\Filament;

use App\Support\Theme;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Filament\Widgets\AccountWidget;
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
            ->login()
            ->passwordReset()
            ->brandName('هوا کالا دنیا')
            ->brandLogo(asset('images/brand/logo-full.webp'))
            // The full logo's blue text is hard to read on a dark background, so dark mode shows the mark only.
            ->darkModeBrandLogo(asset('images/brand/logo-mark.webp'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('images/brand/favicon-32.png'))
            // Brand color comes from the "Appearance" settings page,
            // the same value the public site uses.
            ->colors(fn (): array => [
                'primary' => Color::hex(Theme::primary()),
                'info' => Color::hex(Theme::secondary()),
                'gray' => Color::Slate,
            ])
            // Self-hosted Persian font (public/fonts/vazirmatn), no external CDN.
            ->font(
                'Vazirmatn',
                url: asset('fonts/vazirmatn/vazirmatn.css'),
                provider: LocalFontProvider::class,
            )
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
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
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Rounded corners for every picture in the panel: table thumbnails, upload previews, editor images.
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => '<style>
                .fi-ta-image img, .fi-in-image img, .fi-fo-file-upload .filepond--image-preview-wrapper,
                .fi-fo-file-upload .filepond--item-panel, .fi-fo-rich-editor img, .fi-prose img { border-radius: .75rem; }
                .fi-ta-image img { border-radius: .5rem; object-fit: cover; }
            </style>')
            // Live chat: sound + notification for new visitor messages on every panel page.
            ->renderHook(PanelsRenderHook::BODY_END, fn (): string => auth()->user()?->hasRole(...\App\Support\Roles::CUSTOMER_TEAM) ? Blade::render('<livewire:chat-notifier />') : '');
    }
}
