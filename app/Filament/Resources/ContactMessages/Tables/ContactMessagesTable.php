<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\Staff;
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
                TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('staff.name')
                    ->label('État-Major')
                    ->default('Général')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Expéditeur')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copié')
                    ->copyMessageDuration(1500),

                TextColumn::make('subject')
                    ->label('Sujet')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'information' => 'Information',
                        'recrutement' => 'Recrutement',
                        'presse' => 'Presse',
                        default => $state,
                    }),

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
                    }),

                TextColumn::make('message')
                    ->label('Message')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),

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

                SelectFilter::make('staff_id')
                    ->label('État-Major')
                    ->options(fn () => Staff::query()->orderBy('order')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}