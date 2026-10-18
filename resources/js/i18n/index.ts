import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import en from './en';
import fa from './fa';

export type Locale = 'fa' | 'en';
export type Dictionary = typeof fa;

const dictionaries: Record<Locale, Dictionary> = { fa, en };

/**
 * Current locale + its dictionary + a helper that builds locale-aware URLs.
 * Persian lives at "/", English at "/en".
 */
export function useI18n() {
    const { locale, alternateUrl } = usePage<SharedData & { alternateUrl?: string }>().props;
    const current: Locale = locale === 'en' ? 'en' : 'fa';

    return {
        locale: current,
        dir: current === 'fa' ? ('rtl' as const) : ('ltr' as const),
        t: dictionaries[current],
        url: (path: string) => localizedUrl(path, current),
        // A page can say where its other-language version lives (e.g. an article → the other blog).
        switchUrl: () => alternateUrl ?? switchLocaleUrl(current),
        /** Shows numbers with Persian digits on the Persian site (e.g. phone numbers, counts). */
        digits: (value: string | number) => localizeDigits(value, current),
    };
}

export function localizedUrl(path: string, locale: Locale): string {
    const clean = '/' + path.replace(/^\/+/, '');

    if (locale === 'fa') {
        return clean;
    }

    return clean === '/' ? '/en' : `/en${clean}`;
}

/** URL of the current page in the other language. */
export function switchLocaleUrl(current: Locale): string {
    const path = typeof window === 'undefined' ? '/' : window.location.pathname;
    // Keep the query string, e.g. ?code=… on the tracking page.
    const search = typeof window === 'undefined' ? '' : window.location.search;

    if (current === 'en') {
        const withoutPrefix = path.replace(/^\/en(?=\/|$)/, '');
        return (withoutPrefix === '' ? '/' : withoutPrefix) + search;
    }

    return localizedUrl(path, 'en') + search;
}

const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';

/**
 * Converts 0-9 to ۰-۹ for the Persian site; English stays as is.
 * Use for anything displayed as text. Do NOT use for values people type or copy
 * as codes (tracking codes, emails, URLs) — those stay Latin.
 */
export function localizeDigits(value: string | number, locale: Locale): string {
    const text = String(value);

    return locale === 'fa' ? text.replace(/[0-9]/g, (d) => PERSIAN_DIGITS[Number(d)]) : text;
}
