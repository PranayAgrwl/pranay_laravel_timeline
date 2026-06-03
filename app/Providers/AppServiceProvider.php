<?php

namespace App\Providers;

use App\Listeners\SendLoginNotificationEmail;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
        // --- Event listeners ----------------------------------------------
        // Explicit registration (vs. auto-discovery) so the wiring is easy to
        // find with a single grep across the codebase.
        Event::listen(Login::class, SendLoginNotificationEmail::class);
    }
}
