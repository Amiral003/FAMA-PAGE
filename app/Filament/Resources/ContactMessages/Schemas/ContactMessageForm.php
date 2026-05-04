<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->label('Statut du message')
                ->options([
                    'new' => 'Nouveau',
                    'in_progress' => 'En cours',
                    'done' => 'Traité',
                ])
                ->required(),
        ]);
    }
}