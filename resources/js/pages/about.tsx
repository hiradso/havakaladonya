import ContactCta from '@/components/site/contact-cta';
import PageHeader from '@/components/site/page-header';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { Head } from '@inertiajs/react';
import { Clock, Handshake, Layers, Route } from 'lucide-react';

const VALUE_ICONS = [Handshake, Route, Clock, Layers];

/** Team members with photos from the company's media library. Roles are confirmed with the employer before adding. */
const TEAM = [
    { photo: 'team-zhila-seifi-800', fa: 'ژیلا سیفی درخشان', en: 'Zhila Seifi Derakhshan', founder: true },
    { photo: 'team-khatam-gorji-640', fa: 'خاتم گرجی', en: 'Khatam Gorji', founder: false },
    { photo: 'team-mehrdad-sajedpour-359', fa: 'مهرداد ساجدپور', en: 'Mehrdad Sajedpour', founder: false },
];

export default function About() {
    const { t, locale } = useI18n();

    return (
        <SiteLayout>
            <Head title={t.about.metaTitle}>
                <meta name="description" content={t.about.metaDescription} />
            </Head>

            <PageHeader title={t.about.title} lead={t.about.lead} crumbs={[{ label: t.nav.about }]} />

            <section className="mx-auto max-w-7xl px-4 pb-16 sm:px-6">
                <img
                    src="/images/photos/containers-yard-800.webp"
                    srcSet="/images/photos/containers-yard-800.webp 800w, /images/photos/containers-yard-1600.webp 1600w"
                    sizes="(min-width: 1280px) 1232px, 100vw"
                    alt=""
                    width={1600}
                    height={900}
                    className="aspect-[16/9] w-full rounded-3xl object-cover sm:aspect-[21/9]"
                />
            </section>

            {/* Story + timeline */}
            <section className="mx-auto grid max-w-7xl gap-10 px-4 pb-16 sm:px-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:gap-16 lg:pb-24">
                <div className="flex flex-col gap-5">
                    <h2 className="text-3xl font-black sm:text-4xl">{t.about.storyTitle}</h2>
                    {t.about.story.map((p) => (
                        <p key={p} className="text-base leading-8 sm:text-lg sm:leading-9">
                            {p}
                        </p>
                    ))}
                </div>

                <div className="flex flex-col gap-5">
                    <h2 className="text-2xl font-black">{t.about.timelineTitle}</h2>
                    <ol className="flex flex-col">
                        {t.about.timeline.map((item, i) => {
                            const last = i === t.about.timeline.length - 1;
                            return (
                                <li key={item.year} className="relative flex gap-4 pb-8 last:pb-0">
                                    {!last && <span className="bg-border absolute start-[0.6875rem] top-7 bottom-1 w-0.5" aria-hidden="true" />}
                                    <span
                                        className="bg-primary ring-primary/25 relative z-10 mt-1 size-6 shrink-0 rounded-full ring-4"
                                        aria-hidden="true"
                                    />
                                    <div className="flex flex-col gap-1">
                                        <strong className="text-primary-text text-2xl font-black">{item.year}</strong>
                                        <span className="leading-7">{item.text}</span>
                                    </div>
                                </li>
                            );
                        })}
                    </ol>
                </div>
            </section>

            {/* Values */}
            <section className="bg-card border-y">
                <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-24">
                    <h2 className="mb-8 text-3xl font-black sm:text-4xl">{t.about.valuesTitle}</h2>
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {t.about.values.map((value, i) => {
                            const Icon = VALUE_ICONS[i % VALUE_ICONS.length];
                            return (
                                <li key={value.title} className="bg-background flex flex-col gap-3 rounded-2xl border p-6">
                                    <Icon className="text-brand2-text dark:text-primary-text size-7" aria-hidden="true" />
                                    <strong className="text-lg font-black">{value.title}</strong>
                                    <p className="text-muted-foreground leading-7">{value.text}</p>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            </section>

            {/* Team */}
            <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-24" aria-labelledby="team-title">
                <h2 id="team-title" className="mb-8 text-3xl font-black sm:text-4xl">
                    {t.about.teamTitle}
                </h2>
                <ul className="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3">
                    {TEAM.map((member) => (
                        <li key={member.photo} className="flex flex-col gap-3">
                            <img
                                src={`/images/photos/${member.photo}.webp`}
                                alt={member[locale]}
                                width={640}
                                height={800}
                                loading="lazy"
                                className="bg-muted aspect-[4/5] w-full rounded-2xl object-cover object-top"
                            />
                            <div className="flex flex-col">
                                <strong className="font-black sm:text-lg">{member[locale]}</strong>
                                {member.founder && <span className="text-muted-foreground text-sm">{t.about.founder}</span>}
                            </div>
                        </li>
                    ))}
                </ul>
            </section>

            <ContactCta />
        </SiteLayout>
    );
}
