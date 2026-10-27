<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Support\Roles;
use App\Support\Theme;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Vite;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Number;
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
                .fi-ta-image:not(.fi-circular) img, .fi-in-image img, .fi-fo-file-upload .filepond--image-preview-wrapper,
                .fi-fo-file-upload .filepond--item-panel, .fi-fo-rich-editor img, .fi-prose img { border-radius: .75rem; }
                .fi-ta-image:not(.fi-circular) img { border-radius: .5rem; object-fit: cover; }
                .fi-ta-image.fi-circular img, .fi-user-avatar, .fi-avatar { border-radius: 9999px; }
                /* Tooltips (tippy): rounded, soft shadow, readable Persian text, in both themes. */
                .tippy-box { border-radius: .75rem; font-family: inherit; font-size: .78rem; line-height: 1.8;
                    --tippy-background-color: #1f2937; color: #f9fafb;
                    box-shadow: 0 12px 32px -12px rgb(0 0 0 / .45), 0 0 0 1px rgb(255 255 255 / .06); }
                .tippy-box > .tippy-content { padding: .45rem .8rem; }
                .tippy-box[data-theme~="light"] { --tippy-background-color: #fff; color: #374151;
                    --tippy-box-shadow: 0 12px 32px -12px rgb(15 23 42 / .28), 0 0 0 1px rgb(15 23 42 / .07); }
                .tippy-box[data-theme~="dark"] { --tippy-background-color: #1f2937; color: #f3f4f6;
                    box-shadow: 0 12px 32px -10px rgb(0 0 0 / .7), 0 0 0 1px rgb(255 255 255 / .09); }
                .tippy-box .hkd-tip { display: grid; gap: .2rem; min-width: 11rem; }
                .tippy-box .hkd-tip-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
                .tippy-box .hkd-tip-row span { opacity: .7; }
                .tippy-box .hkd-tip-row b { font-weight: 700; font-variant-numeric: tabular-nums; }
                .tippy-box .hkd-tip-row b.lo { color: #dc2626; } .tippy-box .hkd-tip-row b.hi { color: #16a34a; }
                .tippy-box[data-theme~="dark"] .hkd-tip-row b.lo { color: #f87171; }
                .tippy-box[data-theme~="dark"] .hkd-tip-row b.hi { color: #4ade80; }
                .tippy-box .hkd-tip-foot { margin-top: .25rem; padding-top: .3rem; border-top: 1px solid currentColor;
                    border-top-color: color-mix(in oklab, currentColor 15%, transparent); font-size: .7rem; opacity: .7; }
            </style>')
            // Persian digits in the panel: Filament formats page numbers and "showing 1 to 10 of 36"
            // with Number::format (fa locale gives ۱۲۳); badges and "per page" options are plain text,
            // so a small script converts those.
            ->bootUsing(fn () => Number::useLocale('fa'))
            ->renderHook(PanelsRenderHook::BODY_END, fn (): string => <<<'HTML'
                <script>
                    (() => {
                        const fa = (t) => t.replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
                        const targets = '.fi-pagination option, .fi-badge, .fi-sidebar-item-badge-ctn, .fi-icon-btn-badge-ctn, .fi-tabs-item-badge';
                        const convert = () => {
                            document.querySelectorAll(targets).forEach((el) => {
                                // Only number-only labels (counts, page sizes); never data like codes or emails.
                                const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
                                for (let n = walker.nextNode(); n; n = walker.nextNode()) {
                                    if (/^\s*[0-9]+\s*$/.test(n.nodeValue)) n.nodeValue = fa(n.nodeValue);
                                }
                            });
                        };
                        let queued = false;
                        new MutationObserver(() => {
                            if (queued) return;
                            queued = true;
                            setTimeout(() => { queued = false; convert(); }, 30);
                        }).observe(document.body, { childList: true, subtree: true, characterData: true });
                        convert();
                    })();
                </script>
                HTML)
            // Dashboard charts (Apache ECharts), loaded only on the dashboard.
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Dashboard::hasCharts()
                    ? (string) app(Vite::class)(['resources/js/admin/charts.ts'])
                    : '',
                scopes: Dashboard::class,
            )
            // Live chat: sound + notification for new visitor messages on every panel page.
            ->renderHook(PanelsRenderHook::BODY_END, fn (): string => auth()->user()?->hasRole(...Roles::CUSTOMER_TEAM) ? Blade::render('<livewire:chat-notifier />') : '');
    }
}
