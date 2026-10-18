import { useI18n } from '@/i18n';
import { formatDate } from '@/lib/format';
import type { PostCard as PostCardData } from '@/types/blog';
import { Link } from '@inertiajs/react';
import { Newspaper } from 'lucide-react';

export default function PostCard({ post, headingLevel = 'h2' }: { post: PostCardData; headingLevel?: 'h2' | 'h3' }) {
    const { t, locale, digits } = useI18n();
    const Heading = headingLevel;

    return (
        <article className="group bg-card hover:border-primary relative flex h-full flex-col overflow-hidden rounded-2xl border transition-colors">
            {post.cover ? (
                <img src={post.cover} alt="" loading="lazy" width={800} height={450} className="bg-muted aspect-[16/9] w-full object-cover" />
            ) : (
                <div className="bg-secondary text-muted-foreground flex aspect-[16/9] w-full items-center justify-center" aria-hidden="true">
                    <Newspaper className="size-10" strokeWidth={1.25} />
                </div>
            )}
            <div className="flex flex-1 flex-col gap-3 p-5">
                <Heading className="text-lg leading-8 font-black">
                    {/* The whole card is clickable through this link's ::after */}
                    <Link href={post.url} className="after:absolute after:inset-0">
                        {post.title}
                    </Link>
                </Heading>
                {post.excerpt && <p className="text-muted-foreground line-clamp-3 text-sm leading-7">{post.excerpt}</p>}
                <div className="text-muted-foreground mt-auto flex flex-wrap items-center gap-x-3 gap-y-1 pt-2 text-xs">
                    {post.date && <time dateTime={post.date}>{formatDate(post.date, locale)}</time>}
                    <span aria-hidden="true">·</span>
                    <span>
                        {digits(post.minutes)} {t.blog.minutes}
                    </span>
                </div>
            </div>
        </article>
    );
}
