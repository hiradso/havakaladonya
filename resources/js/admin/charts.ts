/**
 * Dashboard charts for the admin panel (Apache ECharts, Apache-2.0 license).
 * Loaded only on the dashboard page; each widget calls window.hkdChart(el, config).
 */
import { BarChart, LineChart, PieChart } from 'echarts/charts';
import { DataZoomComponent, GridComponent, LegendComponent, TitleComponent, ToolboxComponent, TooltipComponent } from 'echarts/components';
import * as echarts from 'echarts/core';
import { SVGRenderer } from 'echarts/renderers';

echarts.use([
    BarChart,
    LineChart,
    PieChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    ToolboxComponent,
    DataZoomComponent,
    TitleComponent,
    SVGRenderer,
]);

type Format = 'money' | 'count' | 'percent';

interface SeriesConfig {
    name: string;
    data: number[];
    type?: 'bar' | 'line';
    color?: string;
    area?: boolean;
    stack?: string;
}

interface ChartConfig {
    kind: 'cartesian' | 'hbar' | 'donut';
    categories?: string[];
    series: SeriesConfig[];
    format?: Format;
    height?: number;
    /** Let the viewer switch between bars and lines. */
    switchable?: boolean;
}

const FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const fa = (value: string | number) => String(value).replace(/\d/g, (d) => FA_DIGITS[Number(d)]);
const group = (n: number) => fa(Math.round(n).toLocaleString('en-US')).replace(/,/g, '٬');

/** 1250000000 → «۱٫۳ میلیارد» (axes); full numbers in tooltips. */
function compact(n: number): string {
    const abs = Math.abs(n);
    const sign = n < 0 ? '−' : '';
    const one = (v: number) => fa((Math.round(v * 10) / 10).toString()).replace('.', '٫');
    if (abs >= 1e9) return sign + one(abs / 1e9) + ' میلیارد';
    if (abs >= 1e6) return sign + one(abs / 1e6) + ' میلیون';
    if (abs >= 1e3) return sign + one(abs / 1e3) + ' هزار';
    return sign + fa(Math.round(abs));
}

function full(n: number, format: Format): string {
    if (format === 'money') return (n < 0 ? '−' : '') + group(Math.abs(n)) + ' تومان';
    if (format === 'percent') return fa(n.toFixed(1)).replace('.', '٫') + '٪';
    return group(n);
}

