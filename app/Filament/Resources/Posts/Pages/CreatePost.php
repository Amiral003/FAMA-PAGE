<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Facades\Filament;


class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    // On force l'ID de l'utilisateur connecté juste avant l'écriture en base
    $data['user_id'] = Filament::auth()->id();
    
    return $data;
}

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