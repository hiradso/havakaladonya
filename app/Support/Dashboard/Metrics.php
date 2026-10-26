<?php

namespace App\Support\Dashboard;

use App\Models\ChatConversation;
use App\Models\QuoteRequest;
use App\Models\Shipment;
use App\Support\Digits;
use App\Support\JalaliCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Numbers behind the admin dashboard, for the period, grouping and transport mode chosen in its filters.
 * Days, weeks (starting Saturday) and months follow the Persian calendar in Tehran time.
 */
class Metrics
{
    public const PERIODS = [
        '30d' => '۳۰ روز اخیر',
        '90d' => '۳ ماه اخیر',
        '6m' => '۶ ماه اخیر',
        '12m' => '۱۲ ماه اخیر',
        'year' => 'سال جاری (شمسی)',
        'custom' => 'بازه‌ی دلخواه',
    ];

    public const GROUPINGS = [
        'auto' => 'خودکار',
        'day' => 'روزانه',
        'week' => 'هفتگی',
        'month' => 'ماهانه',
    ];

    private const TZ = 'Asia/Tehran';

    public readonly CarbonImmutable $from;

    public readonly CarbonImmutable $to;

    public readonly string $grouping;

    public readonly ?string $mode;

    /** @var array<string, array{label: string, start: CarbonImmutable}> */
    private array $buckets = [];

    public function __construct(?array $filters = [])
    {
        $filters ??= [];
        $today = CarbonImmutable::now(self::TZ)->startOfDay();
        [$this->from, $this->to] = $this->resolveRange((string) ($filters['period'] ?? '6m'), $filters, $today);

        $grouping = (string) ($filters['grouping'] ?? 'auto');
        $days = $this->from->diffInDays($this->to) + 1;
        $this->grouping = in_array($grouping, ['day', 'week', 'month'], true)
            ? $grouping
            : ($days <= 35 ? 'day' : ($days <= 120 ? 'week' : 'month'));

        $mode = $filters['mode'] ?? null;
        $this->mode = filled($mode) && $mode !== 'all' ? (string) $mode : null;

        $this->buildBuckets();
    }

    public static function fromFilters(?array $filters): self
    {
        return new self($filters);
    }

    /* ------------------------------------------------------------------ finance */

    /** Revenue, cost and profit per bucket (by invoice date). */
    public function financeSeries(): array
    {
        $revenue = $this->emptySeries();
        $cost = $this->emptySeries();
        foreach ($this->invoicedShipments() as $s) {
            $key = $this->bucketKey(CarbonImmutable::parse($s->invoiced_at, self::TZ));
            if (isset($revenue[$key])) {
                $revenue[$key] += (int) $s->invoice_amount;
                $cost[$key] += (int) $s->cost_amount;
            }
        }
        $profit = array_map(fn ($r, $c) => $r - $c, $revenue, $cost);

        return [
            'labels' => $this->labels(),
            'revenue' => array_values($revenue),
            'cost' => array_values($cost),
            'profit' => array_values($profit),
        ];
    }

    /** Totals for the period, with the change against the previous period of the same length. */
    public function financeTotals(): array
    {
        $now = $this->sumFinance($this->invoicedShipments());
        $length = $this->from->diffInDays($this->to) + 1;
        $previous = $this->sumFinance($this->invoicedShipments($this->from->subDays($length), $this->from->subDay()));

        $receivable = (int) Shipment::query()
            ->whereNotNull('invoice_amount')
            ->when($this->mode, fn ($q) => $q->where('transport_mode', $this->mode))
            ->get(['invoice_amount', 'paid_amount'])
            ->sum(fn (Shipment $s) => max(0, (int) $s->invoice_amount - (int) $s->paid_amount));

        return $now + [
            'margin' => $now['revenue'] > 0 ? round($now['profit'] / $now['revenue'] * 100, 1) : null,
            'average' => $now['count'] > 0 ? (int) round($now['revenue'] / $now['count']) : 0,
            'receivable' => $receivable,
            'revenueChange' => self::change($now['revenue'], $previous['revenue']),
            'profitChange' => self::change($now['profit'], $previous['profit']),
        ];
    }

