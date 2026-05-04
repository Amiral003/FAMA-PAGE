<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('status')
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
                    })
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Expéditeur')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copié')
                    ->copyMessageDuration(1500),

                TextColumn::make('subject')
                    ->label('Sujet')
                    ->searchable()
                    ->weight('medium')
                    ->limit(40)
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'information' => 'Information',
                        'recrutement' => 'Recrutement',
                        'presse' => 'Presse',
                        default => $state,
                    }),

                TextColumn::make('message')
                    ->label('Aperçu')
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user_agent')
                    ->label('User-Agent')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'new' => 'Nouveau',
                        'in_progress' => 'En cours',
                        'done' => 'Traité',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()->label('Lire'),
                EditAction::make()->label('Statut'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}