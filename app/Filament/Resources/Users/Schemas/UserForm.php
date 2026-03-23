<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class UserForm
{
    public static function schema(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nom')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\TextInput::make('password')
                ->label('Mot de passe')
                ->password()
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => $state)
                ->required(fn (string $context) => $context === 'create')
                ->visible(fn (string $context) => $context === 'create'),

            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options([
                    'active' => 'Actif',
                    'inactive' => 'Inactif',
                ])
                ->default('active')
                ->required(),

            Forms\Components\Toggle::make('must_change_password')
                ->label('Forcer le changement du mot de passe')
                ->default(true)
                ->inline(false),

            Forms\Components\Select::make('roles')
                ->label('Rôles')
                ->multiple()
                ->relationship('roles', 'name')
                 ->disabled(fn ($record) => $record && $record->id === auth()->id()),
            
            
        ]);
    }
}