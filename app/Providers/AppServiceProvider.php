<?php

namespace App\Providers;

use App\Events\TransactionOrTransferProcessed;
use App\Events\TransactionProcessed;
use App\Events\TransferCompleted;
use App\Listeners\DeductTransactionFee;
use App\Listeners\FeeDeducted;
use App\Models\Account;
use App\Models\Fee;
use App\Policies\AccountPolicy;
use App\Policies\FeePolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
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
        Event::listen(
            TransactionProcessed::class,
            FeeDeducted::class,
        );

        Event::listen(
            TransferCompleted::class,
            FeeDeducted::class,
        );

        Gate::policy(Fee::class, FeePolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
