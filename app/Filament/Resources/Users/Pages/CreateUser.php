<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $data['status'] = $data['status'] ?? 'active';
    $data['must_change_password'] = true;
    $data['password_changed_at'] = null;

    return $data;
}
}
