import PageHeader from '@/components/site/page-header';
import Seo, { useAppUrl } from '@/components/site/seo';
import ServiceIcon from '@/components/site/service-icon';
import { Button } from '@/components/ui/button';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { useContact } from '@/lib/contact';
import type { ServiceCard, ServicePageData } from '@/types/service';
import { Link } from '@inertiajs/react';
import { Check, MessageCircle, Phone } from 'lucide-react';

export default function ServicePage({ service, others = [] }: { service: ServicePageData; others?: ServiceCard[] }) {
    const { t, url } = useI18n();
    const contact = useContact();
    const photo = service.photo;
    const appUrl = useAppUrl();
    const quoteHref = url('/quote') + (service.quoteMode ? `?mode=${service.quoteMode}` : '');

    return (
        <SiteLayout>
            <Seo
                title={service.metaTitle || service.title}
                description={service.metaDescription || service.lead}
                image={photo?.src ?? null}
                breadcrumbs={[
                    { name: t.nav.services, path: url('/services') },
                    { name: service.title, path: url(`/services/${service.slug}`) },
                ]}
                jsonLd={[
                    {
                        '@type': 'Service',
                        name: service.title,
                        description: service.lead,
                        serviceType: service.title,
                        provider: { '@id': `${appUrl}/#organization`, '@type': 'Organization', name: t.brand.name, url: appUrl },
                        areaServed: ['IR', 'Europe', 'Central Asia', 'Russia', 'China', 'India'],
                    },
                ]}
            />

            <PageHeader
                title={service.title}
                lead={service.lead}
                crumbs={[{ label: t.nav.services, href: url('/services') }, { label: service.title }]}
            />

            <div className="mx-auto grid max-w-7xl gap-8 px-4 pb-16 sm:px-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-12 lg:pb-24">
                <article className="flex min-w-0 flex-col gap-10">
                    {photo ? (
                        <img
                            src={photo.src}
                            srcSet={photo.srcset || undefined}
                            sizes="(min-width: 1024px) 860px, 100vw"
                            alt={photo.alt}
                            width={photo.width}
                            height={photo.height}
                            className="aspect-[16/9] w-full rounded-3xl object-cover"
                        />
                    ) : (
                        <div className="bg-card text-brand2-text dark:text-primary-text flex aspect-[21/9] w-full items-center justify-center rounded-3xl border">
                            <ServiceIcon icon={service.icon} className="size-16 sm:size-20" strokeWidth={1.25} />
                        </div>
                    )}

                    <div className="flex flex-col gap-5 text-base leading-8 sm:text-lg sm:leading-9">
                        {service.body.map((p, i) => (
                            <p key={i}>{p}</p>
                        ))}
                    </div>

                    {service.features.length > 0 && (
                        <section aria-labelledby="features-title" className="flex flex-col gap-4">
                            <h2 id="features-title" className="text-2xl font-black sm:text-3xl">
                                {t.servicesPage.whatWeDo}
                            </h2>
                            <ul className="grid gap-3 sm:grid-cols-2">
                                {service.features.map((f, i) => (
                                    <li key={i} className="bg-card flex items-start gap-3 rounded-2xl border p-4">
                                        <span className="bg-primary text-primary-foreground mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full">
                                            <Check className="size-3.5" strokeWidth={3} aria-hidden="true" />
                                        </span>
                                        <span className="leading-7 font-medium">{f}</span>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    )}

                    {service.groups.length > 0 && (
                        <div className="grid gap-4 md:grid-cols-2">
                            {service.groups.map((group) => (
                                <section key={group.title} className="bg-card rounded-2xl border p-5 sm:p-6">
                                    <h2 className="mb-3 text-lg font-bold">{group.title}</h2>
                                    <ol className="flex flex-col divide-y">
                                        {group.items.map((item) => (
                                            <li key={item} className="py-2.5 leading-7 first:pt-0 last:pb-0">
                                                {item}
                                            </li>
                                        ))}
                                    </ol>
                                </section>
                            ))}
                        </div>
                    )}

                    {service.chips && (
                        <section className="flex flex-col gap-4">
                            <h2 className="text-2xl font-black sm:text-3xl">{service.chips.title}</h2>
                            <ul className="flex flex-wrap gap-2">
                                {service.chips.items.map((item) => (
                                    <li key={item} className="bg-secondary rounded-full px-4 py-2 text-sm font-medium">
                                        {item}
                                    </li>
                                ))}
                            </ul>
                        </section>
                    )}
                </article>

                <aside className="flex flex-col gap-4 lg:sticky lg:top-28 lg:self-start">
                    <div className="bg-card flex flex-col gap-4 rounded-3xl border p-6">
                        <h2 className="text-xl font-black">{t.servicesPage.ctaTitle}</h2>
                        <p className="text-muted-foreground leading-7">{t.servicesPage.ctaText}</p>
                        <Button asChild size="lg" className="h-12 font-bold">
                            <Link href={quoteHref}>{t.servicesPage.ctaQuote}</Link>
                        </Button>
                        <div className="grid grid-cols-2 gap-2">
                            <Button asChild variant="outline" size="lg" className="h-12">
                                <a href={contact.phone.href}>
                                    <Phone aria-hidden="true" />
                                    {t.servicesPage.ctaCall}
                                </a>
                            </Button>
                            {contact.whatsapp && (
                                <Button asChild variant="outline" size="lg" className="h-12">
                                    <a href={contact.whatsapp} target="_blank" rel="noopener noreferrer">
                                        <MessageCircle aria-hidden="true" />
                                        {t.servicesPage.ctaWhatsapp}
                                    </a>
                                </Button>
                            )}
                        </div>
                    </div>

                    <nav aria-labelledby="others-title" className="bg-card rounded-3xl border p-6">
                        <h2 id="others-title" className="mb-2 text-lg font-bold">
                            {t.servicesPage.others}
                        </h2>
                        <ul className="flex flex-col">
                            {others.map((s) => (
                                <li key={s.slug}>
                                    <Link
                                        href={url(`/services/${s.slug}`)}
                                        className="text-muted-foreground hover:text-foreground flex min-h-11 items-center gap-3"
                                    >
                                        <ServiceIcon icon={s.icon} className="size-5 shrink-0" />
                                        {s.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </aside>
            </div>
        </SiteLayout>
    );
}
