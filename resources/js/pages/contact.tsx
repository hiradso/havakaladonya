import InputError from '@/components/input-error';
import PageHeader from '@/components/site/page-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { CONTACT } from '@/lib/contact';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, AtSign, CircleCheck, Instagram, Mail, MapPin, MessageCircle, Phone, Printer, Search, Smartphone } from 'lucide-react';
import { type FormEvent, type ReactNode } from 'react';

export default function Contact({ sent }: { sent?: boolean }) {
    const { t, url, digits } = useI18n();
    const c = t.contact;

    const { data, setData, post, processing, errors, reset } = useForm({
        website: '', // honeypot
        name: '',
        phone: '',
        email: '',
        message: '',
    });
    const allErrors = errors as Record<string, string>;

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(url('/contact'), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <SiteLayout>
            <Head title={c.metaTitle}>
                <meta name="description" content={c.metaDescription} />
            </Head>

            <PageHeader title={c.title} lead={c.lead} crumbs={[{ label: t.nav.contact }]} />

            <div className="mx-auto grid max-w-7xl gap-8 px-4 pb-24 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,28rem)] lg:gap-12">
                {/* Contact methods */}
                <div className="flex flex-col gap-4">
                    <ul className="grid gap-3 sm:grid-cols-2">
                        <Method icon={<Phone />} label={c.phone} href={CONTACT.phone.href}>
                            <span dir="ltr">{digits(CONTACT.phone.display)}</span>
                        </Method>
                        <Method icon={<Smartphone />} label={c.mobile} href={CONTACT.mobile.href}>
                            <span dir="ltr">{digits(CONTACT.mobile.display)}</span>
                        </Method>
                        <Method icon={<MessageCircle />} label={c.whatsapp} href={CONTACT.whatsapp} external>
                            {c.whatsappAction}
                        </Method>
                        <Method icon={<Mail />} label={c.email} href={`mailto:${CONTACT.email}`}>
                            <span dir="ltr">{CONTACT.email}</span>
                        </Method>
                        <Method icon={<Instagram />} label={c.instagram} href={CONTACT.instagram.href} external>
                            <span dir="ltr" className="inline-flex items-center gap-0.5">
                                <AtSign className="size-4" aria-hidden="true" />
                                {CONTACT.instagram.handle}
                            </span>
                        </Method>
                        <Method icon={<Printer />} label={c.fax}>
                            <span dir="ltr">{digits(CONTACT.fax.display)}</span>
                        </Method>
                    </ul>

                    <div className="bg-card flex flex-col gap-4 rounded-2xl border p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <div className="flex items-start gap-3">
                            <MapPin className="text-brand2-text dark:text-primary-text mt-1 size-5 shrink-0" aria-hidden="true" />
                            <div className="flex flex-col gap-1">
                                <span className="text-muted-foreground text-xs">{c.address}</span>
                                <address className="leading-7 font-medium not-italic">{t.footer.address}</address>
                            </div>
                        </div>
                        <Button asChild variant="outline" size="lg" className="h-11 shrink-0">
                            <a href={CONTACT.mapUrl} target="_blank" rel="noopener noreferrer">
                                {c.directions}
                            </a>
                        </Button>
                    </div>

                    <div className="flex flex-col gap-3 pt-2">
                        <strong className="text-sm">{c.quickTitle}</strong>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <QuickLink href={url('/quote')} icon={<ArrowRight className="rtl:-scale-x-100" />}>
                                {c.quickQuote}
                            </QuickLink>
                            <QuickLink href={url('/tracking')} icon={<Search />}>
                                {c.quickTracking}
                            </QuickLink>
                        </div>
                    </div>
                </div>

                {/* Message form */}
                <section aria-labelledby="form-title" className="bg-card rounded-3xl border p-5 sm:p-8 lg:self-start">
                    <h2 id="form-title" className="text-2xl font-black">
                        {c.formTitle}
                    </h2>
                    <p className="text-muted-foreground mt-2 leading-7">{c.formText}</p>

                    {sent && (
                        <div role="status" className="mt-5 flex items-start gap-3 rounded-xl border border-emerald-500/40 bg-emerald-500/10 p-4">
                            <CircleCheck className="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400" aria-hidden="true" />
                            <p className="leading-7 font-medium">{c.sent}</p>
                        </div>
                    )}
                    {allErrors.form && (
                        <div
                            role="alert"
                            className="border-destructive/40 bg-destructive/10 text-destructive mt-5 rounded-xl border p-4 text-sm font-medium"
                        >
                            {allErrors.form}
                        </div>
                    )}

                    <form onSubmit={submit} noValidate className="relative mt-6 flex flex-col gap-5">
                        <div aria-hidden="true" className="pointer-events-none absolute start-0 top-0 h-px w-px overflow-hidden opacity-0">
                            <label htmlFor="contact-website">Website</label>
                            <input
                                id="contact-website"
                                tabIndex={-1}
                                autoComplete="off"
                                value={data.website}
                                onChange={(e) => setData('website', e.target.value)}
                            />
                        </div>

                        <FormField id="contact-name" label={c.name} error={errors.name}>
                            <Input
                                id="contact-name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                autoComplete="name"
                                aria-invalid={!!errors.name}
                                className="h-12"
                            />
                        </FormField>
                        <FormField id="contact-phone" label={c.phoneField} error={errors.phone}>
                            <Input
                                id="contact-phone"
                                type="tel"
                                inputMode="tel"
                                dir="ltr"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                autoComplete="tel"
                                aria-invalid={!!errors.phone}
                                className="h-12 text-start rtl:text-right"
                            />
                        </FormField>
                        <FormField id="contact-email" label={c.emailField} optional={c.optional} error={errors.email}>
                            <Input
                                id="contact-email"
                                type="email"
                                dir="ltr"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                autoComplete="email"
                                aria-invalid={!!errors.email}
                                className="h-12 text-start rtl:text-right"
                            />
                        </FormField>
                        <FormField id="contact-message" label={c.message} error={errors.message}>
                            <Textarea
                                id="contact-message"
                                rows={5}
                                value={data.message}
                                onChange={(e) => setData('message', e.target.value)}
                                placeholder={c.messagePlaceholder}
                                aria-invalid={!!errors.message}
                            />
                        </FormField>

                        <Button type="submit" size="lg" disabled={processing} className="h-12 font-bold">
                            {processing ? c.sending : c.submit}
                        </Button>
                    </form>
                </section>
            </div>
        </SiteLayout>
    );
}

