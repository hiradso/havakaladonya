import { useI18n } from '@/i18n';
import { CONTACT } from '@/lib/contact';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

type JsonLd = Record<string, unknown>;

interface SeoProps {
    title: string;
    description: string;
    /** Path or absolute URL of the share image. */
    image?: string | null;
    type?: 'website' | 'article';
    /** Keep the page out of search results (thank-you pages, tracking results…). */
    noindex?: boolean;
    /** The same page exists in the other language at the mirrored address (/x ↔ /en/x). */
    alternates?: boolean;
    /** Breadcrumb trail for Google, without the home page. */
    breadcrumbs?: { name: string; path: string }[];
    /** Extra structured data (Article, Service…). */
    jsonLd?: JsonLd[];
}

const DEFAULT_IMAGE = '/images/photos/port-plane-1600.webp';

/**
 * Everything search engines and link previews read from <head>:
 * title, description, canonical, hreflang, Open Graph, Twitter and JSON-LD.
 * Rendered on the server (SSR), so crawlers see it without running JavaScript.
 */
export default function Seo({ title, description, image, type = 'website', noindex = false, alternates = true, breadcrumbs, jsonLd = [] }: SeoProps) {
    const { props, url: pageUrl } = usePage<SharedData & { appUrl: string }>();
    const { t, locale, url } = useI18n();
    const base = props.appUrl;
    const abs = (path: string) => (/^https?:\/\//.test(path) ? path : base + (path.startsWith('/') ? path : `/${path}`));

    // Canonical: the page address without tracking parameters (only ?page=N of the blog is kept).
    const [path, query = ''] = pageUrl.split('?');
    const pageNumber = Number(new URLSearchParams(query).get('page') ?? '1');
    const canonical = abs(path) + (pageNumber > 1 ? `?page=${pageNumber}` : '');

    const faPath = path.replace(/^\/en(?=\/|$)/, '') || '/';
    const enPath = faPath === '/' ? '/en' : `/en${faPath}`;
    const siteName = t.brand.name;
    const shareImage = abs(image ?? DEFAULT_IMAGE);

    const graph: JsonLd[] = [...jsonLd];
    if (breadcrumbs?.length) {
        graph.push({
            '@type': 'BreadcrumbList',
            itemListElement: [{ name: t.common.home, path: url('/') }, ...breadcrumbs].map((crumb, i) => ({
                '@type': 'ListItem',
                position: i + 1,
                name: crumb.name,
                item: abs(crumb.path),
            })),
        });
    }

    return (
        <Head title={title}>
            <meta head-key="description" name="description" content={description} />
            {noindex && <meta head-key="robots" name="robots" content="noindex, follow" />}
            <link head-key="canonical" rel="canonical" href={canonical} />
            {alternates && !noindex && <link head-key="alt-fa" rel="alternate" hrefLang="fa" href={abs(faPath)} />}
            {alternates && !noindex && <link head-key="alt-en" rel="alternate" hrefLang="en" href={abs(enPath)} />}
            {alternates && !noindex && <link head-key="alt-default" rel="alternate" hrefLang="x-default" href={abs(faPath)} />}

            <meta head-key="og:site_name" property="og:site_name" content={siteName} />
            <meta head-key="og:locale" property="og:locale" content={locale === 'fa' ? 'fa_IR' : 'en_US'} />
            <meta head-key="og:type" property="og:type" content={type} />
            <meta head-key="og:title" property="og:title" content={title} />
            <meta head-key="og:description" property="og:description" content={description} />
            <meta head-key="og:url" property="og:url" content={canonical} />
            <meta head-key="og:image" property="og:image" content={shareImage} />
            <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />

            {graph.length > 0 && (
                <script
                    head-key="jsonld"
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{
                        __html: JSON.stringify({ '@context': 'https://schema.org', '@graph': graph }).replace(/</g, '\\u003c'),
                    }}
                />
            )}
        </Head>
    );
}

/** The company as structured data (shown on the home and contact pages). */
export function organizationLd(base: string, locale: 'fa' | 'en', name: string): JsonLd {
    return {
        '@type': 'Organization',
        '@id': `${base}/#organization`,
        name,
        alternateName: locale === 'fa' ? 'Hava Kala Donya' : 'هوا کالا دنیا',
        url: base + (locale === 'en' ? '/en' : '/'),
        logo: `${base}/images/brand/icon-512.png`,
        email: CONTACT.email,
        telephone: '+98-21-88015232',
        faxNumber: '+98-21-88024527',
        foundingDate: '2012',
        address: {
            '@type': 'PostalAddress',
            streetAddress:
                locale === 'fa' ? 'میدان گلها، خیابان کاج شمالی، پلاک ۱۴۶، طبقه ۲، واحد ۳' : 'Unit 3, 2nd floor, No. 146, North Kaj St., Golha Sq.',
            addressLocality: locale === 'fa' ? 'تهران' : 'Tehran',
            addressCountry: 'IR',
        },
        contactPoint: {
            '@type': 'ContactPoint',
            telephone: '+98-912-333-6855',
            contactType: 'customer service',
            availableLanguage: ['fa', 'en'],
        },
        sameAs: [CONTACT.instagram.href],
    };
}

export function useAppUrl(): string {
    return usePage<SharedData & { appUrl: string }>().props.appUrl;
}
