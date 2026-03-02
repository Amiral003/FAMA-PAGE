<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Schemas\Components\Section;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Message reçu')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Reçu le')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('-'),

                    TextEntry::make('staff.name')
                        ->label('État-Major')
                        ->placeholder('Général'),

                    TextEntry::make('status')
                        ->label('Statut')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'new' => 'danger',
                            'in_progress' => 'warning',
                            'done' => 'success',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'new' => 'Nouveau',
                            'in_progress' => 'En cours',
                            'done' => 'Traité',
                            default => $state,
                        }),

                    TextEntry::make('name')
                        ->label('Expéditeur'),

                    TextEntry::make('email')
                        ->label('Email')
                        ->url(fn ($record) => $record->email ? "mailto:{$record->email}" : null)
                        ->openUrlInNewTab(),

                    TextEntry::make('subject')
                        ->label('Sujet')
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'information' => 'Information',
                            'recrutement' => 'Recrutement',
                            'presse' => 'Presse',
                            default => $state,
                        }),

                    TextEntry::make('message')
                        ->label('Contenu')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Audit & sécurité')
                ->schema([
                    TextEntry::make('ip_address')
                        ->label('Adresse IP')
                        ->placeholder('-'),

                    TextEntry::make('user_agent')
                        ->label('User-Agent')
                        ->placeholder('-')
                        ->columnSpanFull(),

                    TextEntry::make('updated_at')
                        ->label('Dernière mise à jour')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('-'),
                ])
                ->collapsed(), // ✅ replié par défaut
        ]);
    }
}