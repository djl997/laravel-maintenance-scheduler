# Laravel Release Scheduler

[![Latest Version on Packagist](https://img.shields.io/packagist/v/djl997/laravel-release-scheduler.svg?style=flat-square)](https://packagist.org/packages/djl997/laravel-release-scheduler)
[![Total Downloads](https://img.shields.io/packagist/dt/djl997/laravel-release-scheduler.svg?style=flat-square)](https://packagist.org/packages/djl997/laravel-release-scheduler)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Laravel Release Scheduler is a package to manage your application versions, schedule releases, generate changelog and make maintenance mode more user friendly. The goal is to inform end-users of the maintenance schedule and release notes. This package will not manage automated releases and/or release scripts and/or automatically publish generated changelogs.

## Features
- Generate scheduled human readable maintenance messages.
- Generate minor or patch version numbers, with one PHP Artisan Command.
- Generate changelog (Semantic Versioning)
- Automatically hook into `php artisan down` and `php artisan up`
- If no releases are scheduled, it will create an unscheduled release automatically.

### Roadmap
Here is a rough roadmap of things to come (not in any specific order):

- [ ] Generate changelog.md file
- [ ] Improve setup
- [x] Connect recalculation to initial version
- [x] Configure initial version
- [x] Add current version to app layout (+how to)
- [x] Configure first version
- [x] Translations (EN, NL, DE)

## Requirements
Laravel Release Scheduler requires PHP 8+ and Laravel 8+.

## Installation
You can use this package in your project via composer:
```bash
composer require djl997/laravel-release-scheduler
```

### Light version
In the light version, you don't need any database tables. Just configure the version via the `config/release-scheduler.php` config file:
```
php artisan vendor:publish --tag=lrs-config
```

### Full version
Publish migration files:
```
php artisan vendor:publish --tag=lrs-migrations
```

Migrate the required database table `release_schedule`:
```bash
php artisan migrate
```

## Usage
> Note! This package is still in development. You are welcome to use this package, but major changes in API can happen. No promises.

### Commands:
```bash
php artisan releases:list # List all releases
php artisan releases:create # Wizard to create and schedule a new release
php artisan releases:delete {releaseID} # Delete one specific release by ID
php artisan releases:recalculate # Recalculate version structure (semver)
```
### Enable Maintenance Mode:
To activate maintenance mode, run the default Laravel command `php artisan down`. Laravel Release Scheduler will search for scheduled releases scoped to that date and activate them. Note if no releases were scheduled, there will automatically be an unscheduled release created and activated.

### Disable Maintenance Mode:
To deactivate maintenance mode, run `php artisan up`. Laravel Release Scheduler will complete the active release and make it available for a changelog. You can copy-paste this for example to Github.

### Show maintenance message:
```php
use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;

$message = ReleaseSchedule::getMaintenanceMessage();
```

### Show current version:
```php
use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;

$version = ReleaseSchedule::getCurrentVersion();
```
The value is cached in Laravel Cache to prevent unnecessary queries to the database. If the incorrect version is showed you can try to run `php artisan cache:clear` or `php artisan optimize:clear` and check if it will work after that.

## Custom Configuration
If you want to change the [default config](config/config.php) you can publish the config file:
```
php artisan vendor:publish --tag=lrs-config
```

## Events
Laravel Release Scheduler doesn't dispatch it's own events. In stead we hook into the default Laravel Artisan Events: `MaintenanceModeEnabled` and `MaintenanceModeDisabled`. Of course you can do this too.

In addition, you can observe the ReleaseSchedule model in your application's `App\Providers\EventServiceProvider` class:

```php
use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;

ReleaseSchedule::observe(YourObserver::class);
```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

This package is in active development, ideas or improvements are welcome.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.