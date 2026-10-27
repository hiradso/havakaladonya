<?php

namespace App\Support\Rates;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Free-market currency rates in toman for the panel dashboard.
 *
 * Main source: tgju.org's live feed (the JSON its own pages use; free, updated every few seconds,
 * but unofficial, so it may change or block us). Fallback: navasan.tech with a free API key
 * (120 calls a month, so it is used at most every 8 hours and only while tgju fails).
 * The server fetches and stores; the panel only reads our own database, so viewers never need a VPN.
 */
class ExchangeRates
{
    /** code => [Persian name, symbol, tgju key, navasan keys (first one present wins)] */
    public const CURRENCIES = [
        'usd' => ['دلار آمریکا', '$', 'price_dollar_rl', ['usd_sell', 'usd']],
        'eur' => ['یورو', '€', 'price_eur', ['eur']],
        'aed' => ['درهم امارات', 'د.إ', 'price_aed', ['aed_sell', 'aed', 'aed_note']],
        'cny' => ['یوان چین', '¥', 'price_cny', ['cny']],
        'try' => ['لیر ترکیه', '₺', 'price_try', ['try']],
        'gbp' => ['پوند انگلیس', '£', 'price_gbp', ['gbp']],
        'usdt' => ['تتر', '₮', 'crypto-tether-irr', ['usdt']],
    ];

    public const SOURCES = ['tgju' => 'tgju.org', 'navasan' => 'نوسان'];

    /** No successful update for this long → the dashboard marks the rates as old. */
    public const STALE_AFTER_MINUTES = 30;

    public const TGJU_URL = 'https://call4.tgju.org/ajax.json';

    public const NAVASAN_URL = 'https://api.navasan.tech/latest/';

    /** Keeps the free Navasan plan (120 calls / month) within its limit. */
    public const NAVASAN_MIN_HOURS = 8;

    /**
     * Fetch from the sources and store. Returns the source used, or null when every source failed
     * (the old rates then stay in place and the dashboard shows them as old).
     */
    public function update(): ?string
    {
        $now = CarbonImmutable::now();

        try {
            $rates = $this->fromTgju($this->getJson(self::TGJU_URL));
            if (count($rates) >= 3) {
                $this->store($rates, 'tgju', $now);

                return 'tgju';
            }
        } catch (Throwable $e) {
            Log::warning('Exchange rates: tgju failed: '.$e->getMessage());
        }

        $key = config('services.navasan.key');
        if (! $key) {
            return null;
        }

        $lastNavasan = Cache::get('rates:navasan-called-at');
        if ($lastNavasan && $now->diffInHours(CarbonImmutable::parse($lastNavasan)) < self::NAVASAN_MIN_HOURS) {
            return null;
        }

        try {
            Cache::forever('rates:navasan-called-at', $now->toIso8601String());
            $rates = $this->fromNavasan($this->getJson(self::NAVASAN_URL, ['api_key' => $key]));
            if (count($rates) >= 1) {
                $this->store($rates, 'navasan', $now);

                return 'navasan';
            }
        } catch (Throwable $e) {
            Log::warning('Exchange rates: navasan failed: '.$e->getMessage());
        }

        return null;
    }

