<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class PublicRecruitmentPageController extends Controller
{
    public function __invoke(): View
    {
        $posts = Post::query()
            ->published()
            ->where('type', Post::TYPE_PDF)
            ->whereNotNull('pdf_path')
            ->where('pdf_path', '!=', '')
            ->publicOrder()
            ->limit(12)
            ->get([
                'id',
                'title',
                'slug',
                'content',
                'pdf_path',
                'published_at',
                'created_at',
            ]);

        $canonical = url('/recrutement');

        $description = 'Consultez les avis de recrutement, concours d’entrée, résultats et communiqués officiels publiés par les Forces Armées Maliennes.';

        $items = $posts->map(function (Post $post) {
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

            return [
                'title' => $post->title,
                'excerpt' => Str::limit($plainText, 220),
                'article_url' => url('/posts/' . $post->slug),
                'pdf_url' => url('/storage/' . ltrim($post->pdf_path, '/')),
                'published_at' => $post->published_at ?? $post->created_at,
            ];
        });

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Recrutement & Concours | Forces Armées Maliennes',
            'description' => $description,
            'url' => $canonical,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $items->count(),
                'itemListElement' => $items
                    ->values()
                    ->map(function (array $item, int $index) {
                        return [
                            '@type' => 'ListItem',
                            'position' => $index + 1,
                            'item' => [
                                '@type' => 'WebPage',
                                'name' => $item['title'],
                                'url' => $item['article_url'],
                            ],
                        ];
                    })
                    ->all(),
            ],
        ];

        return view('front', [
            'seo' => [
                'title' => 'Recrutement & Concours | Forces Armées Maliennes',
                'description' => $description,
                'canonical' => $canonical,
                'type' => 'website',
                'image' => url('/images/og-default.jpg'),
            ],

            'seoCollection' => [
                'title' => 'Recrutement & Concours',
                'description' => 'Retrouvez les avis de recrutement, concours, résultats et documents officiels des Forces Armées Maliennes.',
                'items' => $items,
            ],

            'jsonLd' => $jsonLd,
        ]);
    }
}
