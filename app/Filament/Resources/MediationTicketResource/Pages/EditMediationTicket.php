<?php

namespace App\Filament\Resources\MediationTicketResource\Pages;

use App\Filament\Resources\MediationTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMediationTicket extends EditRecord
{
    protected static string $resource = MediationTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
