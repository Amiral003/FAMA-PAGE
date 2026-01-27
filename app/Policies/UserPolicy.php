<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Helper pour centraliser l'accès Super Admin
     */
    protected function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Peut voir la liste des utilisateurs ?
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Peut voir un utilisateur spécifique ?
     */
    public function view(User $user, User $model): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Peut créer un utilisateur ?
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Peut modifier un utilisateur ?
     */
    public function update(User $user, User $model): bool
    {
        // Un Super-Admin peut tout modifier
        return $this->isSuperAdmin($user);
    }

    /**
     * Peut supprimer un utilisateur ?
     */
    public function delete(User $user, User $model): bool
    {
        // On ne peut pas se supprimer soi-même
        if ($user->id === $model->id) {
            return false;
        }

        return $this->isSuperAdmin($user);
    }

    /**
     * Action groupée de suppression (Filament Bulk Delete)
     */
    public function deleteAny(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function restore(User $user, User $model): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $this->isSuperAdmin($user);
    }
}