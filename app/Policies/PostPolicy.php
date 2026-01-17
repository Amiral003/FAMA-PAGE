<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;


class PostPolicy
{
    public function before(User $user, string $ability): bool|null{
        if ($user->hasRole('super-admin')){
            return true;
        }
        return null;
    }
    public function viewAny(User $user): bool
    {
        // return true;
        return $user->hasAnyRole(['redacteur', 'validateur', 'super-admin']);
    }

    public function view(User $user, Post $post): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('redacteur');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->hasRole('redacteur')
            && $post->author_id === $user->id
            && $post->status === 'draft';
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->hasRole('super-admin');
    }

    // 🔽 ACTION MÉTIER (CUSTOM)

    public function submit(User $user, Post $post): bool
    {
        return $user->hasRole('redacteur')
            && $post->author_id === $user->id
            && $post->status === 'draft';
    }

    public function approve(User $user, Post $post): bool
    {
        return $user->hasRole('validateur')
            && $post->status === 'pending';
    }

    public function reject(User $user, Post $post): bool
    {
        return $user->hasRole('validateur')
            && $post->status === 'pending';
    }
}
