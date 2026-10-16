import { useI18n } from '@/i18n';
import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';
import type { ReactNode } from 'react';

interface Crumb {
    label: string;
    href?: string;
}

/** Title block used at the top of inner pages, with breadcrumbs. */
export default function PageHeader({ title, lead, crumbs = [], children }: { title: string; lead?: string; crumbs?: Crumb[]; children?: ReactNode }) {
    const { t, url } = useI18n();
    const all: Crumb[] = [{ label: t.common.home, href: url('/') }, ...crumbs];

    return (
        <section className="mx-auto flex max-w-7xl flex-col gap-4 px-4 pt-8 pb-8 sm:px-6 sm:pt-12 lg:pb-12">
            <nav aria-label={t.common.breadcrumb}>
                <ol className="text-muted-foreground flex flex-wrap items-center gap-1 text-sm">
                    {all.map((crumb, i) => (
                        <li key={crumb.label} className="flex items-center gap-1">
                            {i > 0 && <ChevronLeft className="size-4 ltr:rotate-180" aria-hidden="true" />}
                            {crumb.href ? (
                                <Link href={crumb.href} className="hover:text-foreground inline-flex min-h-8 items-center">
                                    {crumb.label}
                                </Link>
                            ) : (
                                <span aria-current="page" className="text-foreground">
                                    {crumb.label}
                                </span>
                            )}
                        </li>
                    ))}
                </ol>
            </nav>
            <h1 className="max-w-4xl text-4xl leading-[1.25] font-black tracking-tight text-balance sm:text-5xl lg:text-6xl">{title}</h1>
            {lead && <p className="text-muted-foreground max-w-3xl text-base leading-8 sm:text-lg">{lead}</p>}
            {children}
        </section>
    );
}
