<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\PostViewDaily;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PostController extends Controller
{
    /**
     * ✅ 9 derniers posts publics (HOME)
     * Optionnel: supporte ?type=flash etc.
     */
   public function latest(Request $request)
{
    return Post::query()
        ->published()
        ->with(['media', 'author'])
        ->publicOrder()
        ->whereIn('type', [
            Post::TYPE_ARTICLE, // actualité
            Post::TYPE_VIDEO,   // video
        ])
        ->limit(5) // ✅ tu avais 9, mais tu veux 5 sur accueil
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
        ]);
}

private function trackPostView(Post $post): void
{
    // 1) On incrémente toujours le total
    $post->increment('total_views');

    $ipHash = $this->makeIpHash(request()->ip());
    $viewDate = now()->toDateString();
    $country = $this->resolveCountry(request()->ip());

    DB::transaction(function () use ($post, $ipHash, $viewDate, $country) {
        $daily = PostViewDaily::query()
            ->where('post_id', $post->id)
            ->where('ip_hash', $ipHash)
            ->where('view_date', $viewDate)
            ->first();

        if ($daily) {
            $daily->increment('hits');
            return;
        }

        PostViewDaily::create([
            'post_id' => $post->id,
            'ip_hash' => $ipHash,
            'country' => $country,
            'view_date' => $viewDate,
            'hits' => 1,
        ]);

        $post->increment('unique_views');
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

        $q = trim((string) $request->query('q', ''));
        $type = trim((string) $request->query('type', ''));

        $query = Post::query()
            ->published()
            ->with(['media', 'author'])
            ->publicOrder();

        // ✅ Filtre par type (si fourni)
        if ($type !== '') {
            $allowed = [
                Post::TYPE_FLASH,
                Post::TYPE_ARTICLE,
                Post::TYPE_RECRUTEMENT,
                Post::TYPE_PDF,
                Post::TYPE_VIDEO, // ✅ ajout
            ];

            if (in_array($type, $allowed, true)) {
                $query->where('type', $type);
            }
        }

        // ✅ Recherche (optionnelle)
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'ilike', "%{$q}%")
                    ->orWhere('content', 'ilike', "%{$q}%");
            });
        }

        // ✅ IMPORTANT: on limite les colonnes renvoyées (plus propre + évite fuite)
        // Laravel paginate() ne supporte pas directement ->get([...])
        // Donc on fait un select() avant paginate.
        $query->select([
            'id',
            'title',
            'slug',
            'content',
            'status',
            'type',
            'thumbnail',
            'pdf_path',

            // ✅ champs vidéo
            'video_url',
            'video_platform',
            'video_thumbnail_url',

            'published_at',
            'validated_at',
            'validated_by',
            'user_id',
            'created_at',
        ]);

        return response()->json($query->paginate($perPage));
    }

    /**
     * ✅ Flashs du bandeau (strict: publiés + moins de 24h)
     * URL: /api/posts/flashes
     */
    public function flashes(Request $request)
    {
        $limit = (int) $request->query('limit', 20);
        $limit = max(5, min($limit, 50));

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
                'slug',
                'type',
                'status',
                'published_at',
                'created_at',
            ]);
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

        $q = trim((string) $request->query('q', ''));

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

                // ✅ champs vidéo
                'video_url',
                'video_platform',
                'video_thumbnail_url',

                // optionnel: description (si tu veux un extrait)
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

        return response()->json($query->paginate($perPage));
    }

    /**
     * ✅ Détail d’un post public par slug
     */

    public function show(string $slug)
{
    $post = Post::query()
        ->published()
        ->where('slug', $slug)
        ->with(['media', 'author', 'validator'])
        ->firstOrFail();

    $this->trackPostView($post);

    return response()->json($post->fresh(['media', 'author', 'validator']));
}

   public function photos(Request $request)
{
    $perPage = (int) $request->query('per_page', 12);
    $perPage = max(6, min($perPage, 36));

    $q = trim((string) $request->query('q', ''));

    $query = Post::query()
        ->published()
        ->where('type', Post::TYPE_ARTICLE) // ✅ UNIQUEMENT actualités
        ->with(['media' => function ($m) {
            $m->orderBy('order');
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

    return response()->json($query->paginate($perPage));
}
}