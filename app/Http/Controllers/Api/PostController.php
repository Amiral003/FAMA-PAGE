<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\SafeHtml;
use Illuminate\Http\Request;
use App\Models\PostViewDaily;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    
public function latest(Request $request)
{
    return Cache::remember('public:posts:latest:v1', 60, function () {
        return Post::query()
            ->published()
            ->with([
                'media',
                'author:id,name,profile_photo_path',
            ])
            ->publicOrder()
            ->whereIn('type', [
                Post::TYPE_ARTICLE,
                Post::TYPE_VIDEO,
            ])
            ->limit(5)
            ->get([
                'id',
                'title',
                'slug',
                'content',
                'status',
                'type',
                'thumbnail',
                'pdf_path',
                'video_url',
                'video_platform',
                'video_thumbnail_url',
                'published_at',
                'validated_at',
                'validated_by',
                'user_id',
                'created_at',
            ])
            ->each(fn (Post $post) => $this->sanitizePostContent($post));
    });
}


private function trackPostView(Post $post): void
{
    $ip = request()->ip();
    $ipHash = $this->makeIpHash($ip);
    $viewDate = now()->toDateString();
    $country = $this->resolveCountry($ip);
    $now = now();

    DB::transaction(function () use ($post, $ipHash, $viewDate, $country, $now) {
        // Toute ouverture compte comme une vue totale.
        $post->increment('total_views');

        // PostgreSQL arbitre atomiquement grâce à la contrainte UNIQUE
        // (post_id, ip_hash, view_date).
        $inserted = DB::table('post_views_daily')->insertOrIgnore([
            'post_id' => $post->id,
            'ip_hash' => $ipHash,
            'country' => $country,
            'view_date' => $viewDate,
            'hits' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($inserted === 1) {
            // Première vue de cette IP pour cet article aujourd'hui.
            $post->increment('unique_views');
            return;
        }

        // La ligne existait déjà : on incrémente seulement le nombre de hits.
        DB::table('post_views_daily')
            ->where('post_id', $post->id)
            ->where('ip_hash', $ipHash)
            ->where('view_date', $viewDate)
            ->increment('hits', 1, [
                'updated_at' => $now,
            ]);
    });
}

private function makeIpHash(?string $ip): string
{
    return hash('sha256', ($ip ?? 'unknown') . '|' . config('app.key'));
}

private function resolveCountry(?string $ip): ?string
{
    // V1 : fallback propre si aucun service geoip n'est installé
    try {
        if (function_exists('geoip')) {
            $result = geoip($ip);
            return $result?->country ?? null;
        }
    } catch (\Throwable $e) {
        // on ne casse jamais l'ouverture du post à cause du geoip
    }

    return null;
}

    /**
     * ✅ Fil d’actualité paginé
     * Supporte:
     * - ?page=1
     * - ?per_page=9
     * - ?q=mot (recherche)
     * - ?type=flash (filtre type)
     */
public function index(Request $request)
{
    $perPage = (int) $request->query('per_page', 9);
    $perPage = max(5, min($perPage, 30));

    $page = max(1, (int) $request->query('page', 1));

    $q = trim((string) $request->query('q', ''));
    $type = trim((string) $request->query('type', ''));

    $allowed = [
        Post::TYPE_ARTICLE,
        Post::TYPE_VIDEO,
    ];

    $normalizedType = in_array($type, $allowed, true) ? $type : '';

    $cacheKey = sprintf(
        'public:posts:index:v1:page:%d:per:%d:type:%s:q:%s',
        $page,
        $perPage,
        $normalizedType !== '' ? $normalizedType : 'all',
        hash('sha256', mb_strtolower($q))
    );

    $posts = Cache::remember($cacheKey, 60, function () use (
        $perPage,
        $page,
        $q,
        $normalizedType
    ) {
        $query = Post::query()
            ->published()
            ->with([
                'media',
                'author:id,name,profile_photo_path',
            ])
            ->whereIn('type', [
                Post::TYPE_ARTICLE,
                Post::TYPE_VIDEO,
            ])
            ->publicOrder();

        if ($normalizedType !== '') {
            $query->where('type', $normalizedType);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'ilike', "%{$q}%")
                    ->orWhere('content', 'ilike', "%{$q}%");
            });
        }

        $query->select([
            'id',
            'title',
            'slug',
            'content',
            'status',
            'type',
            'thumbnail',
            'pdf_path',
            'video_url',
            'video_platform',
            'video_thumbnail_url',
            'published_at',
            'validated_at',
            'validated_by',
            'user_id',
            'created_at',
        ]);

        $posts = $query->paginate(
            perPage: $perPage,
            page: $page
        );

        $posts->getCollection()
            ->each(fn (Post $post) => $this->sanitizePostContent($post));

        return $posts;
    });

    return response()->json($posts);
}

