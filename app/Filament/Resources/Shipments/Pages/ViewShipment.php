<?php

namespace App\Filament\Resources\Shipments\Pages;

use App\Filament\Resources\Shipments\ShipmentResource;
use Filament\Resources\Pages\ViewRecord;

/** Read-only view with the tracking history (for the finance role). */
class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;
}
