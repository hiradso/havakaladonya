import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { DirectionProvider } from '@radix-ui/react-direction';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType } from 'react';
import ReactDOMServer from 'react-dom/server';

/**
 * Server-side rendering: search engines and link previews get the full HTML of public pages.
 * Run with `php artisan inertia:start-ssr` after `npm run build:ssr`.
 * If the SSR server is not running, Laravel falls back to normal browser rendering.
 */

createServer((page) => {
    const siteName = page.props.locale === 'en' ? 'Hava Kala Donya' : 'هوا کالا دنیا';

    return createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} | ${siteName}` : siteName),
        resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob<ComponentType>('./pages/**/*.tsx', { import: 'default' })),
        setup: ({ App, props }) => (
            <DirectionProvider dir={page.props.direction === 'ltr' ? 'ltr' : 'rtl'}>
                <App {...props} />
            </DirectionProvider>
        ),
    });
});
