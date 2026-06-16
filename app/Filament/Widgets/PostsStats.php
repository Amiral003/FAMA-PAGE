<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
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
        $postCounts = Post::query()
            ->whereIn('status', [
                Post::STATUS_BROUILLON,
                Post::STATUS_REVISION,
                Post::STATUS_PUBLIE,
            ])
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pendingCount = (int) ($postCounts[Post::STATUS_BROUILLON] ?? 0)
            + (int) ($postCounts[Post::STATUS_REVISION] ?? 0);

        return [
            Stat::make('Total à valider', $pendingCount)
                ->description('Brouillons et révisions en attente')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(PostResource::getUrl('index', [
                    'status_filter' => 'a_valider',
                ])),

            Stat::make('Total en ligne', (int) ($postCounts[Post::STATUS_PUBLIE] ?? 0))
                ->description('Articles sur le site')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(PostResource::getUrl('index', [
                    'status_filter' => 'publies',
                ])),

            Stat::make('Total contributeurs', User::count())
                ->description('Utilisateurs enregistrés')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
