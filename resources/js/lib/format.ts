import type { Locale } from '@/i18n';

/** Date in the Persian (Jalali) calendar with Persian digits on the Persian site, in Tehran time. */
export function formatDate(iso: string, locale: Locale, style: 'long' | 'medium' = 'long') {
    return new Intl.DateTimeFormat(locale === 'fa' ? 'fa-IR-u-ca-persian' : 'en-GB', {
        dateStyle: style,
        timeZone: 'Asia/Tehran',
    }).format(new Date(iso));
}
