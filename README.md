# Laravel Release Scheduler

[![Latest Version on Packagist](https://img.shields.io/packagist/v/djl997/laravel-release-scheduler.svg?style=flat-square)](https://packagist.org/packages/djl997/laravel-release-scheduler)
[![Total Downloads](https://img.shields.io/packagist/dt/djl997/laravel-release-scheduler.svg?style=flat-square)](https://packagist.org/packages/djl997/laravel-release-scheduler)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Laravel Release Scheduler is a package to manage your application version, release schedule, changelog and maintenance mode. The goal is to inform end-users of the maintenance schedule and release notes. This package is NOT created to manage automated releases and/or release scripts. 

## Features

- Generate scheduled human readable maintenance messages.
- Generate minor or patch version numbers. with one PHP Artisan Command.
- Generate changelog (Semantic Versioning)
- Automatically hook into `php artisan down` and `php artisan up`

## Requirements
Laravel Release Scheduler requires PHP 8+ and Laravel 8+.

## Installation
You can use this package in your project via composer:
```bash
composer require djl997/laravel-release-scheduler
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
```
### Generate maintenance message:
```php
use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;

$message = ReleaseSchedule::getMaintenanceMessage();
```

## Contributing

This package is in active development, ideas are welcome.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.