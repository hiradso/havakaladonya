import { Button } from '@/components/ui/button';
import { useI18n } from '@/i18n';
import { CONTACT } from '@/lib/contact';
import { Link } from '@inertiajs/react';
import { MessageCircle, Phone } from 'lucide-react';

/** Full-width "talk to us" band used at the bottom of inner pages. */
export default function ContactCta({ title, text }: { title?: string; text?: string }) {
    const { t, url } = useI18n();

    return (
        <section className="mx-auto max-w-7xl px-4 pb-24 sm:px-6">
            <div className="bg-card flex flex-col gap-6 rounded-3xl border p-6 sm:p-10 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex flex-col gap-2">
                    <h2 className="text-2xl font-black sm:text-3xl">{title ?? t.servicesPage.bandTitle}</h2>
                    <p className="text-muted-foreground leading-8">{text ?? t.servicesPage.bandText}</p>
                </div>
                <div className="grid gap-2 sm:grid-cols-3 lg:flex lg:shrink-0">
                    <Button asChild size="lg" className="h-12 px-6 font-bold">
                        <Link href={url('/quote')}>{t.servicesPage.ctaQuote}</Link>
                    </Button>
                    <Button asChild size="lg" variant="outline" className="h-12 px-6">
                        <a href={CONTACT.phone.href}>
                            <Phone aria-hidden="true" />
                            {t.servicesPage.ctaCall}
                        </a>
                    </Button>
                    <Button asChild size="lg" variant="outline" className="h-12 px-6">
                        <a href={CONTACT.whatsapp} target="_blank" rel="noopener noreferrer">
                            <MessageCircle aria-hidden="true" />
                            {t.servicesPage.ctaWhatsapp}
                        </a>
                    </Button>
                </div>
            </div>
        </section>
    );
}
