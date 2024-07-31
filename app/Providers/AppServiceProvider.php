<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Fee;
use App\Policies\AccountPolicy;
use App\Policies\FeePolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Gate::policy(Fee::class, FeePolicy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Fee::class, FeePolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
