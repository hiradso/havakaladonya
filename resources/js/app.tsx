import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { DirectionProvider } from '@radix-ui/react-direction';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { route as routeFn } from 'ziggy-js';
import { initializeTheme } from './hooks/use-appearance';

declare global {
    const route: typeof routeFn;
}

const siteName = () => (document.documentElement.lang === 'en' ? 'Hava Kala Donya' : 'هوا کالا دنیا');

createInertiaApp({
    title: (title) => (title ? `${title} | ${siteName()}` : siteName()),
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);
        const dir = props.initialPage.props.direction === 'ltr' ? 'ltr' : 'rtl';

        // DirectionProvider makes menus, dropdowns and sheets open on the correct side in RTL.
        root.render(
            <DirectionProvider dir={dir}>
                <App {...props} />
            </DirectionProvider>,
        );
    },
    progress: {
        color: '#FFB020',
    },
});

// This will set light / dark mode on load...
initializeTheme();
