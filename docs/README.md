# Flowstone Documentation

Welcome to the comprehensive documentation for **Flowstone** - A Laravel Workflow Engine with Symfony Workflow Integration.

## Table of Contents

1. **[Getting Started](01-getting-started/)**
   - [Installation](01-getting-started/01-installation.md)
   - [Quick Start](01-getting-started/02-quick-start.md)
2. **[Configuration](02-configuration/01-configuration.md)**
   - Workflow configuration options
   - UI settings and authorization
   - Performance optimization
3. **[Usage Guide](03-usage/)**
   - [Workflows](03-usage/01-workflows.md) - Core workflow implementation & dashboard
   - [Workflow Details](03-usage/02-workflow-details.md) - Inspect and manage a workflow
   - [Workflow Designer](03-usage/03-workflow-designer.md) - Drag-and-drop visual builder
4. **[API Reference](04-api/01-api-reference.md)**
   - Complete API documentation
   - Models, traits, and helpers

## Overview

Flowstone is a powerful Laravel package that integrates the robust Symfony Workflow engine into your Laravel applications. It provides:

- **Database-driven workflows** - Configure workflows through the database with dynamic places and transitions
- **Predefined status enums** - Ready-to-use workflow states (Draft, Pending, Approved, etc.)
- **Role-based permissions** - Control who can perform transitions using metadata
- **Flexible configuration** - Support for both code and database configuration
- **State machine & workflow types** - Choose the right pattern for your use case
- **Visual Workflow Designer** - Interactive UI for creating and managing workflows
- **Workflow Organization** - Group, categorize, and tag workflows for better management
- **Livewire Components** - Pre-built UI components for workflow management
- **Performance optimized** - Workflow configuration caching and efficient queries
- **Artisan commands** - Easy workflow management via CLI

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
- Configure roles per transition for fine-grained access control

### 🎨 Visual Workflow Designer

- Interactive workflow builder with drag-and-drop interface
- React Flow-based graph visualization
- Real-time workflow preview and testing
- Export and import workflow definitions

### 📁 Workflow Organization

- **Groups**: Organize by department or team (finance, hr, operations)
- **Categories**: Classify by function or domain (approval, e-commerce)
- **Tags**: Add flexible labels for cross-cutting concerns
- Advanced filtering and search capabilities

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

## Quick Links

- 📦 [Installation Guide](01-getting-started/01-installation.md) - Get started in 5 minutes
- 🚀 [Quick Start](01-getting-started/02-quick-start.md) - Your first workflow
- ⚙️ [Configuration](02-configuration/01-configuration.md) - Customize Flowstone
- 📖 [Workflows](03-usage/01-workflows.md) - Detailed implementation
- 🎨 [Workflow Designer](03-usage/03-workflow-designer.md) - Drag-and-drop workflow builder
- 📁 [Workflow Organization](03-usage/02-workflow-organization.md) - Organize workflows
- 🔧 [API Reference](04-api/01-api-reference.md) - Full API documentation

## What's New in Flowstone

- ✨ **Visual Workflow Designer** - React Flow-based interactive workflow builder
- 📁 **Workflow Organization** - Group, categorize, and tag workflows
- 🎨 **Livewire Components** - Pre-built UI components
- 🔍 **Advanced Querying** - Filter by groups, categories, and tags
- 🖥️ **Enhanced UI** - Telescope-like admin interface
- 💾 **Designer Column** - Store visual workflow designs

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
