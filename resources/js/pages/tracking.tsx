import Seo from '@/components/site/seo';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type Locale, useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { CalendarClock, Check, CircleAlert, MapPin, Package, Phone, Plane, Search, Ship, TrainFront, Truck } from 'lucide-react';
import { type FormEvent, useState } from 'react';

type Mode = 'air' | 'sea' | 'road' | 'rail' | 'unsure';

interface TrackingEvent {
    id: number;
    stageId: number;
    stage: string | null;
    location: string | null;
    note: string | null;
    at: string;
}

interface Shipment {
    code: string;
    mode: Mode;
    origin: string;
    destination: string;
    cargo: string | null;
    eta: string | null;
    currentStageId: number | null;
    delivered: boolean;
    stages: { id: number; name: string }[];
    events: TrackingEvent[];
}

interface Props {
    code: string;
    shipment: Shipment | null;
    notFound: boolean;
    tooMany: boolean;
}

const MODE_ICONS = {
    air: Plane,
    sea: Ship,
    road: Truck,
    rail: TrainFront,
    unsure: Package,
} as const;

/** Jalali calendar + Persian digits on the Persian site; always in Tehran time. */
function formatDateTime(iso: string, locale: Locale) {
    return new Intl.DateTimeFormat(locale === 'fa' ? 'fa-IR-u-ca-persian' : 'en-GB', {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone: 'Asia/Tehran',
    }).format(new Date(iso));
}

function formatDate(ymd: string, locale: Locale) {
    // Noon UTC so the day never shifts across time zones.
    return new Intl.DateTimeFormat(locale === 'fa' ? 'fa-IR-u-ca-persian' : 'en-GB', {
        dateStyle: 'long',
        timeZone: 'UTC',
    }).format(new Date(`${ymd}T12:00:00Z`));
}

