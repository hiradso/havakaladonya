<x-filament-widgets::widget>
    <div wire:poll.60s class="hkd-fx">
        <style>
            .hkd-fx-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem .75rem;margin-bottom:.75rem}
            .hkd-fx-title{display:flex;align-items:center;gap:.5rem;font-weight:700;font-size:1rem;color:var(--gray-950)}
            .dark .hkd-fx-title{color:#fff}
            .hkd-fx-title svg{width:1.25rem;height:1.25rem;color:var(--primary-500)}
            .hkd-fx-meta{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;font-size:.75rem;color:var(--gray-500)}
            .hkd-fx-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.15rem .55rem;border-radius:999px;font-weight:600}
            .hkd-fx-badge.live{background:color-mix(in oklab,var(--success-500) 14%,transparent);color:var(--success-700)}
            .dark .hkd-fx-badge.live{color:var(--success-400)}
            .hkd-fx-badge.live i{width:.45rem;height:.45rem;border-radius:999px;background:var(--success-500);animation:hkd-fx-pulse 2s ease-in-out infinite}
            .hkd-fx-badge.old{background:color-mix(in oklab,var(--warning-500) 18%,transparent);color:var(--warning-700)}
            .dark .hkd-fx-badge.old{color:var(--warning-400)}
            @keyframes hkd-fx-pulse{0%,100%{opacity:1}50%{opacity:.35}}
            @media (prefers-reduced-motion:reduce){.hkd-fx-badge.live i{animation:none}}
            .hkd-fx-grid{display:grid;gap:.75rem;grid-template-columns:repeat(2,minmax(0,1fr))}
            @media (min-width:640px){.hkd-fx-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
            @media (min-width:1024px){.hkd-fx-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
            @media (min-width:1700px){.hkd-fx-grid{grid-template-columns:repeat(7,minmax(0,1fr))}}
            .hkd-fx-sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
            .hkd-fx-card{position:relative;overflow:hidden;border-radius:.85rem;padding:.85rem .9rem .7rem;background:#fff;box-shadow:0 1px 2px rgb(0 0 0 / .05);outline:1px solid rgb(3 7 18 / .06);transition:transform .2s ease,box-shadow .2s ease}
            .dark .hkd-fx-card{background:var(--gray-900);outline-color:rgb(255 255 255 / .08)}
            .hkd-fx-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px -10px rgb(0 0 0 / .25)}
            .hkd-fx-top{display:flex;align-items:center;gap:.55rem}
            .hkd-fx-sym{flex:none;display:grid;place-items:center;width:2rem;height:2rem;border-radius:.6rem;font-weight:700;font-size:.85rem;background:color-mix(in oklab,var(--primary-500) 16%,transparent);color:var(--primary-700)}
            .dark .hkd-fx-sym{color:var(--primary-300)}
            .hkd-fx-name{font-size:.8rem;font-weight:600;color:var(--gray-700);line-height:1.3}
            .dark .hkd-fx-name{color:var(--gray-200)}
            .hkd-fx-code{font-size:.65rem;letter-spacing:.06em;color:var(--gray-400);direction:ltr;text-align:right}
            .hkd-fx-price{margin-top:.55rem;font-size:1.3rem;font-weight:800;color:var(--gray-950);font-variant-numeric:tabular-nums;line-height:1.2}
            .dark .hkd-fx-price{color:#fff}
            .hkd-fx-price small{font-size:.65rem;font-weight:500;color:var(--gray-400);margin-inline-start:.25rem}
            .hkd-fx-change{margin-top:.2rem;display:flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;font-variant-numeric:tabular-nums}
            .hkd-fx-change.up{color:var(--success-600)}
            .hkd-fx-change.down{color:var(--danger-600)}
            .hkd-fx-change.flat{color:var(--gray-400)}
            .dark .hkd-fx-change.up{color:var(--success-400)}
            .dark .hkd-fx-change.down{color:var(--danger-400)}
            .hkd-fx-spark{display:block;width:100%;height:1.75rem;margin-top:.45rem}
            .hkd-fx-spark.up{color:var(--success-500)}
            .hkd-fx-spark.down{color:var(--danger-500)}
            .hkd-fx-empty{display:flex;flex-direction:column;align-items:center;gap:.4rem;padding:1.5rem 1rem;text-align:center;font-size:.85rem;line-height:1.9;color:var(--gray-500);border-radius:.85rem;outline:1px dashed rgb(3 7 18 / .12)}
            .dark .hkd-fx-empty{outline-color:rgb(255 255 255 / .12)}
            .hkd-fx-card.is-old{opacity:.6}
            .hkd-fx-old{position:absolute;top:.6rem;inset-inline-end:.6rem;font-size:.6rem;font-weight:700;padding:.05rem .4rem;border-radius:999px;background:color-mix(in oklab,var(--warning-500) 18%,transparent);color:var(--warning-700)}
            .dark .hkd-fx-old{color:var(--warning-400)}
            .hkd-fx-note{margin-top:.6rem;font-size:.7rem;color:var(--gray-400)}
        </style>

        <div class="hkd-fx-head">
            <div class="hkd-fx-title">
                <x-filament::icon icon="heroicon-o-currency-dollar" />
                نرخ ارز بازار آزاد <span style="font-weight:500;font-size:.8rem;color:var(--gray-500)">(به تومان)</span>
            </div>
            @if ($updatedText)
                <div class="hkd-fx-meta">
                    @if ($stale)
                        <span class="hkd-fx-badge old" title="بیش از {{ \App\Support\Digits::toPersian((string) \App\Support\Rates\ExchangeRates::STALE_AFTER_MINUTES) }} دقیقه است که نرخ تازه دریافت نشده؛ پیش از قیمت‌دادن، نرخ روز را چک کنید.">
                            <x-filament::icon icon="heroicon-m-exclamation-triangle" style="width:.9rem;height:.9rem" />
                            نرخ قدیمی
                        </span>
                    @else
                        <span class="hkd-fx-badge live"><i></i> به‌روز</span>
                    @endif
                    <span title="ساعت {{ $updatedClock }}">به‌روزرسانی: {{ $updatedText }}</span>
                    @if ($sourceText)
                        <span>· منبع: {{ $sourceText }}</span>
                    @endif
                </div>
            @endif
        </div>

        @if (count($rates) === 0)
            <div class="hkd-fx-empty">
                <x-filament::icon icon="heroicon-o-arrow-path" style="width:1.75rem;height:1.75rem;opacity:.5" />
                هنوز نرخی دریافت نشده است. نرخ‌ها هر ۵ دقیقه خودکار به‌روز می‌شوند.
            </div>
        @else
            <div class="hkd-fx-grid">
                @foreach ($rates as $rate)
                    @php($dir = $rate['direction'] > 0 ? 'up' : ($rate['direction'] < 0 ? 'down' : 'flat'))
                    <div class="hkd-fx-card {{ $rate['stale'] && ! $stale ? 'is-old' : '' }}" wire:key="fx-{{ $rate['code'] }}" title="{{ collect([$rate['rangeText'], $rate['quotedText']])->filter()->implode("\n") }}">
                        @if ($rate['stale'] && ! $stale)
                            <span class="hkd-fx-old">قدیمی</span>
                        @endif
                        <div class="hkd-fx-top">
                            <span class="hkd-fx-sym" aria-hidden="true">{{ $rate['symbol'] }}</span>
                            <div>
                                <div class="hkd-fx-name">{{ $rate['name'] }}</div>
                                <div class="hkd-fx-code">{{ strtoupper($rate['code']) }}</div>
                            </div>
                        </div>
                        <div class="hkd-fx-price">{{ $rate['priceText'] }}<small>تومان</small></div>
                        <div class="hkd-fx-change {{ $dir }}">
                            @if ($rate['changeText'] === null || $dir === 'flat')
                                بدون تغییر
                            @else
                                <span aria-hidden="true">{{ $dir === 'up' ? '▲' : '▼' }}</span>
                                <span class="hkd-fx-sr">{{ $dir === 'up' ? 'افزایش' : 'کاهش' }}</span>
                                {{ $rate['changeText'] }}
                                @if ($rate['percentText'])
                                    <span style="opacity:.8">({{ $rate['percentText'] }})</span>
                                @endif
                            @endif
                        </div>
                        @if ($rate['spark'])
                            <svg class="hkd-fx-spark {{ $rate['spark']['up'] ? 'up' : 'down' }}" viewBox="0 0 100 28" preserveAspectRatio="none" aria-label="روند ۷ روز اخیر" role="img">
                                <polygon points="{{ $rate['spark']['area'] }}" fill="currentColor" opacity=".12" />
                                <polyline points="{{ $rate['spark']['line'] }}" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                            </svg>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="hkd-fx-note">تغییرها نسبت به پایان روز قبل است؛ خط کوچک زیر هر کارت روند ۷ روز اخیر را نشان می‌دهد.</div>
        @endif
    </div>
</x-filament-widgets::widget>
