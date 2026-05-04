<?php

namespace App\Filament\Resources\SecurityLockouts\Pages;

use App\Filament\Resources\SecurityLockouts\SecurityLockoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSecurityLockouts extends ListRecords
{
    protected static string $resource = SecurityLockoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
