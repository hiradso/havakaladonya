import PostCard from '@/components/site/post-card';
import Seo, { organizationLd, useAppUrl } from '@/components/site/seo';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { getServices } from '@/content/services';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { useContact } from '@/lib/contact';
import { type SharedData } from '@/types';
import type { PostCard as PostCardData } from '@/types/blog';
import { Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, Search } from 'lucide-react';
import { type FormEvent, useState } from 'react';

/* Schematic route map: origin + destinations in a 1000×420 box. */
const ORIGIN = { x: 560, y: 250 };
const DESTINATIONS = [
    { x: 170, y: 120, path: 'M560 250 Q 400 80 170 120', mode: 'land' },
    { x: 470, y: 70, path: 'M560 250 Q 530 110 470 70', mode: 'land' },
    { x: 700, y: 130, path: 'M560 250 Q 650 150 700 130', mode: 'land' },
    { x: 890, y: 200, path: 'M560 250 Q 760 170 890 200', mode: 'airSea' },
    { x: 770, y: 340, path: 'M560 250 Q 690 350 770 340', mode: 'airSea' },
] as const;

function DirArrow({ className = '' }: { className?: string }) {
    // Points "forward" in both directions: right in English, left in Persian.
    return <ArrowRight className={`rtl:-scale-x-100 ${className}`} aria-hidden="true" />;
}

