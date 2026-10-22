{{--
    Loader for the first load / refresh of a page. It is plain HTML (styles in resources/css/app.css,
    same look as resources/js/components/site/page-loader.tsx, which handles page-to-page moves),
    so it shows before any JavaScript runs and hides when the page has finished loading.
    The page content is still fully in the HTML underneath, so search engines are not affected.
--}}
<div id="hkd-boot-loader" class="hkd-loader" data-visible="true" role="status" aria-live="polite">
    <div class="hkd-loader__card">
        <svg class="hkd-loader__art" viewBox="0 0 160 160" aria-hidden="true">
            <defs>
                <radialGradient id="hkd-globe" cx="38%" cy="32%" r="75%">
                    <stop offset="0%" stop-color="var(--brand-2, #0026b4)" stop-opacity="0.55" />
                    <stop offset="100%" stop-color="var(--brand-2, #0026b4)" stop-opacity="1" />
                </radialGradient>
                <clipPath id="hkd-globe-clip">
                    <circle cx="80" cy="80" r="34" />
                </clipPath>
                <linearGradient id="hkd-trail" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="var(--primary, #ffc20e)" stop-opacity="0" />
                    <stop offset="100%" stop-color="var(--primary, #ffc20e)" stop-opacity="1" />
                </linearGradient>
            </defs>
            <circle class="hkd-loader__glow" cx="80" cy="80" r="46" />
            <circle class="hkd-loader__orbit" cx="80" cy="80" r="62" />
            <circle cx="80" cy="80" r="34" fill="url(#hkd-globe)" />
            <g clip-path="url(#hkd-globe-clip)" class="hkd-loader__grid">
                <ellipse cx="80" cy="80" rx="34" ry="12" />
                <line x1="46" y1="80" x2="114" y2="80" />
                <ellipse cx="80" cy="64" rx="30" ry="6" />
                <ellipse cx="80" cy="96" rx="30" ry="6" />
                <ellipse class="hkd-loader__meridian m1" cx="80" cy="80" rx="12" ry="34" />
                <ellipse class="hkd-loader__meridian m2" cx="80" cy="80" rx="24" ry="34" />
                <ellipse class="hkd-loader__meridian m3" cx="80" cy="80" rx="34" ry="34" />
            </g>
            <circle class="hkd-loader__dot d1" cx="66" cy="70" r="2.4" />
            <circle class="hkd-loader__dot d2" cx="92" cy="74" r="2.4" />
            <circle class="hkd-loader__dot d3" cx="78" cy="94" r="2.4" />
            <g class="hkd-loader__flight">
                <path class="hkd-loader__trail" d="M 80 18 A 62 62 0 0 0 26.3 49" />
                <g transform="translate(80 18)">
                    <path class="hkd-loader__plane" d="M10 0 L3 -2 L-1 -9 L-4 -9 L-2 -2 L-7 -1.5 L-9 -4 L-11 -4 L-10 0 L-11 4 L-9 4 L-7 1.5 L-2 2 L-4 9 L-1 9 L3 2 Z" />
                </g>
            </g>
            <g class="hkd-loader__flight2">
                <circle class="hkd-loader__carrier" cx="80" cy="133" r="4" />
            </g>
        </svg>
        <div class="hkd-loader__label">
            {{ app()->getLocale() === 'en' ? 'Loading' : 'در حال بارگذاری' }}
            <span class="hkd-loader__dots" aria-hidden="true"><i></i><i></i><i></i></span>
        </div>
    </div>
</div>
<noscript><style>#hkd-boot-loader { display: none !important; }</style></noscript>
<script>
    (function () {
        var el = document.getElementById('hkd-boot-loader');
        var start = Date.now();
        var MIN_VISIBLE = 700; // so a fast refresh still shows the animation briefly instead of blinking
        var hidden = false;
        function hide() {
            if (hidden || !el) return;
            hidden = true;
            setTimeout(function () {
                el.setAttribute('data-visible', 'false');
                setTimeout(function () { el.remove(); }, 400);
            }, Math.max(0, MIN_VISIBLE - (Date.now() - start)));
        }
        if (document.readyState === 'complete') hide();
        else window.addEventListener('load', hide);
        setTimeout(hide, 6000); // never block the page if something (e.g. a slow image) hangs
    })();
</script>
