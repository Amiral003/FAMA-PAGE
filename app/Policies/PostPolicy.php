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

    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['redacteur', 'validateur']);
    }

    public function view(User $user, Post $post)
    {
        if ($user->hasRole('validateur')) return true;
        return $user->id === $post->user_id;
    }

    public function create(User $user)
    {
        return $user->hasRole('redacteur');
    }

    /**
     * Autorise la modification.
     */
    public function update(User $user, Post $post)
    {
        // Le rédacteur modifie son propre brouillon
        if ($user->hasRole('redacteur') && $post->user_id === $user->id && $post->status === 'brouillon') {
            return true;
        }

        // Le validateur peut modifier n'importe quel post (pour corriger ou changer le statut)
        if ($user->hasRole('validateur')) {
            return true;
        }

        return false;
    }

    /**
     * Approuver : Autorise le validateur quel que soit le statut.
     */
    public function approve(User $user, Post $post)
    {
        return $user->hasRole('validateur');
    }

    /**
     * Rejeter : Autorise le validateur quel que soit le statut.
     */
    public function reject(User $user, Post $post)
    {
        return $user->hasRole('validateur');
    }

    public function delete(User $user, Post $post)
    {
        return $user->hasRole('redacteur') 
            && $post->user_id === $user->id 
            && $post->status === 'brouillon';
    }
}