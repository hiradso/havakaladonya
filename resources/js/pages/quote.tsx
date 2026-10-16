import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/react';
import { Paperclip } from 'lucide-react';
import { type FormEvent, type ReactNode } from 'react';

type FieldConfig = { enabled: boolean; required: boolean };
type OptionalField = 'company' | 'email' | 'cargo_type' | 'weight_kg' | 'volume_cbm' | 'load_type' | 'services' | 'description' | 'attachment';

type QuoteFormData = {
    website: string; // honeypot
    transport_mode: string;
    origin: string;
    destination: string;
    cargo_type: string;
    weight_kg: string;
    volume_cbm: string;
    load_type: string;
    needs_clearance: boolean;
    needs_insurance: boolean;
    description: string;
    attachment: File | null;
    name: string;
    company: string;
    phone: string;
    email: string;
};

const MODES = ['air', 'sea', 'road', 'rail', 'unsure'] as const;
const LOAD_TYPES = ['fcl', 'lcl', 'ftl', 'groupage', 'unsure'] as const;

function initialMode(): string {
    if (typeof window === 'undefined') return '';
    const mode = new URLSearchParams(window.location.search).get('mode') ?? '';
    return (MODES as readonly string[]).includes(mode) ? mode : '';
}

export default function Quote({ form: config }: { form: Record<OptionalField, FieldConfig> }) {
    const { t, url } = useI18n();
    const q = t.quote;

    const { data, setData, post, processing, errors, transform } = useForm<QuoteFormData>({
        website: '',
        // Service pages link here with ?mode=air etc. to pre-select the transport mode.
        transport_mode: initialMode(),
        origin: '',
        destination: '',
        cargo_type: '',
        weight_kg: '',
        volume_cbm: '',
        load_type: '',
        needs_clearance: false,
        needs_insurance: false,
        description: '',
        attachment: null,
        name: '',
        company: '',
        phone: '',
        email: '',
    });

    const show = (key: OptionalField) => config[key]?.enabled;
    const req = (key: OptionalField) => config[key]?.required;
    const allErrors = errors as Record<string, string | undefined>;

    const submit = (e: FormEvent) => {
        e.preventDefault();
        // Only send the fields the form is currently showing.
        transform((d) => {
            const payload: Record<string, unknown> = { ...d };
            (Object.keys(config) as OptionalField[]).forEach((key) => {
                if (!config[key].enabled) {
                    if (key === 'services') {
                        delete payload.needs_clearance;
                        delete payload.needs_insurance;
                    } else {
                        delete payload[key];
                    }
                }
            });
            if (!payload.attachment) delete payload.attachment;
            return payload;
        });
        post(url('/quote'), {
            forceFormData: true,
            preserveScroll: (page) => Object.keys(page.props.errors ?? {}).length > 0,
            onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
        });
    };

    const hasErrors = Object.keys(errors).length > 0;

    return (
        <SiteLayout>
            <Head title={q.metaTitle} />

            <section className="mx-auto max-w-3xl px-4 pt-10 pb-6 sm:px-6 sm:pt-14">
                <h1 className="text-3xl font-black sm:text-5xl">{q.title}</h1>
                <p className="text-muted-foreground mt-4 leading-8">{q.intro}</p>
            </section>

            <form onSubmit={submit} noValidate className="relative mx-auto flex max-w-3xl flex-col gap-6 px-4 pb-24 sm:px-6">
                {hasErrors && (
                    <div role="alert" className="border-destructive/40 bg-destructive/10 text-destructive rounded-xl border p-4 text-sm font-medium">
                        {allErrors.form ?? q.errorSummary}
                    </div>
                )}

                {/* Honeypot — hidden from people, bots fill it in */}
                <div aria-hidden="true" className="pointer-events-none absolute start-0 top-0 h-px w-px overflow-hidden opacity-0">
                    <label htmlFor="website">Website</label>
                    <input id="website" tabIndex={-1} autoComplete="off" value={data.website} onChange={(e) => setData('website', e.target.value)} />
                </div>

                {/* Shipment */}
                <FormCard title={q.sectionShipment}>
                    <fieldset className="flex flex-col gap-3">
                        <legend className="mb-3 text-sm font-bold">
                            {q.transportMode} <RequiredMark label={q.required} />
                        </legend>
                        <ChoiceGroup
                            name="transport_mode"
                            options={MODES.map((m) => ({ value: m, label: q.modes[m] }))}
                            value={data.transport_mode}
                            onChange={(v) => setData('transport_mode', v)}
                            invalid={!!errors.transport_mode}
                        />
                        <InputError message={errors.transport_mode} />
                    </fieldset>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field id="origin" label={q.origin} required requiredLabel={q.required} error={errors.origin}>
                            <Input
                                id="origin"
                                value={data.origin}
                                onChange={(e) => setData('origin', e.target.value)}
                                placeholder={q.originPlaceholder}
                                aria-invalid={!!errors.origin}
                                className="h-12"
                            />
                        </Field>
                        <Field id="destination" label={q.destination} required requiredLabel={q.required} error={errors.destination}>
                            <Input
                                id="destination"
                                value={data.destination}
                                onChange={(e) => setData('destination', e.target.value)}
                                placeholder={q.destinationPlaceholder}
                                aria-invalid={!!errors.destination}
                                className="h-12"
                            />
                        </Field>
                    </div>
                </FormCard>

                {/* Cargo */}
                {(show('cargo_type') ||
                    show('weight_kg') ||
                    show('volume_cbm') ||
                    show('load_type') ||
                    show('services') ||
                    show('description') ||
                    show('attachment')) && (
                    <FormCard title={q.sectionCargo}>
                        {show('cargo_type') && (
                            <Field
                                id="cargo_type"
                                label={q.cargoType}
                                required={req('cargo_type')}
                                requiredLabel={q.required}
                                optionalLabel={q.optional}
                                error={errors.cargo_type}
                            >
                                <Input
                                    id="cargo_type"
                                    value={data.cargo_type}
                                    onChange={(e) => setData('cargo_type', e.target.value)}
                                    placeholder={q.cargoTypePlaceholder}
                                    aria-invalid={!!errors.cargo_type}
                                    className="h-12"
                                />
                            </Field>
                        )}

                        {(show('weight_kg') || show('volume_cbm')) && (
                            <div className="grid grid-cols-2 gap-4">
                                {show('weight_kg') && (
                                    <Field
                                        id="weight_kg"
                                        label={q.weight}
                                        required={req('weight_kg')}
                                        requiredLabel={q.required}
                                        optionalLabel={q.optional}
                                        error={errors.weight_kg}
                                    >
                                        <Input
                                            id="weight_kg"
                                            inputMode="decimal"
                                            value={data.weight_kg}
                                            onChange={(e) => setData('weight_kg', e.target.value)}
                                            aria-invalid={!!errors.weight_kg}
                                            className="h-12"
                                        />
                                    </Field>
                                )}
                                {show('volume_cbm') && (
                                    <Field
                                        id="volume_cbm"
                                        label={q.volume}
                                        required={req('volume_cbm')}
                                        requiredLabel={q.required}
                                        optionalLabel={q.optional}
                                        error={errors.volume_cbm}
                                    >
                                        <Input
                                            id="volume_cbm"
                                            inputMode="decimal"
                                            value={data.volume_cbm}
                                            onChange={(e) => setData('volume_cbm', e.target.value)}
                                            aria-invalid={!!errors.volume_cbm}
                                            className="h-12"
                                        />
                                    </Field>
                                )}
                            </div>
                        )}

                        {show('load_type') && (
                            <fieldset className="flex flex-col gap-3">
                                <legend className="mb-3 text-sm font-bold">
                                    {q.loadType} {req('load_type') ? <RequiredMark label={q.required} /> : <OptionalMark label={q.optional} />}
                                </legend>
                                <ChoiceGroup
                                    name="load_type"
                                    options={LOAD_TYPES.map((l) => ({ value: l, label: q.loadTypes[l] }))}
                                    value={data.load_type}
                                    onChange={(v) => setData('load_type', v)}
                                    invalid={!!errors.load_type}
                                />
                                <InputError message={errors.load_type} />
                            </fieldset>
                        )}

                        {show('services') && (
                            <fieldset className="flex flex-col gap-3">
                                <legend className="mb-3 text-sm font-bold">{q.services}</legend>
                                <CheckRow
                                    id="needs_clearance"
                                    label={q.clearance}
                                    checked={data.needs_clearance}
                                    onChange={(v) => setData('needs_clearance', v)}
                                />
                                <CheckRow
                                    id="needs_insurance"
                                    label={q.insurance}
                                    checked={data.needs_insurance}
                                    onChange={(v) => setData('needs_insurance', v)}
                                />
                            </fieldset>
                        )}

                        {show('description') && (
                            <Field
                                id="description"
                                label={q.description}
                                required={req('description')}
                                requiredLabel={q.required}
                                optionalLabel={q.optional}
                                error={errors.description}
                            >
                                <Textarea
                                    id="description"
                                    rows={4}
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder={q.descriptionPlaceholder}
                                    aria-invalid={!!errors.description}
                                />
                            </Field>
                        )}

                        {show('attachment') && (
                            <Field
                                id="attachment"
                                label={q.attachment}
                                required={req('attachment')}
                                requiredLabel={q.required}
                                optionalLabel={q.optional}
                                error={errors.attachment}
                            >
                                <label
                                    htmlFor="attachment"
                                    className="hover:border-primary-text flex min-h-14 cursor-pointer items-center gap-3 rounded-md border border-dashed px-4 py-3 text-sm"
                                >
                                    <Paperclip className="text-muted-foreground size-5 shrink-0" aria-hidden="true" />
                                    <span className="min-w-0 truncate">{data.attachment?.name ?? q.attachmentHint}</span>
                                </label>
                                <input
                                    id="attachment"
                                    type="file"
                                    className="sr-only"
                                    accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx"
                                    onChange={(e) => setData('attachment', e.target.files?.[0] ?? null)}
                                />
                            </Field>
                        )}
                    </FormCard>
                )}

                {/* Contact */}
                <FormCard title={q.sectionContact}>
                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field id="name" label={q.name} required requiredLabel={q.required} error={errors.name}>
                            <Input
                                id="name"
                                autoComplete="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                aria-invalid={!!errors.name}
                                className="h-12"
                            />
                        </Field>
                        <Field id="phone" label={q.phone} required requiredLabel={q.required} error={errors.phone}>
                            <Input
                                id="phone"
                                type="tel"
                                inputMode="tel"
                                autoComplete="tel"
                                dir="ltr"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                placeholder={q.phonePlaceholder}
                                aria-invalid={!!errors.phone}
                                className="h-12 text-start rtl:text-right"
                            />
                        </Field>
                        {show('company') && (
                            <Field
                                id="company"
                                label={q.company}
                                required={req('company')}
                                requiredLabel={q.required}
                                optionalLabel={q.optional}
                                error={errors.company}
                            >
                                <Input
                                    id="company"
                                    autoComplete="organization"
                                    value={data.company}
                                    onChange={(e) => setData('company', e.target.value)}
                                    aria-invalid={!!errors.company}
                                    className="h-12"
                                />
                            </Field>
                        )}
                        {show('email') && (
                            <Field
                                id="email"
                                label={q.email}
                                required={req('email')}
                                requiredLabel={q.required}
                                optionalLabel={q.optional}
                                error={errors.email}
                            >
                                <Input
                                    id="email"
                                    type="email"
                                    autoComplete="email"
                                    dir="ltr"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    aria-invalid={!!errors.email}
                                    className="h-12 rtl:text-right"
                                />
                            </Field>
                        )}
                    </div>
                </FormCard>

                <Button type="submit" size="lg" disabled={processing} className="h-14 w-full text-base font-black sm:w-auto sm:self-start sm:px-12">
                    {processing ? q.sending : q.submit}
                </Button>
            </form>
        </SiteLayout>
    );
}

