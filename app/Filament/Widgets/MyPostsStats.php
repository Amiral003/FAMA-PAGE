<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MyPostsStats extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('redacteur') ?? false;
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        $drafts = Post::query()
            ->where('user_id', $user->id)
            ->where('status', Post::STATUS_BROUILLON)
            ->count();

        $inReview = Post::query()
            ->where('user_id', $user->id)
            ->where('status', Post::STATUS_REVISION)
            ->count();

        $published = Post::query()
            ->where('user_id', $user->id)
            ->where('status', Post::STATUS_PUBLIE)
            ->count();

        $totalViews = (int) Post::query()
            ->where('user_id', $user->id)
            ->sum('total_views');

        return [
            Stat::make('Mes brouillons', $drafts)
                ->description('Contenus en préparation')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('En révision', $inReview)
                ->description('Contenus en attente de validation')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('warning'),

            Stat::make('Publiés', $published)
                ->description('Contenus déjà en ligne')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Vues cumulées', number_format($totalViews, 0, ',', ' '))
                ->description('Audience totale de mes contenus')
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),
        ];
    }
}