import type { SiteImage } from '@/types';

/** A service in lists (home page, services page, "other services"). Edited in the panel: Site content → Services. */
export interface ServiceCard {
    slug: string;
    icon: string;
    title: string;
    short: string;
}

/** Everything on a service page. */
export interface ServicePageData extends ServiceCard {
    lead: string;
    body: string[];
    features: string[];
    groups: { title: string; items: string[] }[];
    chips: { title: string; items: string[] } | null;
    quoteMode: 'air' | 'sea' | 'road' | 'rail' | null;
    metaTitle: string;
    metaDescription: string;
    photo: SiteImage | null;
}
