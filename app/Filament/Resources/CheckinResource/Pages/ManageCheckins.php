<?php

namespace App\Filament\Resources\CheckinResource\Pages;

use App\Filament\Resources\CheckinResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCheckins extends ManageRecords
{
    protected static string $resource = CheckinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}