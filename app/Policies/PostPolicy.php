<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * ✅ Super-admin passe partout.
     */
    public function before(User $user): bool|null
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    /**
     * ✅ Voir la liste (admin)
     * - Rédacteur et validateur
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['redacteur', 'validateur']);
    }

    /**
     * ✅ Voir un post (admin)
     * - Validateur voit tout
     * - Rédacteur voit ses posts
     */
    public function view(User $user, Post $post): bool
    {
        if ($user->hasRole('validateur')) {
            return true;
        }

        return (int) $post->user_id === (int) $user->id;
    }

    /**
     * ✅ Créer
     * - Rédacteur uniquement
     */
    public function create(User $user): bool
    {
        return $user->hasRole('redacteur');
    }

    /**
     * ✅ Modifier (Edit)
     * - Rédacteur : uniquement ses posts, seulement si brouillon OU revision
     * - Validateur : peut modifier (si tu veux), sinon mets false
     */
    public function update(User $user, Post $post): bool
    {
        if ($user->hasRole('validateur')) {
            return true; // si tu veux qu'il puisse corriger lui-même
        }

        if ($user->hasRole('redacteur')) {
            return (int) $post->user_id === (int) $user->id
                && in_array($post->status, [Post::STATUS_BROUILLON, Post::STATUS_REVISION], true);
        }

        return false;
    }

    /**
     * ✅ Publier (Validateur)
     * Règle métier : publier UNIQUEMENT un brouillon
     */
    public function approve(User $user, Post $post): bool
    {
        return $user->hasRole('validateur')
            && $post->status === Post::STATUS_BROUILLON;
    }

    /**
     * ✅ Rejeter pour révision (Validateur)
     * Règle métier : rejeter UNIQUEMENT un brouillon et publie
     */
    public function reject(User $user, Post $post): bool
{
    return $user->hasRole('validateur')
        && in_array($post->status, [Post::STATUS_BROUILLON, Post::STATUS_PUBLIE], true);
}



    /**
     * ✅ Marquer corrigé (Rédacteur)
     * Règle métier : revision -> brouillon, uniquement l'auteur
     */
    public function markFixed(User $user, Post $post): bool
    {
        return $user->hasRole('redacteur')
            && (int) $post->user_id === (int) $user->id
            && $post->status === Post::STATUS_REVISION;
    }

    /**
     * ✅ Supprimer
     * - Rédacteur uniquement
     * - seulement si brouillon
     * - et seulement ses posts
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->hasRole('redacteur')
            && (int) $post->user_id === (int) $user->id
            && $post->status === Post::STATUS_BROUILLON;
    }
}