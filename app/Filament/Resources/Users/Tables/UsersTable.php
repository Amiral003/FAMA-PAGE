<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->getStateUsing(fn($record): bool => filled($record->two_factor_confirmed_at))
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('must_change_password')
                    ->label('MDP temporaire')
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

                Tables\Filters\Filter::make('without_2fa')
                    ->label('2FA non configuré')
                    ->query(fn($query) => $query->whereNull('two_factor_confirmed_at')),

                Tables\Filters\Filter::make('never_logged_in')
                    ->label('Jamais connecté')
                    ->query(fn($query) => $query->whereNull('last_login_at')),
            ])
            ->actions([
                EditAction::make(),

                Action::make('reset_password')
                    ->label('Réinitialiser MDP')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Réinitialiser le mot de passe')
                    ->modalDescription('Définissez un mot de passe temporaire. L’utilisateur devra le changer à sa prochaine connexion.')
                    ->form([
                        Forms\Components\TextInput::make('temporary_password')
                            ->label('Nouveau mot de passe temporaire')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->confirmed(),

                        Forms\Components\TextInput::make('temporary_password_confirmation')
                            ->label('Confirmer le mot de passe temporaire')
                            ->password()
                            ->revealable()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        if (auth()->id() === $record->id) {
                            Notification::make()
                                ->title('Action impossible')
                                ->body('Vous ne pouvez pas réinitialiser votre propre mot de passe ici.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'password' => Hash::make($data['temporary_password']),
                            'must_change_password' => true,
                            'password_changed_at' => null,
                        ]);
                        if (function_exists('activity')) {
                            \activity('security')
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('admin_reset_password')
                                ->withProperties([
                                    'target_user_id' => $record->id,
                                    'target_email' => $record->email,
                                    'ip' => request()->ip(),
                                ])
                                ->log("Réinitialisation du mot de passe de {$record->name}");
                        }

                        DB::table('security_events')->insert([
                            'user_id' => $record->id,
                            'event_type' => 'admin_reset_password',
                            'email' => $record->email,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'severity' => 'warning',
                            'metadata' => json_encode([
                                'admin_id' => auth()->id(),
                                'admin_email' => auth()->user()?->email,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Mot de passe réinitialisé')
                            ->body('L’utilisateur devra changer son mot de passe à la prochaine connexion.')
                            ->success()
                            ->send();
                    }),

                Action::make('reset_2fa')
                    ->label('Réinitialiser 2FA')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Réinitialiser le 2FA')
                    ->modalDescription('Le QR code actuel sera supprimé. L’utilisateur devra reconfigurer son authentification à deux facteurs à la prochaine connexion.')
                    ->visible(fn($record): bool => filled($record->two_factor_secret) || filled($record->two_factor_confirmed_at))
                    ->action(function ($record) {
                        if (auth()->id() === $record->id) {
                            Notification::make()
                                ->title('Action impossible')
                                ->body('Vous ne pouvez pas réinitialiser votre propre 2FA ici.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->forceFill([
                            'two_factor_secret' => null,
                            'two_factor_recovery_codes' => null,
                            'two_factor_confirmed_at' => null,
                        ])->save();

                        if (function_exists('activity')) {
                            \activity('security')
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('admin_reset_2fa')
                                ->withProperties([
                                    'target_user_id' => $record->id,
                                    'target_email' => $record->email,
                                    'ip' => request()->ip(),
                                ])
                                ->log("Réinitialisation du 2FA de {$record->name}");
                        }


                        DB::table('security_events')->insert([
                            'user_id' => $record->id,
                            'event_type' => 'admin_reset_2fa',
                            'email' => $record->email,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'severity' => 'critical',
                            'metadata' => json_encode([
                                'admin_id' => auth()->id(),
                                'admin_email' => auth()->user()?->email,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('2FA réinitialisé')
                            ->body('L’utilisateur devra scanner un nouveau QR code à sa prochaine connexion.')
                            ->success()
                            ->send();
                    }),

                Action::make('activate')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'inactive')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'active',
                        ]);

                        if (function_exists('activity')) {
                            \activity('security')
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('user_activated')
                                ->withProperties([
                                    'target_user_id' => $record->id,
                                    'target_email' => $record->email,
                                    'ip' => request()->ip(),
                                ])
                                ->log("Activation du compte de {$record->name}");
                        }


                        DB::table('security_events')->insert([
                            'user_id' => $record->id,
                            'event_type' => 'user_activated',
                            'email' => $record->email,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'severity' => 'info',
                            'metadata' => json_encode([
                                'admin_id' => auth()->id(),
                                'admin_email' => auth()->user()?->email,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

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
                    ->visible(fn($record) => $record->status === 'active')
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
                            \activity('security')
                                ->causedBy(auth()->user())
                                ->performedOn($record)
                                ->event('user_deactivated')
                                ->withProperties([
                                    'target_user_id' => $record->id,
                                    'target_email' => $record->email,
                                    'ip' => request()->ip(),
                                ])
                                ->log("Désactivation du compte de {$record->name}");
                        }

                        DB::table('security_events')->insert([
                            'user_id' => $record->id,
                            'event_type' => 'user_deactivated',
                            'email' => $record->email,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'severity' => 'warning',
                            'metadata' => json_encode([
                                'admin_id' => auth()->id(),
                                'admin_email' => auth()->user()?->email,
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Utilisateur désactivé')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