function FormCard({ title, children }: { title: string; children: ReactNode }) {
    return (
        <section className="bg-card flex flex-col gap-6 rounded-2xl border p-5 sm:p-8">
            <h2 className="text-lg font-black sm:text-xl">{title}</h2>
            {children}
        </section>
    );
}

function RequiredMark({ label }: { label: string }) {
    return (
        <span className="text-destructive" title={label}>
            *<span className="sr-only">({label})</span>
        </span>
    );
}

function OptionalMark({ label }: { label: string }) {
    return <span className="text-muted-foreground text-xs font-normal">({label})</span>;
}

function Field({
    id,
    label,
    required,
    requiredLabel,
    optionalLabel,
    error,
    children,
}: {
    id: string;
    label: string;
    required?: boolean;
    requiredLabel: string;
    optionalLabel?: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <div className="flex min-w-0 flex-col gap-2">
            <Label htmlFor={id} className="text-sm font-bold">
                {label} {required ? <RequiredMark label={requiredLabel} /> : optionalLabel ? <OptionalMark label={optionalLabel} /> : null}
            </Label>
            {children}
            <InputError message={error} />
        </div>
    );
}

/** Large tappable options (better than a dropdown on phones). Works as a radio group. */
function ChoiceGroup({
    name,
    options,
    value,
    onChange,
    invalid,
}: {
    name: string;
    options: { value: string; label: string }[];
    value: string;
    onChange: (value: string) => void;
    invalid?: boolean;
}) {
    return (
        <div className="grid grid-cols-2 gap-2 sm:grid-cols-5">
            {options.map((option) => {
                const checked = value === option.value;
                return (
                    <label
                        key={option.value}
                        className={cn(
                            'has-[:focus-visible]:ring-ring flex min-h-12 cursor-pointer items-center justify-center rounded-lg border px-3 text-center text-sm font-medium transition-colors has-[:focus-visible]:ring-2',
                            checked ? 'border-primary bg-primary text-primary-foreground font-bold' : 'hover:bg-accent',
                            invalid && !checked && 'border-destructive/60',
                        )}
                    >
                        <input
                            type="radio"
                            name={name}
                            value={option.value}
                            checked={checked}
                            onChange={() => onChange(option.value)}
                            className="sr-only"
                        />
                        {option.label}
                    </label>
                );
            })}
        </div>
    );
}

function CheckRow({ id, label, checked, onChange }: { id: string; label: string; checked: boolean; onChange: (v: boolean) => void }) {
    return (
        <div className="flex min-h-11 items-center gap-3">
            <Checkbox id={id} checked={checked} onCheckedChange={(v) => onChange(v === true)} />
            <Label htmlFor={id} className="cursor-pointer leading-6 font-normal">
                {label}
            </Label>
        </div>
    );
}