    /** Profit and revenue per transport mode. */
    public function financeByMode(): array
    {
        $rows = [];
        foreach ($this->invoicedShipments() as $s) {
            $rows[$s->transport_mode]['revenue'] = ($rows[$s->transport_mode]['revenue'] ?? 0) + (int) $s->invoice_amount;
            $rows[$s->transport_mode]['profit'] = ($rows[$s->transport_mode]['profit'] ?? 0) + (int) $s->invoice_amount - (int) $s->cost_amount;
        }
        uasort($rows, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);

        return [
            'labels' => array_map(fn ($m) => QuoteRequest::TRANSPORT_MODES[$m] ?? $m, array_keys($rows)),
            'revenue' => array_values(array_column($rows, 'revenue')),
            'profit' => array_values(array_column($rows, 'profit')),
        ];
    }

    /* -------------------------------------------------------------- operations */

    /** Quote requests vs. new shipments per bucket. */
    public function demandSeries(): array
    {
        $quotes = $this->emptySeries();
        foreach ($this->quotes()->pluck('created_at') as $at) {
            $key = $this->bucketKey(CarbonImmutable::parse($at)->setTimezone(self::TZ));
            if (isset($quotes[$key])) {
                $quotes[$key]++;
            }
        }
        $shipments = $this->emptySeries();
        foreach ($this->shipments()->pluck('created_at') as $at) {
            $key = $this->bucketKey(CarbonImmutable::parse($at)->setTimezone(self::TZ));
            if (isset($shipments[$key])) {
                $shipments[$key]++;
            }
        }

        return ['labels' => $this->labels(), 'quotes' => array_values($quotes), 'shipments' => array_values($shipments)];
    }

    public function operationsTotals(): array
    {
        $quotes = $this->quotes()->get(['status']);
        $won = $quotes->where('status', 'won')->count();
        $shipments = $this->shipments()->with('currentStage:id,is_final')->get(['id', 'current_stage_id']);

        $active = Shipment::query()
            ->when($this->mode, fn ($q) => $q->where('transport_mode', $this->mode))
            ->where(fn ($q) => $q->whereNull('current_stage_id')->orWhereHas('currentStage', fn ($s) => $s->where('is_final', false)))
            ->count();

        return [
            'quotes' => $quotes->count(),
            'conversion' => $quotes->count() > 0 ? round($won / $quotes->count() * 100, 1) : null,
            'shipments' => $shipments->count(),
            'delivered' => $shipments->filter(fn ($s) => $s->currentStage?->is_final)->count(),
            'active' => $active,
            'chats' => ChatConversation::query()->whereBetween('created_at', $this->utcRange())->count(),
            'newQuotes' => QuoteRequest::query()->where('status', 'new')->count(),
        ];
    }

