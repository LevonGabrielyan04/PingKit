<?php

namespace App\Providers;

use App\Contracts\ChunkedRequestProviderInterface;
use App\Contracts\HttpCheckLogRepositoryInterface;
use App\Contracts\MonitorRepositoryInterface;
use App\Models\User;
use App\Repositories\HttpCheckLogRepository;
use App\Repositories\MonitorRepository;
use App\Services\ChunkedRequestProvider;
use App\Support\PasswordDefaults;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MonitorRepositoryInterface::class, MonitorRepository::class);
        $this->app->bind(HttpCheckLogRepositoryInterface::class, HttpCheckLogRepository::class);
        $this->app->bind(ChunkedRequestProviderInterface::class, ChunkedRequestProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configurePulse();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        PasswordDefaults::configure();
    }

    /**
     * Authorize access to the Pulse dashboard.
     */
    protected function configurePulse(): void
    {
        Gate::define('viewPulse', function (?User $user = null): bool {
            $adminEmail = config('app.admin_email');

            return $user !== null
                && is_string($adminEmail)
                && $adminEmail !== ''
                && strcasecmp($user->email, $adminEmail) === 0;
        });
    }
}
