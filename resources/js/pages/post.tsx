import ContactCta from '@/components/site/contact-cta';
import PostCard from '@/components/site/post-card';
import { useI18n } from '@/i18n';
import SiteLayout from '@/layouts/site-layout';
import { formatDate } from '@/lib/format';
import type { PostCard as PostCardData } from '@/types/blog';
import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, Clock } from 'lucide-react';

interface Props {
    post: PostCardData & { body: string; metaTitle: string; metaDescription: string };
    related: PostCardData[];
}

export default function PostPage({ post, related }: Props) {
    const { t, url, locale, digits } = useI18n();

    return (
        <SiteLayout>
            <Head title={post.metaTitle}>
                <meta name="description" content={post.metaDescription} />
                <meta property="og:type" content="article" />
                <meta property="og:title" content={post.title} />
                <meta property="og:description" content={post.metaDescription} />
                {post.cover && <meta property="og:image" content={post.cover} />}
            </Head>

            <article>
                <header className="mx-auto flex max-w-3xl flex-col gap-4 px-4 pt-8 pb-8 sm:px-6 sm:pt-12">
                    <nav aria-label={t.common.breadcrumb}>
                        <ol className="text-muted-foreground flex flex-wrap items-center gap-1 text-sm">
                            <li>
                                <Link href={url('/')} className="hover:text-foreground inline-flex min-h-8 items-center">
                                    {t.common.home}
                                </Link>
                            </li>
                            <li className="flex items-center gap-1">
                                <ChevronLeft className="size-4 ltr:rotate-180" aria-hidden="true" />
                                <Link href={url('/blog')} className="hover:text-foreground inline-flex min-h-8 items-center">
                                    {t.nav.blog}
                                </Link>
                            </li>
                        </ol>
                    </nav>
                    <h1 className="text-3xl leading-[1.35] font-black text-balance sm:text-5xl sm:leading-[1.3]">{post.title}</h1>
                    <div className="text-muted-foreground flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                        {post.date && <time dateTime={post.date}>{formatDate(post.date, locale)}</time>}
                        <span aria-hidden="true">·</span>
                        <span className="flex items-center gap-1">
                            <Clock className="size-4" aria-hidden="true" />
                            {digits(post.minutes)} {t.blog.minutes}
                        </span>
                    </div>
                </header>

                {post.cover && (
                    <div className="mx-auto max-w-5xl px-4 sm:px-6">
                        <img src={post.cover} alt="" width={1200} height={675} className="bg-muted aspect-[16/9] w-full rounded-3xl object-cover" />
                    </div>
                )}

                {/* Article HTML comes from the admin panel and is sanitized on the server. */}
                <div className="article-body mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-14" dangerouslySetInnerHTML={{ __html: post.body }} />
            </article>

            {related.length > 0 && (
                <section className="border-t" aria-labelledby="related-title">
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6">
                        <div className="mb-6 flex items-end justify-between gap-4">
                            <h2 id="related-title" className="text-2xl font-black sm:text-3xl">
                                {t.blog.related}
                            </h2>
                            <Link
                                href={url('/blog')}
                                className="text-brand2-text dark:text-primary-text flex min-h-11 items-center text-sm font-bold"
                            >
                                {t.blog.back}
                            </Link>
                        </div>
                        <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((p) => (
                                <li key={p.slug}>
                                    <PostCard post={p} headingLevel="h3" />
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>
            )}

            <ContactCta />
        </SiteLayout>
    );
}
