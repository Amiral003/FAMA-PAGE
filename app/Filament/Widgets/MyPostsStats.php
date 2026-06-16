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

        $stats = Post::query()
            ->where('user_id', $user->id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as drafts,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as in_review,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as published,
                COALESCE(SUM(total_views), 0) as total_views
            ", [
                Post::STATUS_BROUILLON,
                Post::STATUS_REVISION,
                Post::STATUS_PUBLIE,
            ])
            ->first();

        return [
            Stat::make('Mes brouillons', (int) $stats->drafts)
                ->description('Contenus en préparation')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('En révision', (int) $stats->in_review)
                ->description('Contenus en attente de validation')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('warning'),

            Stat::make('Publiés', (int) $stats->published)
                ->description('Contenus déjà en ligne')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Vues cumulées', number_format((int) $stats->total_views, 0, ',', ' '))
                ->description('Audience totale de mes contenus')
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),
        ];
    }
}
