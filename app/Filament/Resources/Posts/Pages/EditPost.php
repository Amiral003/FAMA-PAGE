<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string 
{
    return "Modifier le communiqué : " . $this->record->title;
}

protected function getSaveFormActionLabel(): string
{
    return "Enregistrer les modifications";
}

protected function mutateFormDataBeforeSave(array $data): array
{
    // Dès qu'on enregistre une modification, le statut repasse en brouillon
    $data['status'] = 'brouillon';

    // Optionnel : On peut aussi vider la date de validation
    $data['validated_at'] = null;

    return $data;
}
}

