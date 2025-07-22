# A data extractor based on models and it's relationship

[![Latest Version on Packagist](https://img.shields.io/packagist/v/naimsolong/laravel-data-extractor.svg?style=flat-square)](https://packagist.org/packages/naimsolong/laravel-data-extractor)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/naimsolong/laravel-data-extractor/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/naimsolong/laravel-data-extractor/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/naimsolong/laravel-data-extractor/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/naimsolong/laravel-data-extractor/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/naimsolong/laravel-data-extractor.svg?style=flat-square)](https://packagist.org/packages/naimsolong/laravel-data-extractor)

A Laravel package for extracting data from Eloquent models and their relationships with flexible configuration options. This package provides a simple and intuitive way to extract structured data from your models, making it perfect for API responses, data exports, or any scenario where you need to transform model data into a specific format.

## What It Does

The extractor leverages Laravel's relationship system to automatically include related data based on your configuration, while providing fine-grained control over which fields are included or excluded from the extraction process.

You can use the available options inside config file:

```php
use NaimSolong\DataExtractor\Extract;

// Option
(new Extract)
  ->option('User')
  ->queryId(4)
  ->toSql();

// Source
(new Extract)
  ->source('session')
  ->queryId(3)
  ->toSql();
```

Or you can use model that you have queried:

```php
use NaimSolong\DataExtractor\Extract;
use App\Models\User;

// Extract directly
(new Extract)
  ->toSql(
    User::get()
  );
```

## Installation

You can install the package via composer:

```bash
composer require naimsolong/laravel-data-extractor
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="data-extractor-config"
```

This is the contents of the published config file:

```php
return [
    'is_enabled' => env('DATA_EXTRACTOR_ENABLED', false),

    'options' => [
        [
            'name' => 'Default',
            'description' => 'Extra all user data',
            'format' => 'sql',
            'source' => 'default',
        ],
    ],

    'source' => [
        'default' => [
            'connection' => 'mysql',
            'model' => User::class,
            'relationships' => [
                'mainProfile',
            ],
        ],
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Amirul Naim](https://github.com/naimsolong)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
