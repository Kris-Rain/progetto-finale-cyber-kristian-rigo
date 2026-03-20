<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Solo l'utente stesso può aggiornare il proprio profilo
     */
    public function update(User $user, User $target): bool
    {
        return $user->is($target);
    }

}
