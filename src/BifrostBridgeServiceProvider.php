<?php
namespace EtsvThor\BifrostBridge;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\LaravelPassport\LaravelPassportExtendSocialite;

class BifrostBridgeServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->booting(function () {
            Event::listen(SocialiteWasCalled::class, LaravelPassportExtendSocialite::class);
        });
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
    }

    protected function bootConfig(): void
    {
        // Merge configs
        $this->mergeConfigFrom(__DIR__.'/../config/bifrost.php', 'bifrost');
        $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');
    }

    protected function bootRoutes(): void
    {
        // Register routes
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
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
            Request::macro('verifySignature', function(string $key, string $header = 'X-Signature', string $algo = 'sha256'): bool {
                /** @var \Illuminate\Http\Request $this */

                return ($this->hasHeader($header) && $this->header($header) === hash_hmac($algo, $this->getContent(), $key));
            });
        }
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
