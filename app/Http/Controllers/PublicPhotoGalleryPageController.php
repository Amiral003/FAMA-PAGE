<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PublicPhotoGalleryPageController extends Controller
{
    public function __invoke()
    {
        $posts = Cache::remember('seo:phototheque:posts:v1', 60, function () {
            return Post::query()
                ->published()
                ->where('type', Post::TYPE_ARTICLE)
                ->whereNotNull('slug')
                ->whereHas('media', function ($query) {
                    $query->whereNotNull('file_path');
                })
                ->with([
                    'media' => function ($query) {
                        $query
                            ->whereNotNull('file_path')
                            ->orderBy('order')
                            ->orderBy('id');
                    },
                ])
                ->publicOrder()
                ->limit(12)
                ->get([
                    'id',
                    'title',
                    'slug',
                    'published_at',
                    'created_at',
                ]);
        });

        $photos = $posts
            ->flatMap(function (Post $post) {
                return $post->media->map(function ($media) use ($post) {
                    return [
                        'id' => $media->id,
                        'src' => url('/storage/' . ltrim($media->file_path, '/')),
                        'alt' => $post->title,
                        'caption' => $media->caption ?: $post->title,
                        'post_title' => $post->title,
                        'post_url' => url('/posts/' . $post->slug),
                        'published_at' => $post->published_at ?? $post->created_at,
                    ];
                });
            })
            ->take(24)
            ->values();

        $description = 'Galerie photo officielle des Forces Armées Maliennes : images des activités, opérations, cérémonies et événements des FAMa.';

        $seo = [
            'title' => 'Galerie Photo | Forces Armées Maliennes',
            'description' => $description,
            'canonical' => url('/phototheque'),
            'type' => 'website',
            'image' => $photos->first()['src'] ?? url('/images/og-default.jpg'),
        ];

        $seoPhotoGallery = [
            'photos' => $photos,
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Galerie Photo des Forces Armées Maliennes',
            'description' => $description,
            'url' => url('/phototheque'),
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $photos->count(),
                'itemListElement' => $photos
                    ->values()
                    ->map(function ($photo, $index) {
                        return [
                            '@type' => 'ListItem',
                            'position' => $index + 1,
                            'item' => [
                                '@type' => 'ImageObject',
                                'contentUrl' => $photo['src'],
                                'name' => $photo['alt'],
                                'caption' => $photo['caption'],
                                'url' => $photo['post_url'],
                            ],
                        ];
                    })
                    ->all(),
            ],
        ];

        return view(
            'front',
            compact('seo', 'seoPhotoGallery', 'jsonLd')
        );
    }
}