public function comOps(Request $request)
{
    $perPage = (int) $request->query('per_page', 12);
    $perPage = max(6, min($perPage, 30));

    $page = max(1, (int) $request->query('page', 1));
    $q = trim((string) $request->query('q', ''));

    $cacheKey = sprintf(
        'public:posts:com-ops:v1:page:%d:per:%d:q:%s',
        $page,
        $perPage,
        hash('sha256', mb_strtolower($q))
    );

    $posts = Cache::remember($cacheKey, 60, function () use ($perPage, $page, $q) {
        $query = Post::query()
            ->published()
            ->where('type', Post::TYPE_FLASH)
            ->publicOrder()
            ->select([
                'id',
                'title',
                'slug',
                'content',
                'status',
                'type',
                'thumbnail',
                'published_at',
                'created_at',
            ]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'ilike', "%{$q}%")
                    ->orWhere('content', 'ilike', "%{$q}%");
            });
        }

        $posts = $query->paginate(
            perPage: $perPage,
            page: $page
        );

        $posts->getCollection()
            ->each(fn (Post $post) => $this->sanitizePostContent($post));

        return $posts;
    });

    return response()->json($posts);
}

    /**
     * ✅ Flashs du bandeau (strict: publiés + moins de 24h)
     * URL: /api/posts/flashes
     */
    public function flashes(Request $request)
{
    $limit = (int) $request->query('limit', 20);
    $limit = max(5, min($limit, 50));

    return Cache::remember(
        "public:posts:flashes:v1:limit:{$limit}",
        30,
        function () use ($limit) {
            return Post::query()
                ->published()
                ->where('type', Post::TYPE_FLASH)
                ->whereNotNull('published_at')
                ->where('published_at', '>=', now()->subDay())
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get([
                    'id',
                    'title',
                    'content',
                    'slug',
                    'type',
                    'status',
                    'published_at',
                    'created_at',
                ])
                ->map(function ($post) {
                    $this->sanitizePostContent($post);
                    $post->display_text = str($post->content ?: $post->title)
                        ->squish()
                        ->toString();

                    return $post;
                });
        }
    );
}

    /**
     * ✅ Vidéothèque (uniquement type=video)
     * URL: /api/posts/videos
     * Supporte:
     * - ?page=1
     * - ?per_page=12
     * - ?q=mot (recherche)
     */
    public function videos(Request $request)
{
    $perPage = (int) $request->query('per_page', 12);
    $perPage = max(6, min($perPage, 36));

    $page = max(1, (int) $request->query('page', 1));

    $q = trim((string) $request->query('q', ''));

    $cacheKey = sprintf(
        'public:posts:videos:v1:page:%d:per:%d:q:%s',
        $page,
        $perPage,
        hash('sha256', mb_strtolower($q))
    );

    $posts = Cache::remember($cacheKey, 60, function () use ($perPage, $page, $q) {
        $query = Post::query()
            ->published()
            ->where('type', Post::TYPE_VIDEO)
            ->publicOrder()
            ->select([
                'id',
                'title',
                'slug',
                'type',
                'status',
                'video_url',
                'video_platform',
                'video_thumbnail_url',
                'content',
                'published_at',
                'created_at',
            ]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'ilike', "%{$q}%")
                    ->orWhere('content', 'ilike', "%{$q}%");
            });
        }

        $posts = $query->paginate(
            perPage: $perPage,
            page: $page
        );

        $posts->getCollection()
            ->each(fn (Post $post) => $this->sanitizePostContent($post));

        return $posts;
    });

    return response()->json($posts);
}

    /**
     * ✅ Détail d’un post public par slug
     */

    public function show(string $slug)
{
    $post = Post::query()
        ->published()
        ->where('slug', $slug)
        ->with([
    'media',
    'author:id,name,profile_photo_path',
    'validator:id,name,profile_photo_path',
])
        ->firstOrFail();

    $this->trackPostView($post);

    $this->sanitizePostContent($post);

    return response()->json($post);
}

