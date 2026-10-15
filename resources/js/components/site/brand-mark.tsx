import { useI18n } from '@/i18n';
import { Link } from '@inertiajs/react';

/** Temporary text logo until the real logo file is ready. */
export default function BrandMark() {
    const { t, url } = useI18n();

    return (
        <Link href={url('/')} className="flex min-w-0 items-center gap-3" aria-label={t.brand.name}>
            <span className="border-primary-text text-primary-text flex size-10 shrink-0 items-center justify-center rounded-lg border-2 text-sm font-black">HK</span>
            <span className="flex min-w-0 flex-col leading-tight">
                <strong className="truncate text-base font-black sm:text-lg">{t.brand.name}</strong>
                <span className="text-muted-foreground truncate text-xs">{t.brand.tagline}</span>
            </span>
        </Link>
    );
}
