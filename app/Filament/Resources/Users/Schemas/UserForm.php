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
                ->required(),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required(),

            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                ->required(fn (string $context) => $context === 'create')
                ->visible(fn (string $context) => $context === 'create'),

            Forms\Components\Select::make('roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->label('Rôles'),
        ]);
    }
}

