import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { DirectionProvider } from '@radix-ui/react-direction';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';

/**
 * Server-side rendering: search engines and link previews get the full HTML of public pages.
 * Run with `php artisan inertia:start-ssr` after `npm run build:ssr`.
 * If the SSR server is not running, Laravel falls back to normal browser rendering.
 */

// Account pages (login, settings, dashboard) are not for search engines; they render in the browser only.
const CLIENT_ONLY = /^(auth\/|settings\/|dashboard$|welcome$)/;

const escapeAttr = (value: string) => value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;');

createServer((page) => {
    if (CLIENT_ONLY.test(page.component)) {
        return Promise.resolve({ head: [], body: `<div id="app" data-page="${escapeAttr(JSON.stringify(page))}"></div>` });
    }

    const siteName = page.props.locale === 'en' ? 'Hava Kala Donya' : 'هوا کالا دنیا';

    return createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} | ${siteName}` : siteName),
        resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
        setup: ({ App, props }) => (
            <DirectionProvider dir={page.props.direction === 'ltr' ? 'ltr' : 'rtl'}>
                <App {...props} />
            </DirectionProvider>
        ),
    });
});
