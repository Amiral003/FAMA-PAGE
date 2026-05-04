<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (($this->record->status ?? null) === 'new') {
            $this->record->update([
                'status' => 'in_progress',
            ]);

            $this->record->refresh();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Modifier le statut'),
        ];
    }
}