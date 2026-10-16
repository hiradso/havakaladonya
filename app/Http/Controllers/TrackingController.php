<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\TrackingStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class TrackingController extends Controller
{
    public function show(Request $request): Response
    {
        $locale = app()->getLocale();
        $input = trim((string) $request->query('code', ''));

        $props = [
            'code' => $input,
            'shipment' => null,
            'notFound' => false,
            'tooMany' => false,
        ];

        if ($input === '') {
            return Inertia::render('tracking', $props);
        }

        // Codes are random, but still slow down anyone trying to guess them.
        $key = 'tracking:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return Inertia::render('tracking', ['tooMany' => true] + $props);
        }
        RateLimiter::hit($key, 600);

        $shipment = Shipment::with(['events.stage', 'currentStage'])
            ->where('tracking_code', Shipment::normalizeCode($input))
            ->first();

        if (! $shipment) {
            return Inertia::render('tracking', ['notFound' => true] + $props);
        }

        // Only what the customer needs — no names, phone numbers or internal notes.
        $stages = TrackingStage::where('is_active', true)->ordered()->get();

        return Inertia::render('tracking', [
            ...$props,
            'shipment' => [
                'code' => $shipment->tracking_code,
                'mode' => $shipment->transport_mode,
                'origin' => $shipment->origin,
                'destination' => $shipment->destination,
                'cargo' => $shipment->cargo_description,
                'eta' => $shipment->estimated_delivery?->toDateString(),
                'currentStageId' => $shipment->current_stage_id,
                'delivered' => (bool) $shipment->currentStage?->is_final,
                'stages' => $stages->map(fn (TrackingStage $s) => [
                    'id' => $s->id,
                    'name' => $s->name($locale),
                ])->values(),
                'events' => $shipment->events->map(fn (ShipmentEvent $e) => [
                    'id' => $e->id,
                    'stageId' => $e->tracking_stage_id,
                    'stage' => $e->stage?->name($locale),
                    'location' => $e->location,
                    'note' => $e->note,
                    'at' => $e->happened_at->toIso8601String(),
                ])->values(),
            ],
        ]);
    }
}
