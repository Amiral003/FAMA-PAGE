<?php

namespace App\Filament\Resources\SecurityLockouts\Pages;

use App\Filament\Resources\SecurityLockouts\SecurityLockoutResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSecurityLockout extends EditRecord
{
    protected static string $resource = SecurityLockoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
