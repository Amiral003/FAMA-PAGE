<?php

namespace App\Filament\Widgets;

use App\Models\SecurityEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SecurityTargetedAccounts extends TableWidget
{
    protected static ?string $heading = 'Comptes les plus ciblés / 24h';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SecurityEvent::query()
                    ->selectRaw('DISTINCT ON (email) id, email')
                    ->selectRaw('COUNT(*) OVER (PARTITION BY email) as attempts_count')
                    ->selectRaw('MAX(created_at) OVER (PARTITION BY email) as last_attempt_at')
                    ->where('event_type', 'login_failed')
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->where('created_at', '>=', now()->subDay())
                    ->orderByRaw('email, attempts_count DESC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('email')->label('Email ciblé'),
                Tables\Columns\TextColumn::make('attempts_count')->label('Tentatives')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('last_attempt_at')->label('Dernière tentative')->dateTime(),
            ])
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super-admin');
    }
}
