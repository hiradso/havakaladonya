<?php

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Resources\Pages\ViewRecord;

/** Read-only view (for the finance role). */
class ViewQuoteRequest extends ViewRecord
{
    protected static string $resource = QuoteRequestResource::class;
}
