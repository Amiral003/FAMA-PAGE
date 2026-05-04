<?php

namespace App\Filament\Resources\SecurityLockouts\Pages;

use App\Filament\Resources\SecurityLockouts\SecurityLockoutResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSecurityLockout extends CreateRecord
{
    protected static string $resource = SecurityLockoutResource::class;
}
