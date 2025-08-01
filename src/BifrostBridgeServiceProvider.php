<?php

namespace EtsvThor\BifrostBridge;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

class BifrostBridgeServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->bootConfig();
        $this->bootRoutes();
        $this->bootMacros();
        $this->bootSocialite();
    }

    protected function bootConfig(): void
    {
        // Merge configs
        $this->mergeConfigFrom(__DIR__.'/../config/bifrost.php', 'bifrost');
    }

    protected function bootRoutes(): void
    {
        // Register routes
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        if (
            version_compare($this->app->version(), '11.0', '>=')
            && method_exists(VerifyCsrfToken::class, 'except') // @phpstan-ignore function.alreadyNarrowedType
        ) {
            VerifyCsrfToken::except([
                'webhooks/bifrost',
            ]);
        }
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('bifrost.route_prefix'),
            'middleware' => 'web',
        ];
    }

    protected function bootMacros(): void
    {
        if (! Request::hasMacro('verifySignature')) {
            Request::macro('verifySignature', function (string $key, string $header = 'X-Signature', string $algo = 'sha256'): bool {
                /** @var \Illuminate\Http\Request $this */

                return $this->hasHeader($header) && $this->header($header) === hash_hmac($algo, $this->getContent(), $key);
            });
        }
    }

    protected function bootSocialite(): void
    {
        /** @var \Laravel\Socialite\SocialiteManager */
        $socialite = $this->app->make(SocialiteFactory::class);

        $socialite->extend(
            'bifrost',
            function () use ($socialite) {
                /** @var \Laravel\Socialite\SocialiteManager $socialite */
                return $socialite->buildProvider(BifrostSocialiteProvider::class, config('bifrost.service'));
            }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [BifrostBridge::class, 'bifrost-bridge'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/bifrost.php' => config_path('bifrost.php'),
        ], 'bifrost-config');
    }
}
