import { useNavItems } from '@/components/site/site-header';
import { useI18n } from '@/i18n';
import { useContact } from '@/lib/contact';
import { Link } from '@inertiajs/react';
import { Briefcase, Camera, Clock, Mail, MapPin, MessageCircle, Phone, Send } from 'lucide-react';

export default function SiteFooter() {
    const { t, digits } = useI18n();
    const contact = useContact();
    const items = useNavItems();

    const social = [
        contact.instagram && { href: contact.instagram.href, label: t.contact.instagram, icon: Camera },
        contact.telegram && { href: contact.telegram.href, label: t.contact.telegram, icon: Send },
        contact.linkedin && { href: contact.linkedin, label: t.contact.linkedin, icon: Briefcase },
        contact.whatsapp && { href: contact.whatsapp, label: t.contact.whatsapp, icon: MessageCircle },
    ].filter((item): item is { href: string; label: string; icon: typeof Camera } => Boolean(item));

    return (
        <footer id="footer" className="border-t">
            <div className="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-3">
                <div className="flex flex-col gap-3">
                    <strong className="text-lg font-black">{t.brand.name}</strong>
                    <p className="text-muted-foreground text-sm leading-7">{t.footer.about}</p>
                    {social.length > 0 && (
                        <ul className="flex gap-2">
                            {social.map(({ href, label, icon: Icon }) => (
                                <li key={href}>
                                    <a
                                        href={href}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label={label}
                                        title={label}
                                        className="bg-secondary text-muted-foreground hover:text-foreground flex size-10 items-center justify-center rounded-xl transition-colors"
                                    >
                                        <Icon className="size-5" aria-hidden="true" />
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
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
                    {t.footer.address && (
                        <p className="text-muted-foreground flex gap-2 leading-7">
                            <MapPin className="mt-1 size-4 shrink-0" aria-hidden="true" />
                            {t.footer.address}
                        </p>
                    )}
                    {t.contact.hours && (
                        <p className="text-muted-foreground flex gap-2 leading-7">
                            <Clock className="mt-1 size-4 shrink-0" aria-hidden="true" />
                            {digits(t.contact.hours)}
                        </p>
                    )}
                    {contact.phone.display && (
                        <a href={contact.phone.href} className="text-muted-foreground hover:text-foreground flex min-h-8 items-center gap-2">
                            <Phone className="size-4 shrink-0" aria-hidden="true" />
                            <span dir="ltr">{digits(contact.phone.display)}</span>
                        </a>
                    )}
                    {contact.email && (
                        <a href={`mailto:${contact.email}`} className="text-muted-foreground hover:text-foreground flex min-h-8 items-center gap-2">
                            <Mail className="size-4 shrink-0" aria-hidden="true" />
                            <span dir="ltr">{contact.email}</span>
                        </a>
                    )}
                </div>
            </div>
            <div className="text-muted-foreground border-t py-5 text-center text-xs">{t.footer.rights}</div>
        </footer>
    );
}
