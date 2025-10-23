# Laravel Workflow - Database-Driven Implementation

## Overview

We have successfully implemented a database-driven workflow system that stores workflow configurations in normalized database tables instead of JSON configuration files. This provides much greater flexibility and enables dynamic workflow management through user interfaces.

## Database Schema

### Main Tables

1. **workflows** - Stores workflow configurations
   - `id`, `uuid`, `name`, `description`
   - `type` (enum: 'state_machine', 'workflow')
   - `initial_marking` - Starting state
   - `is_enabled` - Enable/disable workflows
   - `meta` (JSON) - Additional metadata
   - Timestamps and soft deletes

2. **workflow_places** - Stores workflow states/places
   - `id`, `uuid`, `workflow_id`
   - `name` - State identifier (e.g., 'draft', 'pending')
   - `sort_order` - Display ordering
   - `metadata` (JSON) - Symfony metadata

3. **workflow_transitions** - Stores state transitions
   - `id`, `uuid`, `workflow_id`
   - `name` - Transition identifier (e.g., 'submit', 'approve')
   - `from_place`, `to_place` - State change definition
   - `sort_order` - Display ordering
   - `metadata` (JSON) - Symfony metadata (roles, guards, etc.)

## Models

### CleaniqueCoders\Flowstone\Models\Workflow
Main workflow configuration model with relationships to places and transitions.

Key methods:
- `places()` - HasMany relationship to WorkflowPlace
- `transitions()` - HasMany relationship to WorkflowTransition
- `getSymfonyConfig()` - Generates Symfony Workflow configuration array

### CleaniqueCoders\Flowstone\Models\WorkflowPlace
Represents workflow states/places with metadata support.

### CleaniqueCoders\Flowstone\Models\WorkflowTransition
Represents transitions between states with metadata for roles, guards, etc.

## Updated Helper Functions

### get_workflow_config(string $name, string $field = 'name')
Retrieves workflow configuration from database with caching. Now works with the normalized database structure.

### create_workflow(array $configuration, ?Registry $registry = null)
Creates Symfony Workflow instances from database-generated configurations.

## Factories

All models include factories for testing:
- `WorkflowFactory` - Creates workflow configurations
- `WorkflowPlaceFactory` - Creates workflow places
- `WorkflowTransitionFactory` - Creates workflow transitions

The `WorkflowFactory` includes a `withPlacesAndTransitions()` method that creates a complete workflow with default states and transitions.

## Commands

### flowstone:create-workflow
New command to create workflow configurations from the command line:

```bash
php artisan flowstone:create-workflow "Article Workflow" --type=state_machine --initial=draft --description="Workflow for article publishing"
```

Options:
- `--type` - state_machine or workflow (default: state_machine)
- `--initial` - Initial marking (default: draft)
- `--description` - Workflow description
- Interactive option to add default places and transitions

## Migration

The migration creates all three tables with proper foreign key constraints and indexes for performance.

## Testing

Comprehensive test suite covering:
- Model relationships and functionality
- Database-driven workflow generation
- Symfony Workflow integration
- Helper functions
- Factory usage

## Symfony Integration

The system maintains full compatibility with Symfony Workflow by:
- Generating proper Symfony configuration arrays from database data
- Supporting all Symfony metadata features
- Working with existing Symfony Workflow Registry
- Maintaining the same API for workflow operations

## Benefits

1. **Dynamic Configuration** - Workflows can be created/modified without code changes
2. **UI Management** - Easy to build admin interfaces for workflow management
3. **Database Queries** - Can query/filter places and transitions independently
4. **Relationships** - Proper Laravel model relationships and foreign key constraints
5. **Performance** - Proper indexing and caching support
6. **Validation** - Database-level constraints ensure data integrity

## Usage Examples

### Creating Workflow Programmatically

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

$workflow = Workflow::factory()->withPlacesAndTransitions()->create([
    'name' => 'Article Approval',
    'description' => 'Workflow for article approval process',
    'type' => 'state_machine',
    'initial_marking' => 'draft',
]);

// Generate Symfony config
$config = $workflow->getSymfonyConfig();

// Create Symfony workflow instance
$symfonyWorkflow = create_workflow($config);
```

### Using with Helper Functions

```php
// Get workflow config from database
$config = get_workflow_config('Article Approval');

// Create workflow instance
$workflow = create_workflow($config);
```

This implementation provides a solid foundation for building sophisticated workflow management systems while maintaining compatibility with the existing Symfony Workflow ecosystem.
