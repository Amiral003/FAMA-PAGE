<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PostsStats extends BaseWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        // Les validateurs et admins ont besoin de voir la charge globale
        return Auth::user()->hasAnyRole(['super-admin', 'validateur']);
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total à valider', Post::whereIn('status', ['revision', 'brouillon'])->count())
                ->description('Articles attendant une action')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
            

            Stat::make('Total en ligne', Post::where('status', 'publie')->count())
                ->description('Articles sur le site')
                ->color('success'),

            Stat::make('Total contributeurs', \App\Models\User::count())
                ->description('Utilisateurs enregistrés')
                ->color('gray'),
        ];
    }
}