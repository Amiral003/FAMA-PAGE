<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        if ($user->hasRole('super-admin')) {
            return [
                \App\Filament\Widgets\PostsStats::class,
                \App\Filament\Widgets\PostsChart::class,
                \App\Filament\Widgets\PostsAudienceOverview::class,
                \App\Filament\Widgets\AudienceChart::class,
                \App\Filament\Widgets\TopViewedPosts::class,
                \App\Filament\Widgets\SecurityStatsOverview::class,
\App\Filament\Widgets\SecurityRecentEvents::class,
 \App\Filament\Widgets\SecurityTopSuspiciousIps::class,
 \App\Filament\Widgets\SecurityTargetedAccounts::class,
            ];
        }

        if ($user->hasRole('validateur')) {
            return [
                \App\Filament\Widgets\PostsStats::class,
                \App\Filament\Widgets\PostsChart::class,
                \App\Filament\Widgets\PostsAudienceOverview::class,
                \App\Filament\Widgets\AudienceChart::class,
                \App\Filament\Widgets\TopViewedPosts::class,
            ];
        }

        if ($user->hasRole('redacteur')) {
            return [
                \App\Filament\Widgets\MyPostsStats::class,
                \App\Filament\Widgets\TopViewedPosts::class,
                \App\Filament\Widgets\MyRecentPosts::class,
                \App\Filament\Widgets\MyRejectedPosts::class,
                \App\Filament\Widgets\MyDrafts::class,

            ];
        }

        return [];
    }
}