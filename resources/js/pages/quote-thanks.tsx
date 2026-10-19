import Seo from '@/components/site/seo';
import { Button } from '@/components/ui/button';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { Link } from '@inertiajs/react';
import { CircleCheck } from 'lucide-react';

export default function QuoteThanks({ reference }: { reference: string | null }) {
    const { t, url } = useI18n();
    const q = t.quote;

    return (
        <SiteLayout>
            <Seo title={q.thanksTitle} description={t.footer.about} noindex />
            <section className="mx-auto flex max-w-xl flex-col items-center gap-6 px-4 py-20 text-center sm:py-28">
                <CircleCheck className="size-16 text-emerald-500" aria-hidden="true" />
                <h1 className="text-3xl font-black sm:text-4xl">{q.thanksTitle}</h1>
                <p className="text-muted-foreground leading-8">{q.thanksText}</p>

                {reference && (
                    <div className="bg-card w-full rounded-2xl border p-5">
                        <p className="text-muted-foreground text-sm">{q.thanksReference}</p>
                        {/* Kept in Latin digits on purpose: customers read it out or copy it. */}
                        <p dir="ltr" className="mt-1 font-mono text-2xl font-black tracking-wider select-all">
                            {reference}
                        </p>
                    </div>
                )}

                <div className="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <Button asChild size="lg" className="h-12 px-8">
                        <Link href={url('/')}>{q.thanksHome}</Link>
                    </Button>
                    <Button asChild size="lg" variant="outline" className="h-12 px-8">
                        <Link href={url('/quote')}>{q.thanksNew}</Link>
                    </Button>
                </div>
            </section>
        </SiteLayout>
    );
}
