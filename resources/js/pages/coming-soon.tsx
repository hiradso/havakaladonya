import Seo from '@/components/site/seo';
import { Button } from '@/components/ui/button';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { Link } from '@inertiajs/react';
import { Construction } from 'lucide-react';

type PageKey = 'services' | 'tracking' | 'about' | 'blog' | 'contact' | 'quote';

/** Placeholder for pages that are not built yet, so the menu never leads to a 404. */
export default function ComingSoon({ page }: { page: PageKey }) {
    const { t, url } = useI18n();
    const title = t.nav[page];

    return (
        <SiteLayout>
            <Seo title={title} description={t.footer.about} noindex />
            <section className="mx-auto flex max-w-2xl flex-col items-center gap-6 px-4 py-24 text-center sm:py-32">
                <Construction className="text-primary-text size-12" aria-hidden="true" />
                <h1 className="text-3xl font-black sm:text-4xl">{title}</h1>
                <p className="text-muted-foreground leading-8">{t.comingSoon.text}</p>
                <Button asChild size="lg" className="h-12 px-8">
                    <Link href={url('/')}>{t.comingSoon.back}</Link>
                </Button>
            </section>
        </SiteLayout>
    );
}
