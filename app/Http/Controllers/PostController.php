<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('status', Post::STATUS_APPROVED)
            ->latest('validated_at')
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        abort_if($post->status !== Post::STATUS_APPROVED, 404);

        return view('posts.show', compact('post'));
    }
}

