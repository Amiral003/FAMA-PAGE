<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Le Super-Admin outrepasse toutes les vérifications.
     */
    public function before(User $user)
    {
        if ($user->hasRole('super-admin')) return true;
    }

    /**
     * Qui peut voir la liste des posts.
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['redacteur', 'validateur']);
    }

    /**
     * Qui peut voir un post précis.
     */
    public function view(User $user, Post $post)
    {
        // Un validateur peut tout voir
        if ($user->hasRole('validateur')) return true;
        
        // Un rédacteur ne voit que les siens
        return $user->id === $post->user_id;
    }

    public function create(User $user)
    {
        return $user->hasRole('redacteur');
    }

    /**
     * Autorise la modification (nécessaire pour changer le statut).
     */
    public function update(User $user, Post $post)
    {
        // Cas 1 : Le rédacteur modifie son brouillon
        if ($user->hasRole('redacteur') && $post->user_id === $user->id && $post->status === 'brouillon') {
            return true;
        }

        // Cas 2 : Le validateur doit pouvoir modifier pour approuver/rejeter
        if ($user->hasRole('validateur') && $post->status === 'revision') {
            return true;
        }

        return false;
    }

    /**
     * Soumettre pour validation.
     */
    public function submit(User $user, Post $post)
    {
        return $user->hasRole('redacteur')
            && $post->user_id === $user->id
            && $post->status === 'brouillon';
    }

    /**
     * Approuver (Publier).
     */
    public function approve(User $user, Post $post)
    {
        return $user->hasRole('validateur')
            && $post->status === 'revision';
    }

    /**
     * Rejeter (Renvoyer en brouillon).
     */
    public function reject(User $user, Post $post)
    {
        return $user->hasRole('validateur')
            && $post->status === 'revision';
    }

    /**
     * Qui peut supprimer un post.
     */
    public function delete(User $user, Post $post)
    {
        // Seul le rédacteur peut supprimer son propre BROUILLON.
        // Une fois en révision ou publié, seul l'admin peut supprimer (via before).
        return $user->hasRole('redacteur') 
            && $post->user_id === $user->id 
            && $post->status === 'brouillon';
    }
}