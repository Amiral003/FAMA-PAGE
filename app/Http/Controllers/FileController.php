<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    public function show(Post $post)
    {
        // 🔐 Sécurité absolue
        abort_if($post->status !== Post::STATUS_APPROVED, 403);

        $path = $post->file_path;

        abort_if(! Storage::disk('private')->exists($path), 404);

        return response()->file(
            Storage::disk('private')->path($path)
        );
    }
}