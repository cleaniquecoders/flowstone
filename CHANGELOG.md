# Changelog

All notable changes to `laravel-worklfow` will be documented in this file.

## First Release - 2025-10-23

### Release Notes - Flowstone v1.0.0

#### 🎉 Initial Release

We're excited to announce the first stable release of **Flowstone**, a powerful Laravel package that brings the robust Symfony Workflow engine to your Laravel applications.

#### ✨ What's Included

##### Core Features

- **Database-Driven Workflows** - Configure and manage workflows through your database
- **Symfony Workflow Integration** - Built on the proven Symfony Workflow component
- **Role-Based Permissions** - Control transitions with metadata-driven role management
- **Predefined Status Enum** - Ready-to-use workflow states (Draft, Pending, Approved, etc.)
- **Performance Optimized** - Built-in caching and efficient database queries
- **Multiple Workflow Types** - Support for both State Machines and Workflows

##### Laravel Integration

- **Trait-Based Implementation** - Easy integration with `InteractsWithWorkflow` trait
- **Service Provider** - Auto-registered for seamless Laravel integration
- **Database Migrations** - Ready-to-use migration stubs
- **Configuration Publishing** - Customizable configuration files

##### Developer Experience

- **Comprehensive Documentation** - Complete guides and API reference
- **Real-World Examples** - 5 practical examples covering common use cases
- **Full Test Coverage** - Thoroughly tested with Pest framework
- **PSR-4 Compliant** - Following PHP and Laravel standards

#### 📦 Installation

```bash
composer require cleaniquecoders/flowstone

```
#### 🔧 Requirements

- PHP ^8.4
- Laravel ^11.0||^12.0
- Symfony Workflow ^7.3

#### 📚 Documentation

Complete documentation is available in the docs directory, including:

- Installation guide
- Configuration reference
- Usage examples
- API documentation
- Advanced patterns


---

**Full Changelog**: https://github.com/cleaniquecoders/flowstone/commits/v1.0.0
