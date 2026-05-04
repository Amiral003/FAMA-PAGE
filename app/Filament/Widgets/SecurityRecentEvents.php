<?php

namespace App\Filament\Widgets;

use App\Models\SecurityEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SecurityRecentEvents extends TableWidget
{
    protected static ?string $heading = 'Derniers événements de sécurité';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(SecurityEvent::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('event_type')
                    ->label('Événement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login_success' => 'success',
                        'login_failed' => 'warning',
                        'account_locked' => 'danger',
                        'password_reset_success' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('severity')
                    ->label('Niveau')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info' => 'info',
                        'warning' => 'warning',
                        'danger', 'critical' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super-admin');
    }
}