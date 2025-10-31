# Flowstone Documentation

Welcome to the comprehensive documentation for **Flowstone** - A Laravel Workflow Engine with Symfony Workflow Integration.

## Table of Contents

1. [Getting Started](01-getting-started/)
   - [Installation](01-getting-started/01-installation.md)
   - [Quick Start](01-getting-started/02-quick-start.md)
2. [Configuration](02-configuration/01-configuration.md)
3. [Usage Guide](03-usage/01-usage-guide.md)
4. [API Reference](04-api/01-api-reference.md)

## Overview

Flowstone is a powerful Laravel package that integrates the robust Symfony Workflow engine into your Laravel applications. It provides:

- **Database-driven workflows** - Configure workflows through the database
- **Predefined status enums** - Ready-to-use workflow states
- **Role-based permissions** - Control who can perform transitions
- **Flexible configuration** - Support for both code and database configuration
- **State machine & workflow types** - Choose the right pattern for your use case
- **Caching support** - Optimized for performance
- **Artisan commands** - Easy workflow management

## Key Features

### 🔄 Workflow Types

- **State Machine**: Single marking, exclusive states
- **Workflow**: Multiple markings, parallel processes

### 📊 Predefined Status Enum

- Draft → Pending → In Progress → Under Review
- Approved / Rejected / On Hold / Cancelled
- Completed / Failed / Paused / Archived

### 🗄️ Database-Driven Configuration

- Store workflow definitions in the database
- Runtime configuration updates
- Multiple workflow types per application

### 🔐 Role-Based Transitions

- Control who can perform specific transitions
- Metadata-driven permission system
- Integration-ready with authorization systems

### ⚡ Performance Optimized

- Workflow configuration caching
- Efficient database queries
- Minimal overhead

## Quick Example

```php
// Define a model with workflow
class Document extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => 'document-approval');
    }
}

// Use the workflow
$document = new Document();
$document->marking = Status::DRAFT->value;

// Check available transitions
$transitions = $document->getEnabledToTransitions();

// Perform transition (through Symfony Workflow)
$workflow = $document->getWorkflow();
$workflow->apply($document, 'submit_for_review');
```

## System Requirements

- PHP ^8.4
- Laravel ^11.0 || ^12.0
- Symfony Workflow ^7.3

## Next Steps

1. Start with [Installation](01-getting-started/01-installation.md) to set up Flowstone in your project
2. Follow the [Quick Start](01-getting-started/02-quick-start.md) guide for immediate use
3. Explore [Examples](../examples/) for common workflow patterns
4. Check [API Reference](04-api/01-api-reference.md) for detailed method documentation

## Support & Community

- **Issues**: [GitHub Issues](https://github.com/cleaniquecoders/flowstone/issues)
- **Discussions**: [GitHub Discussions](https://github.com/cleaniquecoders/flowstone/discussions)
- **Documentation**: This documentation site

---

*Ready to add powerful workflow capabilities to your Laravel application? Let's get started!*
