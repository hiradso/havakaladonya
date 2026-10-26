import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
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
    const page = usePage<SharedData & { alternateUrl?: string }>();
    const { locale, alternateUrl, site } = page.props;
    const current: Locale = locale === 'en' ? 'en' : 'fa';
    const overrides = site?.texts;
    // Texts edited in the admin panel are laid over the built-in dictionary.
    const t = useMemo(() => withOverrides(dictionaries[current], overrides), [current, overrides]);

    return {
        locale: current,
        dir: current === 'fa' ? ('rtl' as const) : ('ltr' as const),
        t,
        url: (path: string) => localizedUrl(path, current),
        // A page can say where its other-language version lives (e.g. an article → the other blog).
        switchUrl: () => alternateUrl ?? switchLocaleUrl(current, page.url),
        /** Shows numbers with Persian digits on the Persian site (e.g. phone numbers, counts). */
        digits: (value: string | number) => localizeDigits(value, current),
    };
}

/** Deep merge for plain objects; arrays and strings from the panel replace the built-in value. */
function withOverrides<T>(base: T, overrides: unknown): T {
    if (!overrides || typeof overrides !== 'object' || Array.isArray(overrides) || typeof base !== 'object' || base === null) {
        return base;
    }
    const out: Record<string, unknown> = { ...(base as Record<string, unknown>) };
    for (const [key, value] of Object.entries(overrides as Record<string, unknown>)) {
        const current = out[key];
        if (value === null || value === undefined) {
            continue;
        }
        if (typeof value === 'object' && !Array.isArray(value) && current && typeof current === 'object' && !Array.isArray(current)) {
            out[key] = withOverrides(current, value);
        } else if (Array.isArray(value) ? value.length > 0 : typeof value === typeof current || current === undefined) {
            out[key] = value;
        }
    }
    return out as T;
}

export function localizedUrl(path: string, locale: Locale): string {
    const clean = '/' + path.replace(/^\/+/, '');

    if (locale === 'fa') {
        return clean;
    }

    return clean === '/' ? '/en' : `/en${clean}`;
}

/** URL of the current page in the other language. */
export function switchLocaleUrl(current: Locale, pageUrl: string): string {
    // pageUrl is Inertia's page URL (path + query), the same on the server and in the browser.
    const [path = '/', query] = pageUrl.split('?');
    // Keep the query string, e.g. ?code=… on the tracking page.
    const search = query ? `?${query}` : '';

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
