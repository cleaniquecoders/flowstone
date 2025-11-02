# Changelog

All notable changes to `laravel-worklfow` will be documented in this file.

## Added Field Group, Category and Tag - 2025-11-02

### Flowstone v1.1.1 Release Notes

- Documentation: cleaned up and reorganized usage docs; fixed file numbering and adopted consistent kebab-case filenames.
- Screenshots: renamed to match UI labels and updated all references in docs.
- README: refreshed links and screenshot reference.
- Update Workflow model to use `InteractsWithTags` Trait.
- Added `group`, `category` and `tags` field

No breaking changes. No runtime code changes. Safe to update.

## Added UI for Managing Workflows - 2025-11-01

### Flowstone v1.1.0 Release Notes

#### 🎉 What's New

##### Flowstone UI (Admin Panel)

<img width="1238" height="446" alt="Screenshot 2025-11-01 at 11 37 08 PM" src="https://github.com/user-attachments/assets/3b18d0e0-506e-433a-a1b2-14ab05451b62" />
<img width="1234" height="919" alt="Screenshot 2025-11-01 at 11 37 17 PM" src="https://github.com/user-attachments/assets/67a489ee-e168-4287-af4d-42394214fca8" />
<img width="1229" height="952" alt="Screenshot 2025-11-01 at 11 37 30 PM" src="https://github.com/user-attachments/assets/1ef9036d-c918-4308-a3bb-fb04bc59c56e" />
<img width="1225" height="966" alt="Screenshot 2025-11-01 at 11 37 43 PM" src="https://github.com/user-attachments/assets/a0052fcc-4af9-4e2e-a43b-917327e6f239" />
<img width="1207" height="943" alt="Screenshot 2025-11-01 at 11 37 57 PM" src="https://github.com/user-attachments/assets/0ced9074-2d8a-47f5-b61d-34f0fd54c8a7" />
<img width="940" height="641" alt="Screenshot 2025-11-01 at 11 38 08 PM" src="https://github.com/user-attachments/assets/e1aabde8-b4b9-43df-a40d-609979012039" />
<img width="947" height="637" alt="Screenshot 2025-11-01 at 11 38 20 PM" src="https://github.com/user-attachments/assets/f8759822-d35d-4810-a67b-9324f0c4433e" />
- **Visual Workflow Designer** - Interactive workflow visualization powered by React Flow
- **Livewire Integration** - Built-in Livewire components for workflow management:
  - `Dashboard` - Overview of all workflows
  - `WorkflowIndex` - Browse and search workflows
  - `WorkflowShow` - View workflow details
  - `CreateWorkflow` - Create new workflows
  - `EditWorkflow` - Edit existing workflows
  - Metadata management components for places, transitions, and workflows
- **Modern UI Components** - Beautiful Blade components with Tailwind CSS styling
- **Dashboard Route** - New `/flowstone/dashboard` route for workflow management

##### Enhanced Workflow Schema

- **Designer Column** - New `designer` JSON column in workflows table for storing visual layout data
- **Visual Configuration** - Store node positions and graph metadata for the UI designer

##### Developer Experience

- **Asset Publishing** - New command to publish UI assets: `php artisan flowstone:publish-assets`
- **Build Configuration** - Vite setup for frontend asset compilation
- **React Integration** - UMD bundle with React Flow for visual workflow editing

#### 📦 Installation

Update your composer dependencies:

```bash
composer require cleaniquecoders/flowstone:^1.1.0


```
#### 🔧 Migration

If upgrading from v1.0.0, publish and run the new migration:

```bash
php artisan vendor:publish --tag=flowstone-migrations
php artisan migrate


```
#### 🎨 UI Setup

To use the Flowstone UI, publish the frontend assets:

```bash
php artisan flowstone:publish-assets


```
Access the dashboard at: `http://your-app.test/flowstone/dashboard`

#### 📚 Documentation

New documentation added:

- Flowstone UI Guide - Complete UI setup and usage

#### 🔧 Requirements

- PHP ^8.4
- Laravel ^11.0||^12.0
- Livewire ^3.6 (for UI features)
- Node.js 18+ (for building assets)


---

**Full Changelog**: https://github.com/cleaniquecoders/flowstone/compare/v1.0.0...v1.1.0

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
