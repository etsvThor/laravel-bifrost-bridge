<?php

namespace EtsvThor\BifrostBridge\Tests;

use EtsvThor\BifrostBridge\BifrostBridgeServiceProvider;
use EtsvThor\BifrostBridge\Tests\Fixtures\Role;
use EtsvThor\BifrostBridge\Tests\Fixtures\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            SocialiteServiceProvider::class,
            PermissionServiceProvider::class,
            BifrostBridgeServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('permission.models.role', Role::class);

        $app['config']->set('bifrost.enabled', true);
        $app['config']->set('bifrost.auth_push_key', 'test-push-key');
        $app['config']->set('bifrost.auth_push_detach_on_remove', true);
        $app['config']->set('bifrost.auto_assign', false);
        $app['config']->set('bifrost.user.model', User::class);
    }

    public function setUpDatabase(?Application $app): void
    {
        (include __DIR__.'/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub')->up();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
