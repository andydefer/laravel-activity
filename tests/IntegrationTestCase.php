<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Tests;

use AndyDefer\LaravelActivity\ActivityServiceProvider;
use AndyDefer\LaravelActivity\Enums\ActivityType;
use AndyDefer\Repository\RepositoryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class IntegrationTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runMigrations();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \Mockery::close();
    }

    protected function getPackageProviders($app): array
    {
        return [
            RepositoryServiceProvider::class,
            ActivityServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('repository.enum_casts', [
            'activities' => [
                'activity_type' => ActivityType::class,
            ],
        ]);
    }

    protected function runMigrations(): void
    {
        $migrationPaths = [
            __DIR__.'/../database/migrations',
            __DIR__.'/Fixtures/database/migrations',
        ];

        foreach ($migrationPaths as $path) {
            if (is_dir($path)) {
                $this->loadMigrationsFrom($path);
            }
        }
    }
}
