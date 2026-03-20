<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Chiunque può visualizzare articoli accettati
     */
    public function view(User $user, Article $article): bool
    {
        return $article->is_accepted || $user;
    }

    /**
     * Solo writer/super admin possono creare articoli
     */
    public function create(User $user): bool
    {
        return $user->is_writer;
    }

    /**
     * Solo il writer dell'articolo può modificarlo
     */
    public function update(User $user, Article $article): bool
    {
        return $user->is($article->user);
    }

    /**
     * Solo il writer dell'articolo può eliminarlo
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->is($article->user);
    }

    /**
     * Solo revisor/super admin possono accettare o rifiutare articoli
     */
    public function accept_reject(User $user, Article $article): bool
    {
        return $user->is_revisor && !$article->is_accepted;
    }
}
