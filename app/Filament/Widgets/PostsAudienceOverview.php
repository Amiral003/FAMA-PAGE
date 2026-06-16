<?php

namespace App\Filament\Widgets;

use App\Models\PostViewDaily;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PostsAudienceOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['super-admin', 'validateur']) ?? false;
    }

    protected function getStats(): array
    {
        $today = now()->toDateString();
        $sevenDaysAgo = now()->subDays(6)->toDateString();
        $thirtyDaysAgo = now()->subDays(29)->toDateString();

        $stats = PostViewDaily::query()
            ->where('view_date', '>=', $thirtyDaysAgo)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN view_date = ? THEN hits ELSE 0 END), 0) as views_today,
                COALESCE(SUM(CASE WHEN view_date >= ? THEN hits ELSE 0 END), 0) as views_7_days,
                COALESCE(SUM(hits), 0) as views_30_days
            ", [
                $today,
                $sevenDaysAgo,
            ])
            ->first();

        $viewsToday = (int) $stats->views_today;
        $views7Days = (int) $stats->views_7_days;
        $views30Days = (int) $stats->views_30_days;
        $avg7Days = round($views7Days / 7);

        return [
            Stat::make("Vues aujourd'hui", number_format($viewsToday, 0, ',', ' '))
                ->description('Activité du jour')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('primary'),

            Stat::make('Vues sur 7 jours', number_format($views7Days, 0, ',', ' '))
                ->description('Activité récente')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Vues sur 30 jours', number_format($views30Days, 0, ',', ' '))
                ->description('Tendance mensuelle')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Moyenne / jour (7 j)', number_format($avg7Days, 0, ',', ' '))
                ->description('Intensité moyenne récente')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('gray'),
        ];
    }
}
