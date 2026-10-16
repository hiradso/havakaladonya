<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Shipment extends Model
{
    protected $fillable = [
        'quote_request_id', 'customer_name', 'customer_phone', 'internal_notes',
        'transport_mode', 'origin', 'destination', 'cargo_description', 'estimated_delivery',
    ];

    protected function casts(): array
    {
        return [
            'estimated_delivery' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment): void {
            if (blank($shipment->tracking_code)) {
                $shipment->tracking_code = static::newTrackingCode();
            }
        });
    }

    /**
     * Random, hard-to-guess code like HKD-7KQ4M2XP.
     * No 0/O/1/I so customers can read it out over the phone without mistakes.
     */
    public static function newTrackingCode(): string
    {
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $code = 'HKD-'.collect(range(1, 8))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }

    /** Accepts what customers type: lower case, spaces, missing "HKD-" prefix. */
    public static function normalizeCode(string $input): string
    {
        $code = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $input) ?? '');

        if (str_starts_with($code, 'HKD')) {
            $code = substr($code, 3);
        }

        return 'HKD-'.$code;
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class)->orderByDesc('happened_at')->orderByDesc('id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(TrackingStage::class, 'current_stage_id');
    }

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    /** Adds the first active stage (e.g. "Order registered") so the customer sees something right away. */
    public function startTracking(): void
    {
        $first = TrackingStage::where('is_active', true)->ordered()->first();

        if ($first && ! $this->events()->exists()) {
            $this->events()->create(['tracking_stage_id' => $first->id, 'happened_at' => now()]);
        }
    }

    /** The current stage is always the stage of the most recent event. */
    public function refreshCurrentStage(): void
    {
        $latest = $this->events()->first();

        $this->forceFill(['current_stage_id' => $latest?->tracking_stage_id])->saveQuietly();
    }
}
