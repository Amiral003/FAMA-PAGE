<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\PostViewDaily;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostsAudienceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalViews = (int) Post::sum('total_views');
        $totalUniqueViews = (int) Post::sum('unique_views');

        $viewsToday = (int) PostViewDaily::query()
            ->whereDate('view_date', now()->toDateString())
            ->sum('hits');

        $uniqueToday = (int) PostViewDaily::query()
            ->whereDate('view_date', now()->toDateString())
            ->count();

        return [
            Stat::make('Vues totales', number_format($totalViews, 0, ',', ' '))
                ->description('Toutes les vues cumulées')
                ->icon('heroicon-o-eye'),

            Stat::make('Vues uniques', number_format($totalUniqueViews, 0, ',', ' '))
                ->description('Audience cumulée')
                ->icon('heroicon-o-users'),

            Stat::make('Vues aujourd’hui', number_format($viewsToday, 0, ',', ' '))
                ->description('Total journalier')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Visiteurs uniques aujourd’hui', number_format($uniqueToday, 0, ',', ' '))
                ->description('Unicité par post + IP hashée + jour')
                ->icon('heroicon-o-user-group'),
        ];
    }
}