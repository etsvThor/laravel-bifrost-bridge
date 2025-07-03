# laravel-bifrost-bridge
[![Latest Version on Packagist](https://img.shields.io/packagist/v/etsvthor/laravel-bifrost-bridge.svg?style=flat-square)](https://packagist.org/packages/etsvthor/laravel-bifrost-bridge)

Connect a laravel application with the Bifrost

## Installation

First, install [spatie/laravel-permission](https://github.com/spatie/laravel-permission), follow their installation guide

You can then install the package via composer:

```bash
composer require etsvthor/laravel-bifrost-bridge
```

You can publish the config file of bifrost and the underlying spatie permissions config+migrations with:
```bash
php artisan vendor:publish --provider="EtsvThor\\BifrostBridge\\BifrostBridgeServiceProvider" --tag="bifrost-config"
```

- Ensure the `users` has a `oauth_user_id` and `email_verified_at` column.
- In the `User` model, cast `email_verified_at` to `datetime` and add the `HasRoles` trait.
- (only laravel <11) Please add `'webhooks/bifrost'` to the CSRF exceptions in `App\Http\Middleware\VerifyCsrfToken` class

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

Make sure to seed all required roles, otherwise they will not sync

### Resolvers
The `User` and `Role` model can be resolved using a custom resolver

```php
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model as EloquentModel;

// Default behaviour
BifrostBridge::resolveUserClassUsing(function(/* auto injection works here */): EloquentModel {
    return app(config('bifrost.user.model', 'App\\Models\\User'));
});

// Default behaviour
BifrostBridge::resolveRoleClassUsing(function(/* auto injection works here */): Role {
    return app(PermissionRegistrar::class)->getRoleClass();
});

// Disabled role sync
BifrostBridge::resolveRoleClassUsing(fn() => null);

// Override the way a user is resolved
BifrostBridge::resolveAndUpdateUserUsing(function(/* auto injection works here */, BifrostUserData $data): ?EloquentModel {
    // Model should implement \Illuminate\Contracts\Auth\Authenticatable

    return null; // when null is returned, the user is not logged in
})
```

### CSRF (laravel <11 only)
Don't forget to add `'webhooks/bifrost'` to the `$except` array in `App\Http\Middleware\VerifyCsrfToken.php`. 
