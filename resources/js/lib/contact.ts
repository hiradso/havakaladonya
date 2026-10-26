import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

/** Company contact details, edited in the admin panel (Site content → Contact). */
export interface SiteContact {
    phone: { display: string; href: string };
    mobile: { display: string; href: string };
    fax: { display: string };
    whatsapp: string;
    email: string;
    instagram: { handle: string; href: string } | null;
    telegram: { handle: string; href: string } | null;
    linkedin: string | null;
    mapUrl: string;
    phoneIntl: string;
    mobileIntl: string;
    faxIntl: string;
}

/** Used only if the server sent nothing (should not happen). */
export const CONTACT: SiteContact = {
    phone: { display: '021 8801 5232', href: 'tel:+982188015232' },
    mobile: { display: '0912 333 6855', href: 'tel:+989123336855' },
    fax: { display: '021 8802 4527' },
    whatsapp: 'https://wa.me/989123336855',
    email: 'info@havakaladonya.com',
    instagram: { handle: 'havakaladonya', href: 'https://instagram.com/havakaladonya' },
    telegram: null,
    linkedin: null,
    mapUrl: 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent('میدان گلها، خیابان کاج شمالی، پلاک ۱۴۶، تهران'),
    phoneIntl: '+982188015232',
    mobileIntl: '+989123336855',
    faxIntl: '+982188024527',
};

export function useContact(): SiteContact {
    return usePage<SharedData>().props.site?.contact ?? CONTACT;
}
