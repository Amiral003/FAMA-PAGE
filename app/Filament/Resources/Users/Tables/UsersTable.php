<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteBulkAction;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->separator(', '),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('must_change_password')
                    ->label('MDP à changer')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->since()
                    ->sortable()
                    ->placeholder('Jamais connecté'),

                Tables\Columns\TextColumn::make('last_login_ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ]),

                Tables\Filters\Filter::make('never_logged_in')
                    ->label('Jamais connecté')
                    ->query(fn ($query) => $query->whereNull('last_login_at')),
            ])
            ->actions([
                EditAction::make(),

                Action::make('activate')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'inactive')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'active',
                        ]);

                        if (function_exists('activity')) {
                            activity()
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('user_activated')
                                ->log("Activation du compte de {$record->name}");
                        }

                        Notification::make()
                            ->title('Utilisateur activé')
                            ->success()
                            ->send();
                    }),

                Action::make('deactivate')
                    ->label('Désactiver')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'active')
                    ->action(function ($record) {
                        if (auth()->id() === $record->id) {
                            Notification::make()
                                ->title('Action impossible')
                                ->body('Vous ne pouvez pas désactiver votre propre compte.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'status' => 'inactive',
                        ]);

                        if (function_exists('activity')) {
                            activity()
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('user_deactivated')
                                ->log("Désactivation du compte de {$record->name}");
                        }

                        Notification::make()
                            ->title('Utilisateur désactivé')
                            ->success()
                            ->send();
                    }),

                Action::make('force_password_change')
                    ->label('Forcer changement MDP')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'must_change_password' => true,
                        ]);

                        if (function_exists('activity')) {
                            activity()
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('user_force_password_change')
                                ->log("Changement de mot de passe forcé pour {$record->name}");
                        }

                        Notification::make()
                            ->title('Changement de mot de passe forcé')
                            ->success()
                            ->send();
                    }),
            ])
            
            ->bulkActions([
            DeleteBulkAction::make(),
]);
    }
}