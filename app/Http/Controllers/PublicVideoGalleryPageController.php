<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicVideoGalleryPageController extends Controller
{
    public function __invoke()
    {
        $videos = Cache::remember('seo:videotheque:videos:v1', 60, function () {
            return Post::query()
                ->published()
                ->where('type', Post::TYPE_VIDEO)
                ->whereNotNull('slug')
                ->whereNotNull('video_url')
                ->publicOrder()
                ->limit(12)
                ->get([
                    'id',
                    'title',
                    'slug',
                    'video_url',
                    'video_platform',
                    'video_thumbnail_url',
                    'content',
                    'published_at',
                    'created_at',
                ]);
        });

        $items = $videos->map(function (Post $post) {
            $description = Str::limit(
                Str::squish(
                    html_entity_decode(
                        strip_tags((string) $post->content)
                    )
                ),
                220
            );

            if ($description === '') {
                $description = trim($post->title);
            }

            $thumbnail = $post->video_thumbnail_url
                ?: url('/assets/images/video-cover.jpg');

            return [
                'title' => trim($post->title),
                'description' => $description,
                'post_url' => url('/posts/' . $post->slug),
                'video_url' => $post->video_url,
                'embed_url' => $this->youtubeEmbedUrl(
                    $post->video_url,
                    $post->video_platform
                ),
                'thumbnail' => $thumbnail,
                'published_at' => $post->published_at
                    ?? $post->created_at,
                'platform' => $post->video_platform,
            ];
        });

        $description = 'Consultez la vidéothèque officielle des Forces Armées Maliennes : reportages, communiqués vidéo, interviews, opérations, cérémonies et contenus institutionnels validés.';

        $seo = [
            'title' => 'Vidéothèque officielle | Forces Armées Maliennes',
            'description' => $description,
            'canonical' => url('/videotheque'),
            'type' => 'website',
            'image' => $items->first()['thumbnail']
                ?? url('/images/og-default.jpg'),
        ];

        $seoVideoGallery = [
            'videos' => $items,
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Vidéothèque officielle des Forces Armées Maliennes',
            'description' => $description,
            'url' => url('/videotheque'),
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $items->count(),
                'itemListElement' => $items
                    ->values()
                    ->map(function ($video, $index) {
                        $item = [
                            '@type' => 'VideoObject',
                            'name' => $video['title'],
                            'description' => $video['description'],
                            'thumbnailUrl' => [
                                $video['thumbnail'],
                            ],
                            'uploadDate' => $video['published_at']
                                ?->toIso8601String(),
                            'url' => $video['post_url'],
                        ];

                        if ($video['embed_url']) {
                            $item['embedUrl'] = $video['embed_url'];
                        }

                        return [
                            '@type' => 'ListItem',
                            'position' => $index + 1,
                            'item' => $item,
                        ];
                    })
                    ->all(),
            ],
        ];

        return view(
            'front',
            compact('seo', 'seoVideoGallery', 'jsonLd')
        );
    }

    private function youtubeEmbedUrl(
        ?string $url,
        ?string $platform
    ): ?string {
        if (!$url || strtolower((string) $platform) !== 'youtube') {
            return null;
        }

        $parts = parse_url($url);

        if (!$parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $videoId = null;

        if ($host === 'youtu.be' || $host === 'www.youtu.be') {
            $videoId = trim($parts['path'] ?? '', '/');
        }

        if (
            in_array(
                $host,
                ['youtube.com', 'www.youtube.com', 'm.youtube.com'],
                true
            )
        ) {
            parse_str($parts['query'] ?? '', $query);
            $videoId = $query['v'] ?? null;
        }

        if (!$videoId) {
            return null;
        }

        if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            return null;
        }

        return 'https://www.youtube.com/embed/' . $videoId;
    }
}
