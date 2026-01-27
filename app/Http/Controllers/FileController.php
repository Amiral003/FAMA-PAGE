<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(Post $post)
    {
        if (!$post->file_path) {
            abort(404);
        }

        return Storage::disk('private')->response($post->file_path);
    }
}
