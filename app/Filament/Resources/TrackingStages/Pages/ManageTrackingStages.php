<?php

namespace App\Filament\Resources\TrackingStages\Pages;

use App\Filament\Resources\TrackingStages\TrackingStageResource;
use App\Models\TrackingStage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTrackingStages extends ManageRecords
{
    protected static string $resource = TrackingStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('مرحله‌ی جدید')
                // New stages go to the end of the list.
                ->mutateDataUsing(fn (array $data) => $data + ['sort' => (int) TrackingStage::max('sort') + 1]),
        ];
    }
}
