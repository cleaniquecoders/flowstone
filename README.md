# Flowstone

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/flowstone.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/flowstone)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/flowstone/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/flowstone/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/flowstone/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/flowstone/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/flowstone.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/flowstone)

**Flowstone** - A Laravel Workflow Engine with Symfony Workflow Integration

## Installation

You can install the package via composer:

```bash
composer require cleaniquecoders/flowstone
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="flowstone-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="flowstone-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="flowstone-views"
```

## Usage

```php
$flowstone = new CleaniqueCoders\Flowstone();
echo $flowstone->echoPhrase('Hello, CleaniqueCoders!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