const PALETTE = ['#2f54eb', '#ffb020', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316', '#64748b'];

function isDark(): boolean {
    return document.documentElement.classList.contains('dark');
}

function buildOption(config: ChartConfig): echarts.EChartsCoreOption {
    const format = config.format ?? 'count';
    const dark = isDark();
    const text = dark ? '#cbd5e1' : '#475569';
    const line = dark ? 'rgba(255,255,255,.08)' : 'rgba(15,23,42,.08)';
    const font = 'Vazirmatn, Tahoma, sans-serif';
    const base = {
        backgroundColor: 'transparent',
        color: PALETTE,
        textStyle: { fontFamily: font, color: text },
        animationDuration: 700,
        animationEasing: 'cubicOut' as const,
    };
    const tooltipBox = {
        backgroundColor: dark ? 'rgba(15,23,42,.95)' : 'rgba(255,255,255,.97)',
        borderColor: line,
        textStyle: { fontFamily: font, color: dark ? '#e2e8f0' : '#0f172a', fontSize: 12 },
        extraCssText: 'direction: rtl; text-align: right; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12);',
    };

    if (config.kind === 'donut') {
        const s = config.series[0];
        const total = s.data.reduce((a, b) => a + b, 0);
        return {
            ...base,
            tooltip: {
                ...tooltipBox,
                trigger: 'item',
                formatter: (p: { name: string; value: number; percent: number; marker: string }) =>
                    `${p.marker} ${p.name}<br><b>${full(p.value, format)}</b> (${fa(p.percent.toFixed(0))}٪)`,
            },
            legend: { bottom: 0, icon: 'circle', textStyle: { fontFamily: font, color: text } },
            title: {
                text: format === 'money' ? compact(total) : group(total),
                subtext: format === 'money' ? 'تومان' : 'مجموع',
                left: 'center',
                top: '36%',
                textStyle: { fontFamily: font, fontSize: 18, fontWeight: 800, color: dark ? '#f8fafc' : '#0f172a' },
                subtextStyle: { fontFamily: font, color: text },
            },
            series: [
                {
                    name: s.name,
                    type: 'pie',
                    radius: ['52%', '74%'],
                    center: ['50%', '45%'],
                    avoidLabelOverlap: true,
                    itemStyle: { borderRadius: 8, borderColor: dark ? '#111827' : '#fff', borderWidth: 3 },
                    label: { show: false },
                    emphasis: { scale: true, scaleSize: 6 },
                    data: (config.categories ?? []).map((name, i) => ({ name, value: s.data[i] })),
                },
            ],
        };
    }

    const horizontal = config.kind === 'hbar';
    const many = (config.categories?.length ?? 0) > 18;
    const categoryAxis = {
        type: 'category',
        data: config.categories,
        axisLine: { lineStyle: { color: line } },
        axisTick: { show: false },
        axisLabel: { color: text, fontFamily: font, hideOverlap: true, ...(horizontal ? { width: 160, overflow: 'truncate' } : {}) },
        // Right-to-left: time runs from right to left; in horizontal bars the first (largest) item is on top.
        inverse: true,
        position: horizontal ? 'right' : 'bottom',
    };
    const valueAxis = {
        type: 'value',
        inverse: horizontal,
        position: horizontal ? 'bottom' : 'right',
        splitNumber: horizontal ? 3 : 5,
        splitLine: { lineStyle: { color: line, type: 'dashed' } },
        axisLabel: { color: text, fontFamily: font, hideOverlap: true, formatter: (v: number) => (format === 'money' ? compact(v) : fa(v)) },
    };

    return {
        ...base,
        grid: { left: 12, right: 12, top: 48, bottom: many ? 56 : 12, containLabel: true },
        legend: { top: 0, right: 0, icon: 'roundRect', itemWidth: 14, itemHeight: 8, textStyle: { fontFamily: font, color: text } },
        tooltip: {
            ...tooltipBox,
            trigger: 'axis',
            axisPointer: { type: horizontal ? 'shadow' : 'line', lineStyle: { color: line } },
            formatter: (items: { axisValueLabel: string; marker: string; seriesName: string; value: number }[]) =>
                `<b>${items[0]?.axisValueLabel ?? ''}</b><br>` +
                items.map((i) => `${i.marker} ${i.seriesName}: <b>${full(Number(i.value), format)}</b>`).join('<br>'),
        },
        toolbox: {
            top: 0,
            left: 0,
            itemSize: 14,
            iconStyle: { borderColor: text },
            feature: {
                ...(config.switchable ? { magicType: { type: ['bar', 'line'], title: { bar: 'ستونی', line: 'خطی' } } } : {}),
                saveAsImage: { title: 'ذخیره عکس', name: 'hkd-chart', backgroundColor: dark ? '#111827' : '#ffffff' },
            },
        },
        dataZoom: many ? [{ type: 'inside' }, { type: 'slider', height: 18, bottom: 8, borderColor: line, textStyle: { color: text } }] : [],
        xAxis: horizontal ? valueAxis : categoryAxis,
        yAxis: horizontal ? categoryAxis : valueAxis,
        series: config.series.map((s, i) => {
            const color = s.color ?? PALETTE[i % PALETTE.length];
            const isLine = s.type === 'line';
            return {
                name: s.name,
                type: s.type ?? 'bar',
                data: s.data,
                stack: s.stack,
                smooth: isLine,
                symbol: 'circle',
                symbolSize: 7,
                showSymbol: s.data.length <= 31,
                barMaxWidth: horizontal ? 22 : 28,
                itemStyle: { color, borderRadius: isLine ? 0 : horizontal ? [6, 0, 0, 6] : [6, 6, 0, 0] },
                lineStyle: isLine ? { width: 3, color } : undefined,
                areaStyle: s.area
                    ? {
                          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                              { offset: 0, color: color + '55' },
                              { offset: 1, color: color + '05' },
                          ]),
                      }
                    : undefined,
                emphasis: { focus: 'series' },
            };
        }),
    };
}

/** Draws a chart and keeps it in sync with the panel's dark mode and the element's size. */
function hkdChart(el: HTMLElement, config: ChartConfig) {
    el.style.height = `${config.height ?? 320}px`;
    el.style.direction = 'ltr';
    let chart = echarts.init(el, undefined, { renderer: 'svg' });
    chart.setOption(buildOption(config));

    const resize = new ResizeObserver(() => chart.resize());
    resize.observe(el);
    // Filament switches dark mode by toggling a class on <html>.
    let dark = isDark();
    const theme = new MutationObserver(() => {
        if (dark === isDark()) return;
        dark = isDark();
        chart.dispose();
        chart = echarts.init(el, undefined, { renderer: 'svg' });
        chart.setOption(buildOption(config));
    });
    theme.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    return {
        destroy() {
            resize.disconnect();
            theme.disconnect();
            chart.dispose();
        },
    };
}

declare global {
    interface Window {
        hkdChart: typeof hkdChart;
    }
}

window.hkdChart = hkdChart;
window.dispatchEvent(new Event('hkd-charts-ready'));
