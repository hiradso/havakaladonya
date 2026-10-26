import ContactCta from '@/components/site/contact-cta';
import PageHeader from '@/components/site/page-header';
import Seo, { organizationLd, useAppUrl } from '@/components/site/seo';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { useContact } from '@/lib/contact';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Clock, Handshake, Layers, Route } from 'lucide-react';

const VALUE_ICONS = [Handshake, Route, Clock, Layers];

export default function About() {
    const { t, locale, url } = useI18n();
    const appUrl = useAppUrl();
    const contact = useContact();
    const site = usePage<SharedData>().props.site;
    const header = site?.images.aboutHeader;
    const team = site?.team ?? [];
    // Sections can be switched off in the panel (Site content → About).
    const show = (key: string) => site?.show.about[key] !== false;

    return (
        <SiteLayout>
            <Seo
                title={t.about.metaTitle}
                description={t.about.metaDescription}
                image={header?.src ?? '/images/photos/containers-yard-1600.webp'}
                breadcrumbs={[{ name: t.nav.about, path: url('/about') }]}
                jsonLd={[organizationLd(appUrl, locale, t.brand.name, contact, t.footer.address)]}
            />

            <PageHeader title={t.about.title} lead={t.about.lead} crumbs={[{ label: t.nav.about }]} />

            <section className="mx-auto max-w-7xl px-4 pb-16 sm:px-6">
                <img
                    src={header?.src ?? '/images/photos/containers-yard-1600.webp'}
                    srcSet={header?.srcset || undefined}
                    sizes="(min-width: 1280px) 1232px, 100vw"
                    alt={header?.alt ?? ''}
                    width={header?.width ?? 1600}
                    height={header?.height ?? 900}
                    className="aspect-[16/9] w-full rounded-3xl object-cover sm:aspect-[21/9]"
                />
            </section>

            {/* Story + timeline */}
            <section
                className={`mx-auto grid max-w-7xl gap-10 px-4 pb-16 sm:px-6 lg:gap-16 lg:pb-24 ${show('timeline') ? 'lg:grid-cols-[minmax(0,1fr)_24rem]' : ''}`}
            >
                <div className="flex flex-col gap-5">
                    <h2 className="text-3xl font-black sm:text-4xl">{t.about.storyTitle}</h2>
                    {t.about.story.map((p, i) => (
                        <p key={i} className="text-base leading-8 sm:text-lg sm:leading-9">
                            {p}
                        </p>
                    ))}
                </div>

                {show('timeline') && t.about.timeline.length > 0 && (
                    <div className="flex flex-col gap-5">
                        <h2 className="text-2xl font-black">{t.about.timelineTitle}</h2>
                        <ol className="flex flex-col">
                            {t.about.timeline.map((item, i) => {
                                const last = i === t.about.timeline.length - 1;
                                return (
                                    <li key={`${item.year}-${i}`} className="relative flex gap-4 pb-8 last:pb-0">
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
                )}
            </section>

            {/* Values */}
            {show('values') && t.about.values.length > 0 && (
                <section className="bg-card border-y">
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-24">
                        <h2 className="mb-8 text-3xl font-black sm:text-4xl">{t.about.valuesTitle}</h2>
                        <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {t.about.values.map((value, i) => {
                                const Icon = VALUE_ICONS[i % VALUE_ICONS.length];
                                return (
                                    <li key={`${value.title}-${i}`} className="bg-background flex flex-col gap-3 rounded-2xl border p-6">
                                        <Icon className="text-brand2-text dark:text-primary-text size-7" aria-hidden="true" />
                                        <strong className="text-lg font-black">{value.title}</strong>
                                        <p className="text-muted-foreground leading-7">{value.text}</p>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                </section>
            )}

            {/* Team */}
            {show('team') && team.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-24" aria-labelledby="team-title">
                    <h2 id="team-title" className="mb-8 text-3xl font-black sm:text-4xl">
                        {t.about.teamTitle}
                    </h2>
                    <ul className="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3">
                        {team.map((member, i) => (
                            <li key={`${member.name}-${i}`} className="flex flex-col gap-3">
                                {member.photo ? (
                                    <img
                                        src={member.photo.src}
                                        srcSet={member.photo.srcset || undefined}
                                        sizes="(min-width: 768px) 30vw, 50vw"
                                        alt={member.name}
                                        width={member.photo.width}
                                        height={member.photo.height}
                                        loading="lazy"
                                        className="bg-muted aspect-[4/5] w-full rounded-2xl object-cover object-top"
                                    />
                                ) : (
                                    <div className="bg-muted aspect-[4/5] w-full rounded-2xl" aria-hidden="true" />
                                )}
                                <div className="flex flex-col">
                                    <strong className="font-black sm:text-lg">{member.name}</strong>
                                    {member.role && <span className="text-muted-foreground text-sm">{member.role}</span>}
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>
            )}

            <ContactCta />
        </SiteLayout>
    );
}
