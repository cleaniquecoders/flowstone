# Installation

This guide will walk you through installing Flowstone in your Laravel application.

## Requirements

Before installing Flowstone, ensure your system meets these requirements:

- **PHP**: ^8.2 (PHP 8.2, 8.3, or 8.4)
- **Laravel**: ^9.0 || ^10.0 || ^11.0 || ^12.0
- **Database**: MySQL, PostgreSQL, SQLite, or SQL Server
- **Composer**: Latest version recommended

### Version Compatibility Matrix

| Flowstone | PHP Version | Laravel Version | Symfony Workflow | Livewire |
|-----------|-------------|-----------------|------------------|----------|
| 1.3.1+    | 8.2 - 8.4   | 9.x - 12.x      | 6.4 - 7.x        | 3.0+     |
| 1.3.0     | 8.4         | 11.x - 12.x     | 7.3+             | 3.6+     |

**Recommended Combinations:**
- Laravel 9.x: PHP 8.2, Symfony 6.4, Livewire 3.0+
- Laravel 10.x: PHP 8.2+, Symfony 6.4/7.0, Livewire 3.0+
- Laravel 11.x: PHP 8.2+, Symfony 7.0+, Livewire 3.0+
- Laravel 12.x: PHP 8.2+, Symfony 7.0+, Livewire 3.0+

## Installation Steps

### 1. Install via Composer

Install Flowstone using Composer:

```bash
composer require cleaniquecoders/flowstone
```

### 2. Publish and Run Migrations

Publish the migration files:

```bash
php artisan vendor:publish --tag="flowstone-migrations"
```

Run the migrations to create the workflow tables:

```bash
php artisan migrate
```

This will create three tables:

- `workflows` - Main workflow definitions
- `workflow_places` - Workflow states/places
- `workflow_transitions` - Allowed state transitions

### 3. Publish Configuration (Optional)

Publish the configuration file to customize Flowstone:

```bash
php artisan vendor:publish --tag="flowstone-config"
```

This creates `config/flowstone.php` where you can:

- Define default workflow configurations
- Set up custom workflows
- Configure auto-discovery settings
- Customize marking store options
- Configure the Flowstone UI (admin panel)
- Set up authorization gates and middleware

### 4. Publish Views (Optional)

If you plan to use Flowstone's views or customize them:

```bash
php artisan vendor:publish --tag="flowstone-views"
```

### 5. Publish UI Assets (Optional)

If you're using the Flowstone UI (admin panel), publish the compiled JavaScript and CSS assets:

```bash
php artisan vendor:publish --tag="flowstone-ui-assets"
```

Or use the convenience command:

```bash
php artisan flowstone:publish-assets
```

This publishes the React-based workflow designer assets to `public/vendor/flowstone/`.

## Verification

Verify the installation was successful:

### 1. Check Artisan Commands

```bash
php artisan list flowstone
```

You should see:

- `flowstone:install` - Installation command
- `flowstone:create-workflow` - Create new workflow command
- `flowstone:publish-assets` - Publish UI assets command

### 2. Test Basic Functionality

Create a simple test in `tinker`:

```bash
php artisan tinker
```

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

// Create a test workflow
$workflow = Workflow::create([
    'name' => 'Test Workflow',
    'type' => 'state_machine',
    'initial_marking' => 'draft'
]);

echo "Workflow created: " . $workflow->name;
```

### 3. Access the Flowstone UI (Optional)

If you enabled the UI, access it at:

```
http://your-app.test/flowstone
```

By default, the UI is only accessible in the `local` environment. To configure authorization, see the [Configuration Guide](../02-configuration/01-configuration.md#ui-configuration).

## Flowstone UI Configuration

The Flowstone UI provides a visual interface for managing workflows, similar to Laravel Telescope.

### Authorization

Configure who can access the UI by defining a gate in your `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewFlowstone', function ($user) {
    return in_array($user->email, [
        'admin@example.com',
    ]);
});
```

### Environment Variables

Control UI behavior through environment variables:

```env
FLOWSTONE_UI_ENABLED=true
FLOWSTONE_UI_PATH=flowstone
FLOWSTONE_UI_DOMAIN=null
FLOWSTONE_UI_GATE=viewFlowstone
FLOWSTONE_UI_ASSET_URL=/vendor/flowstone
FLOWSTONE_INLINE_ASSETS=true
```

### Middleware Configuration

Customize middleware for UI routes in `config/flowstone.php`:

```php
'ui' => [
    'middleware' => [
        'web',
        'auth', // Add authentication
        'verified', // Add email verification
    ],
],
```

## Common Installation Issues
```

## Service Provider Registration

Flowstone uses Laravel's auto-discovery feature. The service provider will be automatically registered. If you're using Laravel 5.4 or older, manually add:

```php
// config/app.php
'providers' => [
    // ...
    CleaniqueCoders\Flowstone\FlowstoneServiceProvider::class,
],

'aliases' => [
    // ...
    'Flowstone' => CleaniqueCoders\Flowstone\Facades\Flowstone::class,
],
```

## Database Configuration

### MySQL/MariaDB

No additional configuration required. The migrations work out of the box.

### PostgreSQL

Ensure JSON column support is available (PostgreSQL 9.2+).

### SQLite

For development environments using SQLite, ensure your SQLite version supports JSON operations (SQLite 3.38.0+).

### SQL Server

JSON columns are supported in SQL Server 2016+. For older versions, the JSON columns will be stored as NVARCHAR(MAX).

## Caching Configuration

Flowstone uses Laravel's caching system for workflow configurations. Ensure you have a cache driver configured:

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'file'),
```

For production, consider using Redis or Memcached for better performance.

## Environment Variables

Add these optional environment variables to your `.env` file:

```env
# Cache duration for workflows (in minutes, default: 60)
FLOWSTONE_CACHE_DURATION=60

# Enable workflow debug mode (default: false)
FLOWSTONE_DEBUG=false

# Default workflow type (default: state_machine)
FLOWSTONE_DEFAULT_TYPE=state_machine
```

## Troubleshooting

### Permission Issues

If you encounter permission issues with the migrations:

```bash
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
```

### Cache Issues

Clear all caches after installation:

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Composer Memory Limit

If Composer runs out of memory during installation:

```bash
COMPOSER_MEMORY_LIMIT=-1 composer require cleaniquecoders/flowstone
```

## Next Steps

Now that Flowstone is installed:

1. **Read the [Quick Start](02-quick-start.md) guide** to create your first workflow
2. **Explore the [Configuration](../02-configuration/01-configuration.md)** options
3. **Check [Examples](examples.md)** for common patterns
4. **Review [Workflows](../03-usage/01-workflows.md)** for detailed usage

## Alternative Installation Methods

### Development Installation

For package development or contributing:

```bash
git clone https://github.com/cleaniquecoders/flowstone.git
cd flowstone
composer install
./vendor/bin/pest # Run tests
```

### Docker Installation

If using Laravel Sail:

```bash
./vendor/bin/sail composer require cleaniquecoders/flowstone
./vendor/bin/sail artisan migrate
```

---

**Installation complete!** 🎉 You're now ready to add powerful workflow capabilities to your Laravel application.
