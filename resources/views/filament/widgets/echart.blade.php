<x-filament-widgets::widget>
    <x-filament::section :heading="$heading" :description="$description" :icon="$icon">
        @if ($empty)
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;min-height:12rem;color:var(--gray-500);text-align:center;font-size:.875rem;line-height:1.9">
                <x-filament::icon icon="heroicon-o-chart-bar" style="width:2rem;height:2rem;opacity:.5" />
                {{ $emptyText }}
            </div>
        @else
            {{-- Re-created whenever the data changes (filters), so the chart always matches. --}}
            <div
                wire:key="echart-{{ md5(json_encode($config)) }}"
                x-data="{
                    chart: null,
                    init() {
                        const draw = () => { this.chart = window.hkdChart(this.$refs.canvas, @js($config)); };
                        window.hkdChart ? draw() : window.addEventListener('hkd-charts-ready', draw, { once: true });
                    },
                    destroy() { this.chart?.destroy(); },
                }"
            >
                <div x-ref="canvas" style="width:100%;min-height:{{ $config['height'] ?? 320 }}px"></div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
