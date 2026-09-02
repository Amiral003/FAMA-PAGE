<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class PublicPostPageController extends Controller
{
    public function __invoke(string $slug): View
    {
        $post = Post::query()
            ->published()
            ->with([
                'media:id,post_id,file_path,order',
            ])
            ->where('slug', $slug)
            ->firstOrFail([
                'id',
                'title',
                'slug',
                'content',
                'type',
                'thumbnail',
                'published_at',
                'created_at',
                'updated_at',
            ]);

        $plainText = preg_replace(
            '/<[^>]+>/u',
            ' ',
            (string) $post->content
        ) ?? '';

        $plainText = html_entity_decode(
            $plainText,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $plainText = trim(
            preg_replace('/\s+/u', ' ', $plainText) ?? ''
        );

        $description = Str::limit(
            $plainText !== ''
                ? $plainText
                : 'Communiqué officiel des Forces Armées Maliennes.',
            160,
            ''
        );

        $canonical = url('/posts/' . $post->slug);

        $imagePath = $post->thumbnail
            ?: $post->media->first()?->file_path;

        $imageUrl = $imagePath
            ? url('/storage/' . ltrim($imagePath, '/'))
            : url('/images/og-default.jpg');

        $publishedAt = $post->published_at
            ?? $post->created_at;

        $modifiedAt = $post->updated_at
            ?? $publishedAt;

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => $post->type === Post::TYPE_VIDEO
                ? 'Article'
                : 'NewsArticle',

            'headline' => $post->title,
            'description' => $description,
            'image' => [$imageUrl],

            'datePublished' => $publishedAt?->toIso8601String(),
            'dateModified' => $modifiedAt?->toIso8601String(),

            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical,
            ],

            'author' => [
                '@type' => 'Organization',
                'name' => 'Forces Armées Maliennes',
            ],

            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Forces Armées Maliennes',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => url('/images/logo-fama.png'),
                ],
            ],
        ];

        return view('front', [
            'seo' => [
                'title' => $post->title . ' | Forces Armées Maliennes',
                'description' => $description,
                'canonical' => $canonical,
                'type' => $post->type === Post::TYPE_VIDEO
                    ? 'video.other'
                    : 'article',
                'image' => $imageUrl,
            ],

            'seoPost' => [
                'title' => $post->title,
                'content' => $plainText,
                'published_at' => $publishedAt,
            ],

            'jsonLd' => $jsonLd,
        ]);
    }
}