    /** Quote requests by status (how far they got). */
    public function quoteStatuses(): array
    {
        $counts = $this->quotes()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $labels = [];
        $values = [];
        foreach (QuoteRequest::STATUSES as $key => $label) {
            if (($counts[$key] ?? 0) > 0) {
                $labels[] = $label;
                $values[] = (int) $counts[$key];
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /** Busiest routes, by number of shipments or (for finance) by sales. */
    public function topRoutes(int $limit = 8, string $by = 'count'): array
    {
        $rows = $this->shipments()->get(['origin', 'destination', 'invoice_amount'])
            ->groupBy(fn (Shipment $s) => trim($s->origin).' ← '.trim($s->destination))
            ->map(fn (Collection $g) => ['count' => $g->count(), 'revenue' => (int) $g->sum('invoice_amount')])
            ->sortByDesc($by === 'revenue' ? 'revenue' : 'count')
            ->take($limit);

        return [
            'labels' => $rows->keys()->all(),
            'count' => $rows->pluck('count')->values()->all(),
            'revenue' => $rows->pluck('revenue')->values()->all(),
        ];
    }

    /* ---------------------------------------------------------------- helpers */

    public function periodLabel(): string
    {
        return Digits::toPersian(JalaliCalendar::dayLabel($this->from).' '.JalaliCalendar::of($this->from)[0]
            .' تا '.JalaliCalendar::dayLabel($this->to).' '.JalaliCalendar::of($this->to)[0]);
    }

    /** @return Collection<int, Shipment> */
    private function invoicedShipments(?CarbonImmutable $from = null, ?CarbonImmutable $to = null): Collection
    {
        return Shipment::query()
            ->whereNotNull('invoice_amount')
            ->whereBetween('invoiced_at', [($from ?? $this->from)->toDateString(), ($to ?? $this->to)->toDateString()])
            ->when($this->mode, fn ($q) => $q->where('transport_mode', $this->mode))
            ->get(['invoice_amount', 'cost_amount', 'invoiced_at', 'transport_mode']);
    }

    private function sumFinance(Collection $rows): array
    {
        $revenue = (int) $rows->sum('invoice_amount');
        $cost = (int) $rows->sum('cost_amount');

        return ['revenue' => $revenue, 'cost' => $cost, 'profit' => $revenue - $cost, 'count' => $rows->count()];
    }

    private function quotes()
    {
        return QuoteRequest::query()
            ->whereBetween('created_at', $this->utcRange())
            ->when($this->mode, fn ($q) => $q->where('transport_mode', $this->mode));
    }

    private function shipments()
    {
        return Shipment::query()
            ->whereBetween('created_at', $this->utcRange())
            ->when($this->mode, fn ($q) => $q->where('transport_mode', $this->mode));
    }

    /** The period as UTC timestamps (how created_at is stored). */
    private function utcRange(): array
    {
        return [$this->from->startOfDay()->utc(), $this->to->endOfDay()->utc()];
    }

    private static function change(int $now, int $before): ?float
    {
        return $before === 0 ? null : round(($now - $before) / abs($before) * 100, 1);
    }

    private function resolveRange(string $period, array $filters, CarbonImmutable $today): array
    {
        return match ($period) {
            '30d' => [$today->subDays(29), $today],
            '90d' => [$today->subDays(89), $today],
            '12m' => [$today->subMonthsNoOverflow(12)->addDay(), $today],
            'year' => (function () use ($today) {
                [$jy] = JalaliCalendar::of($today);
                [$gy, $gm, $gd] = JalaliCalendar::toGregorian($jy, 1, 1);

                return [CarbonImmutable::create($gy, $gm, $gd, 0, 0, 0, self::TZ), $today];
            })(),
            'custom' => (function () use ($filters, $today) {
                $from = filled($filters['from'] ?? null) ? CarbonImmutable::parse($filters['from'], self::TZ)->startOfDay() : $today->subDays(29);
                $to = filled($filters['to'] ?? null) ? CarbonImmutable::parse($filters['to'], self::TZ)->startOfDay() : $today;
                if ($to->lessThan($from)) {
                    [$from, $to] = [$to, $from];
                }

                // Keep daily charts readable: at most ~3 years.
                return [$from->max($to->subYears(3)), $to];
            })(),
            default => [$today->subMonthsNoOverflow(6)->addDay(), $today],
        };
    }

    private function buildBuckets(): void
    {
        for ($day = $this->from; $day->lessThanOrEqualTo($this->to); $day = $day->addDay()) {
            $key = $this->bucketKey($day);
            if (! isset($this->buckets[$key])) {
                $this->buckets[$key] = ['label' => $this->labelFor($day), 'start' => $day];
            }
        }
    }

    private function bucketKey(CarbonImmutable $date): string
    {
        return match ($this->grouping) {
            'day' => $date->toDateString(),
            // Persian weeks start on Saturday.
            'week' => $date->subDays(($date->dayOfWeek + 1) % 7)->toDateString(),
            default => implode('-', array_slice(JalaliCalendar::of($date), 0, 2)),
        };
    }

    private function labelFor(CarbonImmutable $date): string
    {
        return match ($this->grouping) {
            'day' => JalaliCalendar::dayLabel($date),
            'week' => 'هفته‌ی '.JalaliCalendar::dayLabel($date->subDays(($date->dayOfWeek + 1) % 7)->max($this->from)),
            default => JalaliCalendar::monthLabel(...array_slice(JalaliCalendar::of($date), 0, 2)),
        };
    }

    /** @return array<string, int> */
    private function emptySeries(): array
    {
        return array_fill_keys(array_keys($this->buckets), 0);
    }

    /** @return list<string> */
    private function labels(): array
    {
        return array_values(array_column($this->buckets, 'label'));
    }
}
