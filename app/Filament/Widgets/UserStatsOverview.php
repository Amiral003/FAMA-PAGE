<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        // On ne l'affiche que si l'utilisateur est rédacteur ou admin
        // Le validateur ne verra donc pas ce bloc vide
        return Auth::user()->hasAnyRole(['super-admin', 'redacteur']);
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $query = Post::where('user_id', $user->id);

        return [
            Stat::make('Mes Brouillons', (clone $query)->where('status', 'brouillon')->count())
                ->description('À terminer')
                ->descriptionIcon('heroicon-m-pencil')
                ->color('warning'),

            Stat::make('Mes Révisions', (clone $query)->where('status', 'revision')->count())
                ->description('En attente de validation')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Mes Publications', (clone $query)->where('status', 'publie')->count())
                ->color('success'),
        ];
    }
}