function Method({
    icon,
    label,
    href,
    external,
    children,
}: {
    icon: ReactNode;
    label: string;
    href?: string;
    external?: boolean;
    children: ReactNode;
}) {
    const inner = (
        <>
            <span className="bg-secondary text-brand2-text dark:text-primary-text flex size-11 shrink-0 items-center justify-center rounded-xl [&_svg]:size-5">
                {icon}
            </span>
            <span className="flex min-w-0 flex-col">
                <span className="text-muted-foreground text-xs">{label}</span>
                <span className="truncate font-bold">{children}</span>
            </span>
        </>
    );
    const cls = 'bg-card flex h-full items-center gap-3 rounded-2xl border p-4';

    return (
        <li>
            {href ? (
                <a
                    href={href}
                    className={`${cls} hover:border-primary transition-colors`}
                    {...(external ? { target: '_blank', rel: 'noopener noreferrer' } : {})}
                >
                    {inner}
                </a>
            ) : (
                <div className={cls}>{inner}</div>
            )}
        </li>
    );
}

function QuickLink({ href, icon, children }: { href: string; icon: ReactNode; children: ReactNode }) {
    return (
        <Link
            href={href}
            className="hover:border-primary flex min-h-12 items-center justify-between gap-3 rounded-xl border px-4 font-medium transition-colors [&_svg]:size-4"
        >
            {children}
            {icon}
        </Link>
    );
}

function FormField({ id, label, optional, error, children }: { id: string; label: string; optional?: string; error?: string; children: ReactNode }) {
    return (
        <div className="flex flex-col gap-2">
            <Label htmlFor={id} className="text-sm font-bold">
                {label} {optional && <span className="text-muted-foreground font-normal">({optional})</span>}
            </Label>
            {children}
            <InputError message={error} />
        </div>
    );
}
