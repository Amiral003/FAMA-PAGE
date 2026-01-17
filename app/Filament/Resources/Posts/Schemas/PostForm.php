<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('content'),
                Select::make('status')
                    ->options([
            'Brouillion' => 'Brouillion',
            'En attente' => 'En attente',
            'approuvée' => 'Approuvée',
            'Rejetée' => 'Rejetée',
        ])
                    ->default('Brouillion')
                    ->required(),
                TextInput::make('file_path')
                    ->required(),
                TextInput::make('file_type')
                    ->required(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('validated_at'),
                TextInput::make('validated_by')
                    ->numeric(),
            ]);
    }
}
