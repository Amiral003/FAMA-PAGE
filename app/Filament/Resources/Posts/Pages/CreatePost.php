<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    // 1. Redirection vers la liste
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // 2. Forcer les labels des actions du formulaire
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('créer'),
            
            $this->getCreateAnotherFormAction()
                ->label('créer & créer un autre'),
            
            $this->getCancelFormAction()
                ->label('Annuler'),
        ];
    }

    // 3. Notification de succès
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Communiqué enregistré avec succès';
    }

    public function getTitle(): string 
{
    return "Rédiger un nouveau communiqué officiel";
}
}