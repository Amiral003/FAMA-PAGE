<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\Widget;

class TopViewedPosts extends Widget
{
    protected string $view = 'filament.widgets.top-viewed-posts';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $posts = Post::query()
            ->published()
            ->with(['media'])
            ->orderByDesc('total_views')
            ->limit(5)
            ->get([
                'id',
                'title',
                'slug',
                'type',
                'thumbnail',
                'video_url',
                'video_platform',
                'video_thumbnail_url',
                'published_at',
                'total_views',
                'unique_views',
            ]);

        return [
            'posts' => $posts,
        ];
    }
}