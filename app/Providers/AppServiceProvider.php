<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('categories')) {
            $categories = Category::all();
            View::share(['categories' => $categories]);
        }
        if (Schema::hasTable('tags')) {
            $tags = Tag::all();
            View::share(['tags' => $tags]);
        }

        RateLimiter::for('global', fn(Request $request) => Limit::perMinute(120)->by($request->ip()));
        RateLimiter::for('strict', fn(Request $request) => Limit::perMinute(5)->by($request->ip()));

        Event::listen(Login::class, function (Login $event) {
            if (!$event->user instanceof User) {
                return;
            }

            Log::info(
                'AUTH_LOGIN -' 
                . ' User ID: ' . $event->user->id 
                . ' with email: ' . $event->user->email 
                . ' from IP: ' . request()->ip() 
                . ' using User Agent: ' . request()->userAgent() 
                . ' logged in.'
            );
        });

        Event::listen(Registered::class, function (Registered $event) {
            if (!$event->user instanceof User) {
                return;
            }

            Log::info(
                'AUTH_REGISTER -' 
                . ' User ID: ' . $event->user->id 
                . ' with email: ' . $event->user->email 
                . ' from IP: ' . request()->ip() 
                . ' using User Agent: ' . request()->userAgent() 
                . ' registered.'
            );

        });

        Event::listen(Logout::class, function (Logout $event) {
            if (!$event->user instanceof User) {
                return;
            }

            Log::info(
                'AUTH_LOGOUT -' 
                . ' User ID: ' . $event->user->id 
                . ' with email: ' . $event->user->email 
                . ' from IP: ' . request()->ip() 
                . ' using User Agent: ' . request()->userAgent() 
                . ' logged out.'
            );
        });

        Event::listen(Failed::class, function (Failed $event) {
            Log::critical(
                'AUTH_FAILED -'
                . ' Attempted login with email: ' . $event->credentials['email'] 
                . ' from IP: ' . request()->ip() 
                . ' using User Agent: ' . request()->userAgent() 
                . ' failed to log in.'
            );
        });
    }
}