export default function Tracking({ code, shipment, notFound, tooMany }: Props) {
    const { t, url, locale } = useI18n();
    const [value, setValue] = useState(code ?? '');
    const [loading, setLoading] = useState(false);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        const trimmed = value.trim();
        router.get(url('/tracking'), trimmed ? { code: trimmed } : {}, {
            preserveScroll: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    return (
        <SiteLayout>
            <Seo
                title={shipment ? `${shipment.code} – ${t.tracking.metaTitle}` : t.tracking.metaTitle}
                description={t.tracking.metaDescription}
                noindex={!!code}
            />

            <section className="mx-auto flex max-w-3xl flex-col gap-5 px-4 pt-10 pb-6 sm:px-6 sm:pt-16">
                <h1 className="text-3xl font-black sm:text-5xl">{t.tracking.title}</h1>
                <p className="text-muted-foreground leading-8 sm:text-lg">{t.tracking.intro}</p>

                <form onSubmit={submit} className="bg-card flex flex-col gap-2 rounded-2xl border p-2 shadow-sm sm:flex-row" role="search">
                    <Label htmlFor="tracking-code" className="sr-only">
                        {t.tracking.label}
                    </Label>
                    <div className="relative flex-1">
                        <Search
                            className="text-muted-foreground pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2"
                            aria-hidden="true"
                        />
                        <Input
                            id="tracking-code"
                            value={value}
                            onChange={(e) => setValue(e.target.value)}
                            placeholder={t.tracking.placeholder}
                            autoComplete="off"
                            autoCapitalize="characters"
                            spellCheck={false}
                            className="bg-background h-14 rounded-xl border-0 ps-12 font-mono text-base tracking-wider shadow-none"
                        />
                    </div>
                    <Button type="submit" size="lg" disabled={loading} className="h-14 rounded-xl px-8 text-base font-black">
                        {t.tracking.button}
                    </Button>
                </form>

                {(notFound || tooMany) && (
                    <div
                        role="alert"
                        className="border-destructive/40 bg-destructive/5 text-destructive flex items-start gap-3 rounded-2xl border p-4"
                    >
                        <CircleAlert className="mt-1 size-5 shrink-0" aria-hidden="true" />
                        <p className="leading-7">{tooMany ? t.tracking.tooMany : t.tracking.notFound}</p>
                    </div>
                )}
            </section>

            {shipment && <Result shipment={shipment} />}
        </SiteLayout>
    );
}

function Result({ shipment }: { shipment: Shipment }) {
    const { t, locale } = useI18n();
    const ModeIcon = MODE_ICONS[shipment.mode] ?? Package;
    const currentIndex = shipment.stages.findIndex((s) => s.id === shipment.currentStageId);
    const currentName = shipment.stages[currentIndex]?.name ?? shipment.events[0]?.stage ?? '—';

    return (
        <section className="mx-auto flex max-w-3xl flex-col gap-4 px-4 pb-20 sm:px-6" aria-live="polite">
            {/* Summary */}
            <div className="bg-card overflow-hidden rounded-3xl border">
                <div className="flex flex-wrap items-center justify-between gap-3 border-b p-5 sm:p-6">
                    <span dir="ltr" className="font-mono text-lg font-bold tracking-wider sm:text-xl">
                        {shipment.code}
                    </span>
                    <span
                        className={cn(
                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-bold',
                            shipment.delivered ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' : 'bg-primary text-primary-foreground',
                        )}
                    >
                        {shipment.delivered && <Check className="size-4" aria-hidden="true" />}
                        <span className="sr-only">{t.tracking.status}: </span>
                        {currentName}
                    </span>
                </div>

                <div className="flex items-center gap-3 p-5 sm:p-6">
                    <span className="bg-secondary text-brand2-text dark:text-primary-text flex size-12 shrink-0 items-center justify-center rounded-2xl">
                        <ModeIcon className="size-6" aria-hidden="true" />
                    </span>
                    <div className="flex min-w-0 flex-1 flex-col">
                        <span className="text-muted-foreground text-xs">
                            {t.tracking.route} · {t.tracking.modes[shipment.mode]}
                        </span>
                        <span className="flex min-w-0 flex-wrap items-center gap-x-2 text-lg font-black sm:text-xl">
                            <span dir="auto" className="break-words">
                                {shipment.origin}
                            </span>
                            <span className="text-muted-foreground font-normal" aria-hidden="true">
                                {locale === 'fa' ? '←' : '→'}
                            </span>
                            <span dir="auto" className="break-words">
                                {shipment.destination}
                            </span>
                        </span>
                    </div>
                </div>

                {(shipment.cargo || shipment.eta) && (
                    <dl className="grid gap-4 border-t p-5 sm:grid-cols-2 sm:p-6">
                        {shipment.cargo && (
                            <div className="flex items-start gap-2">
                                <Package className="text-muted-foreground mt-0.5 size-4 shrink-0" aria-hidden="true" />
                                <div>
                                    <dt className="text-muted-foreground text-xs">{t.tracking.cargo}</dt>
                                    <dd dir="auto" className="font-medium">
                                        {shipment.cargo}
                                    </dd>
                                </div>
                            </div>
                        )}
                        {shipment.eta && !shipment.delivered && (
                            <div className="flex items-start gap-2">
                                <CalendarClock className="text-muted-foreground mt-0.5 size-4 shrink-0" aria-hidden="true" />
                                <div>
                                    <dt className="text-muted-foreground text-xs">{t.tracking.eta}</dt>
                                    <dd className="font-medium">{formatDate(shipment.eta, locale)}</dd>
                                </div>
                            </div>
                        )}
                    </dl>
                )}
            </div>

            {/* Stages: done / current / upcoming */}
            {shipment.stages.length > 0 && (
                <div className="bg-card rounded-3xl border p-5 sm:p-6">
                    <h2 className="mb-4 text-lg font-bold">{t.tracking.progress}</h2>
                    <ol className="flex flex-col">
                        {shipment.stages.map((stage, i) => {
                            // A delivered shipment shows every stage, including the last, as done.
                            const done = currentIndex >= 0 && (i < currentIndex || (shipment.delivered && i === currentIndex));
                            const current = i === currentIndex && !shipment.delivered;
                            const last = i === shipment.stages.length - 1;
                            return (
                                <li key={stage.id} className="relative flex gap-3 pb-5 last:pb-0" aria-current={current ? 'step' : undefined}>
                                    {!last && (
                                        <span
                                            className={cn(
                                                'absolute start-[0.6875rem] top-7 bottom-1 w-0.5 rounded',
                                                done || current ? 'bg-primary' : 'bg-border',
                                            )}
                                            aria-hidden="true"
                                        />
                                    )}
                                    <span
                                        className={cn(
                                            'relative z-10 flex size-6 shrink-0 items-center justify-center rounded-full border-2',
                                            done && 'border-primary bg-primary text-primary-foreground',
                                            current && 'border-primary bg-background ring-primary/25 ring-4',
                                            !done && !current && 'border-border bg-background',
                                        )}
                                        aria-hidden="true"
                                    >
                                        {done && <Check className="size-3.5" strokeWidth={3} />}
                                        {current && <span className="bg-primary size-2 rounded-full" />}
                                    </span>
                                    <span className={cn('pt-0.5 leading-6', current ? 'font-black' : done ? 'font-medium' : 'text-muted-foreground')}>
                                        {stage.name}
                                    </span>
                                </li>
                            );
                        })}
                    </ol>
                </div>
            )}

            {/* History with dates, places and notes */}
            <div className="bg-card rounded-3xl border p-5 sm:p-6">
                <h2 className="mb-4 text-lg font-bold">{t.tracking.history}</h2>
                {shipment.events.length === 0 ? (
                    <p className="text-muted-foreground">{t.tracking.noEvents}</p>
                ) : (
                    <ol className="flex flex-col divide-y">
                        {shipment.events.map((event) => (
                            <li key={event.id} className="flex flex-col gap-1 py-4 first:pt-0 last:pb-0">
                                <div className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                    <strong className="font-bold">{event.stage}</strong>
                                    <time dateTime={event.at} className="text-muted-foreground text-sm">
                                        {formatDateTime(event.at, locale)}
                                    </time>
                                </div>
                                {event.location && (
                                    <span className="text-muted-foreground flex items-center gap-1.5 text-sm">
                                        <MapPin className="size-4 shrink-0" aria-hidden="true" />
                                        <span dir="auto">{event.location}</span>
                                    </span>
                                )}
                                {event.note && (
                                    <p dir="auto" className="text-start leading-7 whitespace-pre-line">
                                        {event.note}
                                    </p>
                                )}
                            </li>
                        ))}
                    </ol>
                )}
            </div>

            <div className="flex flex-col items-start gap-3 rounded-3xl border border-dashed p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <p className="font-medium">{t.tracking.help}</p>
                <Button asChild variant="outline" size="lg" className="h-12 w-full sm:w-auto">
                    <a href="tel:+982188015232">
                        <Phone className="size-4" aria-hidden="true" />
                        {t.tracking.call}
                    </a>
                </Button>
            </div>
        </section>
    );
}
