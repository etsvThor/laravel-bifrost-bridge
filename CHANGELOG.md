# Changelog
## 0.5.7
- Dispatch `BifrostLogin` event on succesfull login

## 0.5.6
- Upgrade to Laravel 9
- Remove support for Laravel 7 (EOL)

## 0.5.3 till 0.5.5 - 2021-08-14
- Upgrade to PHP 8

## 0.5.2 - 2021-08-02
- Redirect to intended after login

## 0.5.1 - 2021-08-01
- Bug fixed where user_id=1 did not exist

## 0.5.0 - 2021-05-31
- `auto_assigned` boolean added to exclude syncing manual changes

## 0.4.0 - 2021-01-02

### Added
- scope support
- remember me support

## 0.3.0 - 2020-12-22

### Added
- bifrost socialite provider

### Removed
- laravelpassport socialite provider

## 0.2.0 - 2020-12-21

### Added
- Way to override user resolving

### Changed
- Align multi e-email support with implemented version
- Update resolving of user to bridge object
- Only registers webhook when a push key is set

### Removed
- ResolveUsersWithoutEmail from bridge object

## 0.1.4 - 2020-12-20
- Multiple e-mail addresses support

## 0.1.3 - 2020-12-20
- Notification is shown if the user is logged in locally when bifrost is disabled

## 0.1.2 - 2020-12-19

### Changed
- Fixed BindingResolutionException

## 0.1.1 - 2020-12-19

### Changed
- Fixed typo

## 0.1.0 - 2020-12-19

### Added
- Initial release