export default function Home({ posts = [] }: { posts?: PostCardData[] }) {
    const { t, url, digits, locale } = useI18n();
    const services = getServices(locale);
    const appUrl = useAppUrl();
    const [code, setCode] = useState('');
    const contact = useContact();
    const site = usePage<SharedData>().props.site;
    const hero = site?.images.homeHero;
    // Sections can be switched off in the panel (Site content → Home page).
    const show = (key: string) => site?.show.home[key] !== false;

    const submitTracking = (e: FormEvent) => {
        e.preventDefault();
        router.get(url('/tracking'), code.trim() ? { code: code.trim() } : {});
    };

    return (
        <SiteLayout>
            <Seo
                title={t.home.metaTitle}
                description={t.home.metaDescription}
                jsonLd={[
                    organizationLd(appUrl, locale, t.brand.name, contact, t.footer.address),
                    { '@type': 'WebSite', '@id': `${appUrl}/#website`, url: appUrl, name: t.brand.name, inLanguage: locale },
                ]}
            />

            {/* Hero */}
            <section className="mx-auto flex max-w-7xl flex-col gap-6 px-4 pt-10 pb-8 sm:px-6 sm:pt-16 lg:gap-8 lg:pt-20">
                <h1 className="text-[2.6rem] leading-[1.2] font-black tracking-tight text-balance sm:text-6xl lg:text-7xl xl:text-8xl">
                    {t.home.heroTitleA}
                    {t.home.heroTitleB && <br />}
                    {/* Logo blue on light backgrounds, logo yellow on dark */}
                    <span className="text-brand2-text dark:text-primary-text">{t.home.heroTitleB}</span>
                </h1>
                <p className="text-muted-foreground max-w-2xl text-base leading-8 sm:text-lg">{t.home.heroText}</p>

                <form onSubmit={submitTracking} className="bg-card flex w-full max-w-3xl flex-col gap-2 rounded-2xl border p-2 shadow-sm sm:flex-row">
                    <Label htmlFor="tracking-code" className="sr-only">
                        {t.home.trackingLabel}
                    </Label>
                    <div className="relative flex-1">
                        <Search
                            className="text-muted-foreground pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2"
                            aria-hidden="true"
                        />
                        <Input
                            id="tracking-code"
                            value={code}
                            onChange={(e) => setCode(e.target.value)}
                            placeholder={t.home.trackingPlaceholder}
                            autoComplete="off"
                            className="bg-background h-14 rounded-xl border-0 ps-12 text-base shadow-none"
                        />
                    </div>
                    <div className="grid grid-cols-2 gap-2 sm:flex">
                        <Button type="submit" size="lg" className="h-14 rounded-xl px-7 text-base font-black">
                            {t.home.trackingButton}
                        </Button>
                        <Button asChild size="lg" variant="outline" className="h-14 rounded-xl px-6 text-base">
                            <Link href={url('/quote')}>{t.home.quoteButton}</Link>
                        </Button>
                    </div>
                </form>
            </section>

            {/* Photo banner */}
            <section className="mx-auto max-w-7xl px-4 pb-6 sm:px-6">
                <img
                    src={hero?.src ?? '/images/photos/port-plane-1600.webp'}
                    srcSet={hero?.srcset || undefined}
                    sizes="(min-width: 1280px) 1232px, 100vw"
                    alt={hero?.alt || t.home.photoAlt}
                    width={hero?.width ?? 1600}
                    height={hero?.height ?? 900}
                    fetchPriority="high"
                    className="aspect-[16/9] w-full rounded-3xl object-cover sm:aspect-[21/9]"
                />
            </section>

            {/* Route map */}

            {show('routes') && (
                <section className="mx-auto max-w-7xl px-4 pb-16 sm:px-6" aria-labelledby="routes-title">
                    <div className="bg-card overflow-hidden rounded-3xl border p-4 sm:p-8">
                        <h2 id="routes-title" className="mb-2 text-lg font-bold sm:text-xl">
                            {t.home.routesTitle}
                        </h2>
                        <svg viewBox="0 0 1000 420" className="h-auto w-full" role="img" aria-label={t.home.routesAria}>
                            <g fill="none" strokeWidth="3" strokeDasharray="8 10" strokeLinecap="round">
                                {DESTINATIONS.map((d) => (
                                    <path key={d.path} d={d.path} className={d.mode === 'land' ? 'stroke-primary-text' : 'stroke-brand2-text'} />
                                ))}
                            </g>
                            {DESTINATIONS.map((d) => (
                                <circle key={`c${d.x}`} cx={d.x} cy={d.y} r="9" className="fill-foreground" />
                            ))}
                            <circle cx={ORIGIN.x} cy={ORIGIN.y} r="16" className="fill-primary" />
                            <circle cx={ORIGIN.x} cy={ORIGIN.y} r="28" className="fill-primary/25" />
                            {/* Labels inside the map only where they are large enough to read */}
                            <g className="fill-foreground hidden font-sans sm:inline" fontSize="26" textAnchor="middle">
                                <text x={ORIGIN.x} y={ORIGIN.y + 62} fontWeight="900" fontSize="30">
                                    {t.home.origin}
                                </text>
                                {DESTINATIONS.map((d, i) => (
                                    <text key={`t${d.x}`} x={d.x} y={d.y - 22}>
                                        {t.home.destinations[i]}
                                    </text>
                                ))}
                            </g>
                        </svg>

                        {/* On phones the names are listed under the map instead */}
                        <ul className="mt-4 flex flex-wrap gap-2 sm:hidden">
                            <li className="bg-primary text-primary-foreground rounded-full px-3 py-1 text-sm font-bold">{t.home.origin}</li>
                            {t.home.destinations.map((name) => (
                                <li key={name} className="bg-secondary rounded-full px-3 py-1 text-sm">
                                    {name}
                                </li>
                            ))}
                        </ul>

                        <div className="text-muted-foreground mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                            <span className="flex items-center gap-2">
                                <span className="border-primary-text w-7 border-t-[3px] border-dashed" aria-hidden="true" />
                                {t.home.legendLand}
                            </span>
                            <span className="flex items-center gap-2">
                                <span className="border-brand2-text w-7 border-t-[3px] border-dashed" aria-hidden="true" />
                                {t.home.legendAirSea}
                            </span>
                        </div>
                    </div>
                </section>
            )}

            {/* Services */}

            {show('services') && (
                <section className="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:pb-24" aria-labelledby="services-title">
                    <h2 id="services-title" className="mb-6 text-3xl font-black sm:text-4xl">
                        {t.home.servicesTitle}
                    </h2>
                    <ol>
                        {services.map((service, i) => (
                            <li key={service.slug}>
                                <Link
                                    href={url(`/services/${service.slug}`)}
                                    className="group grid grid-cols-[2.5rem_1fr_auto] items-center gap-x-3 gap-y-1 border-t py-5 sm:grid-cols-[3rem_minmax(0,16rem)_1fr_auto] sm:gap-x-6 sm:py-6"
                                >
                                    <span className="text-primary-text text-sm font-bold tabular-nums">{digits(i + 1)}</span>
                                    <strong className="text-xl font-black sm:text-2xl">{service.title}</strong>
                                    <span className="text-muted-foreground col-start-2 row-start-2 text-sm leading-7 sm:col-start-3 sm:row-start-1 sm:text-base">
                                        {service.short}
                                    </span>
                                    <DirArrow className="text-primary-text col-start-3 row-start-1 size-5 transition-transform group-hover:translate-x-1 sm:col-start-4 rtl:group-hover:-translate-x-1" />
                                </Link>
                            </li>
                        ))}
                    </ol>
                </section>
            )}

            {/* Stats band */}

            {show('stats') && (
                <section className="bg-primary text-primary-foreground">
                    <dl className="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-4 py-12 sm:px-6 md:grid-cols-4 lg:py-16">
                        {t.home.stats.map((stat) => (
                            <div key={stat.label} className="flex flex-col gap-1">
                                <dt className="order-2 text-sm font-medium sm:text-base">{stat.label}</dt>
                                <dd className="order-1 text-4xl font-black sm:text-5xl">{digits(stat.value)}</dd>
                            </div>
                        ))}
                    </dl>
                </section>
            )}

            {/* Latest articles (hidden until something is published) */}
            {show('blog') && posts.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-24" aria-labelledby="blog-title">
                    <div className="mb-6 flex items-end justify-between gap-4">
                        <h2 id="blog-title" className="text-3xl font-black sm:text-4xl">
                            {t.home.blogTitle}
                        </h2>
                        <Link href={url('/blog')} className="text-brand2-text flex min-h-11 items-center gap-1 text-sm font-bold">
                            {t.home.allPosts}
                            <DirArrow className="size-4" />
                        </Link>
                    </div>
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {posts.map((post) => (
                            <li key={post.slug}>
                                <PostCard post={post} headingLevel="h3" />
                            </li>
                        ))}
                    </ul>
                </section>
            )}

            {/* Call to action */}

            {show('cta') && (
                <section className="mx-auto max-w-7xl px-4 pb-24 sm:px-6">
                    <div className="bg-card flex flex-col items-start gap-6 rounded-3xl border p-8 sm:p-12 md:flex-row md:items-center md:justify-between">
                        <div className="flex flex-col gap-2">
                            <h2 className="text-2xl font-black sm:text-3xl">{t.home.ctaTitle}</h2>
                            <p className="text-muted-foreground leading-8">{t.home.ctaText}</p>
                        </div>
                        <Button asChild size="lg" className="h-14 w-full rounded-xl px-8 text-base font-black md:w-auto">
                            <Link href={url('/quote')}>{t.home.ctaButton}</Link>
                        </Button>
                    </div>
                </section>
            )}
        </SiteLayout>
    );
}
