<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity;

use AndyDefer\LaravelActivity\Contracts\Repositories\ActivityRepositoryInterface;
use AndyDefer\LaravelActivity\Contracts\Services\ActivityServiceInterface;
use AndyDefer\LaravelActivity\Repositories\ActivityRepository;
use AndyDefer\LaravelActivity\Services\ActivityService;
use Illuminate\Support\ServiceProvider;

final class ActivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/activity.php',
            'activity',
        );

        $this->registerRepository();
        $this->registerService();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->publishes([
                __DIR__.'/../config/activity.php' => config_path('activity.php'),
            ], 'activity-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'activity-migrations');
        }
    }

    /**
     * Register the activity repository and its contract binding.
     */
    private function registerRepository(): void
    {
        $this->app->singleton(ActivityRepository::class, function (): ActivityRepository {
            return new ActivityRepository;
        });

        $this->app->bind(ActivityRepositoryInterface::class, ActivityRepository::class);
    }

    /**
     * Register the activity service and its contract binding.
     */
    private function registerService(): void
    {
        $this->app->singleton(ActivityService::class, function ($app): ActivityService {
            return new ActivityService(
                activityRepository: $app->make(ActivityRepositoryInterface::class),
            );
        });

        $this->app->bind(ActivityServiceInterface::class, ActivityService::class);
    }
}
