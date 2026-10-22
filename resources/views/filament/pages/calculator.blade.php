<x-filament-panels::page>
    <style>
        .hkc { --hkc-border: var(--gray-200); --hkc-soft: var(--gray-50); --hkc-key: var(--gray-100); }
        .dark .hkc { --hkc-border: rgb(255 255 255 / 0.1); --hkc-soft: rgb(255 255 255 / 0.03); --hkc-key: rgb(255 255 255 / 0.07); }
        .hkc-tabs { display: flex; gap: 0.25rem; overflow-x: auto; padding: 0.3rem; border: 1px solid var(--hkc-border); border-radius: 0.9rem; background: var(--hkc-soft); scrollbar-width: none; }
        .hkc-tabs::-webkit-scrollbar { display: none; }
        .hkc-tab { flex: 1 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.55rem 0.9rem; border-radius: 0.65rem; font-size: 0.875rem; font-weight: 600; color: var(--gray-500); white-space: nowrap; }
        .hkc-tab svg { width: 1.1rem; height: 1.1rem; }
        .hkc-tab[aria-selected="true"] { background: white; color: var(--gray-950); box-shadow: 0 1px 3px rgb(0 0 0 / 0.08); }
        .dark .hkc-tab[aria-selected="true"] { background: rgb(255 255 255 / 0.1); color: white; }
        .hkc-card { background: white; border: 1px solid var(--hkc-border); border-radius: 1rem; padding: 1rem; }
        .dark .hkc-card { background: var(--gray-900); }
        .hkc-card h3 { font-weight: 700; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap; }
        .hkc-grid2 { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); margin-top: 1rem; }
        @media (min-width: 1024px) { .hkc-grid2 { grid-template-columns: minmax(0, 28rem) minmax(0, 1fr); } }
        .hkc-muted { color: var(--gray-500); font-size: 0.8rem; line-height: 1.8; }
        .hkc-input, .hkc-select { width: 100%; border: 1px solid var(--gray-300); border-radius: 0.6rem; padding: 0.5rem 0.65rem; background: transparent; font-size: 0.9rem; min-width: 0; }
        .dark .hkc-input, .dark .hkc-select { border-color: rgb(255 255 255 / 0.15); }
        .dark .hkc-select option { background: var(--gray-900); }
        .hkc-input:focus, .hkc-select:focus { outline: 2px solid var(--primary-500); outline-offset: 0; border-color: transparent; }
        .hkc-label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--gray-600); margin-bottom: 0.25rem; }
        .dark .hkc-label { color: var(--gray-400); }
        .hkc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; border-radius: 0.6rem; padding: 0.45rem 0.8rem; font-size: 0.85rem; font-weight: 600; border: 1px solid var(--hkc-border); }
        .hkc-btn:hover { background: var(--hkc-soft); }
        .hkc-btn-primary { background: var(--primary-500); border-color: transparent; color: var(--gray-950); }
        .hkc-btn-primary:hover { background: var(--primary-400); }
        .hkc-btn svg { width: 1rem; height: 1rem; }

        /* Calculator */
        .hkc-screen { border-radius: 0.9rem; padding: 0.85rem 1rem; background: var(--gray-950); color: white; min-height: 6.5rem; display: flex; flex-direction: column; justify-content: flex-end; gap: 0.2rem; overflow: hidden; }
        .dark .hkc-screen { background: black; border: 1px solid rgb(255 255 255 / 0.1); }
        .hkc-expr { font-size: 1rem; opacity: 0.7; min-height: 1.6rem; overflow-x: auto; white-space: nowrap; text-align: left; direction: ltr; scrollbar-width: none; }
        .hkc-result { font-size: clamp(1.6rem, 6vw, 2.2rem); font-weight: 800; text-align: left; direction: ltr; overflow-x: auto; white-space: nowrap; scrollbar-width: none; }
        .hkc-result.is-preview { opacity: 0.55; }
        .hkc-result.is-error { font-size: 1rem; color: #fca5a5; }
        .hkc-mem { font-size: 0.7rem; font-weight: 700; color: var(--primary-400); min-height: 1rem; }
        .hkc-words { margin-top: 0.6rem; font-size: 0.85rem; line-height: 1.9; color: var(--gray-600); min-height: 1.6rem; }
        .dark .hkc-words { color: var(--gray-400); }
        .hkc-keys { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.45rem; margin-top: 0.75rem; direction: ltr; }
        .hkc-key { height: 3.25rem; border-radius: 0.8rem; font-size: 1.2rem; font-weight: 700; background: var(--hkc-key); transition: transform 0.06s, background 0.15s; user-select: none; }
        .hkc-key:hover { filter: brightness(0.96); }
        .dark .hkc-key:hover { filter: brightness(1.25); }
        .hkc-key:active, .hkc-key.is-pressed { transform: scale(0.94); }
        .hkc-key.op { color: var(--primary-600); background: color-mix(in oklab, var(--primary-500) 14%, transparent); }
        .dark .hkc-key.op { color: var(--primary-400); }
        .hkc-key.fn { font-size: 0.9rem; color: var(--gray-500); }
        .hkc-key.eq { grid-column: span 2; background: var(--primary-500); color: var(--gray-950); }
        .hkc-key.clear { color: var(--danger-600, #dc2626); }
        .hkc-hist { display: flex; flex-direction: column; gap: 0.25rem; max-height: 30rem; overflow-y: auto; }
        .hkc-hist button { text-align: left; direction: ltr; padding: 0.55rem 0.75rem; border-radius: 0.6rem; }
        .hkc-hist button:hover { background: var(--hkc-soft); }
        .hkc-hist .e { font-size: 0.8rem; color: var(--gray-500); overflow-wrap: anywhere; }
        .hkc-hist .r { font-weight: 700; overflow-wrap: anywhere; }

        /* Tables / rows */
        .hkc-rows { display: flex; flex-direction: column; gap: 0.6rem; }
        .hkc-row { display: grid; gap: 0.5rem; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 0.75rem; border: 1px dashed var(--hkc-border); border-radius: 0.8rem; position: relative; }
        @media (min-width: 768px) { .hkc-row { grid-template-columns: repeat(5, minmax(0, 1fr)) auto; align-items: end; } }
        .hkc-row-del { align-self: end; justify-self: start; color: var(--danger-600, #dc2626); padding: 0.5rem; border-radius: 0.5rem; }
        .hkc-row-del:hover { background: color-mix(in oklab, #dc2626 10%, transparent); }
        .hkc-row-del svg { width: 1.1rem; height: 1.1rem; }
        .hkc-row-note { grid-column: 1 / -1; font-size: 0.75rem; color: var(--gray-500); }
        .hkc-stats { display: grid; gap: 0.6rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 768px) { .hkc-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .hkc-stat { border: 1px solid var(--hkc-border); border-radius: 0.8rem; padding: 0.75rem; background: var(--hkc-soft); }
        .hkc-stat .k { font-size: 0.75rem; color: var(--gray-500); }
        .hkc-stat .v { font-size: 1.15rem; font-weight: 800; margin-top: 0.15rem; overflow-wrap: anywhere; }
        .hkc-stat.hl { background: color-mix(in oklab, var(--primary-500) 14%, transparent); border-color: color-mix(in oklab, var(--primary-500) 45%, transparent); }
        .hkc-fields { display: grid; gap: 0.75rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 768px) { .hkc-fields { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .hkc-ct { display: grid; gap: 0.75rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 768px) { .hkc-ct { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .hkc-bar { height: 0.55rem; border-radius: 999px; background: var(--hkc-key); overflow: hidden; margin-top: 0.3rem; }
        .hkc-bar > span { display: block; height: 100%; border-radius: inherit; background: var(--primary-500); transition: width 0.3s; }
        .hkc-bar.weight > span { background: var(--info-500, #0026b4); }
        .hkc-tag { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; background: var(--primary-500); color: var(--gray-950); }
        .hkc-conv { display: grid; gap: 0.6rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 768px) { .hkc-conv { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .hkc-conv-row { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); gap: 0.5rem; align-items: end; padding: 0.75rem; border: 1px solid var(--hkc-border); border-radius: 0.8rem; }
        .hkc-conv-row .eq { padding-bottom: 0.55rem; color: var(--gray-400); }
        .hkc-sum { display: flex; flex-direction: column; gap: 0.4rem; }
        .hkc-sum div { display: flex; justify-content: space-between; gap: 1rem; padding: 0.4rem 0; border-bottom: 1px dashed var(--hkc-border); }
        .hkc-sum div:last-child { border-bottom: 0; font-size: 1.15rem; font-weight: 800; }
        .hkc-sum span:last-child { font-weight: 700; text-align: left; overflow-wrap: anywhere; }
        [x-cloak] { display: none !important; }
    </style>

    <script>
        window.hkdCalculator = function () {
            /* ---------------- Exact decimal numbers (BigInt), so 0.1 + 0.2 = 0.3 ---------------- */
            const P = 30; // digits kept after the point in division and square roots
            const TEN = 10n;
            const pow10 = (n) => TEN ** BigInt(n);
            const D = (m, e) => normD({ m, e });
            function normD(a) {
                let { m, e } = a;
                while (e > 0 && m % TEN === 0n) { m /= TEN; e--; }
                return { m, e };
            }
            function divRound(num, den) {
                const neg = (num < 0n) !== (den < 0n);
                const an = num < 0n ? -num : num, ad = den < 0n ? -den : den;
                let q = an / ad;
                if ((an % ad) * 2n >= ad) q += 1n;
                return neg ? -q : q;
            }
            function parseD(s) {
                s = String(s ?? '').trim();
                if (!/^-?(\d+\.?\d*|\.\d+)$/.test(s)) return null;
                const neg = s.startsWith('-');
                if (neg) s = s.slice(1);
                const [i, f = ''] = s.split('.');
                const m = BigInt((i || '0') + f);
                return D(neg ? -m : m, f.length);
            }
            const add = (a, b) => { const e = Math.max(a.e, b.e); return D(a.m * pow10(e - a.e) + b.m * pow10(e - b.e), e); };
            const neg = (a) => ({ m: -a.m, e: a.e });
            const sub = (a, b) => add(a, neg(b));
            const mul = (a, b) => D(a.m * b.m, a.e + b.e);
            function div(a, b) {
                if (b.m === 0n) throw new Error('div0');
                return D(divRound(a.m * pow10(b.e + P), b.m * pow10(a.e)), P);
            }
            function isqrt(n) {
                if (n < 2n) return n;
                let x = BigInt(Math.floor(Math.sqrt(Number(n)))) + 1n;
                while (true) { const y = (x + n / x) >> 1n; if (y >= x) break; x = y; }
                while (x * x > n) x -= 1n;
                return x;
            }
            function sqrt(a) {
                if (a.m < 0n) throw new Error('sqrtneg');
                return D(isqrt(a.m * pow10(2 * P - a.e)), P);
            }
            function roundD(a, d) {
                if (a.e <= d) return a;
                return D(divRound(a.m, pow10(a.e - d)), d);
            }
            const HUNDRED = D(100n, 0);
            const ZERO = D(0n, 0);
            const isZero = (a) => a.m === 0n;
            const cmp = (a, b) => { const d = sub(a, b).m; return d === 0n ? 0 : d < 0n ? -1 : 1; };
            const maxD = (a, b) => (cmp(a, b) >= 0 ? a : b);
            const fromNum = (n) => parseD(Number.isFinite(n) ? n.toFixed(10) : '0') ?? ZERO;
            const toNum = (a) => Number(dToPlain(a));

            function dToPlain(a) {
                const neg = a.m < 0n;
                let s = (neg ? -a.m : a.m).toString().padStart(a.e + 1, '0');
                const i = s.slice(0, s.length - a.e), f = a.e ? s.slice(s.length - a.e) : '';
                return (neg ? '-' : '') + i + (f ? '.' + f : '');
            }

            /* ---------------- Formatting ---------------- */
            const FA = '۰۱۲۳۴۵۶۷۸۹';
            const fa = (s) => String(s).replace(/\d/g, (d) => FA[d]);
            function group(intPart) { return intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
            /** Decimal → "۱٬۲۳۴٫۵۶" (Persian digits, thousands separators). */
            function fmtD(a, maxDec = 12) {
                const r = normD(roundD(a, maxDec));
                const plain = dToPlain(r);
                const neg = plain.startsWith('-');
                const [i, f] = (neg ? plain.slice(1) : plain).split('.');
                const s = group(i) + (f ? '.' + f : '');
                return (neg ? '−' : '') + fa(s).replace(/,/g, '٬').replace('.', '٫');
            }
            const fmt = (n, maxDec = 2) => fmtD(fromNum(n), maxDec);
            /** Accepts Persian/Arabic digits, "٫" or "/" as the decimal point and thousands separators. */
            function clean(v) {
                return String(v ?? '')
                    .replace(/[۰-۹]/g, (d) => FA.indexOf(d))
                    .replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
                    .replace(/[٫\/]/g, '.')
                    .replace(/[,٬\s]/g, '')
                    .replace(/[−–]/g, '-');
            }
            const num = (v) => { const n = parseFloat(clean(v)); return Number.isFinite(n) ? n : 0; };
            const dec = (v) => parseD(clean(v)) ?? ZERO;

            /* ---------------- Persian number to words ---------------- */
            const ONES = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
            const TEENS = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
            const TENS = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
            const HUNDREDS = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
            const SCALES = ['', 'هزار', 'میلیون', 'میلیارد', 'هزار میلیارد', 'کوادریلیون'];
            function words3(n) {
                const parts = [];
                const h = Math.floor(n / 100), r = n % 100;
                if (h) parts.push(HUNDREDS[h]);
                if (r >= 10 && r < 20) parts.push(TEENS[r - 10]);
                else { if (r >= 20) parts.push(TENS[Math.floor(r / 10)]); if (r % 10) parts.push(ONES[r % 10]); }
                return parts.join(' و ');
            }
            function toWords(big) {
                if (big === 0n) return 'صفر';
                const negative = big < 0n; if (negative) big = -big;
                const groups = [];
                while (big > 0n) { groups.push(Number(big % 1000n)); big /= 1000n; }
                if (groups.length > SCALES.length) return '';
                const parts = [];
                for (let i = groups.length - 1; i >= 0; i--) {
                    if (!groups[i]) continue;
                    parts.push(i === 1 && groups[i] === 1 ? 'هزار' : (words3(groups[i]) + (SCALES[i] ? ' ' + SCALES[i] : '')));
                }
                return (negative ? 'منفی ' : '') + parts.join(' و ');
            }

            /* ---------------- Expression parser (+ − × ÷ % √ parentheses) ---------------- */
            function tokenize(src) {
                const t = [];
                for (let i = 0; i < src.length;) {
                    const c = src[i];
                    if (/[0-9.]/.test(c)) {
                        let j = i; while (j < src.length && /[0-9.]/.test(src[j])) j++;
                        const s = src.slice(i, j);
                        if ((s.match(/\./g) || []).length > 1 || s === '.') throw new Error('syntax');
                        t.push({ k: 'n', v: parseD(s.endsWith('.') ? s.slice(0, -1) : s) }); i = j; continue;
                    }
                    if ('+-×÷%()√'.includes(c)) { t.push({ k: c }); i++; continue; }
                    throw new Error('syntax');
                }
                return t;
            }
            function evaluate(src) {
                const t = tokenize(src); let p = 0;
                const peek = () => t[p]?.k;
                // "200 + 10%" = 220 (percent of the left side), "200 × 10%" = 20
                function expr() {
                    let left = term();
                    while (peek() === '+' || peek() === '-') {
                        const op = t[p++].k; const right = term();
                        const v = right.pct ? mul(left.v, right.v) : right.v;
                        left = { v: op === '+' ? add(left.v, v) : sub(left.v, v) };
                    }
                    return left;
                }
                function term() {
                    let left = factor();
                    while (peek() === '×' || peek() === '÷' || peek() === '(' || peek() === 'n' || peek() === '√') {
                        const k = peek();
                        const op = k === '×' || k === '÷' ? t[p++].k : '×'; // "2(3)" means 2 × 3
                        const right = factor();
                        left = { v: op === '×' ? mul(left.v, right.v) : div(left.v, right.v) };
                    }
                    return left;
                }
                function factor() {
                    if (peek() === '-') { p++; const f = factor(); return { v: neg(f.v), pct: f.pct }; }
                    if (peek() === '+') { p++; return factor(); }
                    if (peek() === '√') { p++; const f = factor(); return { v: sqrt(f.v) }; }
                    let v;
                    if (peek() === 'n') v = t[p++].v;
                    else if (peek() === '(') { p++; v = expr().v; if (peek() === ')') p++; }
                    else throw new Error('syntax');
                    if (peek() === '%') { p++; return { v: div(v, HUNDRED), pct: true }; }
                    return { v };
                }
                if (!t.length) throw new Error('empty');
                const r = expr();
                if (p !== t.length) throw new Error('syntax');
                return r.v;
            }

            const load = (k, d) => { try { const v = JSON.parse(localStorage.getItem(k)); return v ?? d; } catch (e) { return d; } };
            const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };

            const CONTAINERS = [
                { key: '20', name: 'کانتینر ۲۰ فوت (20GP)', cbm: 33.2, kg: 28200, inner: '۵٫۹۰ × ۲٫۳۵ × ۲٫۳۹ متر' },
                { key: '40', name: 'کانتینر ۴۰ فوت (40GP)', cbm: 67.7, kg: 26700, inner: '۱۲٫۰۳ × ۲٫۳۵ × ۲٫۳۹ متر' },
                { key: '40hc', name: 'کانتینر ۴۰ فوت های‌کیوب (40HC)', cbm: 76.4, kg: 26500, inner: '۱۲٫۰۳ × ۲٫۳۵ × ۲٫۶۹ متر' },
            ];
            const MODES = {
                air: { label: 'هوایی (IATA)', divisor: 6000, step: 0.5 },
                express: { label: 'اکسپرس / پیک بین‌المللی', divisor: 5000, step: 0.5 },
                road: { label: 'زمینی (۱ مترمکعب = ۳۳۳ کیلو)', divisor: 3000, step: 1 },
                sea: { label: 'دریایی خرده‌بار (۱ مترمکعب = ۱ تن)', divisor: 1000, step: 1 },
                custom: { label: 'ضریب دلخواه', divisor: null, step: 0 },
            };
            const UNITS = [
                { a: 'کیلوگرم', b: 'پوند', f: 2.20462262185 },
                { a: 'سانتی‌متر', b: 'اینچ', f: 1 / 2.54 },
                { a: 'متر', b: 'فوت', f: 1 / 0.3048 },
                { a: 'مترمکعب', b: 'فوت مکعب', f: 35.3146667215 },
                { a: 'کیلومتر', b: 'مایل', f: 1 / 1.609344 },
                { a: 'تن', b: 'پوند', f: 2204.62262185 },
                { a: 'لیتر', b: 'گالن آمریکا', f: 1 / 3.785411784 },
                { a: 'ریال', b: 'تومان', f: 0.1 },
            ];
            const CURRENCIES = { IRT: 'تومان', USD: 'دلار', EUR: 'یورو', AED: 'درهم', CNY: 'یوان' };

            return {
                tab: load('hkc:tab', 'calc'),
                // calculator
                expr: '',
                result: null,
                error: '',
                done: false,
                memory: null,
                history: load('hkc:history', []),
                // chargeable weight
                mode: 'air',
                customDivisor: '6000',
                rows: [{ l: '', w: '', h: '', qty: '1', kg: '' }],
                // containers
                ctCbm: '',
                ctKg: '',
                ctFill: '85',
                // pricing
                rates: load('hkc:rates', { USD: '', EUR: '', AED: '', CNY: '' }),
                costs: [{ title: 'کرایه حمل', amount: '', cur: 'USD' }, { title: 'هزینه‌های مبدأ و مقصد', amount: '', cur: 'IRT' }],
                margin: '15',
                vat: '10',
                // units
                units: UNITS.map((u) => ({ ...u, va: '', vb: '' })),
                MODES, CONTAINERS, CURRENCIES,

                init() {
                    this.$watch('tab', (v) => save('hkc:tab', v));
                    this.$watch('rates', (v) => save('hkc:rates', v), { deep: true });
                },

                fa, fmt, fmtD,

                /* ----- calculator ----- */
                get exprView() { return fa(this.expr.replace(/-/g, '−')).replace(/\./g, '٫') || '۰'; },
                get preview() {
                    if (this.done || !this.expr) return null;
                    try { return evaluate(this.expr); } catch (e) { return null; }
                },
                get screenValue() {
                    if (this.error) return this.error;
                    const v = this.done ? this.result : this.preview;
                    return v ? fmtD(v) : (this.done ? '۰' : '');
                },
                get words() {
                    const v = this.done ? this.result : this.preview;
                    if (!v) return '';
                    const r = roundD(v, 0);
                    if (cmp(r, v) !== 0) return ''; // only whole numbers
                    const w = toWords(r.m);
                    return w ? w : '';
                },
                press(k) {
                    this.error = '';
                    const ops = '+-×÷';
                    if (this.done) {
                        // after "=", a number starts fresh, an operator continues from the result
                        this.expr = ops.includes(k) || k === '%' ? dToPlain(this.result) : '';
                        this.done = false;
                    }
                    const last = this.expr.slice(-1);
                    if (k === '.') {
                        const tail = this.expr.match(/[0-9.]*$/)[0];
                        if (tail.includes('.')) return;
                        if (!tail) k = '0.';
                    }
                    if ('×÷+'.includes(k) && (this.expr === '' || last === '(')) return;
                    if (ops.includes(k) && ops.includes(last)) {
                        // allow "× −5", otherwise replace the previous operator
                        if (!(k === '-' && '×÷'.includes(last))) this.expr = this.expr.replace(/[+\-×÷]+$/, '');
                    }
                    if (k === '%' && !/[0-9)]$/.test(this.expr)) return;
                    if (k === ')') {
                        const open = (this.expr.match(/\(/g) || []).length - (this.expr.match(/\)/g) || []).length;
                        if (open <= 0 || !/[0-9)%]$/.test(this.expr)) return;
                    }
                    this.expr += k;
                },
                back() {
                    if (this.done) { this.done = false; this.expr = dToPlain(this.result); }
                    this.error = '';
                    this.expr = this.expr.slice(0, -1);
                },
                clear() { this.expr = ''; this.result = null; this.error = ''; this.done = false; },
                equals() {
                    if (!this.expr || this.done) return;
                    let src = this.expr.replace(/[+\-×÷√(]+$/, '');
                    const open = (src.match(/\(/g) || []).length - (src.match(/\)/g) || []).length;
                    src += ')'.repeat(Math.max(0, open));
                    try {
                        this.result = evaluate(src);
                        this.history.unshift({ e: fa(src.replace(/-/g, '−')).replace(/\./g, '٫'), r: dToPlain(normD(roundD(this.result, 20))) });
                        this.history = this.history.slice(0, 40);
                        save('hkc:history', this.history);
                        this.expr = src;
                        this.done = true;
                    } catch (e) {
                        this.error = e.message === 'div0' ? 'تقسیم بر صفر ممکن نیست' : e.message === 'sqrtneg' ? 'جذر عدد منفی تعریف نشده است' : 'عبارت کامل نیست';
                    }
                },
                current() { try { return this.done ? this.result : (this.expr ? evaluate(this.expr) : null); } catch (e) { return null; } },
                memAdd(sign) {
                    const v = this.current(); if (!v) return;
                    this.memory = add(this.memory ?? ZERO, sign > 0 ? v : neg(v));
                },
                memRecall() {
                    if (!this.memory) return;
                    const s = dToPlain(this.memory);
                    if (this.done || this.expr === '') { this.expr = ''; this.done = false; }
                    if (/[0-9.)%]$/.test(this.expr)) this.expr += '×';
                    this.expr += this.memory.m < 0n ? '(' + s + ')' : s;
                },
                memView() { return this.memory ? 'M = ' + fmtD(this.memory) : ''; },
                useHistory(item) { this.expr = item.r; this.done = false; this.error = ''; },
                histView(r) { return fmtD(parseD(r) ?? ZERO); },
                clearHistory() { this.history = []; save('hkc:history', []); },
                async copy(text) {
                    try { await navigator.clipboard.writeText(text); new FilamentNotification().title('کپی شد').success().send(); } catch (e) {}
                },
                copyResult() {
                    const v = this.current(); if (!v) return;
                    this.copy(dToPlain(normD(roundD(v, 12))));
                },
                flash(sel) {
                    const el = this.$root.querySelector(`[data-key="${sel}"]`);
                    if (!el) return;
                    el.classList.add('is-pressed'); setTimeout(() => el.classList.remove('is-pressed'), 120);
                },
                onKey(e) {
                    if (this.tab !== 'calc' || e.ctrlKey || e.metaKey || e.altKey) return;
                    if (e.target.closest('input, textarea, select, [contenteditable]')) return;
                    let k = clean(e.key);
                    const map = { '*': '×', 'x': '×', 'X': '×', '/': '÷', '٪': '%', 'Enter': '=', '=': '=' };
                    if (e.key === '/' ) k = '÷';
                    k = map[e.key] ?? map[k] ?? k;
                    if (/^[0-9]$/.test(k) || ['.', '+', '-', '×', '÷', '%', '(', ')'].includes(k)) { e.preventDefault(); this.press(k); this.flash(k); }
                    else if (k === '=') { e.preventDefault(); this.equals(); this.flash('='); }
                    else if (e.key === 'Backspace') { e.preventDefault(); this.back(); this.flash('back'); }
                    else if (e.key === 'Escape' || e.key === 'Delete') { e.preventDefault(); this.clear(); this.flash('C'); }
                },

                /* ----- chargeable weight ----- */
                get divisor() { return this.mode === 'custom' ? Math.max(1, num(this.customDivisor)) : MODES[this.mode].divisor; },
                rowCbm(r) { return (num(r.l) * num(r.w) * num(r.h) * Math.max(0, num(r.qty))) / 1e6; },
                rowVol(r) { return (num(r.l) * num(r.w) * num(r.h) * Math.max(0, num(r.qty))) / this.divisor; },
                rowActual(r) { return num(r.kg) * Math.max(0, num(r.qty)); },
                get totals() {
                    const t = this.rows.reduce((a, r) => ({
                        pcs: a.pcs + Math.max(0, num(r.qty)), kg: a.kg + this.rowActual(r), cbm: a.cbm + this.rowCbm(r), vol: a.vol + this.rowVol(r),
                    }), { pcs: 0, kg: 0, cbm: 0, vol: 0 });
                    const step = MODES[this.mode].step;
                    let charge = Math.max(t.kg, t.vol);
                    if (step) charge = Math.ceil(charge / step - 1e-9) * step;
                    return { ...t, charge, byVolume: t.vol > t.kg, wm: Math.max(t.cbm, t.kg / 1000) };
                },
                addRow() { this.rows.push({ l: '', w: '', h: '', qty: '1', kg: '' }); },
                removeRow(i) { this.rows.splice(i, 1); if (!this.rows.length) this.addRow(); },
                sendToContainers() {
                    this.ctCbm = this.totals.cbm.toFixed(3); this.ctKg = this.totals.kg.toFixed(1); this.tab = 'container';
                },

                /* ----- containers ----- */
                get ctResults() {
                    const cbm = num(this.ctCbm), kg = num(this.ctKg), f = Math.min(100, Math.max(10, num(this.ctFill) || 85)) / 100;
                    if (cbm <= 0 && kg <= 0) return [];
                    const list = CONTAINERS.map((c) => {
                        const n = Math.max(1, Math.ceil(Math.max(cbm / (c.cbm * f), kg / c.kg) - 1e-9));
                        return { ...c, n, volPct: Math.min(100, (cbm / (n * c.cbm)) * 100), kgPct: Math.min(100, (kg / (n * c.kg)) * 100) };
                    });
                    // best: fewest containers, then the smaller (cheaper) box
                    const best = [...list].sort((a, b) => a.n - b.n || a.cbm - b.cbm)[0];
                    return list.map((c) => ({ ...c, best: c.key === best.key }));
                },

                /* ----- pricing (exact decimals) ----- */
                rateOf(cur) { return cur === 'IRT' ? D(1n, 0) : dec(this.rates[cur]); },
                costToman(c) { return mul(dec(c.amount), this.rateOf(c.cur)); },
                get missingRates() { return [...new Set(this.costs.filter((c) => c.cur !== 'IRT' && num(c.amount) && !num(this.rates[c.cur])).map((c) => CURRENCIES[c.cur]))]; },
                get price() {
                    const cost = this.costs.reduce((s, c) => add(s, this.costToman(c)), ZERO);
                    const profit = div(mul(cost, dec(this.margin)), HUNDRED);
                    const net = add(cost, profit);
                    const vat = div(mul(net, dec(this.vat)), HUNDRED);
                    const total = add(net, vat);
                    const usd = num(this.rates.USD) ? div(total, dec(this.rates.USD)) : null;
                    return { cost, profit, net, vat, total, usd, words: toWords(roundD(total, 0).m) };
                },
                addCost() { this.costs.push({ title: '', amount: '', cur: 'IRT' }); },
                removeCost(i) { this.costs.splice(i, 1); if (!this.costs.length) this.addCost(); },
                money(d) { return fmtD(roundD(d, 0), 0); },
                plain(d) { return dToPlain(normD(roundD(d, 0))); },
                positive(d) { return d && d.m > 0n; },
                nonZero(d) { return d && d.m !== 0n; },

                /* ----- units ----- */
                convA(u) { u.vb = u.va === '' ? '' : fmt(num(u.va) * u.f, 6); },
                convB(u) { u.va = u.vb === '' ? '' : fmt(num(u.vb) / u.f, 6); },
            };
        };
    </script>

    <div class="hkc" x-data="hkdCalculator()" x-on:keydown.window="onKey($event)">
        {{-- Tabs --}}
        <div class="hkc-tabs" role="tablist">
            <button type="button" class="hkc-tab" role="tab" x-bind:aria-selected="tab === 'calc'" x-on:click="tab = 'calc'">
                <x-filament::icon icon="heroicon-o-calculator" /> ماشین‌حساب
            </button>
            <button type="button" class="hkc-tab" role="tab" x-bind:aria-selected="tab === 'weight'" x-on:click="tab = 'weight'">
                <x-filament::icon icon="heroicon-o-scale" /> وزن قابل‌پرداخت
            </button>
            <button type="button" class="hkc-tab" role="tab" x-bind:aria-selected="tab === 'container'" x-on:click="tab = 'container'">
                <x-filament::icon icon="heroicon-o-cube" /> ظرفیت کانتینر
            </button>
            <button type="button" class="hkc-tab" role="tab" x-bind:aria-selected="tab === 'price'" x-on:click="tab = 'price'">
                <x-filament::icon icon="heroicon-o-banknotes" /> قیمت‌گذاری
            </button>
            <button type="button" class="hkc-tab" role="tab" x-bind:aria-selected="tab === 'units'" x-on:click="tab = 'units'">
                <x-filament::icon icon="heroicon-o-arrows-right-left" /> تبدیل واحد
            </button>
        </div>

        {{-- 1) Precise calculator --}}
        <div class="hkc-grid2" x-show="tab === 'calc'" x-cloak>
            <div class="hkc-card">
                <div class="hkc-screen">
                    <div class="hkc-mem" x-text="memView()"></div>
                    <div class="hkc-expr" x-text="exprView"></div>
                    <div class="hkc-result" x-bind:class="{ 'is-preview': !done && !error, 'is-error': error }" x-text="screenValue"></div>
                </div>
                <div class="hkc-words" x-text="words ? 'به حروف: ' + words : ''"></div>

                <div class="hkc-keys">
                    <button type="button" class="hkc-key fn" x-on:click="memory = null">MC</button>
                    <button type="button" class="hkc-key fn" x-on:click="memRecall()">MR</button>
                    <button type="button" class="hkc-key fn" x-on:click="memAdd(1)">M+</button>
                    <button type="button" class="hkc-key fn" x-on:click="memAdd(-1)">M−</button>

                    <button type="button" class="hkc-key clear" data-key="C" x-on:click="clear()">C</button>
                    <button type="button" class="hkc-key op" data-key="(" x-on:click="press('(')">(</button>
                    <button type="button" class="hkc-key op" data-key=")" x-on:click="press(')')">)</button>
                    <button type="button" class="hkc-key fn" data-key="back" x-on:click="back()" aria-label="پاک کردن آخرین رقم">⌫</button>

                    <button type="button" class="hkc-key" data-key="7" x-on:click="press('7')">۷</button>
                    <button type="button" class="hkc-key" data-key="8" x-on:click="press('8')">۸</button>
                    <button type="button" class="hkc-key" data-key="9" x-on:click="press('9')">۹</button>
                    <button type="button" class="hkc-key op" data-key="÷" x-on:click="press('÷')">÷</button>

                    <button type="button" class="hkc-key" data-key="4" x-on:click="press('4')">۴</button>
                    <button type="button" class="hkc-key" data-key="5" x-on:click="press('5')">۵</button>
                    <button type="button" class="hkc-key" data-key="6" x-on:click="press('6')">۶</button>
                    <button type="button" class="hkc-key op" data-key="×" x-on:click="press('×')">×</button>

                    <button type="button" class="hkc-key" data-key="1" x-on:click="press('1')">۱</button>
                    <button type="button" class="hkc-key" data-key="2" x-on:click="press('2')">۲</button>
                    <button type="button" class="hkc-key" data-key="3" x-on:click="press('3')">۳</button>
                    <button type="button" class="hkc-key op" data-key="-" x-on:click="press('-')">−</button>

                    <button type="button" class="hkc-key op" data-key="%" x-on:click="press('%')">٪</button>
                    <button type="button" class="hkc-key" data-key="0" x-on:click="press('0')">۰</button>
                    <button type="button" class="hkc-key" data-key="." x-on:click="press('.')">٫</button>
                    <button type="button" class="hkc-key op" data-key="+" x-on:click="press('+')">+</button>

                    <button type="button" class="hkc-key op" x-on:click="press('√')">√</button>
                    <button type="button" class="hkc-key" x-on:click="press('000')">۰۰۰</button>
                    <button type="button" class="hkc-key eq" data-key="=" x-on:click="equals()">=</button>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap">
                    <button type="button" class="hkc-btn" x-on:click="copyResult()">
                        <x-filament::icon icon="heroicon-o-clipboard-document" /> کپی نتیجه
                    </button>
                </div>
                <p class="hkc-muted" style="margin-top: 0.75rem">
                    محاسبات با دقت کامل اعشاری انجام می‌شود (مثلاً ۰٫۱ + ۰٫۲ دقیقاً ۰٫۳ است).
                    «۲۰۰ + ۱۰٪» یعنی ۲۰۰ به‌اضافه‌ی ۱۰ درصدش (۲۲۰) و «۲۰۰ × ۱۰٪» یعنی ۱۰ درصدِ ۲۰۰.
                    از کیبورد هم می‌توانی استفاده کنی: اعداد، + − * / ، Enter برای مساوی، Backspace و Esc.
                </p>
            </div>

            <div class="hkc-card">
                <h3>
                    <span>تاریخچه</span>
                    <button type="button" class="hkc-btn" x-show="history.length" x-on:click="clearHistory()">پاک کردن</button>
                </h3>
                <div class="hkc-hist">
                    <template x-for="(item, i) in history" x-bind:key="i">
                        <button type="button" x-on:click="useHistory(item)" title="استفاده از این نتیجه">
                            <div class="e" x-text="item.e + ' ='"></div>
                            <div class="r" x-text="histView(item.r)"></div>
                        </button>
                    </template>
                    <p class="hkc-muted" x-show="!history.length">هنوز محاسبه‌ای انجام نشده. با زدن روی هر نتیجه، دوباره از آن استفاده می‌شود.</p>
                </div>
            </div>
        </div>

        {{-- 2) Chargeable weight --}}
        <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem" x-show="tab === 'weight'" x-cloak>
            <div class="hkc-card">
                <h3><span>نوع حمل</span></h3>
                <div class="hkc-fields">
                    <div style="grid-column: span 2">
                        <label class="hkc-label">روش محاسبه</label>
                        <select class="hkc-select" x-model="mode">
                            <template x-for="(m, key) in MODES" x-bind:key="key">
                                <option x-bind:value="key" x-text="m.label" x-bind:selected="key === mode"></option>
                            </template>
                        </select>
                    </div>
                    <div x-show="mode === 'custom'">
                        <label class="hkc-label">ضریب (سانتی‌متر مکعب بر کیلو)</label>
                        <input class="hkc-input" dir="ltr" inputmode="decimal" x-model="customDivisor">
                    </div>
                    <div class="hkc-muted" style="align-self: end">
                        وزن حجمی = طول × عرض × ارتفاع (سانتی‌متر) ÷ <strong x-text="fa(divisor)"></strong>
                    </div>
                </div>
            </div>

            <div class="hkc-card">
                <h3>
                    <span>بسته‌ها</span>
                    <button type="button" class="hkc-btn" x-on:click="addRow()">
                        <x-filament::icon icon="heroicon-o-plus" /> افزودن ردیف
                    </button>
                </h3>
                <div class="hkc-rows">
                    <template x-for="(r, i) in rows" x-bind:key="i">
                        <div class="hkc-row">
                            <div><label class="hkc-label">طول (cm)</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="r.l"></div>
                            <div><label class="hkc-label">عرض (cm)</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="r.w"></div>
                            <div><label class="hkc-label">ارتفاع (cm)</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="r.h"></div>
                            <div><label class="hkc-label">تعداد</label><input class="hkc-input" dir="ltr" inputmode="numeric" x-model="r.qty"></div>
                            <div><label class="hkc-label">وزن هر بسته (kg)</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="r.kg"></div>
                            <button type="button" class="hkc-row-del" x-on:click="removeRow(i)" aria-label="حذف ردیف">
                                <x-filament::icon icon="heroicon-o-trash" />
                            </button>
                            <div class="hkc-row-note" x-show="rowCbm(r) > 0"
                                 x-text="'حجم: ' + fmt(rowCbm(r), 3) + ' مترمکعب · وزن حجمی: ' + fmt(rowVol(r), 2) + ' کیلو · وزن واقعی: ' + fmt(rowActual(r), 2) + ' کیلو'"></div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="hkc-card">
                <h3>
                    <span>نتیجه</span>
                    <button type="button" class="hkc-btn" x-on:click="sendToContainers()" x-show="totals.cbm > 0">
                        <x-filament::icon icon="heroicon-o-cube" /> بررسی جا شدن در کانتینر
                    </button>
                </h3>
                <div class="hkc-stats">
                    <div class="hkc-stat"><div class="k">تعداد بسته</div><div class="v" x-text="fmt(totals.pcs, 0)"></div></div>
                    <div class="hkc-stat"><div class="k">حجم کل (مترمکعب)</div><div class="v" x-text="fmt(totals.cbm, 3)"></div></div>
                    <div class="hkc-stat"><div class="k">وزن واقعی (کیلو)</div><div class="v" x-text="fmt(totals.kg, 2)"></div></div>
                    <div class="hkc-stat"><div class="k">وزن حجمی (کیلو)</div><div class="v" x-text="fmt(totals.vol, 2)"></div></div>
                    <div class="hkc-stat hl" style="grid-column: 1 / -1">
                        <div class="k" x-text="mode === 'sea' ? 'مبنای کرایه دریایی (W/M، واحد بار)' : 'وزن قابل‌پرداخت (Chargeable Weight)'"></div>
                        <div class="v" x-show="mode !== 'sea'" x-text="fmt(totals.charge, 2) + ' کیلوگرم'"></div>
                        <div class="v" x-show="mode === 'sea'" x-text="fmt(totals.wm, 3) + ' واحد (هر واحد = ۱ مترمکعب یا ۱ تن، هر کدام بیشتر)'"></div>
                        <div class="hkc-muted" x-show="totals.kg > 0 || totals.vol > 0"
                             x-text="totals.byVolume ? 'وزن حجمی بیشتر است؛ کرایه بر اساس حجم حساب می‌شود.' : 'وزن واقعی بیشتر است؛ کرایه بر اساس وزن حساب می‌شود.'"></div>
                        <div class="hkc-muted" x-show="mode === 'air' || mode === 'express'">در حمل هوایی و اکسپرس، وزن قابل‌پرداخت به نیم کیلوی بعدی گرد می‌شود.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3) Container fit --}}
        <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem" x-show="tab === 'container'" x-cloak>
            <div class="hkc-card">
                <h3><span>مشخصات بار</span></h3>
                <div class="hkc-fields">
                    <div><label class="hkc-label">حجم کل (مترمکعب)</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="ctCbm"></div>
                    <div><label class="hkc-label">وزن کل (کیلوگرم)</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="ctKg"></div>
                    <div><label class="hkc-label">ضریب پرشدن عملی (٪)</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="ctFill"></div>
                    <div class="hkc-muted" style="align-self: end">به‌خاطر شکل بسته‌ها و فضای خالی بین آن‌ها، معمولاً ۸۰ تا ۹۰ درصد حجم کانتینر قابل استفاده است.</div>
                </div>
            </div>

            <div class="hkc-ct" x-show="ctResults.length">
                <template x-for="c in ctResults" x-bind:key="c.key">
                    <div class="hkc-card" x-bind:style="c.best ? 'border-color: var(--primary-500); box-shadow: 0 0 0 1px var(--primary-500)' : ''">
                        <h3><span x-text="c.name"></span><span class="hkc-tag" x-show="c.best">پیشنهاد</span></h3>
                        <div class="hkc-stat hl"><div class="k">تعداد لازم</div><div class="v" x-text="fa(c.n) + ' کانتینر'"></div></div>
                        <div style="margin-top: 0.75rem">
                            <div class="hkc-muted" x-text="'پرشدن حجم: ' + fmt(c.volPct, 1) + '٪'"></div>
                            <div class="hkc-bar"><span x-bind:style="'width:' + c.volPct + '%'"></span></div>
                        </div>
                        <div style="margin-top: 0.5rem">
                            <div class="hkc-muted" x-text="'استفاده از ظرفیت وزنی: ' + fmt(c.kgPct, 1) + '٪'"></div>
                            <div class="hkc-bar weight"><span x-bind:style="'width:' + c.kgPct + '%'"></span></div>
                        </div>
                        <p class="hkc-muted" style="margin-top: 0.6rem" x-text="'ابعاد داخلی تقریبی: ' + c.inner + ' · حجم ' + fmt(c.cbm, 1) + ' مترمکعب · حداکثر بار ' + fmt(c.kg, 0) + ' کیلو'"></p>
                    </div>
                </template>
            </div>
            <div class="hkc-card hkc-muted" x-show="ctResults.length && Number(String(ctCbm).replace(/[^0-9.]/g, '')) > 0 && Number(String(ctCbm).replace(/[^0-9.]/g, '')) < 15">
                حجم بار کمتر از ۱۵ مترمکعب است؛ معمولاً حمل خرده‌بار (LCL) از گرفتن یک کانتینر کامل به‌صرفه‌تر است.
            </div>
            <p class="hkc-muted" x-show="!ctResults.length">حجم یا وزن بار را وارد کن، یا از تب «وزن قابل‌پرداخت» با دکمه‌ی «بررسی جا شدن در کانتینر» بیا.</p>
            <p class="hkc-muted">ظرفیت‌ها تقریبی‌اند و بسته به خط کشتیرانی و نوع کانتینر کمی فرق می‌کنند؛ وزن مجاز جاده‌ای کشور مقصد هم ممکن است کمتر باشد.</p>
        </div>

        {{-- 4) Pricing --}}
        <div class="hkc-grid2" x-show="tab === 'price'" x-cloak>
            <div class="hkc-card">
                <h3><span>نرخ ارز (به تومان)</span></h3>
                <div class="hkc-fields" style="grid-template-columns: repeat(2, minmax(0, 1fr))">
                    <template x-for="cur in ['USD', 'EUR', 'AED', 'CNY']" x-bind:key="cur">
                        <div>
                            <label class="hkc-label" x-text="'هر ' + CURRENCIES[cur] + ' (' + cur + ')'"></label>
                            <input class="hkc-input" dir="ltr" inputmode="decimal" x-model="rates[cur]">
                        </div>
                    </template>
                </div>
                <p class="hkc-muted" style="margin-top: 0.5rem">نرخ‌ها فقط در همین مرورگر ذخیره می‌شوند و دفعه‌ی بعد سر جایشان هستند.</p>

                <h3 style="margin-top: 1.25rem"><span>سود و مالیات</span></h3>
                <div class="hkc-fields" style="grid-template-columns: repeat(2, minmax(0, 1fr))">
                    <div><label class="hkc-label">درصد سود</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="margin"></div>
                    <div><label class="hkc-label">مالیات بر ارزش افزوده (٪)</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="vat"></div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem">
                <div class="hkc-card">
                    <h3>
                        <span>هزینه‌ها</span>
                        <button type="button" class="hkc-btn" x-on:click="addCost()"><x-filament::icon icon="heroicon-o-plus" /> افزودن هزینه</button>
                    </h3>
                    <div class="hkc-rows">
                        <template x-for="(c, i) in costs" x-bind:key="i">
                            <div class="hkc-row" style="grid-template-columns: minmax(0, 2fr) minmax(0, 1.4fr) minmax(0, 1fr) auto; align-items: end">
                                <div><label class="hkc-label">عنوان</label><input class="hkc-input" x-model="c.title" placeholder="مثلاً ترخیص"></div>
                                <div><label class="hkc-label">مبلغ</label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="c.amount"></div>
                                <div>
                                    <label class="hkc-label">ارز</label>
                                    <select class="hkc-select" x-model="c.cur">
                                        <template x-for="(label, code) in CURRENCIES" x-bind:key="code">
                                            <option x-bind:value="code" x-text="label" x-bind:selected="code === c.cur"></option>
                                        </template>
                                    </select>
                                </div>
                                <button type="button" class="hkc-row-del" x-on:click="removeCost(i)" aria-label="حذف هزینه"><x-filament::icon icon="heroicon-o-trash" /></button>
                                <div class="hkc-row-note" x-show="c.cur !== 'IRT' && nonZero(costToman(c))" x-text="'معادل ' + money(costToman(c)) + ' تومان'"></div>
                            </div>
                        </template>
                    </div>
                    <p class="hkc-muted" style="margin-top: 0.5rem; color: var(--danger-600, #dc2626)" x-show="missingRates.length" x-text="'نرخ ' + missingRates.join('، ') + ' وارد نشده؛ آن هزینه‌ها صفر حساب می‌شوند.'"></p>
                </div>

                <div class="hkc-card">
                    <h3>
                        <span>قیمت نهایی</span>
                        <button type="button" class="hkc-btn" x-on:click="copy(plain(price.total))">
                            <x-filament::icon icon="heroicon-o-clipboard-document" /> کپی
                        </button>
                    </h3>
                    <div class="hkc-sum">
                        <div><span>جمع هزینه‌ها</span><span x-text="money(price.cost) + ' تومان'"></span></div>
                        <div><span x-text="'سود (' + fa(margin || 0) + '٪)'"></span><span x-text="money(price.profit) + ' تومان'"></span></div>
                        <div><span>قیمت قبل از مالیات</span><span x-text="money(price.net) + ' تومان'"></span></div>
                        <div><span x-text="'مالیات بر ارزش افزوده (' + fa(vat || 0) + '٪)'"></span><span x-text="money(price.vat) + ' تومان'"></span></div>
                        <div><span>قیمت نهایی</span><span x-text="money(price.total) + ' تومان'"></span></div>
                    </div>
                    <p class="hkc-muted" style="margin-top: 0.5rem" x-show="positive(price.total)" x-text="'به حروف: ' + price.words + ' تومان'"></p>
                    <p class="hkc-muted" x-show="price.usd" x-text="price.usd ? 'معادل تقریبی ' + fmtD(price.usd, 2) + ' دلار' : ''"></p>
                </div>
            </div>
        </div>

        {{-- 5) Unit conversion --}}
        <div style="margin-top: 1rem" x-show="tab === 'units'" x-cloak>
            <div class="hkc-conv">
                <template x-for="(u, i) in units" x-bind:key="i">
                    <div class="hkc-conv-row">
                        <div><label class="hkc-label" x-text="u.a"></label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="u.va" x-on:input="convA(u)"></div>
                        <span class="eq">⇄</span>
                        <div><label class="hkc-label" x-text="u.b"></label><input class="hkc-input" dir="ltr" inputmode="decimal" x-model="u.vb" x-on:input="convB(u)"></div>
                    </div>
                </template>
            </div>
            <p class="hkc-muted" style="margin-top: 0.75rem">در هر کدام از دو خانه که بنویسی، خانه‌ی دیگر خودکار حساب می‌شود.</p>
        </div>
    </div>
</x-filament-panels::page>
