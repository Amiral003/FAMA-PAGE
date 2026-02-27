<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // On charge la relation 'media' pour avoir accès au file_path
        $posts = Post::with('media')
            ->where('status', Post::STATUS_APPROVED)
            ->latest('validated_at')
            ->paginate(12);

        if (request()->wantsJson()) {
            return response()->json($posts);
        }

        return view('posts.index', compact('posts'));
    }

    public function show($idOrSlug)
    {
        $post = Post::with('media')
            ->where('slug', $idOrSlug)
            ->orWhere('id', $idOrSlug)
            ->firstOrFail();

        if (request()->wantsJson()) {
            return response()->json($post);
        }

        return view('posts.show', compact('post'));
    }
}
