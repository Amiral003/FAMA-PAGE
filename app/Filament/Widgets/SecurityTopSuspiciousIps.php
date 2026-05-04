<?php

namespace App\Filament\Widgets;

use App\Models\SecurityEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SecurityTopSuspiciousIps extends TableWidget
{
    protected static ?string $heading = 'Top IP suspectes / 24h';

    protected int|string|array $columnSpan = 'full';

  public function table(Table $table): Table
{
    return $table
        ->query(
            SecurityEvent::query()
                ->selectRaw('DISTINCT ON (ip_address) id, ip_address')
                ->selectRaw('COUNT(*) OVER (PARTITION BY ip_address) as attempts_count')
                ->selectRaw('MAX(created_at) OVER (PARTITION BY ip_address) as last_attempt_at')
                ->where('event_type', 'login_failed')
                ->whereNotNull('ip_address')
                ->where('created_at', '>=', now()->subDay())
                ->orderByRaw('ip_address, attempts_count DESC')
        )
        ->columns([
            Tables\Columns\TextColumn::make('ip_address')->label('IP'),
            Tables\Columns\TextColumn::make('attempts_count')->label('Tentatives')->badge()->color('danger'),
            Tables\Columns\TextColumn::make('last_attempt_at')->label('Dernière tentative')->dateTime(),
        ])
        ->paginated(false);
}

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super-admin');
    }
}