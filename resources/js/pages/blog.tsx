import PageHeader from '@/components/site/page-header';
import PostCard from '@/components/site/post-card';
import Seo from '@/components/site/seo';
import { Button } from '@/components/ui/button';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { cn } from '@/lib/utils';
import type { PostCard as PostCardData } from '@/types/blog';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Newspaper } from 'lucide-react';

interface Props {
    posts: PostCardData[];
    pagination: { current: number; last: number };
}

export default function Blog({ posts, pagination }: Props) {
    const { t, url, digits } = useI18n();
    const pageUrl = (n: number) => url('/blog') + (n > 1 ? `?page=${n}` : '');
    const title = pagination.current > 1 ? `${t.blog.metaTitle} – ${t.blog.page} ${digits(pagination.current)}` : t.blog.metaTitle;

    return (
        <SiteLayout>
            <Seo title={title} description={t.blog.metaDescription} breadcrumbs={[{ name: t.nav.blog, path: url('/blog') }]} />

            <PageHeader title={t.blog.title} lead={t.blog.lead} crumbs={[{ label: t.nav.blog }]} />

            <section className="mx-auto max-w-7xl px-4 pb-24 sm:px-6">
                {posts.length === 0 ? (
                    <div className="bg-card text-muted-foreground flex flex-col items-center gap-4 rounded-3xl border px-6 py-16 text-center">
                        <Newspaper className="size-10" aria-hidden="true" />
                        <p>{t.blog.empty}</p>
                    </div>
                ) : (
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {posts.map((post) => (
                            <li key={post.slug}>
                                <PostCard post={post} />
                            </li>
                        ))}
                    </ul>
                )}

                {pagination.last > 1 && (
                    <nav aria-label={t.blog.page} className="mt-10 flex items-center justify-center gap-2">
                        <Button asChild={pagination.current > 1} variant="outline" size="icon" className="size-11" disabled={pagination.current <= 1}>
                            {pagination.current > 1 ? (
                                <Link href={pageUrl(pagination.current - 1)} aria-label={t.blog.prev}>
                                    <ChevronRight className="ltr:rotate-180" />
                                </Link>
                            ) : (
                                <span aria-label={t.blog.prev}>
                                    <ChevronRight className="ltr:rotate-180" />
                                </span>
                            )}
                        </Button>
                        {Array.from({ length: pagination.last }, (_, i) => i + 1).map((n) => (
                            <Link
                                key={n}
                                href={pageUrl(n)}
                                aria-current={n === pagination.current ? 'page' : undefined}
                                className={cn(
                                    'flex size-11 items-center justify-center rounded-md border text-sm font-bold',
                                    n === pagination.current ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-accent',
                                )}
                            >
                                {digits(n)}
                            </Link>
                        ))}
                        <Button
                            asChild={pagination.current < pagination.last}
                            variant="outline"
                            size="icon"
                            className="size-11"
                            disabled={pagination.current >= pagination.last}
                        >
                            {pagination.current < pagination.last ? (
                                <Link href={pageUrl(pagination.current + 1)} aria-label={t.blog.next}>
                                    <ChevronLeft className="ltr:rotate-180" />
                                </Link>
                            ) : (
                                <span aria-label={t.blog.next}>
                                    <ChevronLeft className="ltr:rotate-180" />
                                </span>
                            )}
                        </Button>
                    </nav>
                )}
            </section>
        </SiteLayout>
    );
}
