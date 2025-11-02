# Flowstone

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/flowstone.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/flowstone) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/flowstone/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/flowstone/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/flowstone/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/flowstone/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/flowstone.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/flowstone)

**Flowstone** is a powerful Laravel package that integrates the robust **Symfony Workflow** engine into your Laravel applications. Build sophisticated workflow and state machine systems with database-driven configurations, role-based permissions, and seamless Laravel integration.

![Workflow Designer — Document Approval](screenshots/workflow-designer-document-approval.png)

## 🚀 Key Features

- **🔄 Database-Driven Workflows** - Configure workflows through the database for runtime flexibility
- **🏛️ Symfony Workflow Integration** - Built on the proven Symfony Workflow component
- **👥 Role-Based Permissions** - Control who can perform transitions with metadata-driven roles
- **📊 Predefined Status Enum** - Ready-to-use workflow states (Draft, Pending, Approved, etc.)
- **⚡ Performance Optimized** - Workflow configuration caching and efficient queries
- **🎯 Multiple Workflow Types** - Support for both State Machines and Workflows

## Table of Contents

- [Installation](#installation)
- [Documentation](#documentation)
- [Examples](#examples)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Installation

Install Flowstone via Composer:

```bash
composer require cleaniquecoders/flowstone
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="flowstone-migrations"
php artisan migrate
```

Optionally, publish the configuration file:

```bash
php artisan vendor:publish --tag="flowstone-config"
```

## Documentation

Comprehensive documentation is available in the [`docs/`](docs/) directory:

- **[Getting Started](docs/01-getting-started/)** - Installation and quick start guide
- **[Configuration](docs/02-configuration/01-configuration.md)** - Configure workflows, UI, and performance
- **[Usage Guide](docs/03-usage/)** - Workflows, details, and visual designer
- **[API Reference](docs/04-api/01-api-reference.md)** - Complete API documentation

## Examples

Real-world examples are available in the [`examples/`](examples/) directory:

- **[Bug Tracking](examples/bug-tracking/)** - Issue lifecycle management
- **[Content Publishing](examples/content-publishing/)** - Content approval workflow
- **[Document Approval](examples/document-approval/)** - Multi-step approval process
- **[E-commerce Order](examples/ecommerce-order/)** - Order fulfillment workflow
- **[Employee Onboarding](examples/employee-onboarding/)** - New hire process

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
