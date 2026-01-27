<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;


class PostsStats extends StatsOverviewWidget
{
   protected function getStats(): array
{
    return [
        // Stat::make('Posts validés', Post::where('status', 'approved')->count())
        //     ->icon('heroicon-o-check-circle')
        //     ->color('success'),

        Stat::make('Total Communiqués', Post::count())
            ->description('Total dans la base')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('info'),

        Stat::make('À traiter', Post::whereIn('status', ['revision', 'brouillon'])->count())
    ->description('En attente de validation')
    ->descriptionIcon('heroicon-m-clock')
    ->color('warning'),

        Stat::make('Publiés', Post::where('status', 'publie')->count())
            ->description('En ligne actuellement')
            ->descriptionIcon('heroicon-m-check-badge')
            ->color('success'),
    ];
}

}

