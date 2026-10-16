<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentEvent extends Model
{
    protected $fillable = ['tracking_stage_id', 'location', 'note', 'happened_at', 'created_by'];

    protected function casts(): array
    {
        return [
            'happened_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ShipmentEvent $event): void {
            $event->created_by ??= auth()->id();
            $event->happened_at ??= now();
        });

        // Keep the shipment's "current stage" in sync whenever its history changes.
        static::saved(fn (ShipmentEvent $event) => $event->shipment?->refreshCurrentStage());
        static::deleted(fn (ShipmentEvent $event) => $event->shipment?->refreshCurrentStage());
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(TrackingStage::class, 'tracking_stage_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
