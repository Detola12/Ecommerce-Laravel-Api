<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function create(User $user)
    {
        return $user->is_admin || $user->is_merchant;
    }

    public function update(User $user)
    {
        return $user->is_admin || $user->is_merchant;
    }

    public function delete(User $user)
    {
        return $user->is_admin || $user->is_merchant;
    }
}
