<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicNewsPageController extends Controller
{
    public function __invoke()
    {
        $posts = Cache::remember('seo:actualites:posts:v1', 60, function () {
            return Post::query()
                ->published()
                ->whereIn('type', [
                    Post::TYPE_ARTICLE,
                    Post::TYPE_VIDEO,
                ])
                ->whereNotNull('slug')
                ->publicOrder()
                ->limit(12)
                ->get([
                    'id',
                    'title',
                    'slug',
                    'content',
                    'type',
                    'published_at',
                    'created_at',
                ]);
        });

        $description = 'Actualités, communiqués et informations officielles des Forces Armées Maliennes. Retrouvez les dernières publications des FAMa.';

        $seo = [
            'title' => 'Actualités & Communiqués | Forces Armées Maliennes',
            'description' => $description,
            'canonical' => url('/actualites'),
            'type' => 'website',
            'image' => url('/images/og-default.jpg'),
        ];

        $seoNews = [
            'posts' => $posts,
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Actualités & Communiqués des Forces Armées Maliennes',
            'description' => $description,
            'url' => url('/actualites'),
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $posts->values()->map(function ($post, $index) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'url' => url('/posts/' . $post->slug),
                        'name' => $post->title,
                    ];
                })->all(),
            ],
        ];

        return view('front', compact('seo', 'seoNews', 'jsonLd'));
    }
}