    /** @return array<string, mixed> */
    protected function getJson(string $url, array $query = []): array
    {
        $response = Http::timeout(20)
            ->connectTimeout(8)
            ->retry(2, 1500, throw: false)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.8',
            ])
            ->get($url, $query);

        $response->throw();
        $json = $response->json();

        if (! is_array($json)) {
            throw new \RuntimeException('Not JSON');
        }

        return $json;
    }

    /**
     * tgju: {"current": {"price_eur": {"p": "3,048,300", "d": "25,200", "dp": 0.83, "dt": "high|low",
     * "h": "...", "l": "...", "ts": "2026-10-26 15:35:17"}}} — prices in rial.
     *
     * @return array<string, array<string, mixed>>
     */
    public function fromTgju(array $json): array
    {
        $current = $json['current'] ?? [];
        $out = [];

        foreach (self::CURRENCIES as $code => [, , $key]) {
            $q = $current[$key] ?? null;
            $price = is_array($q) ? self::number($q['p'] ?? null) : null;
            if (! $price) {
                continue;
            }
            $sign = ($q['dt'] ?? '') === 'low' ? -1 : 1;
            $change = self::number($q['d'] ?? null);
            $percent = self::number($q['dp'] ?? null);

            $out[$code] = [
                'price' => $price / 10,
                'change' => $change === null ? null : $sign * $change / 10,
                'change_percent' => $percent === null ? null : $sign * abs($percent),
                'high' => ($h = self::number($q['h'] ?? null)) ? $h / 10 : null,
                'low' => ($l = self::number($q['l'] ?? null)) ? $l / 10 : null,
                'quoted_at' => self::tehranTime($q['ts'] ?? null),
            ];
        }

        return $out;
    }

    /**
     * Navasan: {"usd_sell": {"value": "271500", "change": 2000, "timestamp": 1793016000, "date": "1405-08-04 15:30:00"}}
     * — prices in toman.
     *
     * @return array<string, array<string, mixed>>
     */
    public function fromNavasan(array $json): array
    {
        $out = [];

        foreach (self::CURRENCIES as $code => [, , , $keys]) {
            foreach ($keys as $key) {
                $q = $json[$key] ?? null;
                $price = is_array($q) ? self::number($q['value'] ?? null) : null;
                if (! $price) {
                    continue;
                }
                $change = self::number($q['change'] ?? null);
                $previous = $change === null ? null : $price - $change;
                $out[$code] = [
                    'price' => $price,
                    'change' => $change,
                    'change_percent' => $previous ? round($change / $previous * 100, 2) : null,
                    'high' => null,
                    'low' => null,
                    'quoted_at' => isset($q['timestamp']) && is_numeric($q['timestamp'])
                        ? CarbonImmutable::createFromTimestamp((int) $q['timestamp'])
                        : null,
                ];
                break;
            }
        }

        return $out;
    }

    /** @param array<string, array<string, mixed>> $rates */
    protected function store(array $rates, string $source, CarbonImmutable $now): void
    {
        DB::transaction(function () use ($rates, $source, $now) {
            foreach ($rates as $code => $r) {
                DB::table('exchange_rates')->updateOrInsert(['code' => $code], [
                    'price' => $r['price'],
                    'change' => $r['change'],
                    'change_percent' => $r['change_percent'],
                    'high' => $r['high'],
                    'low' => $r['low'],
                    'source' => $source,
                    'quoted_at' => $r['quoted_at'],
                    'fetched_at' => $now,
                ]);
            }

            // One snapshot an hour is plenty for a 7-day trend line.
            $lastSnapshot = DB::table('exchange_rate_history')->max('recorded_at');
            if (! $lastSnapshot || $now->diffInMinutes(CarbonImmutable::parse($lastSnapshot)) >= 55) {
                DB::table('exchange_rate_history')->insert(collect($rates)->map(fn ($r, $code) => [
                    'code' => $code,
                    'price' => $r['price'],
                    'recorded_at' => $now,
                ])->values()->all());
                DB::table('exchange_rate_history')->where('recorded_at', '<', $now->subDays(30))->delete();
            }
        });
    }

    /**
     * Everything the dashboard card needs, read from our own database only.
     *
     * @return array{rates: list<array<string, mixed>>, fetchedAt: ?CarbonImmutable, stale: bool, source: ?string}
     */
    public static function forDashboard(int $trendDays = 7): array
    {
        $rows = DB::table('exchange_rates')->get()->keyBy('code');
        $since = CarbonImmutable::now()->subDays($trendDays);
        $history = DB::table('exchange_rate_history')
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at')
            ->get(['code', 'price'])
            ->groupBy('code');

        $staleBefore = CarbonImmutable::now()->subMinutes(self::STALE_AFTER_MINUTES);
        $rates = [];
        foreach (self::CURRENCIES as $code => [$name, $symbol]) {
            $r = $rows->get($code);
            if (! $r) {
                continue;
            }
            $rates[] = [
                'code' => $code,
                'name' => $name,
                'symbol' => $symbol,
                'price' => (float) $r->price,
                'change' => $r->change === null ? null : (float) $r->change,
                'percent' => $r->change_percent === null ? null : (float) $r->change_percent,
                'high' => $r->high === null ? null : (float) $r->high,
                'low' => $r->low === null ? null : (float) $r->low,
                'quotedAt' => $r->quoted_at ? CarbonImmutable::parse($r->quoted_at) : null,
                'stale' => CarbonImmutable::parse($r->fetched_at)->lt($staleBefore),
                'trend' => ($history->get($code) ?? collect())->pluck('price')->map(fn ($p) => (float) $p)->all(),
            ];
        }

        $fetchedAt = $rows->max('fetched_at');
        $fetchedAt = $fetchedAt ? CarbonImmutable::parse($fetchedAt) : null;
        $source = $rows->sortByDesc('fetched_at')->first()?->source;

        return [
            'rates' => $rates,
            'fetchedAt' => $fetchedAt,
            'stale' => ! $fetchedAt || $fetchedAt->lt($staleBefore),
            'source' => $source,
        ];
    }

    /** '2,715,000' / 0.83 / '<span>…</span>' → number, or null. */
    public static function number(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (! is_string($value)) {
            return null;
        }
        $clean = preg_replace('/<[^>]+>|[,%\s]/u', '', \App\Support\Digits::toLatin($value));

        return is_numeric($clean) ? (float) $clean : null;
    }

    protected static function tehranTime(?string $value): ?CarbonImmutable
    {
        if (! $value || ! preg_match('/^\d{4}-\d\d-\d\d \d\d:\d\d(:\d\d)?$/', $value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value, 'Asia/Tehran')->setTimezone(config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }
}
