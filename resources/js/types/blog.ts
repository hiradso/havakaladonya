/** A blog article as list pages receive it (App\Models\Post::toCard). */
export interface PostCard {
    slug: string;
    url: string;
    title: string;
    excerpt: string;
    cover: string | null;
    date: string | null;
    minutes: number;
}
