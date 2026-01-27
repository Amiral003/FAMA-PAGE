<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * 9 derniers posts (HOME)
     */
    public function latest()
    {
        return Post::query()
            ->where('status', 'publie')
            ->orderByDesc('validated_at')
            ->limit(9)
            ->get([
                'id',
                'title',
                'description',
                'file_path',
                'validated_at',
            ]);
    }

    /**
     * Liste paginée (PORTFOLIO)
     */
    // public function index()
    // {
    //     return Post::query()
    //         ->where('status', 'publie')
    //         ->orderByDesc('validated_at')
    //         ->paginate(12);
    // }

    public function index()
{
    $posts = Post::where('status', Post::STATUS_PUBLIE)
        ->with('media') // CRUCIAL : Charge la relation avec les images
        ->latest('published_at')
        ->paginate(9);

    return response()->json($posts);
}

public function show($slug)
{
    $post = Post::where('slug', $slug)
        ->where('status', Post::STATUS_PUBLIE)
        ->with(['media', 'user']) // On charge les images ET l'auteur
        ->firstOrFail();

    return response()->json($post);
}

    /**
     * Détail d’un post
     */
   
}
