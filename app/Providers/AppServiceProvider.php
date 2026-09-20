<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
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
        RedirectIfAuthenticated::redirectUsing(static function ($request): string {
            $user = $request->user();

            if ($user && $user->isAdmin()) {
                return route('admin.dashboard');
            }

            if ($user && $user->isEmployee()) {
                return route('employee.dashboard');
            }

            return route('login');
        });
    }
}
