<?php

namespace App\Filament\Resources\ExpositorResource\Pages;

use App\Filament\Resources\ExpositorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpositors extends ListRecords
{
    protected static string $resource = ExpositorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
