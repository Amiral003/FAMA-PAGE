<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\User;
use App\Filament\Resources\Posts\PostResource; 
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PostsStats extends BaseWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super-admin', 'validateur']);
    }

    protected function getStats(): array
    {
        return [
            // LIEN VERS LES POSTS À VALIDER
            Stat::make('Total à valider', Post::whereIn('status', ['revision', 'brouillon'])->count())
                ->description('Brouillons et révisions en attente')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                // On utilise l'URL de la ressource avec les filtres de table
->url(PostResource::getUrl('index', [
    'status_filter' => 'a_valider'
])),
            // LIEN VERS LES POSTS EN LIGNE
            Stat::make('Total en ligne', Post::where('status', 'publie')->count())
                ->description('Articles sur le site')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
->url(PostResource::getUrl('index', [
    'status_filter' => 'publies'
])),
            Stat::make('Total contributeurs', User::count())
                ->description('Utilisateurs enregistrés')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}