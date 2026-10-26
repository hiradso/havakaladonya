import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    locale: 'fa' | 'en';
    direction: 'rtl' | 'ltr';
    theme: { primary: string; primaryForeground: string; secondary: string; secondaryForeground: string };
    quote: { message: string; author: string };
    auth: Auth;
    /** Texts, pictures and contact details edited in the admin panel (App\\Support\\SiteContent). */
    site?: SiteData;
    [key: string]: unknown;
}

export interface SiteImage {
    src: string;
    srcset?: string;
    width: number;
    height: number;
    alt: string;
}

export interface SiteData {
    /** Overrides for the i18n dictionary of the current language (home, about, contact, footer). */
    texts: Record<string, Record<string, unknown>>;
    contact: import('@/lib/contact').SiteContact;
    images: { homeHero: SiteImage; aboutHeader: SiteImage };
    team: { name: string; role: string; photo: SiteImage | null }[];
    show: { home: Record<string, boolean>; about: Record<string, boolean> };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}
