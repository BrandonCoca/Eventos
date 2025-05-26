<?php

namespace App\Filament\Resources\ParticipanteResource\Pages;

use App\Filament\Resources\ParticipanteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParticipantes extends ListRecords
{
    protected static string $resource = ParticipanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
