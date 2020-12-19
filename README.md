# laravel-bifrost-bridge
[![Latest Version on Packagist](https://img.shields.io/packagist/v/etsvthor/laravel-bifrost-bridge.svg?style=flat-square)](https://packagist.org/packages/etsvthor/laravel-bifrost-bridge)

Connect a laravel application with the Bifrost

## Installation

You can then install the package via composer:

```bash
composer require etsvthor/laravel-bifrost-bridge
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="EtsvThor\\BifrostBridge\\BifrostBridgeServiceProvider" --tag="bifrost-config"
```

## Environment
Add the following to your `.env` file and fill them in:
```php
# Required configuration
BIFROST_ENABLED=true
BIFROST_CLIENT_ID=
BIFROST_CLIENT_SECRET=
BIFROST_AUTH_PUSH_KEY=

# Optional configuration with its defaults
BIFROST_REDIRECT_URL="/login/callback"
BIFROST_HOST="https://bifrost.thor.edu"
BIFROST_ROUTE_PREFIX=
```

## Configuration
In the configuration file, one can specify some thing about the user model, but have some sensible defaults

See [config/bifrost.php](config/bifrost.php) for all options.

### Resolvers
The `User` and `Role` model can be resolved using a custom resolver

```php
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model as EloquentModel;

// Default behaviour
BifrostBridge::resolveUserClassUsing(function(/* auto injection works here */): EloquentModel {
    return app('App\\Models\\User');
});

// Default behaviour
BifrostBridge::resolveRoleClassUsing(function(/* auto injection works here */): Role {
    return app(PermissionRegistrar::class)->getRoleClass();
});

// Disabled role sync
BifrostBridge::resolveRoleClassUsing(fn() => null);

// If the system does not get an email, a user is retrieved using this method
BifrostBridge::resolveUserWithoutEmailUsing(function(/* auto injection works here */, BifrostUserData $data): ?EloquentModel {
    return null; // when null is returned, the user is not logged in
})
```