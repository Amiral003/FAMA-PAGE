<?php

namespace App\Filament\Resources\SecurityLockouts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SecurityLockoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('ip_address'),
                TextInput::make('reason')
                    ->required()
                    ->default('brute_force'),
                TextInput::make('severity')
                    ->required()
                    ->default('danger'),
                DateTimePicker::make('locked_until')
                    ->required(),
                TextInput::make('metadata'),
            ]);
    }
}
