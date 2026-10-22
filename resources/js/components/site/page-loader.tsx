import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

/** Show the loader only when a page change takes longer than this (fast pages never flash it). */
const SHOW_DELAY = 150;
/** Once visible, keep it at least this long so it never blinks. */
const MIN_VISIBLE = 450;

/**
 * Page-to-page loader: a frosted, see-through layer with a little globe and a plane
 * flying around it in the brand colors. Client-only (mounted next to the Inertia app),
 * so it never appears in the server-rendered HTML.
 */
export default function PageLoader() {
    const [visible, setVisible] = useState(false);
    const shownAt = useRef(0);
    const showTimer = useRef<number | undefined>(undefined);
    const hideTimer = useRef<number | undefined>(undefined);
    const active = useRef(0);

    useEffect(() => {
        const removeStart = router.on('start', (event) => {
            const visit = event.detail.visit;
            // Background requests (prefetch, partial reloads of a few props) are not page changes.
            if (visit.prefetch || (visit.only && visit.only.length > 0)) {
                return;
            }

            active.current += 1;
            window.clearTimeout(hideTimer.current);
            window.clearTimeout(showTimer.current);
            showTimer.current = window.setTimeout(() => {
                shownAt.current = Date.now();
                setVisible(true);
            }, SHOW_DELAY);
        });

        const removeFinish = router.on('finish', (event) => {
            const visit = event.detail.visit;
            if (visit.prefetch || (visit.only && visit.only.length > 0)) {
                return;
            }

            active.current = Math.max(0, active.current - 1);
            if (active.current > 0) {
                return;
            }

            window.clearTimeout(showTimer.current);
            const elapsed = Date.now() - shownAt.current;
            hideTimer.current = window.setTimeout(() => setVisible(false), shownAt.current ? Math.max(0, MIN_VISIBLE - elapsed) : 0);
            shownAt.current = 0;
        });

        return () => {
            removeStart();
            removeFinish();
            window.clearTimeout(showTimer.current);
            window.clearTimeout(hideTimer.current);
        };
    }, []);

    const fa = typeof document === 'undefined' || document.documentElement.lang !== 'en';
    const label = fa ? 'در حال بارگذاری' : 'Loading';

    return (
        <div className="hkd-loader" data-visible={visible} aria-hidden={!visible} role="status" aria-live="polite">
            <div className="hkd-loader__card">
                <svg className="hkd-loader__art" viewBox="0 0 160 160" aria-hidden="true">
                    <defs>
                        <radialGradient id="hkd-globe" cx="38%" cy="32%" r="75%">
                            <stop offset="0%" stopColor="var(--brand-2, #0026b4)" stopOpacity="0.55" />
                            <stop offset="100%" stopColor="var(--brand-2, #0026b4)" stopOpacity="1" />
                        </radialGradient>
                        <clipPath id="hkd-globe-clip">
                            <circle cx="80" cy="80" r="34" />
                        </clipPath>
                        <linearGradient id="hkd-trail" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stopColor="var(--primary, #ffc20e)" stopOpacity="0" />
                            <stop offset="100%" stopColor="var(--primary, #ffc20e)" stopOpacity="1" />
                        </linearGradient>
                    </defs>

                    {/* soft glow */}
                    <circle className="hkd-loader__glow" cx="80" cy="80" r="46" />

                    {/* outer orbit */}
                    <circle className="hkd-loader__orbit" cx="80" cy="80" r="62" />

                    {/* globe */}
                    <circle cx="80" cy="80" r="34" fill="url(#hkd-globe)" />
                    <g clipPath="url(#hkd-globe-clip)" className="hkd-loader__grid">
                        <ellipse cx="80" cy="80" rx="34" ry="12" />
                        <line x1="46" y1="80" x2="114" y2="80" />
                        <ellipse cx="80" cy="64" rx="30" ry="6" />
                        <ellipse cx="80" cy="96" rx="30" ry="6" />
                        <g className="hkd-loader__spin-meridians">
                            <ellipse className="hkd-loader__meridian m1" cx="80" cy="80" rx="12" ry="34" />
                            <ellipse className="hkd-loader__meridian m2" cx="80" cy="80" rx="24" ry="34" />
                            <ellipse className="hkd-loader__meridian m3" cx="80" cy="80" rx="34" ry="34" />
                        </g>
                    </g>

                    {/* route dots that light up */}
                    <circle className="hkd-loader__dot d1" cx="66" cy="70" r="2.4" />
                    <circle className="hkd-loader__dot d2" cx="92" cy="74" r="2.4" />
                    <circle className="hkd-loader__dot d3" cx="78" cy="94" r="2.4" />

                    {/* plane + trail on the orbit */}
                    <g className="hkd-loader__flight">
                        <path className="hkd-loader__trail" d="M 80 18 A 62 62 0 0 0 26.3 49" />
                        <g transform="translate(80 18)">
                            <path
                                className="hkd-loader__plane"
                                d="M10 0 L3 -2 L-1 -9 L-4 -9 L-2 -2 L-7 -1.5 L-9 -4 L-11 -4 L-10 0 L-11 4 L-9 4 L-7 1.5 L-2 2 L-4 9 L-1 9 L3 2 Z"
                            />
                        </g>
                    </g>

                    {/* second, slower carrier going the other way */}
                    <g className="hkd-loader__flight2">
                        <circle className="hkd-loader__carrier" cx="80" cy="133" r="4" />
                    </g>
                </svg>

                <div className="hkd-loader__label">
                    {label}
                    <span className="hkd-loader__dots" aria-hidden="true">
                        <i />
                        <i />
                        <i />
                    </span>
                </div>
            </div>
        </div>
    );
}
