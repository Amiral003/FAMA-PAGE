<?php

namespace App\Filament\Widgets;

use App\Models\SecurityEvent;
use App\Models\SecurityLockout;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SecurityStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Échecs login / 24h', SecurityEvent::where('event_type', 'login_failed')->where('created_at', '>=', now()->subDay())->count())
                ->description('Tentatives échouées')
                ->color('warning'),

            Stat::make('Alertes critiques / 24h', SecurityEvent::where('severity', 'critical')->where('created_at', '>=', now()->subDay())->count())
                ->description('Souvent super-admin ciblé')
                ->color('danger'),

            Stat::make('Blocages actifs', SecurityLockout::where('locked_until', '>', now())->count())
                ->description('Comptes/IP bloqués')
                ->color('danger'),

            Stat::make('Connexions réussies / 24h', SecurityEvent::where('event_type', 'login_success')->where('created_at', '>=', now()->subDay())->count())
                ->description('Accès valides')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super-admin');
    }
}