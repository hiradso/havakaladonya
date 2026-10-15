import BrandMark from '@/components/site/brand-mark';
import ThemeToggle from '@/components/site/theme-toggle';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useI18n } from '@/i18n';
import { Link } from '@inertiajs/react';
import { Menu } from 'lucide-react';

export function useNavItems() {
    const { t, url } = useI18n();

    return [
        { label: t.nav.services, href: url('/services') },
        { label: t.nav.tracking, href: url('/tracking') },
        { label: t.nav.about, href: url('/about') },
        { label: t.nav.blog, href: url('/blog') },
        { label: t.nav.contact, href: url('/contact') },
    ];
}

export default function SiteHeader() {
    const { t, url, dir, switchUrl } = useI18n();
    const items = useNavItems();

    return (
        <header className="bg-background/85 supports-[backdrop-filter]:bg-background/70 sticky top-0 z-40 border-b backdrop-blur">
            <div className="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:h-20">
                <div className="min-w-0 flex-1 lg:flex-none">
                    <BrandMark />
                </div>

                {/* Desktop navigation */}
                <nav aria-label={t.nav.menu} className="hidden flex-1 items-center justify-center gap-1 lg:flex">
                    {items.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className="text-foreground/80 hover:text-foreground hover:bg-accent rounded-md px-3 py-2 text-[15px] font-medium transition-colors"
                        >
                            {item.label}
                        </Link>
                    ))}
                </nav>

                <div className="flex shrink-0 items-center gap-1 sm:gap-3">
                    <a
                        href={switchUrl()}
                        aria-label={t.nav.switchLanguageLabel}
                        className="text-muted-foreground hover:text-foreground hidden h-11 items-center px-2 text-sm font-medium sm:flex"
                    >
                        {t.nav.switchLanguage}
                    </a>
                    <ThemeToggle />
                    <Button asChild className="hidden h-11 px-5 font-bold sm:inline-flex">
                        <Link href={url('/quote')}>{t.nav.quote}</Link>
                    </Button>

                    {/* Mobile menu */}
                    <Sheet>
                        <SheetTrigger asChild>
                            <Button variant="ghost" size="icon" className="size-11 lg:hidden" aria-label={t.nav.openMenu}>
                                <Menu className="size-6" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side={dir === 'rtl' ? 'right' : 'left'} className="flex w-[85vw] max-w-sm flex-col gap-6">
                            <SheetHeader>
                                <SheetTitle className="text-start">{t.nav.menu}</SheetTitle>
                            </SheetHeader>
                            <nav aria-label={t.nav.menu} className="flex flex-col gap-1">
                                {items.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className="hover:bg-accent flex min-h-12 items-center rounded-lg px-3 text-lg font-medium"
                                    >
                                        {item.label}
                                    </Link>
                                ))}
                            </nav>
                            <div className="mt-auto flex flex-col gap-3">
                                <Button asChild size="lg" className="h-12 font-bold">
                                    <Link href={url('/quote')}>{t.nav.quote}</Link>
                                </Button>
                                <Button asChild size="lg" variant="outline" className="h-12">
                                    <a href={switchUrl()} aria-label={t.nav.switchLanguageLabel}>
                                        {t.nav.switchLanguage}
                                    </a>
                                </Button>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    );
}
