import ContactCta from '@/components/site/contact-cta';
import PageHeader from '@/components/site/page-header';
import ServiceIcon from '@/components/site/service-icon';
import { getServices } from '@/content/services';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

export default function Services() {
    const { t, url, locale } = useI18n();
    const services = getServices(locale);

    return (
        <SiteLayout>
            <Head title={t.servicesPage.metaTitle}>
                <meta name="description" content={t.servicesPage.metaDescription} />
            </Head>

            <PageHeader title={t.servicesPage.title} lead={t.servicesPage.lead} crumbs={[{ label: t.nav.services }]} />

            <section className="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:pb-24">
                <ul className="grid gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3">
                    {services.map((service) => (
                        <li key={service.slug} className="flex">
                            <Link
                                href={url(`/services/${service.slug}`)}
                                className="group bg-card hover:border-primary focus-visible:border-primary flex w-full items-start gap-4 rounded-2xl border p-4 transition-colors sm:flex-col sm:p-6"
                            >
                                <span className="bg-secondary text-brand2-text dark:text-primary-text flex size-12 shrink-0 items-center justify-center rounded-xl">
                                    <ServiceIcon slug={service.slug} className="size-6" />
                                </span>
                                <span className="flex min-w-0 flex-1 flex-col gap-1 sm:gap-2">
                                    <strong className="text-lg font-black sm:text-xl">{service.title}</strong>
                                    <span className="text-muted-foreground text-sm leading-7 sm:text-base">{service.short}</span>
                                </span>
                                <span className="text-brand2-text dark:text-primary-text hidden items-center gap-1 text-sm font-bold sm:flex">
                                    {t.common.more}
                                    <ArrowRight
                                        className="size-4 transition-transform group-hover:translate-x-1 rtl:-scale-x-100 rtl:group-hover:-translate-x-1"
                                        aria-hidden="true"
                                    />
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            </section>

            <ContactCta />
        </SiteLayout>
    );
}