public function photos(Request $request)
{
    $perPage = (int) $request->query('per_page', 12);
    $perPage = max(6, min($perPage, 36));

    $page = max(1, (int) $request->query('page', 1));
    $q = trim((string) $request->query('q', ''));

    $cacheKey = sprintf(
        'public:posts:photos:v1:page:%d:per:%d:q:%s',
        $page,
        $perPage,
        hash('sha256', mb_strtolower($q))
    );

    $posts = Cache::remember($cacheKey, 60, function () use ($perPage, $page, $q) {
        $query = Post::query()
            ->published()
            ->where('type', Post::TYPE_ARTICLE)
            ->with(['media' => function ($m) {
                $m->orderBy('order')->orderBy('id');
            }])
            ->publicOrder()
            ->select([
                'id',
                'title',
                'slug',
                'type',
                'published_at',
                'created_at',
            ]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'ilike', "%{$q}%")
                    ->orWhere('content', 'ilike', "%{$q}%");
            });
        }

        return $query->paginate(
            perPage: $perPage,
            page: $page
        );
    });

    return response()->json($posts);
}
public function recruitment(Request $request)
{
    $perPage = (int) $request->query('per_page', 12);
    $perPage = max(6, min($perPage, 24));

    $page = max(1, (int) $request->query('page', 1));
    $q = trim((string) $request->query('q', ''));

    $cacheKey = sprintf(
        'public:posts:recruitment:v1:page:%d:per:%d:q:%s',
        $page,
        $perPage,
        hash('sha256', mb_strtolower($q))
    );

    $posts = Cache::remember($cacheKey, 120, function () use ($perPage, $page, $q) {
        $query = Post::query()
            ->published()
            ->where('type', Post::TYPE_PDF)
            ->whereNotNull('pdf_path')
            ->publicOrder()
            ->select([
                'id',
                'title',
                'slug',
                'content',
                'status',
                'type',
                'thumbnail',
                'pdf_path',
                'published_at',
                'created_at',
            ]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'ilike', "%{$q}%")
                    ->orWhere('content', 'ilike', "%{$q}%");
            });
        }

        $posts = $query->paginate(
            perPage: $perPage,
            page: $page
        );

        $posts->getCollection()
            ->each(fn (Post $post) => $this->sanitizePostContent($post));

        return $posts;
    });

    return response()->json($posts);
}

public function latestPdfs(Request $request)
{
    $limit = (int) $request->query('limit', 3);
    $limit = max(1, min($limit, 6));

    $posts = Cache::remember(
        "public:posts:latest-pdfs:v1:limit:{$limit}",
        120,
        function () use ($limit) {
            return Post::query()
                ->published()
                ->where('type', Post::TYPE_PDF)
                ->whereNotNull('pdf_path')
                ->publicOrder()
                ->limit($limit)
                ->get([
                    'id',
                    'title',
                    'slug',
                    'type',
                    'thumbnail',
                    'pdf_path',
                    'published_at',
                    'created_at',
                ]);
        }
    );

    return response()->json($posts);
}


private function sanitizePostContent(Post $post): void
{
    $post->content = SafeHtml::clean($post->content);
}
}
