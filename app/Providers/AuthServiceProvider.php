<?php

namespace App\Providers;

use App\Models\Article;
use App\Policies\ArticlePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Article::class => ArticlePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register Gates for role-based permissions
        
        // View all users: admin only
        Gate::define('view-all-users', function ($user) {
            return $user->hasRole('admin');
        });

        // Assign roles: admin only
        Gate::define('assign-roles', function ($user) {
            return $user->hasRole('admin');
        });

        // Create article: author only
        Gate::define('create-article', function ($user) {
            return $user->hasRole('author');
        });

        // Edit own article: author only (ownership check in policy)
        Gate::define('update-article', function ($user, $article) {
            return $user->hasRole('author') && $article->author_id === $user->id;
        });

        // Publish article: editor and admin
        Gate::define('publish-article', function ($user) {
            return $user->hasRole('editor') || $user->hasRole('admin');
        });

        // Delete article: admin only
        Gate::define('delete-article', function ($user) {
            return $user->hasRole('admin');
        });

        // View published articles: all authenticated users (handled in controller)
        Gate::define('view-published', function ($user) {
            return true; // All authenticated users
        });

        // View own articles: author (handled in controller)
        Gate::define('view-own-articles', function ($user) {
            return $user->hasRole('author');
        });
    }
}
