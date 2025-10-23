# Flowstone

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/flowstone.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/flowstone) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/flowstone/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/flowstone/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/flowstone/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/flowstone/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/flowstone.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/flowstone)

**Flowstone** is a powerful Laravel package that integrates the robust **Symfony Workflow** engine into your Laravel applications. Build sophisticated workflow and state machine systems with database-driven configurations, role-based permissions, and seamless Laravel integration.

## 🚀 Key Features

- **🔄 Database-Driven Workflows** - Configure workflows through the database for runtime flexibility
- **🏛️ Symfony Workflow Integration** - Built on the proven Symfony Workflow component
- **👥 Role-Based Permissions** - Control who can perform transitions with metadata-driven roles
- **📊 Predefined Status Enum** - Ready-to-use workflow states (Draft, Pending, Approved, etc.)
- **⚡ Performance Optimized** - Workflow configuration caching and efficient queries
- **🎯 Multiple Workflow Types** - Support for both State Machines and Workflows

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
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

## Quick Start

### 1. Create a Workflow-Enabled Model

```php
<?php

namespace App\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use CleaniqueCoders\Flowstone\Enums\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Document extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = ['title', 'content', 'status', 'author_id'];

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => 'document-approval');
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }

    public function getMarking(): string
    {
        return $this->status ?? Status::DRAFT->value;
    }

    public function setMarking(string $marking): void
    {
        $this->status = $marking;
    }
}
```

### 2. Configure Your Workflow

Create a workflow configuration in the database:

```php
use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;

// Create workflow
$workflow = Workflow::create([
    'name' => 'document-approval',
    'type' => 'state_machine',
    'initial_marking' => 'draft',
]);

// Add places (states)
foreach (['draft', 'review', 'approved', 'rejected'] as $place) {
    WorkflowPlace::create([
        'workflow_id' => $workflow->id,
        'name' => $place,
    ]);
}

// Add transitions
WorkflowTransition::create([
    'workflow_id' => $workflow->id,
    'name' => 'submit',
    'from_place' => 'draft',
    'to_place' => 'review',
]);
```

### 3. Use the Workflow

```php
// Create a document
$document = Document::create([
    'title' => 'My Document',
    'content' => 'Document content...',
    'status' => 'draft',
]);

// Get available transitions
$transitions = $document->getEnabledToTransitions();
// Returns: ['review' => 'Review']

// Apply a transition
$workflow = $document->getWorkflow();
if ($workflow->can($document, 'submit')) {
    $workflow->apply($document, 'submit');
    $document->save(); // Now status is 'review'
}
```

## Documentation

Comprehensive documentation is available in the [`docs/`](docs/) directory:

- **[Installation Guide](docs/installation.md)** - Detailed setup instructions
- **[Configuration Reference](docs/configuration.md)** - Complete configuration options
- **[Quick Start Guide](docs/quick-start.md)** - Get up and running quickly
- **[Usage Guide](docs/usage-guide.md)** - Detailed usage instructions
- **[API Reference](docs/api-reference.md)** - Complete API documentation
- **[Database Workflows](docs/database-workflows.md)** - Database-driven configurations
- **[Advanced Usage](docs/advanced-usage.md)** - Complex scenarios and patterns

## Examples

Real-world examples are available in the [`examples/`](examples/) directory:

1. **[Document Approval](examples/document-approval/)** - Classic approval process with roles
2. **[E-commerce Orders](examples/ecommerce-order/)** - Order lifecycle management
3. **[Content Publishing](examples/content-publishing/)** - Editorial workflow with scheduling
4. **[Bug Tracking](examples/bug-tracking/)** - Issue management for development teams
5. **[Employee Onboarding](examples/employee-onboarding/)** - HR workflow with multi-department coordination

Each example includes complete implementation with models, controllers, views, and tests.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
