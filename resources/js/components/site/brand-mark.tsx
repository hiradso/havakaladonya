import { useI18n } from '@/i18n';
import { Link } from '@inertiajs/react';

export default function BrandMark() {
    const { t, url } = useI18n();

    return (
        <Link href={url('/')} className="flex min-w-0 items-center gap-3" aria-label={t.brand.name}>
            {/* White chip keeps the blue/yellow logo readable in dark mode too. */}
            <span className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-black/5">
                <img
                    src="/images/brand/logo-mark.webp"
                    srcSet="/images/brand/logo-mark.webp 1x, /images/brand/logo-mark@2x.webp 2x"
                    alt=""
                    width={160}
                    height={108}
                    className="h-auto w-full"
                />
            </span>
            <span className="flex min-w-0 flex-col leading-tight">
                <strong className="truncate text-base font-black sm:text-lg">{t.brand.name}</strong>
                <span className="text-muted-foreground truncate text-xs">{t.brand.tagline}</span>
            </span>
        </Link>
    );
}
