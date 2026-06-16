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
        $since = now()->subDay();

        $eventStats = SecurityEvent::query()
            ->where('created_at', '>=', $since)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN event_type = 'login_failed' THEN 1 ELSE 0 END), 0) as login_failed,
                COALESCE(SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END), 0) as critical_alerts,
                COALESCE(SUM(CASE WHEN event_type = 'login_success' THEN 1 ELSE 0 END), 0) as login_success
            ")
            ->first();

        $activeLockouts = SecurityLockout::query()
            ->where('locked_until', '>', now())
            ->count();

        return [
            Stat::make('Échecs login / 24h', (int) $eventStats->login_failed)
                ->description('Tentatives échouées')
                ->color('warning'),

            Stat::make('Alertes critiques / 24h', (int) $eventStats->critical_alerts)
                ->description('Souvent super-admin ciblé')
                ->color('danger'),

            Stat::make('Blocages actifs', $activeLockouts)
                ->description('Comptes/IP bloqués')
                ->color('danger'),

            Stat::make('Connexions réussies / 24h', (int) $eventStats->login_success)
                ->description('Accès valides')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super-admin');
    }
}
