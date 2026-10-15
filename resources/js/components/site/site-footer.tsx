import { useNavItems } from '@/components/site/site-header';
import { useI18n } from '@/i18n';
import { Link } from '@inertiajs/react';
import { Mail, MapPin, Phone } from 'lucide-react';

export default function SiteFooter() {
    const { t, digits } = useI18n();
    const items = useNavItems();

    return (
        <footer className="border-t">
            <div className="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-3">
                <div className="flex flex-col gap-3">
                    <strong className="text-lg font-black">{t.brand.name}</strong>
                    <p className="text-muted-foreground text-sm leading-7">{t.footer.about}</p>
                </div>

                <div className="flex flex-col gap-3">
                    <strong>{t.footer.quickLinks}</strong>
                    <ul className="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        {items.map((item) => (
                            <li key={item.href}>
                                <Link href={item.href} className="text-muted-foreground hover:text-foreground inline-flex min-h-8 items-center">
                                    {item.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>

                <div className="flex flex-col gap-3 text-sm">
                    <strong>{t.footer.contact}</strong>
                    <p className="text-muted-foreground flex gap-2 leading-7">
                        <MapPin className="mt-1 size-4 shrink-0" aria-hidden="true" />
                        {t.footer.address}
                    </p>
                    <a href="tel:+982188015232" className="text-muted-foreground hover:text-foreground flex min-h-8 items-center gap-2">
                        <Phone className="size-4 shrink-0" aria-hidden="true" />
                        <span dir="ltr">{digits('021 8801 5232')}</span>
                    </a>
                    <a href="mailto:info@havakaladonya.com" className="text-muted-foreground hover:text-foreground flex min-h-8 items-center gap-2">
                        <Mail className="size-4 shrink-0" aria-hidden="true" />
                        <span dir="ltr">info@havakaladonya.com</span>
                    </a>
                </div>
            </div>
            <div className="text-muted-foreground border-t py-5 text-center text-xs">{t.footer.rights}</div>
        </footer>
    );
}
